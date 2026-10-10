<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seguridad_roles_sidebar_orden')) {
            return;
        }

        Schema::table('seguridad_roles_sidebar_orden', function (Blueprint $table) {
            if (!Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
                $table->string('nombre_sidebar', 200)->nullable()->after('orden');
            }
            if (!Schema::hasColumn('seguridad_roles_sidebar_orden', 'icon_sidebar')) {
                $table->string('icon_sidebar', 120)->nullable()->after('nombre_sidebar');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('seguridad_roles_sidebar_orden')) {
            return;
        }

        Schema::table('seguridad_roles_sidebar_orden', function (Blueprint $table) {
            if (Schema::hasColumn('seguridad_roles_sidebar_orden', 'icon_sidebar')) {
                $table->dropColumn('icon_sidebar');
            }
            if (Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
                $table->dropColumn('nombre_sidebar');
            }
        });
    }
};
