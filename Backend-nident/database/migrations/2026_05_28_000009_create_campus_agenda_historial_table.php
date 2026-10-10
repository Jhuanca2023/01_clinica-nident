<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campus_agenda_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_tarea')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('accion', 80);        // 'mover', 'crear', 'editar', 'eliminar', 'avance'
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_proyecto')
                  ->references('id_proyecto')
                  ->on('campus_proyectos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('campus_agenda_historial');
    }
};
