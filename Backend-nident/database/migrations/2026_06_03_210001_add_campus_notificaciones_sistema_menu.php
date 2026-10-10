<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const URLS = [
        '/campus/dashboard',
        '/campus/notificaciones',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $idModulo = $this->resolveCampusModuloId();
        $menus = [
            ['nombre' => 'Campus DeliverBox', 'url' => '/campus/dashboard', 'Icon' => 'dashboard'],
            ['nombre' => 'Alertas campus', 'url' => '/campus/notificaciones', 'Icon' => 'notifications_active'],
        ];

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');

        foreach ($menus as $menu) {
            if (DB::table('sistema_menu')->where('url', $menu['url'])->exists()) {
                continue;
            }

            $maxOrden++;
            $data = [
                'nombre'     => $menu['nombre'],
                'url'        => $menu['url'],
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
                $data['id_modulo'] = $idModulo;
            }
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
            $this->assignMenuToAllRoles($idMenu);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        foreach (self::URLS as $url) {
            $ids = DB::table('sistema_menu')->where('url', $url)->pluck('id_menu');
            foreach ($ids as $idMenu) {
                if (Schema::hasTable('seguridad_roles_menu')) {
                    DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->delete();
                }
                DB::table('sistema_menu')->where('id_menu', $idMenu)->delete();
            }
        }
    }

    private function resolveCampusModuloId(): ?int
    {
        if (!Schema::hasTable('sistema_modulo')) {
            return null;
        }

        if (Schema::hasTable('sistema_menu')) {
            $fromMenu = DB::table('sistema_menu')
                ->whereIn('url', ['/campus/dashboard', '/campus/admin', '/campus/notificaciones'])
                ->whereNotNull('id_modulo')
                ->value('id_modulo');

            if ($fromMenu) {
                return (int) $fromMenu;
            }
        }

        $idModulo = DB::table('sistema_modulo')
            ->where(function ($q) {
                $q->where('nombre', 'like', '%Campus%')
                    ->orWhere('nombre', 'like', '%DeliverBox%');
            })
            ->where('Activo', 'S')
            ->value('id_modulo');

        if ($idModulo) {
            return (int) $idModulo;
        }

        $data = [
            'nombre'     => 'Campus',
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('sistema_modulo', 'Orden')) {
            $data['Orden'] = ((int) DB::table('sistema_modulo')->max('Orden')) + 1;
        }

        return (int) DB::table('sistema_modulo')->insertGetId($data);
    }

    private function assignMenuToAllRoles(int $idMenu): void
    {
        if (!Schema::hasTable('seguridad_roles_menu')) {
            return;
        }

        foreach (DB::table('seguridad_roles')->pluck('id_roles') as $idRoles) {
            if (!DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRoles,
                    'id_menu'  => $idMenu,
                ]);
            }
        }
    }
};
