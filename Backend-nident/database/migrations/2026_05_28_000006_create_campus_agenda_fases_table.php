<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campus_agenda_fases', function (Blueprint $table) {
            $table->id('id_fase');
            $table->unsignedBigInteger('id_proyecto');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('color', 20)->default('#1976d2');
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->foreign('id_proyecto')
                  ->references('id_proyecto')
                  ->on('campus_proyectos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('campus_agenda_fases');
    }
};
