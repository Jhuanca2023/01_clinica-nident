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
        Schema::create('administracion_backups', function (Blueprint $table) {
            $table->increments('id_backup');
            $table->string('nombre_archivo', 255);
            $table->string('ruta', 500);
            $table->unsignedBigInteger('tamaño')->nullable();
            $table->string('tipo', 50)->default('database');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_backups');
    }
};
