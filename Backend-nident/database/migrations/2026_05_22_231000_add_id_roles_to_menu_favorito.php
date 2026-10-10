<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seguridad_usuario_menu_favorito')) {
            return;
        }

        if (!Schema::hasColumn('seguridad_usuario_menu_favorito', 'id_roles')) {
            Schema::table('seguridad_usuario_menu_favorito', function (Blueprint $table) {
                $table->unsignedInteger('id_roles')->default(0)->after('id_usuario');
                $table->index(['id_usuario', 'id_roles'], 'idx_favorito_usuario_rol');
            });
        }

        Schema::table('seguridad_usuario_menu_favorito', function (Blueprint $table) {
            try {
                $table->dropUnique('uq_usuario_menu_favorito_path');
            } catch (\Throwable $e) {
            }
            try {
                $table->unique(['id_usuario', 'id_roles', 'path'], 'uq_usuario_rol_menu_favorito_path');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('seguridad_usuario_menu_favorito')) {
            return;
        }

        Schema::table('seguridad_usuario_menu_favorito', function (Blueprint $table) {
            try {
                $table->dropUnique('uq_usuario_rol_menu_favorito_path');
            } catch (\Throwable $e) {
            }
        });

        if (Schema::hasColumn('seguridad_usuario_menu_favorito', 'id_roles')) {
            Schema::table('seguridad_usuario_menu_favorito', function (Blueprint $table) {
                $table->dropColumn('id_roles');
            });
        }
    }
};
