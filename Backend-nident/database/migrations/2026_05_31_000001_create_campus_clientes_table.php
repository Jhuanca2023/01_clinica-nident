<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campus_clientes')) {
            return;
        }

        Schema::create('campus_clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('nombre', 120);
            $table->string('apellido', 120)->nullable();
            $table->string('empresa', 200)->nullable();
            $table->string('ruc', 20)->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->enum('estado', ['prospecto', 'pendiente_email', 'pendiente_activacion', 'activo', 'inactivo'])
                ->default('prospecto');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('ruc');
            $table->index('dni');
            $table->index('email');
            $table->index('estado');
            $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_clientes');
    }
};
