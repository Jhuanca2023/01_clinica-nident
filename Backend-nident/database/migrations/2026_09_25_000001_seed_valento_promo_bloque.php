<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sección "Ofertas & Promociones" del home con el contenido de la plantilla
 * public/temp02/index.html (.promo-section). Un solo bloque, como la plantilla.
 */
return new class extends Migration
{
    private const DISCLAIMER = 'Válido en nuestra tienda online. Hasta agotar stock. Mínimo 1 unidad por producto. Imágenes referenciales.';

    public function up(): void
    {
        if (! Schema::hasTable('web_promo_bloque') || ! Schema::hasTable('web_promo_item')) {
            return;
        }

        $bloque = DB::table('web_promo_bloque')->where('Activo', 'S')->orderBy('orden')->first()
            ?? DB::table('web_promo_bloque')->orderBy('orden')->first();

        $datosBloque = [
            'titulo_seccion'    => 'Ofertas Destacadas',
            'subtitulo_seccion' => 'Ofertas & Promociones',
            'hero_etiqueta'     => 'Semplicemente Valento',
            'hero_titulo'       => "Viste a\n*tu*\nequipo",
            'hero_subtitulo'    => 'Laboral · Promocional · Deportivo',
            'imagen_hero'       => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&w=1400&q=80',
            'orden'             => 1,
            'Activo'            => 'S',
        ];

        if ($bloque) {
            $idBloque = $bloque->id_promo_bloque;
            DB::table('web_promo_bloque')->where('id_promo_bloque', $idBloque)->update($datosBloque);
        } else {
            $idBloque = DB::table('web_promo_bloque')->insertGetId($datosBloque + ['slug' => 'ofertas-promociones']);
        }

        // La plantilla tiene un solo bloque: desactivar los demás (no se borran)
        DB::table('web_promo_bloque')->where('id_promo_bloque', '!=', $idBloque)->update(['Activo' => 'N']);

        $items = [
            ['Laboral',     "Polo\nPiqué",        'Hasta', 45.00,  'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=700&q=80'],
            ['Promocional', "Logo\nEmpresa",      '2x',    99.90,  'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=700&q=80'],
            ['Deportivo',   "Dry\nTeam",          '2x',    89.90,  'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=700&q=80'],
            ['Protección',  "Alta\nVisibilidad",  '3x',    129.90, 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?auto=format&fit=crop&w=700&q=80'],
        ];

        $existentes = DB::table('web_promo_item')
            ->where('id_promo_bloque', $idBloque)
            ->orderBy('orden')
            ->pluck('id_promo_item')
            ->values();

        foreach ($items as $i => [$categoria, $nombre, $oferta, $precio, $imagen]) {
            $datos = [
                'id_promo_bloque'        => $idBloque,
                'categoria'              => $categoria,
                'nombre'                 => $nombre,
                'tipo_oferta'            => 'pack',
                'multiplicador'          => $oferta,
                'precio'                 => $precio,
                'descuento_porcentaje'   => null,
                'mostrar_precio_portada' => 'N',
                'imagen'                 => $imagen,
                'descripcion'            => self::DISCLAIMER,
                'orden'                  => $i + 1,
                'Activo'                 => 'S',
            ];

            if (isset($existentes[$i])) {
                DB::table('web_promo_item')->where('id_promo_item', $existentes[$i])->update($datos);
            } else {
                DB::table('web_promo_item')->insert($datos);
            }
        }

        // Ítems sobrantes del bloque: desactivar
        if ($existentes->count() > count($items)) {
            DB::table('web_promo_item')
                ->whereIn('id_promo_item', $existentes->slice(count($items))->all())
                ->update(['Activo' => 'N']);
        }
    }

    public function down(): void
    {
        // Seed de contenido: no se revierte.
    }
};
