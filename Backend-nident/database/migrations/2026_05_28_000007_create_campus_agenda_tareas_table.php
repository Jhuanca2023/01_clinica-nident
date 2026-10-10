<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campus_agenda_tareas', function (Blueprint $table) {
            $table->id('id_tarea');
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_fase')->nullable();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('responsable', 120)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['pendiente','en_progreso','en_revision','completado','bloqueado'])
                  ->default('pendiente');
            $table->enum('prioridad', ['baja','media','alta','critica'])->default('media');
            $table->tinyInteger('porcentaje_avance')->default(0);
            $table->boolean('es_hito')->default(false);
            $table->string('color', 20)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->foreign('id_proyecto')
                  ->references('id_proyecto')
                  ->on('campus_proyectos')
                  ->onDelete('cascade');

            $table->foreign('id_fase')
                  ->references('id_fase')
                  ->on('campus_agenda_fases')
                  ->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('campus_agenda_tareas');
    }
};
