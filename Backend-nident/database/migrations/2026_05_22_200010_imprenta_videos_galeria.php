<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_pagina_videos')) {
            Schema::create('web_pagina_videos', function (Blueprint $table) {
                $table->id('id');
                $table->string('banner_titulo', 255)->nullable();
                $table->string('banner_url_imagen', 500)->nullable();
                $table->string('seccion_titulo', 500)->nullable();
                $table->text('seccion_descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_videos')) {
            Schema::create('web_videos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tag', 80)->nullable();
                $table->string('titulo', 255)->nullable();
                $table->text('descripcion')->nullable();
                $table->enum('tipo_video', ['archivo', 'youtube'])->default('archivo');
                $table->string('url_video', 500)->nullable();
                $table->string('url_youtube', 500)->nullable();
                $table->string('url_imagen_portada', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        $img = 'temp02/assets/img/jh_importaciones/inicio';

        $seccionTitulo = 'Creando Espirales';
        $seccionDesc = 'Descubre cómo nuestras espirales de alta calidad pueden transformar tus proyectos.';

        if (Schema::hasTable('web_video_seccion')) {
            $old = DB::table('web_video_seccion')->where('id', 1)->first();
            if ($old) {
                $seccionTitulo = $old->seccion_titulo ?: $seccionTitulo;
                $seccionDesc = $old->seccion_descripcion ?: $seccionDesc;
            }
        }

        if (Schema::hasTable('web_pagina_videos') && DB::table('web_pagina_videos')->count() === 0) {
            DB::table('web_pagina_videos')->insert([
                'banner_titulo'       => 'Videos',
                'banner_url_imagen'   => $img . '/seccion%202.jpg',
                'seccion_titulo'      => $seccionTitulo,
                'seccion_descripcion' => $seccionDesc,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        if (Schema::hasTable('web_videos') && DB::table('web_videos')->count() === 0) {
            $rows = [];
            if (Schema::hasTable('web_video')) {
                foreach (DB::table('web_video')->orderBy('orden')->get() as $i => $v) {
                    $rows[] = [
                        'tag'                => $v->tag,
                        'titulo'             => $v->titulo,
                        'descripcion'        => $v->descripcion,
                        'tipo_video'         => 'archivo',
                        'url_video'          => $v->url_video,
                        'url_youtube'        => null,
                        'url_imagen_portada' => null,
                        'orden'              => $v->orden ?? ($i + 1),
                        'Activo'             => $v->Activo ?? 'S',
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
            }
            if (count($rows) === 0) {
                $now = now();
                $rows = [
                    [
                        'tag' => 'FABRICACIÓN', 'titulo' => 'Creando Espirales',
                        'descripcion' => 'Proceso de fabricación de espirales de alta calidad para tus proyectos de encuadernación.',
                        'tipo_video' => 'archivo', 'url_video' => $img . '/video_espirales.mp4',
                        'url_youtube' => null, 'url_imagen_portada' => null,
                        'orden' => 1, 'Activo' => 'S', 'created_at' => $now, 'updated_at' => $now,
                    ],
                    [
                        'tag' => 'PROCESO', 'titulo' => 'Fabricación de Espirales PVC',
                        'descripcion' => 'Conoce el proceso completo de fabricación de espirales PVC en Liam Vercceli.',
                        'tipo_video' => 'archivo', 'url_video' => $img . '/video_espirales_2.mp4',
                        'url_youtube' => null, 'url_imagen_portada' => null,
                        'orden' => 2, 'Activo' => 'S', 'created_at' => $now, 'updated_at' => $now,
                    ],
                ];
            }
            if (count($rows) > 0) {
                DB::table('web_videos')->insert($rows);
            }
        }

        if (Schema::hasTable('web_header')) {
            $nav = [
                ['label' => 'Inicio', 'href' => '/'],
                ['label' => 'Nosotros', 'href' => '/nosotros'],
                ['label' => 'Productos', 'href' => '/productos'],
                ['label' => 'Videos', 'href' => '/videos'],
                ['label' => 'Máquinarias', 'href' => '/maquinarias'],
                ['label' => 'Contáctanos', 'href' => '/contacto'],
            ];
            DB::table('web_header')->where('id_header', 1)->update([
                'nav_items'         => json_encode($nav, JSON_UNESCAPED_UNICODE),
                'side_menu_enlaces' => json_encode($nav, JSON_UNESCAPED_UNICODE),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_videos');
        Schema::dropIfExists('web_pagina_videos');
    }
};
