<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_testimonios', function (Blueprint $table) {
            $table->increments('id_testimonio');
            $table->string('badge_seccion', 200)->nullable();
            $table->string('seccion_titulo', 500)->nullable();
            $table->string('nombre', 150)->nullable();
            $table->string('subtitulo', 200)->nullable();
            $table->text('testimonio')->nullable();
            $table->decimal('calificacion', 3, 1)->default(5.0);
            $table->string('url_avatar', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_testimonios')->insert([
            [
                'badge_seccion'  => 'TESTIMONIOS Y RESEÑAS',
                'seccion_titulo' => 'Negocios reales. Resultados reales. Clientes felices.',
                'nombre'         => 'Cliente Satisfecho',
                'subtitulo'      => 'Cliente de valentostile',
                'testimonio'     => '"Llevaba meses intentando manejar mis redes solo y sin resultados. Con valentostile empecé a vender desde el primer mes. ¡Los recomiendo al 100%!"',
                'calificacion'   => 4.8,
                'url_avatar'     => null,
                'orden'          => 1,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'badge_seccion'  => 'TESTIMONIOS Y RESEÑAS',
                'seccion_titulo' => 'Negocios reales. Resultados reales. Clientes felices.',
                'nombre'         => 'Cliente Satisfecho',
                'subtitulo'      => 'Cliente de valentostile',
                'testimonio'     => '"Contraté el Plan Básico y en la primera semana ya tenía clientes escribiéndome. El equipo es muy profesional y siempre están disponibles para ayudar."',
                'calificacion'   => 4.8,
                'url_avatar'     => null,
                'orden'          => 2,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'badge_seccion'  => 'TESTIMONIOS Y RESEÑAS',
                'seccion_titulo' => 'Negocios reales. Resultados reales. Clientes felices.',
                'nombre'         => 'Cliente Satisfecho',
                'subtitulo'      => 'Cliente de valentostile',
                'testimonio'     => '"¡Increíble! Antes no vendía nada por Facebook y ahora recibo mensajes de clientes todos los días. valentostile transformó mi negocio por completo."',
                'calificacion'   => 4.8,
                'url_avatar'     => null,
                'orden'          => 3,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'badge_seccion'  => 'TESTIMONIOS Y RESEÑAS',
                'seccion_titulo' => 'Negocios reales. Resultados reales. Clientes felices.',
                'nombre'         => 'Cliente Satisfecho',
                'subtitulo'      => 'Cliente de valentostile',
                'testimonio'     => '"¡Increíble! Antes no vendía nada por Facebook y ahora recibo mensajes de clientes todos los días. valentostile transformó mi negocio por completo."',
                'calificacion'   => 4.8,
                'url_avatar'     => null,
                'orden'          => 4,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'badge_seccion'  => 'TESTIMONIOS Y RESEÑAS',
                'seccion_titulo' => 'Negocios reales. Resultados reales. Clientes felices.',
                'nombre'         => 'Cliente Satisfecho',
                'subtitulo'      => 'Cliente de valentostile',
                'testimonio'     => '"Llevaba meses intentando manejar mis redes solo y sin resultados. Con valentostile empecé a vender desde el primer mes. ¡Los recomiendo al 100%!"',
                'calificacion'   => 4.8,
                'url_avatar'     => null,
                'orden'          => 5,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_testimonios');
    }
};
