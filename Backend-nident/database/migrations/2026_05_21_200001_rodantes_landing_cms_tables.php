<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_eventos')) {
            Schema::create('web_eventos', function (Blueprint $table) {
                $table->increments('id_evento');
                $table->string('seccion_titulo', 500)->nullable();
                $table->string('seccion_subtitulo', 1000)->nullable();
                $table->string('titulo', 255)->nullable();
                $table->string('slug', 120)->nullable();
                $table->string('alt_imagen', 500)->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_publicaciones')) {
            Schema::create('web_publicaciones', function (Blueprint $table) {
                $table->increments('id_publicacion');
                $table->string('seccion_titulo', 500)->nullable();
                $table->string('seccion_subtitulo', 1000)->nullable();
                $table->string('titulo', 255)->nullable();
                $table->text('resumen')->nullable();
                $table->string('chip', 80)->nullable()->default('Publicación');
                $table->string('url_enlace', 500)->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_contadores')) {
            Schema::create('web_contadores', function (Blueprint $table) {
                $table->increments('id_contador');
                $table->string('valor', 30)->nullable();
                $table->string('sufijo', 10)->nullable()->default('+');
                $table->string('etiqueta', 255)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_about_contador')) {
            Schema::create('web_about_contador', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_about')->default(1);
                $table->string('valor', 30)->nullable();
                $table->string('sufijo', 10)->nullable()->default('+');
                $table->string('etiqueta', 255)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('web_slider')) {
            Schema::table('web_slider', function (Blueprint $table) {
                if (!Schema::hasColumn('web_slider', 'slide_tag')) {
                    $table->string('slide_tag', 255)->nullable()->after('subtitulo');
                }
                if (!Schema::hasColumn('web_slider', 'texto_boton_2')) {
                    $table->string('texto_boton_2', 120)->nullable()->after('texto_boton');
                }
                if (!Schema::hasColumn('web_slider', 'url_link_2')) {
                    $table->string('url_link_2', 500)->nullable()->after('url_link');
                }
            });
        }

        if (Schema::hasTable('web_about')) {
            Schema::table('web_about', function (Blueprint $table) {
                if (!Schema::hasColumn('web_about', 'subtitulo')) {
                    $table->string('subtitulo', 500)->nullable()->after('titulo');
                }
                if (!Schema::hasColumn('web_about', 'lema_label')) {
                    $table->string('lema_label', 120)->nullable();
                }
                if (!Schema::hasColumn('web_about', 'lema_texto')) {
                    $table->text('lema_texto')->nullable();
                }
                if (!Schema::hasColumn('web_about', 'texto_extendido')) {
                    $table->longText('texto_extendido')->nullable();
                }
                if (!Schema::hasColumn('web_about', 'btn_texto')) {
                    $table->string('btn_texto', 120)->nullable();
                }
                if (!Schema::hasColumn('web_about', 'btn_url')) {
                    $table->string('btn_url', 500)->nullable();
                }
            });
        }

        if (Schema::hasTable('web_header')) {
            Schema::table('web_header', function (Blueprint $table) {
                if (!Schema::hasColumn('web_header', 'etiqueta_hero_lateral')) {
                    $table->string('etiqueta_hero_lateral', 120)->nullable();
                }
                if (!Schema::hasColumn('web_header', 'btn_visitanos_texto')) {
                    $table->string('btn_visitanos_texto', 100)->nullable();
                }
                if (!Schema::hasColumn('web_header', 'btn_visitanos_url')) {
                    $table->string('btn_visitanos_url', 500)->nullable();
                }
                if (!Schema::hasColumn('web_header', 'btn_eventos_texto')) {
                    $table->string('btn_eventos_texto', 100)->nullable();
                }
                if (!Schema::hasColumn('web_header', 'btn_eventos_url')) {
                    $table->string('btn_eventos_url', 500)->nullable();
                }
            });
        }

        $this->seedDefaults();
    }

    private function seedDefaults(): void
    {
        if (Schema::hasTable('web_header') && DB::table('web_header')->exists()) {
            DB::table('web_header')->where('id_header', 1)->update([
                'url_logo'              => 'temp02/assets/image/logos/bibliotecas-rodante-logo-menu.png',
                'nav_items'             => json_encode([
                    ['label' => 'Inicio', 'href' => '#inicio'],
                    ['label' => 'Nosotros', 'href' => '#nosotros'],
                    ['label' => 'Proyectos', 'href' => '#eventos', 'children' => [
                        ['label' => 'Eventos', 'href' => '#eventos'],
                    ]],
                    ['label' => 'Tienda', 'href' => '#'],
                    ['label' => 'Contáctanos', 'href' => '#contacto'],
                ], JSON_UNESCAPED_UNICODE),
                'telefono_label'        => 'Llámanos:',
                'telefono_numero'       => '934 200 140',
                'telefono_href'         => 'tel:+51934200140',
                'btn_texto'             => 'Visítanos',
                'etiqueta_hero_lateral' => 'Bibliotecas Rodantes',
                'btn_visitanos_texto'   => 'Visítanos',
                'btn_visitanos_url'     => '#contacto',
                'btn_eventos_texto'     => 'Eventos',
                'btn_eventos_url'       => '#eventos',
                'updated_at'            => now(),
            ]);
        }

        if (Schema::hasTable('web_slider')) {
            $slides = [
                [
                    'slide_tag'     => 'Conocimiento sin fronteras',
                    'titulo'        => 'Llevamos la <em>Lectura</em><br>a tu Comunidad',
                    'descripcion'   => 'Bibliotecas Rodantes acerca el saber a cada rincón.<br>Historias que transforman, cultura que une.',
                    'texto_boton'   => 'Únete ahora',
                    'url_link'      => '#contacto',
                    'texto_boton_2' => 'Explorar servicios',
                    'url_link_2'    => '#servicios',
                    'url_imagen'    => 'temp02/assets/image/inicio/slider-2.jpg',
                ],
                [
                    'slide_tag'     => 'Para todas las edades',
                    'titulo'        => 'Un Mundo de <em>Historias</em><br>te Espera',
                    'descripcion'   => 'Colecciones cuidadosamente seleccionadas para niños,<br>jóvenes y adultos.',
                    'texto_boton'   => 'Ver colección',
                    'url_link'      => '#servicios',
                    'texto_boton_2' => 'Conócenos',
                    'url_link_2'    => '#nosotros',
                    'url_imagen'    => 'temp02/assets/image/inicio/slider-5.jpg',
                ],
                [
                    'slide_tag'     => 'Itinerancia cultural',
                    'titulo'        => 'La Biblioteca<br>Llega <em>Hasta Ti</em>',
                    'descripcion'   => 'Servicios de lectura itinerante para escuelas, parques<br>y comunidades.',
                    'texto_boton'   => 'Contáctanos',
                    'url_link'      => '#contacto',
                    'texto_boton_2' => 'Nuestras rutas',
                    'url_link_2'    => '#eventos',
                    'url_imagen'    => 'temp02/assets/image/inicio/slider-6.jpg',
                ],
                [
                    'slide_tag'     => 'Fomento a la lectura',
                    'titulo'        => 'Leer es <em>Viajar</em><br>sin Moverse',
                    'descripcion'   => 'Promovemos el hábito lector con talleres, encuentros<br>y actividades culturales.',
                    'texto_boton'   => 'Agendar visita',
                    'url_link'      => '#contacto',
                    'texto_boton_2' => 'Noticias',
                    'url_link_2'    => '#publicaciones',
                    'url_imagen'    => 'temp02/assets/image/inicio/slider-7.jpg',
                ],
            ];

            foreach ($slides as $i => $slide) {
                $id = $i + 1;
                if (DB::table('web_slider')->where('id_slider', $id)->exists()) {
                    DB::table('web_slider')->where('id_slider', $id)->update(array_merge($slide, [
                        'Activo'     => 'S',
                        'updated_at' => now(),
                    ]));
                } else {
                    DB::table('web_slider')->insert(array_merge($slide, [
                        'id_slider'  => $id,
                        'Activo'     => 'S',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }

        if (Schema::hasTable('web_about')) {
            if (DB::table('web_about')->where('id_about', 1)->exists()) {
                DB::table('web_about')->where('id_about', 1)->update([
                    'titulo'          => '<span class="color-primary">Quiénes</span> Somos',
                    'subtitulo'       => 'Lectura, inclusión y cultura<br>que llegan a cada comunidad',
                    'lema_label'      => 'Nuestro lema',
                    'lema_texto'      => '«Rueda, rueda Bibliotecas Rodantes… llevando lectura y cultura en movimiento.»',
                    'descripcion'     => '<p class="mb-16"><strong>Bibliotecas Rodantes</strong> es una asociación cultural y comunitaria que promueve la lectura, la inclusión y el desarrollo social mediante bibliotecas móviles.</p>',
                    'texto_extendido' => '<p class="mb-16">Nuestra labor comprende bibliotecas rodantes, lectura inclusiva y actividades culturales en cada visita.</p>',
                    'url_imagen'      => 'temp02/assets/image/inicio/librerias-rodantes-02.jpg',
                    'btn_texto'       => 'Conócenos más',
                    'btn_url'         => '#nosotros',
                    'Activo'          => 'S',
                    'updated_at'      => now(),
                ]);
            } else {
                DB::table('web_about')->insert([
                    'id_about'        => 1,
                    'titulo'          => '<span class="color-primary">Quiénes</span> Somos',
                    'subtitulo'       => 'Lectura, inclusión y cultura<br>que llegan a cada comunidad',
                    'lema_label'      => 'Nuestro lema',
                    'lema_texto'      => '«Rueda, rueda Bibliotecas Rodantes… llevando lectura y cultura en movimiento.»',
                    'descripcion'     => '<p class="mb-16"><strong>Bibliotecas Rodantes</strong> es una asociación cultural y comunitaria.</p>',
                    'texto_extendido' => null,
                    'url_imagen'      => 'temp02/assets/image/inicio/librerias-rodantes-02.jpg',
                    'btn_texto'       => 'Conócenos más',
                    'btn_url'         => '#nosotros',
                    'Activo'          => 'S',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        if (Schema::hasTable('web_about_contador') && DB::table('web_about_contador')->count() === 0) {
            $stats = [
                ['valor' => '120', 'etiqueta' => 'Comunidades visitadas', 'orden' => 1],
                ['valor' => '8000', 'etiqueta' => 'Lectores atendidos', 'orden' => 2],
                ['valor' => '10', 'etiqueta' => 'Años de experiencia', 'orden' => 3],
            ];
            foreach ($stats as $s) {
                DB::table('web_about_contador')->insert(array_merge($s, [
                    'id_about'   => 1,
                    'sufijo'     => '+',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_contadores') && DB::table('web_contadores')->count() === 0) {
            $items = [
                ['valor' => '120', 'etiqueta' => 'Comunidades visitadas', 'orden' => 1],
                ['valor' => '8000', 'etiqueta' => 'Lectores atendidos', 'orden' => 2],
                ['valor' => '500', 'etiqueta' => 'Libros distribuidos', 'orden' => 3],
                ['valor' => '30', 'etiqueta' => 'Conversatorios realizados', 'orden' => 4],
            ];
            foreach ($items as $item) {
                DB::table('web_contadores')->insert(array_merge($item, [
                    'sufijo'     => '+',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_eventos') && DB::table('web_eventos')->count() === 0) {
            $seccionTitulo = 'Nuestros <span class="color-primary">Eventos</span>';
            $seccionSub = 'En Raíces y Letras — Bibliotecas Rodantes LEC, el diálogo entre quien escribe y quien lee enciende el amor por los libros.';
            $eventos = [
                ['titulo' => 'Fiesta de los Libros', 'slug' => 'fiesta-de-los-libros', 'alt_imagen' => 'Fiesta de los Libros', 'url_imagen' => 'temp02/assets/image/inicio/galeria-01.jpg', 'orden' => 1],
                ['titulo' => 'Conversatorio Raíces y Letras', 'slug' => 'conversatorio-raices-y-letras', 'alt_imagen' => 'Conversatorio Raíces y Letras', 'url_imagen' => 'temp02/assets/image/inicio/galeria-02.jpg', 'orden' => 2],
                ['titulo' => 'Ven y cuenta tu relato', 'slug' => 'ven-y-cuenta-tu-relato', 'alt_imagen' => 'Ven y cuenta tu relato', 'url_imagen' => 'temp02/assets/image/inicio/galeria-03.jpg', 'orden' => 3],
                ['titulo' => 'Educación digital', 'slug' => 'educacion-digital', 'alt_imagen' => 'Educación digital', 'url_imagen' => 'temp02/assets/image/inicio/galeria-04.jpg', 'orden' => 4],
                ['titulo' => 'Libros cartoneros', 'slug' => 'libros-cartoneros', 'alt_imagen' => 'Taller de libros cartoneros', 'url_imagen' => 'temp02/assets/image/inicio/galeria-05.jpg', 'orden' => 5],
                ['titulo' => 'Biblioteca rodante en comunidad', 'slug' => 'biblioteca-rodante-en-comunidad', 'alt_imagen' => 'Biblioteca rodante en comunidad', 'url_imagen' => 'temp02/assets/image/inicio/galeria-06.jpg', 'orden' => 6],
            ];
            foreach ($eventos as $ev) {
                DB::table('web_eventos')->insert(array_merge($ev, [
                    'seccion_titulo'    => $seccionTitulo,
                    'seccion_subtitulo' => $seccionSub,
                    'Activo'            => 'S',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_servicios') && DB::table('web_servicios')->count() === 0) {
            $srv = [
                ['titulo' => 'Bibliobús y bibliotecas rodantes', 'descripcion' => 'Llevamos libros y colecciones itinerantes a escuelas y barrios.', 'url_foto' => 'temp02/assets/image/inicio/servicio-rodante-01.jpg', 'orden' => 1],
                ['titulo' => 'Conversatorios y Raíces y Letras', 'descripcion' => 'Encuentros con escritores lambayecanos.', 'url_foto' => 'temp02/assets/image/inicio/servicio-rodante-02.jpg', 'orden' => 2],
                ['titulo' => 'Educación digital consciente', 'descripcion' => 'Talleres sobre redes sociales y ciberacoso.', 'url_foto' => 'temp02/assets/image/inicio/servicio-rodante-03.jpg', 'orden' => 3],
                ['titulo' => 'Talleres creativos y comunitarios', 'descripcion' => 'Libros cartoneros, narración oral y encuentros comunitarios.', 'url_foto' => 'temp02/assets/image/inicio/servicio-rodante-04.jpg', 'orden' => 4],
            ];
            foreach ($srv as $s) {
                DB::table('web_servicios')->insert(array_merge($s, [
                    'seccion_titulo' => 'Nuestros <span class="color-primary">Servicios</span>',
                    'Activo'         => 'S',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_publicaciones') && DB::table('web_publicaciones')->count() === 0) {
            $pubs = [
                ['titulo' => 'Un encuentro tradición oral', 'resumen' => 'Una tarde de leyendas y saberes compartidos.', 'url_imagen' => 'temp02/assets/image/inicio/publicacion-01.jpg', 'orden' => 1],
                ['titulo' => 'Ven y cuenta tu relato', 'resumen' => 'Niños, jóvenes y familias comparten relatos.', 'url_imagen' => 'temp02/assets/image/inicio/publicacion-02.jpg', 'orden' => 2],
                ['titulo' => 'Leyendas que inspiran a leer', 'resumen' => 'Cada leyenda contada despierta curiosidad por los libros.', 'url_imagen' => 'temp02/assets/image/inicio/publicacion-03.jpg', 'orden' => 3],
                ['titulo' => 'Raíces y letras en cada rincón', 'resumen' => 'Lectura y conversatorios en escuelas y comunidades.', 'url_imagen' => 'temp02/assets/image/inicio/publicacion-04.jpg', 'orden' => 4],
            ];
            foreach ($pubs as $p) {
                DB::table('web_publicaciones')->insert(array_merge($p, [
                    'seccion_titulo'    => 'Publicaciones <span class="color-primary">Recientes</span>',
                    'seccion_subtitulo' => 'Lo último de Bibliotecas Rodantes — relatos y fomento lector en Lambayeque',
                    'chip'              => 'Publicación',
                    'Activo'            => 'S',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_about_contador');
        Schema::dropIfExists('web_publicaciones');
        Schema::dropIfExists('web_eventos');
        Schema::dropIfExists('web_contadores');

        if (Schema::hasTable('web_slider')) {
            Schema::table('web_slider', function (Blueprint $table) {
                foreach (['slide_tag', 'texto_boton_2', 'url_link_2'] as $col) {
                    if (Schema::hasColumn('web_slider', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('web_about')) {
            Schema::table('web_about', function (Blueprint $table) {
                foreach (['subtitulo', 'lema_label', 'lema_texto', 'texto_extendido', 'btn_texto', 'btn_url'] as $col) {
                    if (Schema::hasColumn('web_about', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('web_header')) {
            Schema::table('web_header', function (Blueprint $table) {
                foreach (['etiqueta_hero_lateral', 'btn_visitanos_texto', 'btn_visitanos_url', 'btn_eventos_texto', 'btn_eventos_url'] as $col) {
                    if (Schema::hasColumn('web_header', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
