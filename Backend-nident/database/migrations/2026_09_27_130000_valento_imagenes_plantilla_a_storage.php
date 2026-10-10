<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Imágenes de la plantilla Valento-Original: las semillas anteriores guardaban
 * URLs de Unsplash o rutas temp02/img/... Aquí se descargan/copian a
 * public/storage_/<carpeta del módulo> y la BD pasa a apuntar a esa ruta local.
 *
 * Cada fila recibe su propio archivo: al reemplazar una imagen desde el admin
 * el controlador borra la anterior, y no debe afectar a otra fila.
 *
 * Idempotente: si el archivo ya existe no se vuelve a descargar. Si la descarga
 * falla, la fila conserva su URL original.
 */
return new class extends Migration
{
    // tabla, clave, columna, carpeta en storage_ (la misma que usa el controlador del admin)
    private const DESTINOS = [
        ['web_slider',                        'id_slider',             'url_imagen',         'slider'],
        ['administracion_producto_categoria', 'id_producto_categoria', 'url_imagen',         'categorias'],
        ['administracion_producto',           'id_producto',           'url_imagen',         'producto_principal'],
        ['administracion_producto_fotos',     'id_producto_foto',      'url_imagen',         'producto_principal_fotos'],
        ['web_promo_bloque',                  'id_promo_bloque',       'imagen_hero',        'promo_bloque'],
        ['web_promo_item',                    'id_promo_item',         'imagen',             'promo_item'],
        ['web_lookbook',                      'id',                    'url_imagen',         'lookbook'],
        ['web_lookbook',                      'id',                    'url_imagen_lateral', 'lookbook'],
        ['web_footer_banner',                 'id',                    'url_imagen',         'footer_banner'],
        ['web_pagina_nosotros',               'id',                    'url_imagen_hero',    'nosotros_hero'],
        ['web_pagina_nosotros',               'id',                    'url_imagen_seccion', 'nosotros_seccion'],
        ['web_pagina_nosotros',               'id',                    'banner_url_imagen',  'pagina_banner'],
        ['web_pagina_contacto',               'id',                    'banner_url_imagen',  'pagina_banner'],
    ];

    /** Ancho de descarga según el uso (las semillas traen w=600..1800). */
    private const ANCHO = [
        'slider'        => 1920,
        'footer_banner' => 1920,
        'nosotros_hero' => 1920,
        'pagina_banner' => 1920,
        'promo_bloque'  => 1400,
        'lookbook'      => 1200,
    ];

    /** Hero de la plantilla: respaldo del slider cuando no hay slides activos (hero_slider.blade.php). */
    private const HERO = [
        'https://images.unsplash.com/photo-1523381210434-271e8be1f52b',
        'https://images.unsplash.com/photo-1562157873-818bc0726f68',
        'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f',
    ];

    private string $publico;

    /** @var array<string, string> origen|carpeta => archivo ya descargado */
    private array $cache = [];

    public function up(): void
    {
        $this->publico = rtrim(env('APP_PUBLIC_PATH', public_path()), '/\\');

        foreach (self::HERO as $url) {
            $this->asegurarArchivo($url, 'storage_/slider/' . $this->nombreArchivo($url), 'slider');
        }

        foreach (self::DESTINOS as [$tabla, $clave, $columna, $carpeta]) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
                continue;
            }

            $filas = DB::table($tabla)
                ->where(fn ($q) => $q->where($columna, 'like', '%images.unsplash.com%')
                    ->orWhere($columna, 'like', 'temp02/img/%'))
                ->get([$clave, $columna]);

            foreach ($filas as $fila) {
                $origen = $fila->{$columna};
                $relativa = 'storage_/' . $carpeta . '/' . $this->nombreArchivo($origen, $fila->{$clave});

                if ($this->asegurarArchivo($origen, $relativa, $carpeta)) {
                    DB::table($tabla)->where($clave, $fila->{$clave})->update([$columna => $relativa]);
                }
            }
        }
    }

    public function down(): void
    {
        // Las imágenes quedan en storage_ y se gestionan desde el admin.
    }

    private function nombreArchivo(string $origen, int|string|null $id = null): string
    {
        $base = preg_match('#/photo-([0-9a-f\-]+)#i', $origen, $m)
            ? $m[1] . '.jpg'
            : basename(parse_url($origen, PHP_URL_PATH));

        return 'valento-' . ($id !== null ? $id . '-' : '') . $base;
    }

    private function asegurarArchivo(string $origen, string $relativa, string $carpeta): bool
    {
        $destino = $this->publico . '/' . $relativa;
        if (is_file($destino) && filesize($destino) > 0) {
            return true;
        }

        if (! is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }

        // La misma imagen ya se bajó para otra fila de esta carpeta: se copia
        $previo = $this->cache[$origen . '|' . $carpeta] ?? null;
        if ($previo && copy($previo, $destino)) {
            return true;
        }

        $ok = str_starts_with($origen, 'http')
            ? $this->descargar($origen, $destino, $carpeta)
            : $this->copiarLocal($origen, $destino);

        if ($ok) {
            $this->cache[$origen . '|' . $carpeta] = $destino;
        }

        return $ok;
    }

    /** Ruta local de la plantilla (temp02/img/...). */
    private function copiarLocal(string $origen, string $destino): bool
    {
        $local = $this->publico . '/' . ltrim($origen, '/');

        return is_file($local) && copy($local, $destino);
    }

    /** Unsplash: se normaliza el ancho y la calidad. */
    private function descargar(string $origen, string $destino, string $carpeta): bool
    {
        $ancho = self::ANCHO[$carpeta] ?? 800;
        $url = strtok($origen, '?') . '?auto=format&fit=crop&fm=jpg&q=80&w=' . $ancho
            . (str_contains($origen, 'h=') ? '&h=' . $ancho : '');

        try {
            $respuesta = Http::timeout(60)->get($url);
            if ($respuesta->successful() && strlen($respuesta->body()) > 1000) {
                return file_put_contents($destino, $respuesta->body()) !== false;
            }
            Log::warning('Imagen de plantilla no descargada', ['url' => $url, 'status' => $respuesta->status()]);
        } catch (\Throwable $e) {
            Log::warning('Imagen de plantilla no descargada', ['url' => $url, 'error' => $e->getMessage()]);
        }

        return false;
    }
};
