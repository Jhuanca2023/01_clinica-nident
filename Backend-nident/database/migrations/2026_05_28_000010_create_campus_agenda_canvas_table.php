<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campus_agenda_canvas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proyecto');
            $table->longText('estado_json');       // JSON serializado del Canvas
            $table->string('version', 20)->default('1.0');
            $table->unsignedBigInteger('guardado_por')->nullable();
            $table->timestamps();

            $table->foreign('id_proyecto')
                  ->references('id_proyecto')
                  ->on('campus_proyectos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('campus_agenda_canvas');
    }
};
