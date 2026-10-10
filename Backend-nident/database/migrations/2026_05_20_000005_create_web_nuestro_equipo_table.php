<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_nuestro_equipo', function (Blueprint $table) {
            $table->id('id_miembro');
            $table->string('seccion_badge', 200)->nullable();
            $table->string('seccion_titulo', 300)->nullable();
            $table->string('cargo', 120);
            $table->string('titulo', 220);
            $table->text('descripcion')->nullable();
            $table->string('url_facebook', 400)->nullable();
            $table->string('url_whatsapp', 400)->nullable();
            $table->string('url_imagen', 400)->nullable();
            $table->unsignedTinyInteger('orden')->default(99);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_nuestro_equipo')->insert([
            [
                'seccion_badge'  => 'Nuestro Equipo',
                'seccion_titulo' => 'Expertos en marketing digital a tu servicio',
                'cargo'          => 'Estratega Digital',
                'titulo'         => 'Especialista en Campañas',
                'descripcion'    => 'Diseña estrategias de publicidad en Facebook e Instagram que conectan tu marca con clientes reales y generan ventas todos los días.',
                'url_facebook'   => 'https://www.facebook.com/socialmediaIG/',
                'url_whatsapp'   => '#',
                'url_imagen'     => 'temp02/assets/img/team-img-1.jpg',
                'orden'          => 1,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'seccion_badge'  => null,
                'seccion_titulo' => null,
                'cargo'          => 'Diseñador Gráfico',
                'titulo'         => 'Creativo de Contenidos',
                'descripcion'    => 'Crea flyers, videos y publicaciones visualmente impactantes que destacan tu negocio en el feed y atraen la atención de tu público.',
                'url_facebook'   => 'https://www.facebook.com/socialmediaIG/',
                'url_whatsapp'   => '#',
                'url_imagen'     => 'temp02/assets/img/team-img-2.jpg',
                'orden'          => 2,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'seccion_badge'  => null,
                'seccion_titulo' => null,
                'cargo'          => 'Community Manager',
                'titulo'         => 'Gestora de Redes Sociales',
                'descripcion'    => 'Se encarga de mantener activas tus redes, responder mensajes y crear comunidad alrededor de tu marca para fidelizar clientes.',
                'url_facebook'   => 'https://www.facebook.com/socialmediaIG/',
                'url_whatsapp'   => '#',
                'url_imagen'     => 'temp02/assets/img/team-img-3.jpg',
                'orden'          => 3,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'seccion_badge'  => null,
                'seccion_titulo' => null,
                'cargo'          => 'Asesor de Ventas',
                'titulo'         => 'Consultor de Resultados',
                'descripcion'    => 'Te guía para convertir cada interacción en redes sociales en una oportunidad de venta real. Seguimiento diario y estrategia personalizada.',
                'url_facebook'   => 'https://www.facebook.com/socialmediaIG/',
                'url_whatsapp'   => '#',
                'url_imagen'     => 'temp02/assets/img/team-img-4.jpg',
                'orden'          => 4,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_nuestro_equipo');
    }
};
