<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $menus = [
        ['nombre' => 'Eventos', 'url' => '/eventos/index', 'Icon' => 'photo_library'],
        ['nombre' => 'Publicaciones', 'url' => '/publicaciones/index', 'Icon' => 'article'],
        ['nombre' => 'Contadores', 'url' => '/contadores/index', 'Icon' => 'pin'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $idModulo = null;
        if (Schema::hasTable('sistema_modulo')) {
            $idModulo = DB::table('sistema_modulo')
                ->where('nombre', 'like', '%Paginas%')
                ->orWhere('nombre', 'like', '%Páginas%')
                ->orWhere('nombre', 'like', '%Web%')
                ->value('id_modulo');
        }

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');

        foreach ($this->menus as $menu) {
            if (DB::table('sistema_menu')->where('url', $menu['url'])->exists()) {
                continue;
            }

            $maxOrden++;
            $data = [
                'id_modulo'  => $idModulo,
                'nombre'     => $menu['nombre'],
                'url'        => $menu['url'],
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $data['Icon'] = $menu['Icon'];
            }
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $data['orden'] = $maxOrden;
            }
            if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
                $data['id_menu_padre'] = null;
            }

            $idMenu = DB::table('sistema_menu')->insertGetId($data);

            if (Schema::hasTable('seguridad_roles_menu')) {
                $roles = DB::table('seguridad_roles')->pluck('id_roles');
                foreach ($roles as $idRoles) {
                    if (!DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                        DB::table('seguridad_roles_menu')->insert([
                            'id_roles' => $idRoles,
                            'id_menu'  => $idMenu,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        foreach ($this->menus as $menu) {
            $ids = DB::table('sistema_menu')->where('url', $menu['url'])->pluck('id_menu');
            foreach ($ids as $idMenu) {
                if (Schema::hasTable('seguridad_roles_menu')) {
                    DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->delete();
                }
                DB::table('sistema_menu')->where('id_menu', $idMenu)->delete();
            }
        }
    }
};
