<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seguridad_roles_sidebar_orden')
            || !Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
            return;
        }

        if (Schema::hasTable('sistema_modulo')) {
            foreach (DB::table('sistema_modulo')->get(['id_modulo', 'nombre', 'updated_at']) as $mod) {
                $nombre = trim((string) ($mod->nombre ?? ''));
                if ($nombre === '') {
                    continue;
                }

                DB::table('seguridad_roles_sidebar_orden')
                    ->where('tipo', 'modulo')
                    ->where('id_modulo', $mod->id_modulo)
                    ->where(function ($q) use ($nombre) {
                        $q->whereNull('nombre_sidebar')
                            ->orWhere('nombre_sidebar', '!=', $nombre);
                    })
                    ->when(
                        Schema::hasColumn('seguridad_roles_sidebar_orden', 'updated_at') && !empty($mod->updated_at),
                        fn ($q) => $q->where(function ($inner) use ($mod) {
                            $inner->whereNull('updated_at')
                                ->orWhere('updated_at', '<', $mod->updated_at);
                        })
                    )
                    ->update([
                        'nombre_sidebar' => $nombre,
                        'updated_at'     => now(),
                    ]);
            }
        }

        if (Schema::hasTable('sistema_menu')) {
            foreach (DB::table('sistema_menu')->get(['id_menu', 'nombre', 'updated_at']) as $menu) {
                $nombre = trim((string) ($menu->nombre ?? ''));
                if ($nombre === '') {
                    continue;
                }

                DB::table('seguridad_roles_sidebar_orden')
                    ->where('tipo', 'menu')
                    ->where('id_menu', $menu->id_menu)
                    ->where(function ($q) use ($nombre) {
                        $q->whereNull('nombre_sidebar')
                            ->orWhere('nombre_sidebar', '!=', $nombre);
                    })
                    ->when(
                        Schema::hasColumn('seguridad_roles_sidebar_orden', 'updated_at') && !empty($menu->updated_at),
                        fn ($q) => $q->where(function ($inner) use ($menu) {
                            $inner->whereNull('updated_at')
                                ->orWhere('updated_at', '<', $menu->updated_at);
                        })
                    )
                    ->update([
                        'nombre_sidebar' => $nombre,
                        'updated_at'     => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // No reversible.
    }
};
