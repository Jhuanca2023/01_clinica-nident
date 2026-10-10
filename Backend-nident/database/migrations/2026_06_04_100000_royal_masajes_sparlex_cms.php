<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_promo_banner')) {
            Schema::create('web_promo_banner', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary()->default(1);
                $table->string('subtitulo', 500)->nullable();
                $table->string('titulo', 1000)->nullable();
                $table->string('btn_texto', 120)->nullable();
                $table->string('btn_url', 500)->nullable();
                $table->string('url_imagen_fondo', 500)->nullable();
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });

            DB::table('web_promo_banner')->insert([
                'id'               => 1,
                'subtitulo'        => '📍 Atención privada en Lima — Agenda únicamente con reserva',
                'titulo'           => 'Libera el Estrés<br>Reconecta Contigo',
                'btn_texto'        => 'WhatsApp 982 311 335',
                'btn_url'          => 'https://wa.me/51982311335',
                'url_imagen_fondo' => 'temp02/img/carousel-1.jpg',
                'Activo'           => 'S',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        if (!Schema::hasTable('web_experiencias')) {
            Schema::create('web_experiencias', function (Blueprint $table) {
                $table->increments('id_experiencia');
                $table->string('badge', 40)->nullable();
                $table->string('duracion', 80)->nullable();
                $table->string('titulo', 255)->nullable();
                $table->string('subtitulo', 255)->nullable();
                $table->text('descripcion')->nullable();
                $table->string('precio_nota', 120)->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->string('btn_texto', 80)->nullable()->default('Reservar');
                $table->string('btn_url', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });

            $exp = [
                ['badge' => '90 min', 'duracion' => '90 minutos', 'titulo' => 'Sabor Simple del Tantra', 'subtitulo' => 'Masaje Tántrico LITE', 'descripcion' => 'Ríndete a caricias suaves y sensuales en una escapada corta pero inolvidable. Perfecto para quienes desean un anticipo de la magia, incluso cuando el tiempo es escaso.', 'precio_nota' => 'Consultar precio', 'url_imagen' => 'temp02/img/gallery-1.jpg', 'orden' => 1],
                ['badge' => '120 min', 'duracion' => '120 minutos', 'titulo' => 'El Toque Sensual del Tantra', 'subtitulo' => 'Masaje Tántrico CLÁSICO', 'descripcion' => 'Sumérgete en un ritual profundamente indulgente donde la ternura se encuentra con el deseo. Cada caricia te lleva a una relajación profunda: un viaje exquisito para cuerpo y alma.', 'precio_nota' => 'Consultar precio', 'url_imagen' => 'temp02/img/gallery-3.jpg', 'orden' => 2],
                ['badge' => '150 min', 'duracion' => '150 minutos', 'titulo' => 'Masaje Tántrico de Lujo', 'subtitulo' => 'Masaje Tántrico DELUXE', 'descripcion' => 'Una experiencia de bienestar completa que combina todas las técnicas sensoriales y tántricas en un ambiente exclusivo diseñado para tu máximo placer y relajación.', 'precio_nota' => 'Consultar precio', 'url_imagen' => 'temp02/img/gallery-5.jpg', 'orden' => 3],
                ['badge' => '180 - 300 min', 'duracion' => '180 – 300 minutos', 'titulo' => 'Masaje Tántrico Royal', 'subtitulo' => 'Masaje Tántrico ROYAL', 'descripcion' => 'La experiencia definitiva: una sesión extendida que va más allá del masaje. Un ritual completo de reconexión, equilibrio energético y bienestar total para la mujer ejecutiva.', 'precio_nota' => 'Consultar precio', 'url_imagen' => 'temp02/img/gallery-7.jpg', 'orden' => 4],
            ];
            foreach ($exp as $row) {
                DB::table('web_experiencias')->insert(array_merge($row, [
                    'btn_texto'  => 'Reservar',
                    'btn_url'    => 'https://wa.me/51982311335',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        if (Schema::hasTable('web_pagina_galeria')) {
            Schema::table('web_pagina_galeria', function (Blueprint $table) {
                if (!Schema::hasColumn('web_pagina_galeria', 'categoria')) {
                    $table->string('categoria', 40)->default('todas')->after('contexto');
                }
                if (!Schema::hasColumn('web_pagina_galeria', 'titulo_overlay')) {
                    $table->string('titulo_overlay', 255)->nullable()->after('alt_imagen');
                }
            });

            if (DB::table('web_pagina_galeria')->count() === 0) {
                $imgs = [
                    ['gallery-1.jpg', 'Masaje Sensorial', 'sensorial'],
                    ['gallery-2.jpg', 'Relajación Profunda', 'relajacion'],
                    ['gallery-3.jpg', 'Masaje Tántrico', 'tantrico'],
                    ['gallery-4.jpg', 'Espacio Privado', 'vip'],
                    ['gallery-5.jpg', 'Masaje Sensorial', 'sensorial'],
                    ['gallery-6.jpg', 'Relajación', 'relajacion'],
                    ['gallery-7.jpg', 'Masaje Tántrico', 'tantrico'],
                    ['gallery-8.jpg', 'Espacio VIP', 'vip'],
                ];
                $orden = 1;
                foreach ($imgs as [$file, $titulo, $cat]) {
                    DB::table('web_pagina_galeria')->insert([
                        'contexto'       => 'royal_galeria',
                        'categoria'      => $cat,
                        'url_imagen'     => 'temp02/img/' . $file,
                        'alt_imagen'     => $titulo,
                        'titulo_overlay' => $titulo,
                        'orden'          => $orden++,
                        'Activo'         => 'S',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        if (Schema::hasTable('web_pagina_galeria_seccion') === false) {
            Schema::create('web_pagina_galeria_seccion', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary()->default(1);
                $table->string('seccion_titulo', 500)->nullable();
                $table->string('seccion_subtitulo', 1000)->nullable();
                $table->timestamps();
            });
            DB::table('web_pagina_galeria_seccion')->insert([
                'id'                  => 1,
                'seccion_titulo'      => 'Ambiente Diseñado Para Tus Sentidos',
                'seccion_subtitulo'   => 'Nuestra Galería',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        if (Schema::hasTable('web_header') && !Schema::hasColumn('web_header', 'topbar_promo')) {
            Schema::table('web_header', function (Blueprint $table) {
                $table->string('topbar_promo', 500)->nullable()->after('top_telefonos');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_experiencias');
        Schema::dropIfExists('web_promo_banner');
        Schema::dropIfExists('web_pagina_galeria_seccion');

        if (Schema::hasTable('web_pagina_galeria')) {
            Schema::table('web_pagina_galeria', function (Blueprint $table) {
                foreach (['categoria', 'titulo_overlay'] as $col) {
                    if (Schema::hasColumn('web_pagina_galeria', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('web_header') && Schema::hasColumn('web_header', 'topbar_promo')) {
            Schema::table('web_header', function (Blueprint $table) {
                $table->dropColumn('topbar_promo');
            });
        }
    }
};
