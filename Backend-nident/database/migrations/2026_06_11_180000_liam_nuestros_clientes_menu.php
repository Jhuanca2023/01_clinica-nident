<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MENU_URL = '/carrusel/index';

    public function up(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        $idModulo = $this->resolveTiendaModuloId();
        $row = DB::table('sistema_menu')->where('url', self::MENU_URL)->first();

        if ($row) {
            $update = [
                'nombre'     => 'Nuestros clientes',
                'Activo'     => 'S',
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $update['Icon'] = 'groups';
            }
            if (Schema::hasColumn('sistema_menu', 'id_modulo') && $idModulo) {
                $update['id_modulo'] = $idModulo;
            }
            DB::table('sistema_menu')->where('id_menu', $row->id_menu)->update($update);
            $this->assignMenuToAllRoles((int) $row->id_menu);
        } else {
            $data = [
                'nombre'     => 'Nuestros clientes',
                'url'        => self::MENU_URL,
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
                $data['id_modulo'] = $idModulo;
            }
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $data['Icon'] = 'groups';
            }
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $data['orden'] = ((int) DB::table('sistema_menu')->max('orden')) + 1;
            }
            $idMenu = DB::table('sistema_menu')->insertGetId($data);
            $this->assignMenuToAllRoles($idMenu);
        }

        if (Schema::hasTable('web_carrusel')) {
            $fallback = 'temp02/img/logos/logo-1.png';
            DB::table('web_carrusel')
                ->whereNull('url_imagen')
                ->orWhere('url_imagen', '')
                ->update(['url_imagen' => $fallback, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sistema_menu')) {
            DB::table('sistema_menu')->where('url', self::MENU_URL)->update([
                'nombre'     => 'Carrusel logos',
                'updated_at' => now(),
            ]);
        }
    }

    private function resolveTiendaModuloId(): ?int
    {
        if (! Schema::hasTable('sistema_modulo')) {
            return null;
        }

        $id = DB::table('sistema_modulo')
            ->where('nombre', 'like', '%Tienda Virtual%')
            ->value('id_modulo');

        return $id ? (int) $id : null;
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
