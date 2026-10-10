<?php

namespace App\Support;

use App\Models\WebFooter;
use App\Models\WebHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RossetyCatalog
{
    public static function assetBase(): string
    {
        return rtrim(asset('rossety/assets'), '/');
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        $fallback = self::fallbackFromJs();
        $negocio = $fallback['negocio'] ?? [];
        $header = null;
        if (Schema::hasTable('web_header')) {
            $header = WebHeader::query()->where('Activo', 'S')->orderBy('id_header')->first();
        }
        if ($header) {
            $tel = trim((string) ($header->telefono_numero ?? ''));
            if ($tel !== '') {
                $negocio['telefono'] = $tel;
            }
            $wa = preg_replace('/\D+/', '', (string) ($header->topbar_wa_numero ?? ''));
            if ($wa !== '') {
                $negocio['whatsapp'] = str_starts_with($wa, '51') ? $wa : '51'.$wa;
            }
            $ciudad = trim((string) ($header->top_ubicacion ?? ''));
            if ($ciudad !== '') {
                $negocio['direccion'] = $ciudad;
            }
        }

        $categorias = self::categorias($fallback['categorias'] ?? []);
        $productos = self::productos($fallback['productos'] ?? []);

        $payload = [
            'negocio' => $negocio,
            'categorias' => $categorias,
            'productos' => $productos,
            'slides' => self::slides(),
            'heroCards' => self::heroCards(),
            'pasos' => self::pasos(),
            'cuentas' => self::cuentas($negocio['pagos'] ?? []),
            'motivos' => self::motivos(),
            'opiniones' => self::opiniones(),
        ];

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewShared(): array
    {
        $header = null;
        if (Schema::hasTable('web_header')) {
            $header = WebHeader::query()->where('Activo', 'S')->orderBy('id_header')->first();
        }

        $logoUrl = self::logoUrl($header);

        $tel = trim((string) ($header->telefono_numero ?? ''));
        if ($tel === '' || str_contains($tel, '939')) {
            $tel = '989 914 154';
        }
        $ciudad = trim((string) ($header->top_ubicacion ?? ''));
        if ($ciudad === '') {
            $ciudad = 'Relave, Ayacucho';
        }
        $direccion = str_contains($ciudad, 'Perú') ? $ciudad : $ciudad.', Perú';

        return [
            'rsLogo' => $logoUrl,
            'rsTelefono' => $tel,
            'rsCiudad' => $ciudad,
            'rsDireccion' => $direccion,
            'rsCatalog' => self::payload(),
            'rsHeader' => self::headerWeb($header),
        ];
    }

    /** Logo del header: mismo criterio en la web y en el admin. */
    public static function logoUrl(?WebHeader $header): string
    {
        $logo = trim((string) ($header->url_logo ?? ''));
        $logoEsAjena = $logo === ''
            || str_contains($logo, 'temp02')
            || str_contains($logo, 'impacto')
            || str_contains($logo, 'valento');
        if ($logoEsAjena) {
            return asset('rossety/assets/images/inicio/logo-principal.svg');
        }
        if (str_starts_with($logo, 'http')) {
            return $logo;
        }

        return asset(ltrim($logo, '/'));
    }

    /**
     * Textos editables del header (admin ▸ Inicio & Header) con el respaldo que mostraba la web.
     *
     * @return array<string, mixed>
     */
    public static function headerWeb(?WebHeader $header): array
    {
        $txt = static fn ($v, string $def) => trim((string) $v) !== '' ? trim((string) $v) : $def;

        $nav = is_array($header->nav_items ?? null) ? $header->nav_items : [];
        $nav = array_values(array_filter($nav, fn ($i) => is_array($i) && trim((string) ($i['label'] ?? '')) !== ''));
        if (! $nav) {
            $nav = [
                ['label' => 'Inicio', 'href' => '/'],
                ['label' => 'Tienda', 'href' => '/tienda'],
                ['label' => 'Nosotros', 'href' => '/nosotros'],
                ['label' => 'Contacto', 'href' => '/contacto'],
            ];
        }

        $footer = Schema::hasTable('web_footer') ? WebFooter::where('id_footer', 1)->first() : null;

        return [
            'mensaje' => $txt($header->topbar_mensaje_centro ?? '', 'Detalles únicos para cada ocasión · Pide por WhatsApp'),
            'promoTexto' => $txt($header->nav_promo_texto ?? '', 'Temporada girasoles'),
            'promoUrl' => $txt($header->nav_promo_url ?? '', '/tienda?cat=girasoles'),
            'buscador' => $txt($header->buscador_placeholder ?? '', '¿Qué estás buscando?'),
            'nav' => $nav,
            'redes' => LiamPageData::redesSociales($footer) ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function fallbackFromJs(): array
    {
        $path = public_path('rossety/assets/js/rossety/data/catalog.js');
        if (! is_file($path)) {
            return ['negocio' => [], 'categorias' => [], 'productos' => []];
        }
        $raw = (string) file_get_contents($path);
        $raw = preg_replace('/^\s*\/\*.*?\*\/\s*/s', '', $raw);
        $raw = preg_replace('/^\s*window\.ROSSETY\s*=\s*/', '', $raw);
        $raw = preg_replace('/;\s*$/', '', trim($raw));
        $data = json_decode($raw, true);

        return is_array($data) ? $data : ['negocio' => [], 'categorias' => [], 'productos' => []];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fallback
     * @return array<int, array<string, mixed>>
     */
    private static function categorias(array $fallback): array
    {
        if (! Schema::hasTable('administracion_producto_categoria')) {
            return $fallback;
        }
        $rows = DB::table('administracion_producto_categoria')
            ->where('Activo', 'S')
            ->whereIn('slug', ['ramos', 'peluches', 'cajas', 'accesorios', 'hotwheels', 'complementos', 'girasoles'])
            ->get()
            ->keyBy('slug');
        if ($rows->isEmpty()) {
            return $fallback;
        }
        $out = [];
        foreach ($fallback as $cat) {
            $slug = (string) ($cat['slug'] ?? '');
            $row = $rows->get($slug);
            if ($row) {
                $cat['name'] = $row->nombre ?: $cat['name'];
            }
            $out[] = $cat;
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fallback
     * @return array<int, array<string, mixed>>
     */
    private static function productos(array $fallback): array
    {
        if (! Schema::hasTable('administracion_producto')) {
            return self::prefixImages($fallback);
        }
        $rows = DB::table('administracion_producto as p')
            ->leftJoin('administracion_producto_categoria as c', 'c.id_producto_categoria', '=', 'p.id_producto_categoria')
            ->where('p.Activo', 'S')
            ->where('p.codigo_producto', 'like', 'PRS-%')
            ->orderBy('p.id_producto')
            ->get(['p.*', 'c.slug as cat_slug', 'c.nombre as cat_nombre']);
        if ($rows->isEmpty()) {
            return self::prefixImages($fallback);
        }

        $destacados = [];
        if (Schema::hasTable('web_producto_destacado') && Schema::hasColumn('web_producto_destacado', 'id_producto')) {
            $destacados = DB::table('web_producto_destacado')
                ->where('Activo', 'S')
                ->pluck('id_producto')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $base = self::assetBase();
        $out = [];
        foreach ($rows as $row) {
            $img = trim((string) ($row->url_imagen ?? ''));
            if ($img !== '' && ! str_starts_with($img, 'http')) {
                $img = asset(ltrim($img, '/'));
            }
            $incluye = array_values(array_filter(preg_split('/\r\n|\r|\n/', (string) $row->descripcion) ?: []));
            $tag = trim((string) ($row->etiqueta_badge ?? ''));
            if ($destacados !== [] && in_array((int) $row->id_producto, $destacados, true)) {
                $tag = 'destacado';
            }
            $precio = $row->precio === null ? null : (float) $row->precio;
            $out[] = [
                'id' => (string) $row->slug,
                'dbId' => (int) $row->id_producto,
                'codigo' => (string) $row->codigo_producto,
                'nombre' => (string) $row->nombre,
                'cat' => (string) ($row->cat_slug ?: ''),
                'sub' => (string) ($row->ubicacion ?: ''),
                'precio' => $precio,
                'imagen' => $img !== '' ? $img : $base.'/images/catalogo/placeholder.svg',
                'modelo' => (string) ($row->codigo_producto_new ?: ''),
                'incluye' => $incluye,
                'tag' => $tag !== '' ? $tag : null,
                'precioNota' => ($row->cat_slug === 'cajas' && $precio !== null) ? 'Por cantidad' : null,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    private static function prefixImages(array $products): array
    {
        $base = self::assetBase();
        foreach ($products as &$p) {
            $img = (string) ($p['imagen'] ?? '');
            if ($img !== '' && ! str_starts_with($img, 'http') && ! str_starts_with($img, '/')) {
                $p['imagen'] = $base.'/'.ltrim($img, '/');
            }
        }

        return $products;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function slides(): array
    {
        if (! Schema::hasTable('web_hero_slide')) {
            return [];
        }
        return DB::table('web_hero_slide')->where('Activo', 'S')->orderBy('orden')->get()->map(fn ($r) => [
            'eyebrow' => $r->eyebrow,
            'titulo' => $r->titulo,
            'descripcion' => $r->descripcion,
            'cta' => $r->cta_texto,
            'url' => $r->cta_url,
            'imagen' => self::publicUrl((string) $r->url_imagen),
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function heroCards(): array
    {
        if (! Schema::hasTable('web_hero_card')) {
            return [];
        }
        return DB::table('web_hero_card')->where('Activo', 'S')->orderBy('orden')->get()->map(fn ($r) => [
            'badge' => $r->badge,
            'titulo' => $r->titulo,
            'precio' => $r->precio_texto,
            'url' => $r->url,
            'imagen' => self::publicUrl((string) $r->url_imagen),
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function pasos(): array
    {
        if (! Schema::hasTable('web_paso_pedido')) {
            return [];
        }
        return DB::table('web_paso_pedido')->where('Activo', 'S')->orderBy('orden')->get()->map(fn ($r) => [
            'titulo' => $r->titulo,
            'texto' => $r->texto,
        ])->all();
    }

    /**
     * @param  array<int, mixed>  $fallback
     * @return array<int, array<string, mixed>>
     */
    private static function cuentas(array $fallback): array
    {
        if (! Schema::hasTable('web_cuenta_pago')) {
            return $fallback;
        }
        $rows = DB::table('web_cuenta_pago')->where('Activo', 'S')->orderBy('orden')->get();
        if ($rows->isEmpty()) {
            return $fallback;
        }
        return $rows->map(fn ($r) => [
            'banco' => $r->banco,
            'tipo' => $r->tipo,
            'numero' => $r->numero,
            'titular' => $r->titular,
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function motivos(): array
    {
        if (! Schema::hasTable('web_motivo_rossety')) {
            return [];
        }
        return DB::table('web_motivo_rossety')->where('Activo', 'S')->orderBy('orden')->get()->map(fn ($r) => [
            'icono' => $r->icono,
            'titulo' => $r->titulo,
            'texto' => $r->texto,
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function opiniones(): array
    {
        if (! Schema::hasTable('web_testimonios') || ! Schema::hasColumn('web_testimonios', 'badge_seccion')) {
            return [];
        }
        return DB::table('web_testimonios')
            ->where('Activo', 'S')
            ->where('badge_seccion', 'rossety')
            ->orderBy('orden')
            ->get()
            ->map(fn ($r) => [
                'nombre' => $r->nombre,
                'ciudad' => $r->subtitulo,
                'texto' => $r->testimonio,
                'estrellas' => (int) round((float) $r->calificacion),
                'avatar' => self::publicUrl((string) ($r->url_avatar ?? '')),
            ])->all();
    }

    private static function publicUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
