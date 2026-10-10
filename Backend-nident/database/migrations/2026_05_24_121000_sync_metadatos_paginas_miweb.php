<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sincroniza metadatos_paginas con rutas públicas (LucdeSoft / miweb).
 * Corrige nombre_pagina NULL en registros legacy e inserta páginas CMS nuevas.
 */
return new class extends Migration
{
    private function mapLegacyIds(): array
    {
        return [
            1  => 'web_gallery',
            2  => 'web_contact',
            3  => 'home',
            4  => 'web_servicio_cliente',
            5  => 'web_ubicacion',
            6  => 'web_politica_privacidad',
            7  => 'web_delivery_informacion',
            8  => 'web_about',
            9  => 'web_devoluciones',
            10 => 'web_garantia',
            11 => 'web_libro_reclamos',
            12 => 'web_shopDetail_lista',
            13 => 'web_terminos_condiciones',
        ];
    }

    public function up(): void
    {
        if (! Schema::hasTable('metadatos_paginas')) {
            return;
        }

        $now = now();

        foreach ($this->mapLegacyIds() as $id => $nombre) {
            DB::table('metadatos_paginas')
                ->where('id', $id)
                ->update([
                    'nombre_pagina' => $nombre,
                    'updated_at'    => $now,
                ]);
        }

        $nuevos = [
            [
                'nombre_pagina'      => 'web_productos',
                'titulo_pagina'      => 'Servicios y Productos Digitales | LucdeSoft',
                'descripcion_pagina' => 'Conoce nuestros servicios de desarrollo web, software a medida, apps móviles, e-commerce y soluciones digitales para empresas en Perú.',
            ],
            [
                'nombre_pagina'      => 'web_videos',
                'titulo_pagina'      => 'Videos y Demos | LucdeSoft',
                'descripcion_pagina' => 'Videos de proyectos, demos de software y soluciones digitales desarrolladas por LucdeSoft.',
            ],
            [
                'nombre_pagina'      => 'web_maquinarias',
                'titulo_pagina'      => 'Soluciones Tecnológicas | LucdeSoft',
                'descripcion_pagina' => 'Herramientas, plataformas y soluciones tecnológicas que impulsan la transformación digital de tu negocio.',
            ],
            [
                'nombre_pagina'      => 'blogs',
                'titulo_pagina'      => 'Publicaciones y Blog | LucdeSoft',
                'descripcion_pagina' => 'Artículos, novedades y consejos sobre desarrollo web, software y tecnología de LucdeSoft.',
            ],
        ];

        foreach ($nuevos as $row) {
            $exists = DB::table('metadatos_paginas')
                ->where('nombre_pagina', $row['nombre_pagina'])
                ->exists();

            if ($exists) {
                DB::table('metadatos_paginas')
                    ->where('nombre_pagina', $row['nombre_pagina'])
                    ->update(['activo' => 'S', 'updated_at' => $now]);
                continue;
            }

            DB::table('metadatos_paginas')->insert(array_merge($row, [
                'activo'     => 'S',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $activar = array_merge(array_values($this->mapLegacyIds()), [
            'web_productos', 'web_videos', 'web_maquinarias', 'blogs',
        ]);

        DB::table('metadatos_paginas')
            ->whereIn('nombre_pagina', $activar)
            ->update(['activo' => 'S', 'updated_at' => $now]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('metadatos_paginas')) {
            return;
        }

        $now = now();

        DB::table('metadatos_paginas')
            ->whereIn('nombre_pagina', ['web_productos', 'web_videos', 'web_maquinarias', 'blogs'])
            ->delete();

        foreach ($this->mapLegacyIds() as $id => $nombre) {
            DB::table('metadatos_paginas')
                ->where('id', $id)
                ->update(['nombre_pagina' => null, 'updated_at' => $now]);
        }
    }
};
