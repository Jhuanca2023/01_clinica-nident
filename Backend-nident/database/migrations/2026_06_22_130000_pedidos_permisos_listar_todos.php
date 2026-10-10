<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MENU_URL = '/pedidos/index';

    private const OBJETOS_PEDIDOS = [
        'PEDIDO_LISTAR_TODOS',
        'PEDIDO_DETALLE',
        'PEDIDO_ASIGNAR',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('sistema_menu') || ! Schema::hasTable('sistema_objetos')) {
            return;
        }

        $idMenu = DB::table('sistema_menu')->where('url', self::MENU_URL)->value('id_menu');
        if (! $idMenu) {
            return;
        }

        $roles = DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->pluck('id_roles');

        foreach (self::OBJETOS_PEDIDOS as $nombreObjeto) {
            $idObjeto = DB::table('sistema_objetos')->where('nombre', $nombreObjeto)->value('id_objetos');
            if (! $idObjeto) {
                continue;
            }

            $idMenuObjeto = DB::table('sistema_menu_objetos')
                ->where('id_menu', $idMenu)
                ->where('id_objetos', $idObjeto)
                ->value('id_menu_objetos');

            if (! $idMenuObjeto) {
                $idMenuObjeto = DB::table('sistema_menu_objetos')->insertGetId([
                    'id_menu'    => $idMenu,
                    'id_objetos' => $idObjeto,
                    'Activo'     => 'S',
                    'orden'      => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (! Schema::hasTable('seguridad_menu_objetos_roles')) {
                continue;
            }

            foreach ($roles as $idRoles) {
                $exists = DB::table('seguridad_menu_objetos_roles')
                    ->where('id_roles', $idRoles)
                    ->where('id_menu_objetos', $idMenuObjeto)
                    ->exists();

                if (! $exists) {
                    DB::table('seguridad_menu_objetos_roles')->insert([
                        'id_roles'       => $idRoles,
                        'id_menu_objetos' => $idMenuObjeto,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No revertir permisos de pedidos.
    }
};
