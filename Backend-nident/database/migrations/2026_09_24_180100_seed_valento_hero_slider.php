<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Desactiva slides spa/Liam y siembra el hero Valento (texto exacto de temp02/index.html).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_slider')) {
            return;
        }

        $activoCol = Schema::hasColumn('web_slider', 'Activo')
            ? 'Activo'
            : (Schema::hasColumn('web_slider', 'activo') ? 'activo' : null);

        if ($activoCol) {
            DB::table('web_slider')->update([$activoCol => 'N']);
        }

        $slides = [
            [
                'subtitulo'    => 'Semplicemente Valento',
                'titulo'       => 'Ropa laboral para tu empresa',
                'descripcion'  => 'Uniformes, polos y prendas listas para personalizar, con stock permanente.',
                'texto_boton'  => 'Ver catálogo',
                'url_link'     => '/tienda',
                'url_imagen'   => null,
            ],
            [
                'subtitulo'    => 'Mayorista',
                'titulo'       => 'Textil promocional con tu logo',
                'descripcion'  => 'Camisetas, polos y vestuario para equipos, eventos y colectividades.',
                'texto_boton'  => 'Conocer Valento',
                'url_link'     => '/nosotros',
                'url_imagen'   => null,
            ],
            [
                'subtitulo'    => 'Desde 1994',
                'titulo'       => 'Stock permanente, despacho ágil',
                'descripcion'  => 'Abastecemos profesionales y empresas con la mejor relación calidad-precio.',
                'texto_boton'  => 'Cotizar ahora',
                'url_link'     => '/contacto',
                'url_imagen'   => null,
            ],
        ];

        $pk = Schema::hasColumn('web_slider', 'id_slider') ? 'id_slider' : 'id';
        $maxId = (int) DB::table('web_slider')->max($pk);

        foreach ($slides as $i => $slide) {
            $row = $slide;
            if ($activoCol) {
                $row[$activoCol] = 'S';
            }
            if (Schema::hasColumn('web_slider', 'orden')) {
                $row['orden'] = $i + 1;
            }
            if (Schema::hasColumn('web_slider', 'created_at')) {
                $row['created_at'] = now();
            }
            if (Schema::hasColumn('web_slider', 'updated_at')) {
                $row['updated_at'] = now();
            }
            // Evitar colisión de PK: insertar sin forzar id si autoincrement
            try {
                DB::table('web_slider')->insert($row);
            } catch (\Throwable $e) {
                $row[$pk] = $maxId + $i + 1;
                DB::table('web_slider')->insert($row);
            }
        }
    }

    public function down(): void
    {
        // Seed de contenido: no se revierte.
    }
};
