<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MENU_URL = '/pedidos/index';

    public function up(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        $idModulo = DB::table('sistema_modulo')
            ->where('nombre', 'Tienda Virtual')
            ->value('id_modulo');

        if (! $idModulo) {
            return;
        }

        $exists = DB::table('sistema_menu')->where('url', self::MENU_URL)->exists();

        if (! $exists) {
            DB::table('sistema_menu')
                ->where('id_modulo', $idModulo)
                ->where('orden', '>=', 5)
                ->increment('orden');

            $idMenu = DB::table('sistema_menu')->insertGetId([
                'nombre'     => 'Pedidos',
                'url'        => self::MENU_URL,
                'Activo'     => 'S',
                'id_modulo'  => $idModulo,
                'Icon'       => 'shopping_cart',
                'orden'      => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $current = DB::table('sistema_menu')->where('url', self::MENU_URL)->first();

            if ((int) ($current->id_modulo ?? 0) !== (int) $idModulo || (int) ($current->orden ?? 99) !== 5) {
                DB::table('sistema_menu')
                    ->where('id_modulo', $idModulo)
                    ->where('orden', '>=', 5)
                    ->where('url', '!=', self::MENU_URL)
                    ->increment('orden');
            }

            DB::table('sistema_menu')->where('url', self::MENU_URL)->update([
                'nombre'     => 'Pedidos',
                'id_modulo'  => $idModulo,
                'Activo'     => 'S',
                'Icon'       => 'shopping_cart',
                'orden'      => 5,
                'updated_at' => now(),
            ]);

            $idMenu = (int) $current->id_menu;
        }

        $this->assignMenuToAllRoles($idMenu);
    }

    private function assignMenuToAllRoles(int $idMenu): void
    {
        if (! Schema::hasTable('seguridad_roles_menu') || ! Schema::hasTable('seguridad_roles')) {
            return;
        }

        $roles = DB::table('seguridad_roles')->pluck('id_roles');
        foreach ($roles as $idRoles) {
            $exists = DB::table('seguridad_roles_menu')
                ->where('id_roles', $idRoles)
                ->where('id_menu', $idMenu)
                ->exists();

            if (! $exists) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRoles,
                    'id_menu'  => $idMenu,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No revertir: el menú Pedidos puede existir desde instalaciones anteriores.
    }
};
