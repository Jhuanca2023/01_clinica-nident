<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_servicios', function (Blueprint $table) {
            $table->increments('id_servicio');
            $table->string('seccion_titulo', 500)->nullable();
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_icono', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_servicios')->insert([
            [
                'seccion_titulo' => 'Todo lo que tu marca necesita para vender en redes sociales',
                'titulo'         => 'Campañas Publicitarias Efectivas',
                'descripcion'    => 'Creamos y gestionamos campañas en Facebook e Instagram diseñadas para atraer clientes reales y generar ventas desde el primer mes.',
                'url_icono'      => null,
                'orden'          => 1,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'seccion_titulo' => 'Todo lo que tu marca necesita para vender en redes sociales',
                'titulo'         => 'Diseño Profesional de Flyers y Videos',
                'descripcion'    => 'Publicaciones visuales y llamativas que representan tu marca con estilo y generan interacción en tus redes sociales.',
                'url_icono'      => null,
                'orden'          => 2,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'seccion_titulo' => 'Todo lo que tu marca necesita para vender en redes sociales',
                'titulo'         => 'Seguimiento Diario de Resultados',
                'descripcion'    => 'Monitoreamos el desempeño de tus publicaciones y campañas cada día para optimizar y maximizar tu inversión.',
                'url_icono'      => null,
                'orden'          => 3,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'seccion_titulo' => 'Todo lo que tu marca necesita para vender en redes sociales',
                'titulo'         => 'Asesoría Directa para Vender Más',
                'descripcion'    => 'Te acompañamos con estrategias personalizadas para que conviertas cada mensaje en una venta. Deja tu marca en manos de expertos.',
                'url_icono'      => null,
                'orden'          => 4,
                'Activo'         => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_servicios');
    }
};
