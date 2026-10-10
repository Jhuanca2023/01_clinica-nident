<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $url = '/menu/orden';
        if (DB::table('sistema_menu')->where('url', $url)->exists()) {
            return;
        }

        $idModulo = null;
        if (Schema::hasTable('sistema_modulo')) {
            $idModulo = DB::table('sistema_modulo')
                ->where(function ($q) {
                    $q->where('nombre', 'like', '%Configuracion%')
                        ->orWhere('nombre', 'like', '%Administracion%')
                        ->orWhere('nombre', 'like', '%Administración%');
                })
                ->orderBy('id_modulo')
                ->value('id_modulo');
        }

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');
        $data = [
            'id_modulo' => $idModulo,
            'nombre' => 'Orden de mi menú',
            'url' => $url,
            'Activo' => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $data['Icon'] = 'drag_indicator';
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $data['orden'] = $maxOrden + 1;
        }
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $data['id_menu_padre'] = null;
        }

        $idMenu = DB::table('sistema_menu')->insertGetId($data);

        if (Schema::hasTable('seguridad_roles_menu')) {
            $roles = DB::table('seguridad_roles')->pluck('id_roles');
            foreach ($roles as $idRoles) {
                $exists = DB::table('seguridad_roles_menu')
                    ->where('id_roles', $idRoles)
                    ->where('id_menu', $idMenu)
                    ->exists();
                if (!$exists) {
                    DB::table('seguridad_roles_menu')->insert([
                        'id_roles' => $idRoles,
                        'id_menu' => $idMenu,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $ids = DB::table('sistema_menu')->where('url', '/menu/orden')->pluck('id_menu');
        foreach ($ids as $idMenu) {
            if (Schema::hasTable('seguridad_roles_menu')) {
                DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->delete();
            }
            DB::table('sistema_menu')->where('id_menu', $idMenu)->delete();
        }
    }
};
