<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de menús más visitados por usuario (estilo Amazon).
 * Prefijo / nombre: menu_historial_sessiones
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_historial_sessiones')) {
            return;
        }

        Schema::create('menu_historial_sessiones', function (Blueprint $table) {
            $table->bigIncrements('id_historial');
            $table->unsignedBigInteger('id_usuario');
            $table->string('path', 255);
            $table->string('titulo', 150)->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedInteger('visitas')->default(0);
            $table->timestamp('ultima_visita')->nullable();
            $table->timestamps();

            $table->unique(['id_usuario', 'path'], 'uq_menu_hist_sess_usuario_path');
            $table->index(['id_usuario', 'visitas', 'ultima_visita'], 'idx_menu_hist_sess_ranking');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_historial_sessiones');
    }
};
