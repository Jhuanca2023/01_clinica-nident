<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_proyectos', function (Blueprint $table) {
            $table->id('id_proyecto');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['en_progreso','en_revision','completado','pausado'])->default('en_progreso');
            $table->date('fecha_entrega')->nullable();
            $table->integer('progreso')->default(0); // 0-100
            $table->string('icono', 50)->default('web'); // web, erp, ecommerce, education, landing, api
            $table->string('color', 20)->default('#2196f3');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_proyectos');
    }
};
