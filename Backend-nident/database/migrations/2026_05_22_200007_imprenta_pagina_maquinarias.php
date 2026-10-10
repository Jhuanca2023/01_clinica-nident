<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $img = 'temp02/assets/img/jh_importaciones/inicio';

        if (!Schema::hasTable('web_pagina_maquinarias')) {
            Schema::create('web_pagina_maquinarias', function (Blueprint $table) {
                $table->increments('id');
                $table->string('banner_titulo', 255)->nullable();
                $table->string('banner_url_imagen', 500)->nullable();
                $table->string('seccion_titulo', 500)->nullable();
                $table->text('seccion_descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_maquinarias_item')) {
            Schema::create('web_maquinarias_item', function (Blueprint $table) {
                $table->increments('id');
                $table->string('titulo', 255);
                $table->string('url_imagen_front', 500)->nullable();
                $table->string('url_imagen_back', 500)->nullable();
                $table->string('btn_texto', 120)->default('Me interesa');
                $table->string('btn_url', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (DB::table('web_pagina_maquinarias')->count() === 0) {
            DB::table('web_pagina_maquinarias')->insert([
                'id'                  => 1,
                'banner_titulo'       => 'Máquinarias',
                'banner_url_imagen'   => "{$img}/seccion%202.jpg",
                'seccion_titulo'      => 'Nuestras máquinarias',
                'seccion_descripcion' => 'En Liam Vercceli, ofrecemos una amplia gama de maquinarias especializadas en encuadernación y laminado. Descubre equipos de calidad para impulsar tu negocio.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        if (Schema::hasTable('web_maquinarias_item') && DB::table('web_maquinarias_item')->count() === 0) {
            $items = [
                ['titulo' => 'Selladoras de bolsas', 'url_imagen_front' => "{$img}/advisor_espiraladoras.jpg", 'orden' => 1],
                ['titulo' => 'Enmicadoras Al calor', 'url_imagen_front' => "{$img}/advisor_micas.jpg", 'url_imagen_back' => "{$img}/jh_importaciones_img_1.jpg", 'orden' => 2],
                ['titulo' => 'Cortadora de Foil Stamping', 'url_imagen_front' => "{$img}/advisor_espirales.jpg", 'url_imagen_back' => "{$img}/seccion%201.jpg", 'orden' => 3],
            ];
            foreach ($items as $it) {
                DB::table('web_maquinarias_item')->insert(array_merge($it, [
                    'btn_texto'  => 'Me interesa',
                    'btn_url'    => 'https://wa.me/51981629466',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_header')) {
            $row = DB::table('web_header')->where('id_header', 1)->first();
            if ($row && $row->nav_items) {
                $nav = json_decode($row->nav_items, true) ?: [];
                $changed = false;
                foreach ($nav as &$item) {
                    if (($item['label'] ?? '') === 'Máquinarias' && str_contains($item['href'] ?? '', '#maquinarias')) {
                        $item['href'] = '/maquinarias';
                        $changed = true;
                    }
                }
                if ($changed) {
                    DB::table('web_header')->where('id_header', 1)->update([
                        'nav_items'         => json_encode($nav, JSON_UNESCAPED_UNICODE),
                        'side_menu_enlaces' => json_encode($nav, JSON_UNESCAPED_UNICODE),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }

        if (Schema::hasTable('sistema_menu') && !DB::table('sistema_menu')->where('url', '/pagina-maquinarias/index')->exists()) {
            $maxOrden = (int) DB::table('sistema_menu')->max('orden');
            $data = [
                'nombre'     => 'Página Máquinarias',
                'url'        => '/pagina-maquinarias/index',
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $data['orden'] = $maxOrden + 1;
            }
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $data['Icon'] = 'precision_manufacturing';
            }
            DB::table('sistema_menu')->insert($data);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_maquinarias_item');
        Schema::dropIfExists('web_pagina_maquinarias');
    }
};
