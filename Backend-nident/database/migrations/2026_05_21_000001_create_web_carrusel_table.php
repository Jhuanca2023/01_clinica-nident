<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_carrusel', function (Blueprint $table) {
            $table->increments('id_carrusel');
            $table->string('label', 200)->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->integer('orden')->default(0);
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_carrusel')->insert([
            ['label' => 'Estrategia Digital',       'url_imagen' => null, 'orden' => 1, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'Gestión de Redes',          'url_imagen' => null, 'orden' => 2, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'Campañas Publicitarias',    'url_imagen' => null, 'orden' => 3, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'Resultados Medibles',       'url_imagen' => null, 'orden' => 4, 'Activo' => 'S', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_carrusel');
    }
};
