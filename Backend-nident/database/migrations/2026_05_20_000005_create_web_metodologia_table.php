<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_metodologia', function (Blueprint $table) {
            $table->increments('id_metodologia');
            $table->string('badge_seccion', 200)->nullable();
            $table->string('seccion_titulo', 500)->nullable();
            $table->integer('paso')->default(1);
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_metodologia')->insert([
            [
                'badge_seccion'  => '¿CÓMO TRABAJAMOS?',
                'seccion_titulo' => 'Así impulsamos tu negocio en 3 pasos',
                'paso'           => 1,
                'titulo'         => 'Nos contactas y cotizamos',
                'descripcion'    => 'Escríbenos por WhatsApp o llámanos. Te damos una asesoría gratuita y diseñamos el plan ideal para tu negocio.',
                'url_imagen'     => 'temp02/assets/img/works-icon-1.png',
                'orden'          => 1,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'badge_seccion'  => '¿CÓMO TRABAJAMOS?',
                'seccion_titulo' => 'Así impulsamos tu negocio en 3 pasos',
                'paso'           => 2,
                'titulo'         => 'Creamos tu estrategia digital',
                'descripcion'    => 'Nuestro equipo diseña campañas, flyers y contenido profesional adaptado a tu marca y a tu público objetivo.',
                'url_imagen'     => 'temp02/assets/img/works-icon-2.png',
                'orden'          => 2,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'badge_seccion'  => '¿CÓMO TRABAJAMOS?',
                'seccion_titulo' => 'Así impulsamos tu negocio en 3 pasos',
                'paso'           => 3,
                'titulo'         => 'Lanzamos y medimos resultados',
                'descripcion'    => 'Publicamos, gestionamos tus redes y hacemos seguimiento diario para que recibas mensajes de clientes todos los días.',
                'url_imagen'     => 'temp02/assets/img/works-icon-3.png',
                'orden'          => 3,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_metodologia');
    }
};
