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

        $menus = [
            ['nombre' => 'Banner Promo', 'url' => '/promo-banner/index', 'icon' => 'campaign'],
            ['nombre' => 'Experiencias', 'url' => '/experiencias/index', 'icon' => 'spa'],
            ['nombre' => 'Galería Royal', 'url' => '/pagina-galeria/index', 'icon' => 'photo_library'],
        ];

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');

        foreach ($menus as $menu) {
            if (DB::table('sistema_menu')->where('url', $menu['url'])->exists()) {
                continue;
            }
            $maxOrden++;
            $data = [
                'nombre'       => $menu['nombre'],
                'url'          => $menu['url'],
                'Activo'       => 'S',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $data['orden'] = $maxOrden;
            }
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $data['Icon'] = $menu['icon'];
            }
            DB::table('sistema_menu')->insert($data);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }
        DB::table('sistema_menu')->whereIn('url', [
            '/promo-banner/index',
            '/experiencias/index',
            '/pagina-galeria/index',
        ])->delete();
    }
};
