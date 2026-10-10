<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_actividad', function (Blueprint $table) {
            $table->id('id_actividad');
            $table->unsignedBigInteger('id_proyecto');
            $table->string('descripcion', 300);
            $table->string('tipo', 50)->default('archivo'); // archivo, enlace, proyecto
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamps();
            $table->foreign('id_proyecto')->references('id_proyecto')->on('campus_proyectos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_actividad');
    }
};
