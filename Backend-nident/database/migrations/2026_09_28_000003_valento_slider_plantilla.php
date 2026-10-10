<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Slider del home = 3 slides de la plantilla temp02 (mismo contenido que el respaldo
 * de hero_slider.blade.php), activos y con imágenes accesibles desde cualquier equipo.
 * Los slides 1–3 heredados (otros clientes, imágenes en storage_/ que no existen) se reutilizan;
 * el resto queda inactivo.
 */
return new class extends Migration
{
    private const SLIDES = [
        [
            'subtitulo'   => 'Semplicemente Valento',
            'titulo'      => 'Ropa laboral para tu empresa',
            'descripcion' => 'Uniformes, polos y prendas listas para personalizar, con stock permanente.',
            'texto_boton' => 'Ver catálogo',
            'url_link'    => '/tienda',
            'url_imagen'  => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=1600&q=75',
        ],
        [
            'subtitulo'   => 'Mayorista',
            'titulo'      => 'Textil promocional con tu logo',
            'descripcion' => 'Camisetas, polos y vestuario para equipos, eventos y colectividades.',
            'texto_boton' => 'Conocer Valento',
            'url_link'    => '/nosotros',
            'url_imagen'  => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=1600&q=75',
        ],
        [
            'subtitulo'   => 'Desde 1994',
            'titulo'      => 'Stock permanente, despacho ágil',
            'descripcion' => 'Abastecemos profesionales y empresas con la mejor relación calidad-precio.',
            'texto_boton' => 'Cotizar ahora',
            'url_link'    => '/contacto',
            'url_imagen'  => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=1600&q=75',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('web_slider')) {
            return;
        }

        DB::transaction(function () {
            $now = now();
            $ids = DB::table('web_slider')->orderBy('id_slider')->pluck('id_slider')->values();

            foreach (self::SLIDES as $i => $slide) {
                $datos = array_filter(
                    $slide + ['Activo' => 'S', 'updated_at' => $now],
                    fn ($col) => Schema::hasColumn('web_slider', $col),
                    ARRAY_FILTER_USE_KEY
                );
                // Limpia campos del cliente anterior que no usa la plantilla
                foreach (['slide_tag', 'url_link_2', 'texto_boton_2'] as $col) {
                    if (Schema::hasColumn('web_slider', $col)) {
                        $datos[$col] = null;
                    }
                }

                if (isset($ids[$i])) {
                    DB::table('web_slider')->where('id_slider', $ids[$i])->update($datos);
                } else {
                    DB::table('web_slider')->insert($datos + ['created_at' => $now]);
                }
            }

            // Slides sobrantes (contenido de otros clientes): inactivos
            if ($ids->count() > count(self::SLIDES)) {
                DB::table('web_slider')
                    ->whereIn('id_slider', $ids->slice(count(self::SLIDES))->all())
                    ->update(['Activo' => 'N', 'updated_at' => $now]);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
