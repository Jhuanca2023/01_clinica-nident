<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('web_header')) {
            Schema::table('web_header', function (Blueprint $table) {
                if (!Schema::hasColumn('web_header', 'top_ubicacion')) {
                    $table->string('top_ubicacion', 500)->nullable();
                    $table->string('top_email', 200)->nullable();
                    $table->string('top_telefonos', 200)->nullable();
                    $table->string('url_whatsapp_top', 500)->nullable();
                    $table->string('url_contacto_top', 500)->nullable();
                    $table->json('side_menu_enlaces')->nullable();
                    $table->json('redes_side')->nullable();
                }
            });
        }

        if (Schema::hasTable('web_porque_elejirnos') && !Schema::hasColumn('web_porque_elejirnos', 'url_imagen_fondo')) {
            Schema::table('web_porque_elejirnos', function (Blueprint $table) {
                $table->string('url_imagen_fondo', 500)->nullable()->after('url_imagen_centro');
            });
        }

        if (Schema::hasTable('web_contadores') && !Schema::hasColumn('web_contadores', 'icono_clase')) {
            Schema::table('web_contadores', function (Blueprint $table) {
                $table->string('icono_clase', 120)->nullable()->after('etiqueta');
            });
        }

        if (Schema::hasTable('web_servicios') && !Schema::hasColumn('web_servicios', 'subtitulo')) {
            Schema::table('web_servicios', function (Blueprint $table) {
                $table->string('subtitulo', 255)->nullable()->after('titulo');
                $table->string('btn_texto', 120)->nullable();
                $table->string('btn_url', 500)->nullable();
            });
        }

        Schema::create('web_about_caracteristica', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_about')->default(1);
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('url_enlace', 500)->nullable();
            $table->string('texto_enlace', 80)->nullable()->default('Ver más');
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_confianza_item', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('url_enlace', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_contadores_seccion', function (Blueprint $table) {
            $table->id('id');
            $table->string('url_imagen_fondo', 500)->nullable();
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_producto_destacado_seccion', function (Blueprint $table) {
            $table->id('id');
            $table->string('seccion_titulo', 500)->nullable();
            $table->string('seccion_subtitulo', 500)->nullable()->default('Productos destacados');
            $table->timestamps();
        });

        Schema::create('web_producto_destacado', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 255)->nullable();
            $table->string('subtitulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('url_video_youtube', 500)->nullable();
            $table->string('btn_texto', 120)->nullable()->default('Me interesa');
            $table->string('btn_url', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_producto_catalogo_seccion', function (Blueprint $table) {
            $table->id('id');
            $table->string('seccion_titulo', 500)->nullable();
            $table->text('seccion_descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('web_producto_catalogo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 255)->nullable();
            $table->string('badge', 80)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('btn_texto', 120)->nullable()->default('Me interesa');
            $table->string('btn_url', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_linea_producto_seccion', function (Blueprint $table) {
            $table->id('id');
            $table->string('seccion_titulo', 500)->nullable();
            $table->text('seccion_descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('web_linea_producto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 255)->nullable();
            $table->string('badge', 80)->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('icono_clase', 120)->nullable();
            $table->string('url_enlace', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_video_seccion', function (Blueprint $table) {
            $table->id('id');
            $table->string('seccion_titulo', 500)->nullable();
            $table->text('seccion_descripcion')->nullable();
            $table->string('bloque_titulo', 255)->nullable();
            $table->text('bloque_descripcion')->nullable();
            $table->string('bloque_url_imagen', 500)->nullable();
            $table->string('bloque_btn_texto', 120)->nullable();
            $table->string('bloque_btn_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('web_video', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag', 80)->nullable();
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_video', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_cliente_seccion', function (Blueprint $table) {
            $table->id('id');
            $table->string('tag', 120)->nullable();
            $table->string('titulo', 500)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('cta_texto', 500)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('web_cliente_estadistica', function (Blueprint $table) {
            $table->increments('id');
            $table->string('valor', 80)->nullable();
            $table->string('etiqueta', 255)->nullable();
            $table->string('icono_clase', 120)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_cliente_logo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 200)->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('url_enlace', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        Schema::create('web_contacto_landing', function (Blueprint $table) {
            $table->id('id');
            $table->string('titulo', 500)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('email_destino', 200)->nullable();
            $table->json('asuntos')->nullable();
            $table->timestamps();
        });

        Schema::create('web_contacto_mensaje', function (Blueprint $table) {
            $table->id('id');
            $table->string('nombre', 150)->nullable();
            $table->string('apellido', 150)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('asunto', 200)->nullable();
            $table->string('telefono', 60)->nullable();
            $table->text('mensaje')->nullable();
            $table->enum('email_enviado', ['S', 'N'])->default('N');
            $table->timestamps();
        });

        $this->seedJhDefaults();
    }

    private function seedJhDefaults(): void
    {
        $img = 'temp02/assets/img/jh_importaciones/inicio';

        if (Schema::hasTable('web_header') && DB::table('web_header')->exists()) {
            DB::table('web_header')->where('id_header', 1)->update([
                'url_logo'           => "{$img}/logo_jh.png",
                'top_ubicacion'      => 'Jr. Junín 1776 int. 20, Cercado de Lima',
                'top_email'          => 'jyhimportaciones@hotmil.com',
                'top_telefonos'      => '981 629 466 / 958 444 413',
                'url_whatsapp_top'   => 'https://wa.me/51981629466',
                'url_contacto_top'   => '#contacto',
                'nav_items'          => json_encode([
                    ['label' => 'Inicio', 'href' => '/'],
                    ['label' => 'Nosotros', 'href' => '/nosotros'],
                    ['label' => 'Productos', 'href' => '/#productos'],
                    ['label' => 'Videos', 'href' => '/#videos'],
                    ['label' => 'Máquinarias', 'href' => '/#maquinarias'],
                    ['label' => 'Contáctanos', 'href' => '/contacto'],
                ], JSON_UNESCAPED_UNICODE),
                'side_menu_enlaces'  => json_encode([
                    ['label' => 'Sobre Nosotros', 'href' => '#nosotros'],
                    ['label' => 'Productos', 'href' => '#productos'],
                    ['label' => 'Videos', 'href' => '#videos'],
                    ['label' => 'Contáctanos', 'href' => '#contacto'],
                ], JSON_UNESCAPED_UNICODE),
                'redes_side'         => json_encode([
                    ['red' => 'facebook', 'url' => '#'],
                    ['red' => 'whatsapp', 'url' => 'https://wa.me/51981629466'],
                    ['red' => 'tiktok', 'url' => 'https://www.tiktok.com/@jyh.eirl'],
                ], JSON_UNESCAPED_UNICODE),
                'updated_at'         => now(),
            ]);
        }

        if (Schema::hasTable('web_slider')) {
            $slides = [
                ['subtitulo' => 'Liam Vercceli', 'titulo' => 'Encuadernados de Calidad para tus Documentos', 'descripcion' => 'Somos distribuidores de alta calidad especializados en encuadernación profesional.', 'texto_boton' => 'Me interesa', 'url_link' => 'https://wa.me/51981629466', 'url_imagen' => "{$img}/jh_importaciones_slider_1.jpg"],
                ['subtitulo' => 'Tu socio confiable', 'titulo' => 'Liam Vercceli', 'descripcion' => 'Desde suministros de oficina hasta equipos especializados.', 'texto_boton' => 'Me interesa', 'url_link' => 'https://wa.me/51981629466', 'url_imagen' => "{$img}/jh_importaciones_slider_2.jpg"],
                ['subtitulo' => 'Encuadernación profesional', 'titulo' => 'Los mejores Espirales del Mercado', 'descripcion' => 'Espirales de alta resistencia y durabilidad.', 'texto_boton' => 'Ver productos', 'url_link' => '#productos', 'url_imagen' => "{$img}/jh_importaciones_slider_3.jpg"],
            ];
            foreach ($slides as $i => $s) {
                $id = $i + 1;
                if (DB::table('web_slider')->where('id_slider', $id)->exists()) {
                    DB::table('web_slider')->where('id_slider', $id)->update(array_merge($s, ['Activo' => 'S', 'updated_at' => now()]));
                } else {
                    DB::table('web_slider')->insert(array_merge($s, ['id_slider' => $id, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]));
                }
            }
        }

        if (Schema::hasTable('web_about') && DB::table('web_about')->where('id_about', 1)->exists()) {
            DB::table('web_about')->where('id_about', 1)->update([
                'subtitulo'   => 'Sobre Nosotros',
                'titulo'      => 'Liam Vercceli',
                'descripcion' => '<p>En Liam Vercceli, distribuimos y fabricamos productos de alta calidad para tus necesidades de oficina.</p><p>Somos distribuidores especializados en encuadernación profesional a nivel nacional.</p>',
                'url_imagen'  => "{$img}/seccion%201.jpg",
                'btn_texto'   => 'Me interesa',
                'btn_url'     => 'https://wa.me/51981629466',
                'Activo'      => 'S',
                'updated_at'  => now(),
            ]);
        }

        if (Schema::hasTable('web_about_caracteristica') && DB::table('web_about_caracteristica')->count() === 0) {
            $chars = [
                ['titulo' => 'Espirales PVC', 'descripcion' => 'Fabricación nacional en todos los diámetros y colores.', 'url_imagen' => "{$img}/advisor_espirales.jpg", 'url_enlace' => '#productos', 'orden' => 1],
                ['titulo' => 'Micas y enmicados', 'descripcion' => 'Láminas A3 y A4, micas liso, cubo y catedral.', 'url_imagen' => "{$img}/advisor_micas.jpg", 'url_enlace' => '#productos', 'orden' => 2],
                ['titulo' => 'Encuadernación nacional', 'descripcion' => 'Espiraladoras, selladoras y suministros para tu negocio.', 'url_imagen' => "{$img}/advisor_espiraladoras.jpg", 'url_enlace' => '#contacto', 'orden' => 3],
            ];
            foreach ($chars as $c) {
                DB::table('web_about_caracteristica')->insert(array_merge($c, ['id_about' => 1, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]));
            }
        }

        if (Schema::hasTable('web_porque_elejirnos') && DB::table('web_porque_elejirnos')->exists()) {
            DB::table('web_porque_elejirnos')->where('id_porque', 1)->update([
                'titulo'            => 'Confianza',
                'descripcion'       => 'Te brindamos productos y servicios de calidad. Operamos a nivel nacional.',
                'url_imagen_fondo'  => "{$img}/seccion%202.jpg",
                'updated_at'        => now(),
            ]);
        }

        if (Schema::hasTable('web_confianza_item') && DB::table('web_confianza_item')->count() === 0) {
            $items = [
                ['titulo' => 'Cartones plastificados', 'descripcion' => 'Cartones plastificados en A4, A3 y oficio de 180 micrones.', 'url_imagen' => "{$img}/jh_importaciones_img_1.jpg", 'url_enlace' => '#productos', 'orden' => 1],
                ['titulo' => 'Espirales plásticos', 'descripcion' => 'Fabricantes de espirales en PVC, del diámetro 7 al 50.', 'url_imagen' => "{$img}/advisor_espirales.jpg", 'url_enlace' => '#productos', 'orden' => 2],
                ['titulo' => 'Micas para anillados', 'descripcion' => 'Micas liso, cubo y catedral. Amplio stock en colores.', 'url_imagen' => "{$img}/advisor_micas.jpg", 'url_enlace' => '#productos', 'orden' => 3],
            ];
            foreach ($items as $it) {
                DB::table('web_confianza_item')->insert(array_merge($it, ['Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]));
            }
        }

        if (Schema::hasTable('web_contadores_seccion') && DB::table('web_contadores_seccion')->count() === 0) {
            DB::table('web_contadores_seccion')->insert([
                'url_imagen_fondo' => 'temp02/assets/img/banner/2.jpg',
                'Activo'           => 'S',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        if (Schema::hasTable('web_contadores')) {
            DB::table('web_contadores')->where('Activo', 'S')->delete();
            $cts = [
                ['valor' => '212', 'etiqueta' => 'Espirales PVC', 'icono_clase' => 'flaticon-contract', 'orden' => 1],
                ['valor' => '128', 'etiqueta' => 'Micas y enmicados', 'icono_clase' => 'flaticon-professor', 'orden' => 2],
                ['valor' => '8970', 'etiqueta' => 'Clientes satisfechos', 'icono_clase' => 'flaticon-online', 'orden' => 3],
                ['valor' => '640', 'etiqueta' => 'Productos', 'icono_clase' => 'flaticon-reading', 'orden' => 4],
            ];
            foreach ($cts as $c) {
                DB::table('web_contadores')->insert(array_merge($c, ['sufijo' => '', 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]));
            }
        }

        $this->seedProductosVideosClientesContacto($img);
    }

    private function seedProductosVideosClientesContacto(string $img): void
    {
        if (Schema::hasTable('web_producto_destacado_seccion') && DB::table('web_producto_destacado_seccion')->count() === 0) {
            DB::table('web_producto_destacado_seccion')->insert(['id' => 1, 'seccion_subtitulo' => 'Productos destacados', 'created_at' => now(), 'updated_at' => now()]);
        }
        if (Schema::hasTable('web_producto_destacado') && DB::table('web_producto_destacado')->count() === 0) {
            $d = [
                ['titulo' => 'Cartones plastificados', 'descripcion' => 'Cartones plastificados en A4, A3 y oficio.', 'url_imagen' => "{$img}/jh_importaciones_img_1.jpg", 'url_video_youtube' => 'https://www.youtube.com/watch?v=vQqZIFCab9o', 'orden' => 1],
                ['titulo' => 'Espirales plásticos', 'descripcion' => 'Fabricantes de espirales en PVC.', 'url_imagen' => "{$img}/jh_importaciones_img_2.jpeg", 'url_video_youtube' => 'https://www.youtube.com/watch?v=vQqZIFCab9o', 'orden' => 2],
                ['titulo' => 'Micas para anillados', 'descripcion' => 'Distribuimos micas para anillados.', 'url_imagen' => "{$img}/jh_importaciones_img_3.jpg", 'url_video_youtube' => 'https://www.youtube.com/watch?v=vQqZIFCab9o', 'orden' => 3],
            ];
            foreach ($d as $row) {
                DB::table('web_producto_destacado')->insert(array_merge($row, [
                    'btn_texto' => 'Me interesa', 'btn_url' => 'https://wa.me/51981629466', 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_producto_catalogo_seccion') && DB::table('web_producto_catalogo_seccion')->count() === 0) {
            DB::table('web_producto_catalogo_seccion')->insert([
                'id' => 1, 'seccion_titulo' => 'Nuestros Productos',
                'seccion_descripcion' => '¡Sella con confianza y calidad en cada empaque con nuestra selladora de bolsas!',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if (Schema::hasTable('web_producto_catalogo') && DB::table('web_producto_catalogo')->count() === 0) {
            $cat = [
                ['titulo' => 'Cartones plastificados', 'badge' => 'J&H', 'url_imagen' => "{$img}/jh_importaciones_img_1.jpg", 'orden' => 1],
                ['titulo' => 'Espirales plásticos', 'badge' => 'PVC', 'url_imagen' => "{$img}/jh_importaciones_img_2.jpeg", 'orden' => 2],
                ['titulo' => 'Micas para anillados', 'badge' => 'J&H', 'url_imagen' => "{$img}/jh_importaciones_img_3.jpg", 'orden' => 3],
                ['titulo' => 'Láminas de enmicado', 'badge' => 'A3/A4', 'url_imagen' => "{$img}/jh_importaciones_img_4.png", 'orden' => 4],
                ['titulo' => 'Nuestra Selladora de Bolsas', 'badge' => 'Sellado', 'url_imagen' => "{$img}/jh_importaciones_img_5.jpg", 'orden' => 5],
            ];
            foreach ($cat as $c) {
                DB::table('web_producto_catalogo')->insert(array_merge($c, [
                    'descripcion' => '', 'btn_texto' => 'Me interesa', 'btn_url' => 'https://wa.me/51981629466',
                    'Activo' => 'S', 'created_at' => now(), 'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_linea_producto_seccion') && DB::table('web_linea_producto_seccion')->count() === 0) {
            DB::table('web_linea_producto_seccion')->insert([
                'id' => 1, 'seccion_titulo' => 'Líneas de Producto',
                'seccion_descripcion' => 'Fabricamos y distribuimos espirales PVC, micas, espiraladoras y más.',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('web_video_seccion') && DB::table('web_video_seccion')->count() === 0) {
            DB::table('web_video_seccion')->insert([
                'id' => 1,
                'seccion_titulo'       => 'Creando Espirales',
                'seccion_descripcion'  => 'Descubre cómo nuestras espirales de alta calidad pueden transformar tus proyectos.',
                'bloque_titulo'        => 'Fabricamos Espirales para Encuadernación',
                'bloque_descripcion'   => 'En Liam Vercceli, somos fabricantes y distribuidores de espirales de PVC.',
                'bloque_url_imagen'    => "{$img}/jh_importaciones_img_4.png",
                'bloque_btn_texto'     => 'Ver más',
                'bloque_btn_url'       => 'https://wa.me/51981629466',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        if (Schema::hasTable('web_video') && DB::table('web_video')->count() === 0) {
            DB::table('web_video')->insert([
                ['tag' => 'Fabricación', 'titulo' => 'Creando Espirales', 'descripcion' => 'Descubre cómo nuestras espirales pueden transformar tus proyectos.', 'url_video' => "{$img}/Video_01.mp4", 'orden' => 1, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()],
                ['tag' => 'Proceso', 'titulo' => 'Fabricación de Espirales PVC', 'descripcion' => 'Conoce nuestro proceso de fabricación.', 'url_video' => "{$img}/Video_2.mp4", 'orden' => 2, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Schema::hasTable('web_contacto_landing') && DB::table('web_contacto_landing')->count() === 0) {
            DB::table('web_contacto_landing')->insert([
                'id' => 1,
                'titulo'        => 'Escríbenos un mensaje',
                'descripcion'   => 'Cuéntanos qué producto necesitas y te responderemos a la brevedad.',
                'url_imagen'    => "{$img}/jh_importaciones_img_5.jpg",
                'email_destino' => config('mail.from.address'),
                'asuntos'       => json_encode(['Espirales PVC', 'Micas y cartones', 'Enmicado', 'Selladora de bolsas']),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_contacto_mensaje');
        Schema::dropIfExists('web_contacto_landing');
        Schema::dropIfExists('web_cliente_logo');
        Schema::dropIfExists('web_cliente_estadistica');
        Schema::dropIfExists('web_cliente_seccion');
        Schema::dropIfExists('web_video');
        Schema::dropIfExists('web_video_seccion');
        Schema::dropIfExists('web_linea_producto');
        Schema::dropIfExists('web_linea_producto_seccion');
        Schema::dropIfExists('web_producto_catalogo');
        Schema::dropIfExists('web_producto_catalogo_seccion');
        Schema::dropIfExists('web_producto_destacado');
        Schema::dropIfExists('web_producto_destacado_seccion');
        Schema::dropIfExists('web_contadores_seccion');
        Schema::dropIfExists('web_confianza_item');
        Schema::dropIfExists('web_about_caracteristica');
    }
};
