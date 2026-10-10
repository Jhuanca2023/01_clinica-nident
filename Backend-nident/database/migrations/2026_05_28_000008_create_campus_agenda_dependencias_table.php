<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campus_agenda_dependencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tarea');        // tarea sucesora
            $table->unsignedBigInteger('id_predecesora');  // debe terminar antes
            $table->timestamps();

            $table->unique(['id_tarea', 'id_predecesora']);

            $table->foreign('id_tarea')
                  ->references('id_tarea')
                  ->on('campus_agenda_tareas')
                  ->onDelete('cascade');

            $table->foreign('id_predecesora')
                  ->references('id_tarea')
                  ->on('campus_agenda_tareas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('campus_agenda_dependencias');
    }
};
