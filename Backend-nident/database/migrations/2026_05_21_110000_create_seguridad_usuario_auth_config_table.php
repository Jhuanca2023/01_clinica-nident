<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seguridad_usuario_auth_config')) {
            Schema::create('seguridad_usuario_auth_config', function (Blueprint $table) {
                $table->id('id_seguridad_usuario_auth_config');
                $table->unsignedBigInteger('id_usuario')->unique();
                $table->boolean('permitir_local')->default(true);
                $table->boolean('permitir_google')->default(true);
                $table->boolean('permitir_microsoft')->default(true);
                $table->boolean('requiere_2fa')->default(false);
                $table->boolean('permite_2fa_voluntario')->default(true);
                $table->string('metodo_predeterminado', 20)->nullable();
                $table->timestamps();
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_usuario_auth_config');
    }
};
