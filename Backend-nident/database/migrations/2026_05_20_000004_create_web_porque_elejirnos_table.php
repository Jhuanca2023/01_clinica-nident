<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_porque_elejirnos', function (Blueprint $table) {
            $table->id('id_porque');
            $table->string('badge_texto', 200)->default('¿Por qué elegir valentostile?');
            $table->string('titulo', 300)->default('Detrás de cada negocio exitoso hay una gran estrategia digital');
            $table->text('descripcion')->nullable();
            $table->json('beneficios')->nullable();
            $table->unsignedSmallInteger('stat1_numero')->default(500);
            $table->string('stat1_sufijo', 10)->default('+');
            $table->string('stat1_texto', 200)->default('Negocios que ya confían en valentostile para crecer en redes sociales');
            $table->unsignedSmallInteger('stat2_numero')->default(94);
            $table->string('stat2_sufijo', 10)->default('%');
            $table->string('stat2_texto', 200)->default('De clientes satisfechos que renuevan su plan mes a mes con nosotros');
            $table->unsignedSmallInteger('stat3_numero')->default(120);
            $table->string('stat3_sufijo', 10)->default('S/.');
            $table->string('stat3_texto', 200)->default('Desde este precio mensual tienes todo incluido: diseño, campañas y seguimiento');
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_porque_elejirnos')->insert([
            'badge_texto'   => '¿Por qué elegir valentostile?',
            'titulo'        => 'Detrás de cada negocio exitoso hay una gran estrategia digital',
            'descripcion'   => 'Nos encargamos de toda tu presencia en redes sociales para que tú te concentres en hacer crecer tu negocio. Resultados reales desde el primer mes.',
            'beneficios'    => json_encode([
                'Publicaciones diarias en tus redes sociales',
                'Campañas publicitarias con resultados reales',
                'Diseños profesionales y llamativos',
                'Asesoría directa para vender más',
            ]),
            'stat1_numero'  => 500,
            'stat1_sufijo'  => '+',
            'stat1_texto'   => 'Negocios que ya confían en valentostile para crecer en redes sociales',
            'stat2_numero'  => 94,
            'stat2_sufijo'  => '%',
            'stat2_texto'   => 'De clientes satisfechos que renuevan su plan mes a mes con nosotros',
            'stat3_numero'  => 120,
            'stat3_sufijo'  => 'S/.',
            'stat3_texto'   => 'Desde este precio mensual tienes todo incluido: diseño, campañas y seguimiento',
            'Activo'        => 'S',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_porque_elejirnos');
    }
};
