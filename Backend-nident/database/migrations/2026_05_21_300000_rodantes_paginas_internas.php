<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_pagina_nosotros')) {
            Schema::create('web_pagina_nosotros', function (Blueprint $table) {
                $table->increments('id');
                $table->string('banner_titulo', 255)->nullable();
                $table->string('galeria_titulo', 500)->nullable();
                $table->string('galeria_subtitulo', 1000)->nullable();
                $table->timestamps();
            });
            DB::table('web_pagina_nosotros')->insert([
                'banner_titulo'     => 'Nosotros',
                'galeria_titulo'    => 'Galería de <span class="color-primary">Eventos</span>',
                'galeria_subtitulo' => 'Momentos de lectura y cultura en nuestras comunidades',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        if (!Schema::hasTable('web_pagina_marca')) {
            Schema::create('web_pagina_marca', function (Blueprint $table) {
                $table->increments('id_marca');
                $table->string('nombre', 120)->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_pagina_galeria')) {
            Schema::create('web_pagina_galeria', function (Blueprint $table) {
                $table->increments('id_galeria');
                $table->string('contexto', 40)->default('pagina_nosotros');
                $table->string('url_imagen', 500)->nullable();
                $table->string('alt_imagen', 255)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_pagina_contacto')) {
            Schema::create('web_pagina_contacto', function (Blueprint $table) {
                $table->increments('id');
                $table->string('banner_titulo', 255)->nullable();
                $table->string('form_titulo', 255)->nullable();
                $table->text('form_subtitulo')->nullable();
                $table->string('url_imagen_form', 500)->nullable();
                $table->text('mapa_embed_url')->nullable();
                $table->string('info_titulo', 255)->nullable();
                $table->text('info_texto')->nullable();
                $table->string('telefono_etiqueta', 120)->nullable();
                $table->string('telefono', 80)->nullable();
                $table->string('email_etiqueta', 120)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('ubicacion_etiqueta', 120)->nullable();
                $table->text('ubicacion')->nullable();
                $table->string('horario_etiqueta', 120)->nullable();
                $table->string('horario_linea1', 120)->nullable();
                $table->string('horario_linea2', 120)->nullable();
                $table->string('horario_dias', 120)->nullable();
                $table->timestamps();
            });
            DB::table('web_pagina_contacto')->insert([
                'banner_titulo'    => 'Contáctanos',
                'form_titulo'      => 'Escríbenos',
                'form_subtitulo'   => '¡Nos encantaría escucharte! Cuéntanos tu idea, tu comunidad o tu escuela.',
                'url_imagen_form'  => 'temp02/assets/image/inicio/atencion.jpg',
                'mapa_embed_url'   => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.5!2d-79.9!3d-6.77!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNDYnMDAuMCJTIDc5wrU0JzAwLjAiVw!5e0!3m2!1ses!2spe!4v1',
                'info_titulo'      => 'Ponte en contacto',
                'info_texto'       => 'Coordinamos visitas, talleres y conversatorios en Lambayeque y otras regiones.',
                'telefono_etiqueta'=> 'Teléfono',
                'telefono'         => '934 200 140',
                'email_etiqueta'   => 'Correo',
                'email'            => 'contacto@bibliotecasrodantes.pe',
                'ubicacion_etiqueta'=> 'Ubicación',
                'ubicacion'        => 'Lambayeque, Perú',
                'horario_etiqueta' => 'Horario',
                'horario_linea1'   => '08:00 am - 01:00 pm',
                'horario_linea2'   => 'Lunes - Viernes',
                'horario_dias'     => 'Sábado: 09:00 am - 12:00 pm',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        if (Schema::hasTable('web_publicaciones')) {
            Schema::table('web_publicaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('web_publicaciones', 'slug')) {
                    $table->string('slug', 160)->nullable()->unique()->after('titulo');
                }
                if (!Schema::hasColumn('web_publicaciones', 'contenido')) {
                    $table->longText('contenido')->nullable()->after('resumen');
                }
                if (!Schema::hasColumn('web_publicaciones', 'autor')) {
                    $table->string('autor', 120)->nullable()->after('contenido');
                }
                if (!Schema::hasColumn('web_publicaciones', 'categoria')) {
                    $table->string('categoria', 120)->nullable()->after('autor');
                }
                if (!Schema::hasColumn('web_publicaciones', 'fecha_publicacion')) {
                    $table->date('fecha_publicacion')->nullable()->after('categoria');
                }
            });

            $rows = DB::table('web_publicaciones')
                ->where(function ($q) {
                    $q->whereNull('slug')->orWhere('slug', '');
                })
                ->orderBy('id_publicacion')
                ->get();
            foreach ($rows as $row) {
                $slug = \Illuminate\Support\Str::slug($row->titulo ?? 'publicacion-' . $row->id_publicacion);
                DB::table('web_publicaciones')->where('id_publicacion', $row->id_publicacion)->update([
                    'slug'              => $slug,
                    'fecha_publicacion' => $row->fecha_publicacion ?? now()->toDateString(),
                    'autor'             => $row->autor ?? 'Bibliotecas Rodantes',
                    'categoria'         => $row->categoria ?? 'Publicación',
                ]);
            }
        }

        if (Schema::hasTable('web_contadores') && !Schema::hasColumn('web_contadores', 'contexto')) {
            Schema::table('web_contadores', function (Blueprint $table) {
                $table->string('contexto', 40)->default('landing')->after('id_contador');
            });
            DB::table('web_contadores')->whereNull('contexto')->update(['contexto' => 'landing']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_pagina_galeria');
        Schema::dropIfExists('web_pagina_marca');
        Schema::dropIfExists('web_pagina_contacto');
        Schema::dropIfExists('web_pagina_nosotros');

        if (Schema::hasTable('web_publicaciones')) {
            Schema::table('web_publicaciones', function (Blueprint $table) {
                foreach (['slug', 'contenido', 'autor', 'categoria', 'fecha_publicacion'] as $col) {
                    if (Schema::hasColumn('web_publicaciones', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('web_contadores') && Schema::hasColumn('web_contadores', 'contexto')) {
            Schema::table('web_contadores', function (Blueprint $table) {
                $table->dropColumn('contexto');
            });
        }
    }
};
