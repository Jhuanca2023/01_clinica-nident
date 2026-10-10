<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_URLS = [
        // Lucdesoft
        '/ejemplares/index',
        '/portafolio/index',
        '/metodologia/index',
        '/servicios/index',
        '/contacto-landing/index',
        '/porque_elejirnos/index',
        '/eventos/index',
        '/publicaciones/index',
        // Sparlex / Royal Masajes
        '/promo-banner/index',
        '/experiencias/index',
        '/masaje-faq/index',
        '/pagina-experiencias/index',
        '/pagina-masajes/index',
    ];

    public function up(): void
    {
        if (! $this->tableExists('sistema_menu')) {
            return;
        }

        $menuIds = DB::table('sistema_menu')->whereIn('url', self::MENU_URLS)->pluck('id_menu');
        if ($menuIds->isEmpty()) {
            return;
        }

        if ($this->tableExists('seguridad_roles_menu')) {
            DB::table('seguridad_roles_menu')->whereIn('id_menu', $menuIds)->delete();
        }

        DB::table('sistema_menu')->whereIn('id_menu', $menuIds)->delete();
    }

    public function down(): void
    {
        // No se restauran entradas de módulos retirados.
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
};
