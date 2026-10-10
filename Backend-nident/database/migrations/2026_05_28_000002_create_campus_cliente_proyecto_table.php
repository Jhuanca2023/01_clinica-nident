<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_cliente_proyecto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_proyecto');
            $table->timestamps();
            $table->unique(['id_user','id_proyecto']);
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_proyecto')->references('id_proyecto')->on('campus_proyectos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_cliente_proyecto');
    }
};
