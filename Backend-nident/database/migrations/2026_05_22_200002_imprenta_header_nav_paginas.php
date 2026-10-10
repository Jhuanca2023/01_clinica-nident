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

        if (Schema::hasTable('web_contacto_landing')) {
            DB::table('web_contacto_landing')->where('id', 1)->update([
                'email_destino' => config('mail.contact_to', env('MAIL_CONTACT_TO', env('MAIL_FROM_ADDRESS'))),
                'updated_at'    => now(),
            ]);
        }

        DB::table('web_header')->where('id_header', 1)->update([
            'nav_items' => json_encode([
                ['label' => 'Inicio', 'href' => '/'],
                ['label' => 'Nosotros', 'href' => '/nosotros'],
                ['label' => 'Productos', 'href' => '/#productos'],
                ['label' => 'Videos', 'href' => '/#videos'],
                ['label' => 'Máquinarias', 'href' => '/#maquinarias'],
                ['label' => 'Contáctanos', 'href' => '/contacto'],
            ], JSON_UNESCAPED_UNICODE),
            'side_menu_enlaces' => json_encode([
                ['label' => 'Inicio', 'href' => '/'],
                ['label' => 'Nosotros', 'href' => '/nosotros'],
                ['label' => 'Productos', 'href' => '/#productos'],
                ['label' => 'Videos', 'href' => '/#videos'],
                ['label' => 'Máquinarias', 'href' => '/#maquinarias'],
                ['label' => 'Contáctanos', 'href' => '/contacto'],
            ], JSON_UNESCAPED_UNICODE),
            'url_contacto_top' => '/contacto',
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        // sin rollback automático del menú anterior
    }
};
