<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seguridad_roles_sidebar_orden')) {
            return;
        }

        Schema::create('seguridad_roles_sidebar_orden', function (Blueprint $table) {
            $table->increments('id_seguridad_roles_sidebar_orden');
            $table->integer('id_roles');
            $table->char('tipo', 10); // menu | modulo
            $table->unsignedInteger('id_menu')->nullable();
            $table->unsignedInteger('id_modulo')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['id_roles', 'orden'], 'idx_roles_sidebar_orden');
            $table->index(['id_roles', 'tipo', 'id_menu'], 'idx_roles_sidebar_menu');
            $table->index(['id_roles', 'tipo', 'id_modulo'], 'idx_roles_sidebar_modulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_roles_sidebar_orden');
    }
};
