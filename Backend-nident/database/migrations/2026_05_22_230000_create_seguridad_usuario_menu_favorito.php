<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seguridad_usuario_menu_favorito')) {
            return;
        }

        Schema::create('seguridad_usuario_menu_favorito', function (Blueprint $table) {
            $table->increments('id_favorito');
            $table->unsignedBigInteger('id_usuario');
            $table->string('path', 255);
            $table->unsignedInteger('id_menu')->nullable();
            $table->unsignedInteger('id_modulo')->nullable();
            $table->string('nombre', 150);
            $table->string('icon', 80)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->unsignedInteger('visitas')->default(0);
            $table->timestamp('ultima_visita')->nullable();
            $table->timestamps();

            $table->unique(['id_usuario', 'path'], 'uq_usuario_menu_favorito_path');
            $table->index(['id_usuario', 'ultima_visita'], 'idx_favorito_usuario_visita');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_usuario_menu_favorito');
    }
};
