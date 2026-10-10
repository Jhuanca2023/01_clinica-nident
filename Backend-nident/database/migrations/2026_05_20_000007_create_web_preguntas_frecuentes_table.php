<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_preguntas_frecuentes', function (Blueprint $table) {
            $table->increments('id_pregunta');
            $table->string('badge_seccion', 200)->nullable();
            $table->string('seccion_titulo', 500)->nullable();
            $table->string('telefono_principal_label', 150)->nullable();
            $table->string('telefono_principal', 50)->nullable();
            $table->string('telefono_callcenter_label', 150)->nullable();
            $table->string('telefono_callcenter', 50)->nullable();
            $table->string('pregunta', 500)->nullable();
            $table->text('respuesta')->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_preguntas_frecuentes')->insert([
            [
                'badge_seccion'             => 'Preguntas Frecuentes',
                'seccion_titulo'            => '¿Tienes dudas? Te respondemos',
                'telefono_principal_label'  => 'Llámanos directamente:',
                'telefono_principal'        => '939 691 536',
                'telefono_callcenter_label' => 'Call Center:',
                'telefono_callcenter'       => '901 967 564',
                'pregunta'                  => '¿Cuánto cuesta el servicio de manejo de redes sociales?',
                'respuesta'                 => 'Nuestros planes están diseñados para adaptarse a cualquier presupuesto. Contamos con opciones desde básicas hasta premium. Contáctanos y te asesoramos sin compromiso.',
                'orden'                     => 1,
                'Activo'                    => 'S',
                'created_at'                => now(),
                'updated_at'                => now(),
            ],
            [
                'badge_seccion'             => 'Preguntas Frecuentes',
                'seccion_titulo'            => '¿Tienes dudas? Te respondemos',
                'telefono_principal_label'  => 'Llámanos directamente:',
                'telefono_principal'        => '939 691 536',
                'telefono_callcenter_label' => 'Call Center:',
                'telefono_callcenter'       => '901 967 564',
                'pregunta'                  => '¿Qué incluye exactamente el servicio?',
                'respuesta'                 => 'El servicio incluye gestión de redes sociales, diseño de contenido profesional, campañas publicitarias, seguimiento de resultados y asesoría personalizada según el plan que elijas.',
                'orden'                     => 2,
                'Activo'                    => 'S',
                'created_at'                => now(),
                'updated_at'                => now(),
            ],
            [
                'badge_seccion'             => 'Preguntas Frecuentes',
                'seccion_titulo'            => '¿Tienes dudas? Te respondemos',
                'telefono_principal_label'  => 'Llámanos directamente:',
                'telefono_principal'        => '939 691 536',
                'telefono_callcenter_label' => 'Call Center:',
                'telefono_callcenter'       => '901 967 564',
                'pregunta'                  => '¿En cuánto tiempo veré resultados?',
                'respuesta'                 => 'Los primeros resultados se empiezan a notar desde el primer mes. Sin embargo, el crecimiento sostenido y significativo se evidencia entre los 2 y 3 meses de trabajo constante.',
                'orden'                     => 3,
                'Activo'                    => 'S',
                'created_at'                => now(),
                'updated_at'                => now(),
            ],
            [
                'badge_seccion'             => 'Preguntas Frecuentes',
                'seccion_titulo'            => '¿Tienes dudas? Te respondemos',
                'telefono_principal_label'  => 'Llámanos directamente:',
                'telefono_principal'        => '939 691 536',
                'telefono_callcenter_label' => 'Call Center:',
                'telefono_callcenter'       => '901 967 564',
                'pregunta'                  => '¿Trabajan con cualquier tipo de negocio?',
                'respuesta'                 => 'Sí, trabajamos con negocios de todos los rubros: restaurantes, tiendas, clínicas, servicios, e-commerce y más. Adaptamos la estrategia digital al tipo de negocio y público objetivo.',
                'orden'                     => 4,
                'Activo'                    => 'S',
                'created_at'                => now(),
                'updated_at'                => now(),
            ],
            [
                'badge_seccion'             => 'Preguntas Frecuentes',
                'seccion_titulo'            => '¿Tienes dudas? Te respondemos',
                'telefono_principal_label'  => 'Llámanos directamente:',
                'telefono_principal'        => '939 691 536',
                'telefono_callcenter_label' => 'Call Center:',
                'telefono_callcenter'       => '901 967 564',
                'pregunta'                  => '¿Cómo me contacto para empezar?',
                'respuesta'                 => 'Puedes escribirnos por WhatsApp, llamarnos directamente o llenar el formulario en nuestra web. Un asesor te responderá en menos de 24 horas para coordinar una reunión sin costo.',
                'orden'                     => 5,
                'Activo'                    => 'S',
                'created_at'                => now(),
                'updated_at'                => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_preguntas_frecuentes');
    }
};
