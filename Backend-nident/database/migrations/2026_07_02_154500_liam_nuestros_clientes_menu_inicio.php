<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MENU_URL = '/carrusel/index';

    /** Módulo "Inicio & Header" — visible en el sidebar del admin local */
    private const INICIO_HEADER_MODULO = 63;

    public function up(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        $row = DB::table('sistema_menu')->where('url', self::MENU_URL)->first();
        if (! $row) {
            return;
        }

        $update = [
            'nombre'     => 'Nuestros clientes',
            'Activo'     => 'S',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
            $update['id_modulo'] = self::INICIO_HEADER_MODULO;
        }
        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $update['Icon'] = 'groups';
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $update['orden'] = 2;
        }

        DB::table('sistema_menu')->where('id_menu', $row->id_menu)->update($update);
        $this->assignMenuToAllRoles((int) $row->id_menu);
    }

    public function down(): void
    {
        // Sin reversión automática.
    }

    private function assignMenuToAllRoles(int $idMenu): void
    {
        if (! Schema::hasTable('seguridad_roles_menu') || ! Schema::hasTable('seguridad_roles')) {
            return;
        }

        foreach (DB::table('seguridad_roles')->pluck('id_roles') as $idRoles) {
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
};
