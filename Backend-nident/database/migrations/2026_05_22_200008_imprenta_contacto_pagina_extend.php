<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('web_pagina_contacto')) {
            Schema::table('web_pagina_contacto', function (Blueprint $table) {
                if (!Schema::hasColumn('web_pagina_contacto', 'form_descripcion')) {
                    $table->longText('form_descripcion')->nullable()->after('form_subtitulo');
                    $table->text('telefonos_texto')->nullable();
                    $table->text('emails_texto')->nullable();
                    $table->string('productos_placeholder', 255)->nullable();
                    $table->string('suscribe_titulo', 255)->nullable();
                    $table->text('suscribe_texto')->nullable();
                    $table->string('suscribe_placeholder', 255)->nullable();
                }
            });

            $mapa = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.79!2d-77.0426!3d-12.0501!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c8f8c8c8c8c8%3A0x0!2zSm4uIEp1bmluIDE3NzYsIExpbWE!5e0!3m2!1ses!2spe!4v1700000000000';

            DB::table('web_pagina_contacto')->where('id', 1)->update([
                'form_titulo'          => 'Contáctanos para más información',
                'form_descripcion'   => '¡Dale un toque profesional a tus documentos con nuestras espiraladoras, enmicadoras y equipos de encuadernación! Ofrecemos soluciones de impresión exprés y productos de calidad. ¡Haz que cada página cuente y deje una impresión duradera!',
                'telefonos_texto'      => "981629466\n958444413\n992886659",
                'emails_texto'         => "jyhimportaciones@hotmail.com\nhuber_06_15@hotmail.com",
                'ubicacion'            => 'Jr. Junín 1776 interior 20, Cercado de Lima — al frente de la estación de tren',
                'productos_placeholder'=> 'Anillos de Plástico',
                'mapa_embed_url'       => $mapa,
                'suscribe_titulo'      => 'Suscríbete a nuestro boletín',
                'suscribe_texto'       => 'Escríbenos tu correo y te contactaremos con novedades y ofertas.',
                'suscribe_placeholder' => 'Tu correo electrónico',
                'updated_at'           => now(),
            ]);
        }

        if (Schema::hasTable('web_contacto_mensaje')) {
            Schema::table('web_contacto_mensaje', function (Blueprint $table) {
                if (!Schema::hasColumn('web_contacto_mensaje', 'producto_interes')) {
                    $table->string('producto_interes', 255)->nullable()->after('asunto');
                    $table->date('fecha_mensaje')->nullable()->after('producto_interes');
                }
            });
        }

        if (!Schema::hasTable('web_contacto_columna')) {
            Schema::create('web_contacto_columna', function (Blueprint $table) {
                $table->increments('id');
                $table->string('titulo', 500)->nullable();
                $table->text('descripcion')->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });

            $cols = [
                [
                    'titulo'      => '¡Soluciones rápidas y eficientes en encuadernación y laminación!',
                    'descripcion' => 'Con nosotros, podrás contar con trabajos impecables y entregas puntuales que se adaptan a tus necesidades específicas. No dudes en contactarnos si necesitas asistencia.',
                    'orden'       => 1,
                ],
                [
                    'titulo'      => 'Fabricación y distribución exprés: ¡Calidad y rapidez garantizadas!',
                    'descripcion' => 'Desde la elección de los materiales hasta el diseño final, en Liam Vercceli nos aseguramos de que cada producto refleje la profesionalidad que buscas. ¡Comunícate con nosotros para una atención personalizada!',
                    'orden'       => 2,
                ],
                [
                    'titulo'      => '¡Tu aliado en proyectos de impresión y papelería profesional!',
                    'descripcion' => 'Ya sea que necesites espirales de PVC, guillotinas o encuadernadoras, en Liam Vercceli tenemos todo lo que necesitas para completar tus proyectos de forma rápida y eficiente. Contáctanos para más información sobre nuestros productos y servicios.',
                    'orden'       => 3,
                ],
            ];
            foreach ($cols as $c) {
                DB::table('web_contacto_columna')->insert(array_merge($c, [
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_contacto_columna');
    }
};
