<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permisos por rol para módulos que son solo link (sin menús hijos en sistema_menu).
     * Ej.: Categoria (id_modulo 22) con url /categoria/index.
     */
    public function up(): void
    {
        if (Schema::hasTable('seguridad_roles_modulo')) {
            return;
        }
        Schema::create('seguridad_roles_modulo', function (Blueprint $table) {
            $table->unsignedInteger('id_roles');
            $table->unsignedInteger('id_modulo');
            $table->timestamps();
            $table->primary(['id_roles', 'id_modulo']);
            $table->foreign('id_roles')->references('id_roles')->on('seguridad_roles')->onDelete('cascade');
            $table->foreign('id_modulo')->references('id_modulo')->on('sistema_modulo')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_roles_modulo');
    }
};
