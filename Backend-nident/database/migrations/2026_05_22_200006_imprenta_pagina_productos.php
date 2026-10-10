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

        if (!Schema::hasTable('web_pagina_productos')) {
            Schema::create('web_pagina_productos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('banner_titulo', 255)->nullable();
                $table->string('banner_url_imagen', 500)->nullable();
                $table->string('intro_titulo', 255)->nullable();
                $table->text('intro_descripcion')->nullable();
                $table->string('intro_btn_texto', 120)->nullable();
                $table->string('intro_btn_url', 500)->nullable();
                $table->string('intro_url_imagen', 500)->nullable();
                $table->string('prioridad_etiqueta', 120)->nullable();
                $table->string('prioridad_titulo', 255)->nullable();
                $table->text('prioridad_descripcion')->nullable();
                $table->string('prioridad_btn_texto', 120)->nullable();
                $table->string('prioridad_btn_url', 500)->nullable();
                $table->string('prioridad_url_imagen', 500)->nullable();
                $table->string('frase_titulo', 255)->nullable();
                $table->text('frase_texto')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_productos_pagina_galeria_seccion')) {
            Schema::create('web_productos_pagina_galeria_seccion', function (Blueprint $table) {
                $table->increments('id');
                $table->string('seccion_titulo', 500)->nullable();
                $table->text('seccion_descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_productos_pagina_galeria')) {
            Schema::create('web_productos_pagina_galeria', function (Blueprint $table) {
                $table->increments('id');
                $table->string('titulo', 255);
                $table->text('descripcion')->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->string('btn_texto', 120)->default('Me interesa');
                $table->string('btn_url', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (DB::table('web_pagina_productos')->count() === 0) {
            DB::table('web_pagina_productos')->insert([
                'id'                   => 1,
                'banner_titulo'        => 'Productos',
                'banner_url_imagen'    => "{$img}/seccion%202.jpg",
                'intro_titulo'         => 'Nuestros Productos',
                'intro_descripcion'    => 'En Liam Vercceli, ofrecemos una variedad de servicios diseñados para satisfacer todas tus necesidades empresariales. Nuestro objetivo es ser tu socio confiable, proporcionando soluciones de alta calidad que impulsen el crecimiento de tu negocio.',
                'intro_btn_texto'      => 'MÁS INFORMACIÓN',
                'intro_btn_url'        => '/contacto',
                'intro_url_imagen'     => "{$img}/jh_importaciones_img_5.jpg",
                'prioridad_etiqueta'   => 'PRIORIDAD',
                'prioridad_titulo'     => 'Nuestros clientes son nuestra prioridad',
                'prioridad_descripcion'=> 'En nuestra empresa de encuadernación, entendemos que nuestros clientes son el corazón de nuestro negocio. Desde el primer contacto hasta la entrega final, nos comprometemos a brindar un servicio excepcional que supere sus expectativas.',
                'prioridad_btn_texto'  => 'CONTACTO',
                'prioridad_btn_url'    => '/contacto',
                'prioridad_url_imagen' => "{$img}/seccion%201.jpg",
                'frase_titulo'         => '¿Tienes algunas preguntas?',
                'frase_texto'          => 'Puedes contactarnos en cualquier momento 981629466',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        if (Schema::hasTable('web_productos_pagina_galeria_seccion') && DB::table('web_productos_pagina_galeria_seccion')->count() === 0) {
            DB::table('web_productos_pagina_galeria_seccion')->insert([
                'id'                  => 1,
                'seccion_titulo'      => 'Calidad Inigualable, Precios Imbatibles',
                'seccion_descripcion' => 'Desde suministros de oficina hasta encuadernación profesional, nuestras soluciones efectivas ayudan a tu negocio sin afectar tu presupuesto. ¡Descubre lo fácil que es conseguir excelencia a un precio accesible!',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        if (Schema::hasTable('web_productos_pagina_galeria') && DB::table('web_productos_pagina_galeria')->count() === 0) {
            $items = [
                ['titulo' => 'Set de 5 Piezas de Malla metálica', 'descripcion' => 'Organizadores de escritorio en malla metálica.', 'url_imagen' => "{$img}/jh_importaciones_img_1.jpg", 'orden' => 1],
                ['titulo' => 'Bandeja de 4 pisos de malla metálica', 'descripcion' => 'Bandeja multifuncional para oficina.', 'url_imagen' => "{$img}/advisor_micas.jpg", 'orden' => 2],
                ['titulo' => 'Papel Fotográfico A4 brillo 180GR.', 'descripcion' => 'Papel fotográfico de alta calidad.', 'url_imagen' => "{$img}/advisor_espirales.jpg", 'orden' => 3],
                ['titulo' => 'Engrapadora de 100 hojas.', 'descripcion' => 'Engrapadora profesional de alto rendimiento.', 'url_imagen' => "{$img}/advisor_espiraladoras.jpg", 'orden' => 4],
            ];
            foreach ($items as $it) {
                DB::table('web_productos_pagina_galeria')->insert(array_merge($it, [
                    'btn_texto'  => 'Me interesa',
                    'btn_url'    => 'https://wa.me/51981629466',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('sistema_menu') && !DB::table('sistema_menu')->where('url', '/pagina-productos/index')->exists()) {
            $maxOrden = (int) DB::table('sistema_menu')->max('orden');
            $idModulo = Schema::hasTable('sistema_modulo')
                ? DB::table('sistema_modulo')->where('nombre', 'like', '%Paginas%')->orWhere('nombre', 'like', '%Web%')->value('id_modulo')
                : null;
            $data = [
                'nombre'     => 'Página Productos',
                'url'        => '/pagina-productos/index',
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
                $data['id_modulo'] = $idModulo;
            }
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $data['Icon'] = 'inventory_2';
            }
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $data['orden'] = $maxOrden + 1;
            }
            DB::table('sistema_menu')->insert($data);
        }

        if (Schema::hasTable('web_header')) {
            $row = DB::table('web_header')->where('id_header', 1)->first();
            if ($row && $row->nav_items) {
                $nav = json_decode($row->nav_items, true) ?: [];
                $changed = false;
                foreach ($nav as &$item) {
                    if (($item['label'] ?? '') === 'Productos' && str_contains($item['href'] ?? '', '#productos')) {
                        $item['href'] = '/productos';
                        $changed = true;
                    }
                }
                if ($changed) {
                    DB::table('web_header')->where('id_header', 1)->update([
                        'nav_items' => json_encode($nav, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_productos_pagina_galeria');
        Schema::dropIfExists('web_productos_pagina_galeria_seccion');
        Schema::dropIfExists('web_pagina_productos');
    }
};
