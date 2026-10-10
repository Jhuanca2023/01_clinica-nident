<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_planes', function (Blueprint $table) {
            $table->increments('id_plan');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 8, 2)->default(0);
            $table->string('precio_nota', 150)->nullable();
            $table->json('caracteristicas')->nullable();
            $table->string('url_whatsapp', 500)->nullable();
            $table->tinyInteger('es_destacado')->default(0);
            $table->string('icono', 10)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_planes')->insert([
            [
                'nombre'          => 'Plan Básico',
                'descripcion'     => 'Ideal para emprendedores que recién comienzan su presencia en redes sociales.',
                'precio'          => 120.00,
                'precio_nota'     => 'Sin permanencia mínima',
                'caracteristicas' => json_encode(['Gestión de 1 red social', '12 publicaciones al mes', 'Diseño de flyers profesionales', 'Seguimiento mensual', 'Asesoría básica de ventas']),
                'url_whatsapp'    => '#',
                'es_destacado'    => 0,
                'icono'           => '💡',
                'orden'           => 1,
                'Activo'          => 'S',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nombre'          => 'Plan Estándar',
                'descripcion'     => 'Para negocios que quieren crecer y generar más clientes desde sus redes.',
                'precio'          => 200.00,
                'precio_nota'     => 'Sin permanencia mínima',
                'caracteristicas' => json_encode(['Gestión de 2 redes sociales', '20 publicaciones al mes', 'Campaña publicitaria incluida', 'Seguimiento semanal', 'Asesoría directa de ventas']),
                'url_whatsapp'    => '#',
                'es_destacado'    => 0,
                'icono'           => '🚀',
                'orden'           => 2,
                'Activo'          => 'S',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nombre'          => 'Plan Premium',
                'descripcion'     => 'La solución completa para negocios que quieren dominar las redes sociales.',
                'precio'          => 350.00,
                'precio_nota'     => 'Sin permanencia mínima',
                'caracteristicas' => json_encode(['Gestión de 3 redes sociales', '30 publicaciones al mes + videos', 'Campañas publicitarias avanzadas', 'Seguimiento diario de resultados', 'Asesoría VIP y atención prioritaria']),
                'url_whatsapp'    => '#',
                'es_destacado'    => 1,
                'icono'           => '⚡',
                'orden'           => 3,
                'Activo'          => 'S',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nombre'          => 'Plan Élite',
                'descripcion'     => 'Para grandes empresas que buscan máxima visibilidad y resultados extraordinarios.',
                'precio'          => 500.00,
                'precio_nota'     => 'Sin permanencia mínima',
                'caracteristicas' => json_encode(['Gestión de 5 redes sociales', '50 publicaciones al mes + videos', 'Campañas multi-plataforma', 'Reportes detallados semanales', 'Asesoría VIP dedicada 24/7']),
                'url_whatsapp'    => '#',
                'es_destacado'    => 0,
                'icono'           => '👑',
                'orden'           => 4,
                'Activo'          => 'S',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_planes');
    }
};
