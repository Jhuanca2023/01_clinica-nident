<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seguridad_roles_modulo_inactivo')) {
            return;
        }

        Schema::create('seguridad_roles_modulo_inactivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_roles');
            $table->unsignedBigInteger('id_modulo');
            $table->timestamps();
            $table->unique(['id_roles', 'id_modulo'], 'rol_modulo_inactivo_uq');
            $table->index('id_roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_roles_modulo_inactivo');
    }
};
