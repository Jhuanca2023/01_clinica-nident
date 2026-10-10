<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    public function up(): void

    {

        if (Schema::hasTable('web_pagina_nosotros')) {

            Schema::table('web_pagina_nosotros', function (Blueprint $table) {

                if (! Schema::hasColumn('web_pagina_nosotros', 'titulo_hero')) {

                    $table->string('titulo_hero', 500)->nullable();

                    $table->string('subtitulo_hero', 1000)->nullable();

                    $table->string('url_imagen_hero', 500)->nullable();

                    $table->string('titulo_seccion', 500)->nullable();

                    $table->text('descripcion_seccion')->nullable();

                    $table->string('url_imagen_seccion', 500)->nullable();

                    $table->string('historia_badge', 120)->nullable();

                    $table->json('valores_json')->nullable();

                    $table->json('timeline_json')->nullable();

                    $table->string('cta_titulo', 500)->nullable();

                    $table->text('cta_texto')->nullable();

                    $table->string('cta_btn_texto', 120)->nullable();

                    $table->string('cta_btn_url', 500)->nullable();

                }

            });



            $valores = [

                ['icon' => 'fa-star', 'titulo' => 'Excelencia', 'texto' => 'Cada polo pasa por un riguroso control de calidad antes de llegar a tus manos. Sin excepciones.'],

                ['icon' => 'fa-leaf', 'titulo' => 'Sostenibilidad', 'texto' => 'Usamos materiales naturales y procesos responsables que cuidan el medio ambiente y a nuestra comunidad.'],

                ['icon' => 'fa-heart', 'titulo' => 'Pasión', 'texto' => 'Amamos lo que hacemos. Esa pasión se refleja en cada costura, cada color y cada diseño que creamos.'],

                ['icon' => 'fa-users', 'titulo' => 'Comunidad', 'texto' => 'Somos parte de Lima. Apoyamos el trabajo local y crecemos junto a nuestra comunidad de clientes.'],

                ['icon' => 'fa-shield', 'titulo' => 'Confianza', 'texto' => 'Transparencia en precios, envíos y devoluciones. Tu satisfacción es nuestra prioridad absoluta.'],

                ['icon' => 'fa-bolt', 'titulo' => 'Innovación', 'texto' => 'Renovamos constantemente nuestros diseños para mantenernos a la vanguardia de la moda peruana.'],

            ];



            $timeline = [

                ['year' => '2015', 'titulo' => 'El comienzo', 'texto' => 'Abrimos nuestro primer taller en Miraflores con 3 personas y una máquina de coser.'],

                ['year' => '2017', 'titulo' => 'Primera tienda física', 'texto' => 'Inauguramos nuestra primera tienda en Surco. Más de 500 clientes en el primer mes.'],

                ['year' => '2019', 'titulo' => 'Expansión digital', 'texto' => 'Lanzamos nuestra tienda online y en 6 meses llegamos a más de 15 departamentos del Perú.'],

                ['year' => '2022', 'titulo' => '10,000 clientes', 'texto' => 'Superamos los 10,000 clientes satisfechos y lanzamos nuestra línea Premium con algodón Pima.'],

                ['year' => '2024', 'titulo' => 'Reconocimiento nacional', 'texto' => 'Premiados como una de las marcas emergentes de moda masculina más influyentes del Perú.'],

            ];



            DB::table('web_pagina_nosotros')->where('id', 1)->update([

                'titulo_hero'        => 'Más que moda, es identidad',

                'subtitulo_hero'     => 'Confeccionamos polos de excelencia que expresan quién eres. Calidad sin concesiones, estilo sin límites.',

                'url_imagen_hero'    => 'temp02/img/inicio/2.jpg',

                'titulo_seccion'     => 'Nació de una pasión por la excelencia',

                'descripcion_seccion'=> '<p>Liam Vercceli nació en Lima, Perú, con una misión clara: llevar al mercado polos de altísima calidad que combinen diseño, durabilidad y estilo.</p><p>Cada prenda que confeccionamos lleva consigo el compromiso de nuestro equipo: materiales seleccionados, cortes precisos y acabados impecables.</p>',

                'url_imagen_seccion' => 'temp02/img/inicio/3.jpg',

                'historia_badge'     => 'Desde 2015',

                'valores_json'       => json_encode($valores, JSON_UNESCAPED_UNICODE),

                'timeline_json'      => json_encode($timeline, JSON_UNESCAPED_UNICODE),

                'cta_titulo'         => '¿Listo para vestir con excelencia?',

                'cta_texto'          => 'Descubre nuestra colección y encuentra el polo perfecto para ti.',

                'cta_btn_texto'      => 'Ver Colección',

                'cta_btn_url'        => '/tienda',

                'updated_at'         => now(),

            ]);

        }



        if (Schema::hasTable('web_nosotros_quienes_beneficio')) {

            DB::table('web_nosotros_quienes_beneficio')->where('Activo', 'S')->delete();

            $items = [

                '100% algodón pima peruano',

                'Más de 50 modelos exclusivos por temporada',

                'Envíos a todo el Perú en 24-48h',

                'Garantía de satisfacción en cada compra',

            ];

            foreach ($items as $i => $texto) {

                DB::table('web_nosotros_quienes_beneficio')->insert([

                    'texto'      => $texto,

                    'orden'      => $i + 1,

                    'Activo'     => 'S',

                    'created_at' => now(),

                    'updated_at' => now(),

                ]);

            }

        }



        if (Schema::hasTable('web_contadores')) {

            $hasContexto = Schema::hasColumn('web_contadores', 'contexto');

            $query = DB::table('web_contadores');

            if ($hasContexto) {

                $query->where('contexto', 'nosotros');

            }

            if ($query->count() === 0) {

                $stats = [

                    ['valor' => '12000', 'sufijo' => '+', 'etiqueta' => 'Clientes satisfechos', 'orden' => 1],

                    ['valor' => '50', 'sufijo' => '+', 'etiqueta' => 'Modelos por temporada', 'orden' => 2],

                    ['valor' => '9', 'sufijo' => '', 'etiqueta' => 'Años en el mercado', 'orden' => 3],

                    ['valor' => '25', 'sufijo' => '+', 'etiqueta' => 'Departamentos atendidos', 'orden' => 4],

                ];

                foreach ($stats as $s) {

                    $row = array_merge($s, [

                        'Activo'     => 'S',

                        'created_at' => now(),

                        'updated_at' => now(),

                    ]);

                    if ($hasContexto) {

                        $row['contexto'] = 'nosotros';

                    }

                    DB::table('web_contadores')->insert($row);

                }

            }

        }



        if (Schema::hasTable('web_pagina_contacto')) {

            if (! Schema::hasColumn('web_pagina_contacto', 'hero_subtitulo')) {

                Schema::table('web_pagina_contacto', function (Blueprint $table) {

                    $table->string('hero_subtitulo', 500)->nullable()->after('banner_titulo');

                });

            }



            DB::table('web_pagina_contacto')->where('id', 1)->update([

                'banner_titulo'    => 'Hablemos de moda',

                'hero_subtitulo'   => 'Estamos aquí para ayudarte — respuesta en menos de 1 hora',

                'form_titulo'      => 'Envíanos un mensaje',

                'form_subtitulo'   => 'Te responderemos en menos de 24 horas hábiles.',

                'telefono'         => '+51 999 999 999',

                'telefonos_texto'  => "+51 999 999 999\nWhatsApp disponible",

                'email'            => 'contacto@rossety.pe',

                'emails_texto'     => "contacto@rossety.pe\nventas@rossety.pe",

                'ubicacion'        => "Av. Faisanes 420, Urb. La Campiña\nChorrillos, Lima — Perú",

                'horario_linea1'   => 'Lun–Vie: 9am – 7pm',

                'horario_linea2'   => 'Sáb: 9am – 5pm | Dom: 10am – 2pm',

                'horario_dias'     => 'Feriados: Cerrado',

                'mapa_embed_url'   => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.5!2d-77.02!3d-12.18!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTLCsDEwJzQ4LjAiUyA3N8KwMDEnMTIuMCJX!5e0!3m2!1ses!2spe!4v1',

                'updated_at'       => now(),

            ]);

        }



        if (Schema::hasTable('sistema_menu')) {

            $exists = DB::table('sistema_menu')->where('url', '/contadores/index')->exists();

            if (! $exists) {

                $maxOrden = (int) DB::table('sistema_menu')->max('orden');

                $idModulo = DB::table('sistema_modulo')->where('nombre', 'like', '%Tienda Virtual%')->value('id_modulo');

                $data = [

                    'nombre'     => 'Contadores nosotros',

                    'url'        => '/contadores/index',

                    'Activo'     => 'S',

                    'created_at' => now(),

                    'updated_at' => now(),

                ];

                if (Schema::hasColumn('sistema_menu', 'id_modulo') && $idModulo) {

                    $data['id_modulo'] = $idModulo;

                }

                if (Schema::hasColumn('sistema_menu', 'Icon')) {

                    $data['Icon'] = 'pin';

                }

                if (Schema::hasColumn('sistema_menu', 'orden')) {

                    $data['orden'] = $maxOrden + 1;

                }

                DB::table('sistema_menu')->insert($data);

            } else {

                DB::table('sistema_menu')->where('url', '/contadores/index')->update([

                    'nombre'     => 'Contadores nosotros',

                    'Activo'     => 'S',

                    'updated_at' => now(),

                ]);

            }

        }

    }



    public function down(): void

    {

        if (Schema::hasTable('web_pagina_nosotros')) {

            Schema::table('web_pagina_nosotros', function (Blueprint $table) {

                foreach ([

                    'titulo_hero', 'subtitulo_hero', 'url_imagen_hero',

                    'titulo_seccion', 'descripcion_seccion', 'url_imagen_seccion',

                    'historia_badge', 'valores_json', 'timeline_json',

                    'cta_titulo', 'cta_texto', 'cta_btn_texto', 'cta_btn_url',

                ] as $col) {

                    if (Schema::hasColumn('web_pagina_nosotros', $col)) {

                        $table->dropColumn($col);

                    }

                }

            });

        }



        if (Schema::hasTable('web_pagina_contacto') && Schema::hasColumn('web_pagina_contacto', 'hero_subtitulo')) {

            Schema::table('web_pagina_contacto', function (Blueprint $table) {

                $table->dropColumn('hero_subtitulo');

            });

        }

    }

};


