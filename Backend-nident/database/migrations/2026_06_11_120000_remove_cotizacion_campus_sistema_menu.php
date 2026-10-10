<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_URLS = [
        '/cotizacion/index',
        '/campus/dashboard',
        '/campus/notificaciones',
        '/campus/admin',
    ];

    public function up(): void
    {
        if (! $this->tableExists('sistema_menu')) {
            return;
        }

        $menuIds = DB::table('sistema_menu')
            ->whereIn('url', self::MENU_URLS)
            ->pluck('id_menu');

        if ($menuIds->isEmpty()) {
            return;
        }

        if ($this->tableExists('seguridad_roles_menu')) {
            DB::table('seguridad_roles_menu')
                ->whereIn('id_menu', $menuIds)
                ->delete();
        }

        DB::table('sistema_menu')
            ->whereIn('id_menu', $menuIds)
            ->delete();
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
