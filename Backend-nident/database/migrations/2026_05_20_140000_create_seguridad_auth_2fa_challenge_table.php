<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seguridad_auth_2fa_challenge')) {
            Schema::create('seguridad_auth_2fa_challenge', function (Blueprint $table) {
                $table->id('id_seguridad_auth_2fa_challenge');
                $table->string('challenge_token', 64)->unique();
                $table->unsignedBigInteger('id_usuario');
                $table->boolean('remember')->default(true);
                $table->string('ip', 45)->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
                $table->index(['id_usuario', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_auth_2fa_challenge');
    }
};
