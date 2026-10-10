<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina artefactos legacy del treeview de permisos (reemplazados por SeguridadMenuTreeviewService).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW`');

        if (Schema::hasTable('datatreeview')) {
            Schema::drop('datatreeview');
        }
    }

    public function down(): void
    {
        // No se restaura el SP ni datatreeview; usar scripts en database/scripts/ si fuera necesario.
    }
};
