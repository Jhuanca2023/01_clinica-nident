<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campus_cliente_notas')) {
            Schema::create('campus_cliente_notas', function (Blueprint $table) {
                $table->id('id_nota');
                $table->unsignedBigInteger('id_cliente');
                $table->string('titulo', 200);
                $table->enum('tipo', ['nota', 'enlace', 'video'])->default('nota');
                $table->longText('contenido')->nullable();
                $table->string('url', 500)->nullable();
                $table->string('color', 20)->default('#1976d2');
                $table->boolean('fijado')->default(false);
                $table->unsignedBigInteger('id_user')->nullable();
                $table->timestamps();

                $table->foreign('id_cliente')->references('id_cliente')->on('campus_clientes')->cascadeOnDelete();
                $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
                $table->index(['id_cliente', 'fijado']);
            });
        }

        if (!Schema::hasTable('campus_cliente_codigo')) {
            Schema::create('campus_cliente_codigo', function (Blueprint $table) {
                $table->id('id_archivo');
                $table->unsignedBigInteger('id_cliente');
                $table->string('nombre', 160);
                $table->string('lenguaje', 40)->default('plaintext');
                $table->longText('contenido')->nullable();
                $table->unsignedSmallInteger('orden')->default(0);
                $table->unsignedBigInteger('id_user')->nullable();
                $table->timestamps();

                $table->foreign('id_cliente')->references('id_cliente')->on('campus_clientes')->cascadeOnDelete();
                $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
                $table->unique(['id_cliente', 'nombre']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_cliente_codigo');
        Schema::dropIfExists('campus_cliente_notas');
    }
};
