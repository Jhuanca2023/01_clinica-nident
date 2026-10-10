<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mueve Backup y Acceso Google bajo el menú Páginas en el árbol de permisos (y sidebar).
 */
return new class extends Migration
{
    public function up(): void
    {
        $idPaginas = DB::table('sistema_menu')->where('nombre', 'Páginas')->value('id_menu');
        if (!$idPaginas) {
            return;
        }

        DB::table('sistema_menu')
            ->whereIn('nombre', ['Backup', 'Acceso Google'])
            ->where('id_modulo', 4)
            ->update(['id_menu_padre' => $idPaginas]);
    }

    public function down(): void
    {
        DB::table('sistema_menu')
            ->whereIn('nombre', ['Backup', 'Acceso Google'])
            ->where('id_modulo', 4)
            ->update(['id_menu_padre' => null]);
    }
};
