<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sistema_menu', 'orden_sidebar')) {
            Schema::table('sistema_menu', function (Blueprint $table) {
                $table->unsignedInteger('orden_sidebar')->nullable()->after('orden');
            });
        }

        if (!Schema::hasColumn('sistema_modulo', 'orden')) {
            Schema::table('sistema_modulo', function (Blueprint $table) {
                $table->unsignedInteger('orden')->default(0)->after('Activo');
            });
        }

        if (!Schema::hasColumn('sistema_modulo', 'orden_sidebar')) {
            Schema::table('sistema_modulo', function (Blueprint $table) {
                $table->unsignedInteger('orden_sidebar')->nullable()->after('orden');
            });
        }

        $this->backfillOrdenSidebar();
    }

    public function down(): void
    {
        if (Schema::hasColumn('sistema_menu', 'orden_sidebar')) {
            Schema::table('sistema_menu', function (Blueprint $table) {
                $table->dropColumn('orden_sidebar');
            });
        }

        if (Schema::hasColumn('sistema_modulo', 'orden_sidebar')) {
            Schema::table('sistema_modulo', function (Blueprint $table) {
                $table->dropColumn('orden_sidebar');
            });
        }
    }

    private function backfillOrdenSidebar(): void
    {
        $orden = 0;
        $hasParent = Schema::hasColumn('sistema_menu', 'id_menu_padre');
        $hasMenuOrden = Schema::hasColumn('sistema_menu', 'orden');

        $menusQuery = DB::table('sistema_menu')
            ->whereNull('id_modulo')
            ->where('Activo', 'S');

        if ($hasParent) {
            $menusQuery->whereNull('id_menu_padre');
        }

        if ($hasMenuOrden) {
            $menusQuery->orderBy('orden');
        } else {
            $menusQuery->orderBy('id_menu');
        }

        foreach ($menusQuery->get() as $menu) {
            DB::table('sistema_menu')
                ->where('id_menu', $menu->id_menu)
                ->update(['orden_sidebar' => $orden++]);
        }

        $modsQuery = DB::table('sistema_modulo')->where('Activo', 'S');
        if (Schema::hasColumn('sistema_modulo', 'orden')) {
            $modsQuery->orderBy('orden');
        } else {
            $modsQuery->orderBy('id_modulo');
        }

        foreach ($modsQuery->get() as $mod) {
            DB::table('sistema_modulo')
                ->where('id_modulo', $mod->id_modulo)
                ->update(['orden_sidebar' => $orden++]);
        }
    }
};
