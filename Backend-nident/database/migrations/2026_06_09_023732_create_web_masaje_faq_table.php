<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('web_masaje_faq', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 300);
            $table->longText('contenido');
            $table->string('icono', 80)->default('fas fa-spa');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->string('Activo', 1)->default('S');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_masaje_faq');
    }
};
