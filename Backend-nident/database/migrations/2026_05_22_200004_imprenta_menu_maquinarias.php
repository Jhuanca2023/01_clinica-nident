<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_header')) {
            return;
        }

        $nav = [
            ['label' => 'Inicio', 'href' => '/'],
            ['label' => 'Nosotros', 'href' => '/nosotros'],
            ['label' => 'Productos', 'href' => '/productos'],
            ['label' => 'Videos', 'href' => '/#videos'],
            ['label' => 'Máquinarias', 'href' => '/#maquinarias'],
            ['label' => 'Contáctanos', 'href' => '/contacto'],
        ];

        DB::table('web_header')->where('id_header', 1)->update([
            'nav_items' => json_encode($nav, JSON_UNESCAPED_UNICODE),
            'side_menu_enlaces' => json_encode($nav, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);

        if (Schema::hasTable('web_footer')) {
            $footerNav = [
                ['label' => 'Nosotros', 'href' => '/nosotros'],
                ['label' => 'Productos', 'href' => '/productos'],
                ['label' => 'Videos', 'href' => '/#videos'],
                ['label' => 'Máquinarias', 'href' => '/#maquinarias'],
                ['label' => 'Contáctanos', 'href' => '/contacto'],
            ];
            if (Schema::hasColumn('web_footer', 'nav_footer')) {
                DB::table('web_footer')->where('id_footer', 1)->update([
                    'nav_footer' => json_encode($footerNav, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
