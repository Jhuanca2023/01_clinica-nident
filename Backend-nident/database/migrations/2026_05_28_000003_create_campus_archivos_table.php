<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_archivos', function (Blueprint $table) {
            $table->id('id_archivo');
            $table->unsignedBigInteger('id_proyecto');
            $table->enum('modulo', ['documentos','boletas','presentaciones','manuales']);
            $table->string('nombre', 200);
            $table->string('descripcion', 500)->nullable();
            $table->string('ruta_archivo', 500); // storage path
            $table->string('nombre_original', 200);
            $table->string('extension', 20);
            $table->unsignedBigInteger('tamano')->default(0); // bytes
            $table->string('subido_por', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->foreign('id_proyecto')->references('id_proyecto')->on('campus_proyectos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_archivos');
    }
};
