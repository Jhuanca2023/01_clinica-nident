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
        Schema::create('product_user_ratings', function (Blueprint $table) {
            $table->id();
            $table->integer('id_producto');
            $table->unsignedBigInteger('id_usuario');
            $table->tinyInteger('rating');
            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('administracion_producto')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['id_producto', 'id_usuario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_user_ratings');
    }
};
