<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_enlaces', function (Blueprint $table) {
            $table->id('id_enlace');
            $table->unsignedBigInteger('id_proyecto');
            $table->string('nombre', 150);
            $table->string('descripcion', 300)->nullable();
            $table->string('url', 1000);
            $table->enum('categoria', ['github','gitlab','hosting','dominio','figma','drive','api','servidor','panel','otro'])->default('otro');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->foreign('id_proyecto')->references('id_proyecto')->on('campus_proyectos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_enlaces');
    }
};
