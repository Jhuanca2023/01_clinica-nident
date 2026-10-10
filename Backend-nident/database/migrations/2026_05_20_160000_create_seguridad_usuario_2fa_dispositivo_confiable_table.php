<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguridad_usuario_2fa_dispositivo_confiable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->string('token_hash', 64);
            $table->timestamp('expires_at');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->index(['id_usuario', 'token_hash'], 'usr_2fa_disp_conf_user_token_idx');
            $table->index('expires_at', 'usr_2fa_disp_conf_exp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_usuario_2fa_dispositivo_confiable');
    }
};
