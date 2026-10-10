<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Flag para controlar qué categorías aparecen en el panel de categorías del home.
 * Las existentes quedan en S (siguen visibles); las nuevas pueden optar por N.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto_categoria')) {
            return;
        }

        if (! Schema::hasColumn('administracion_producto_categoria', 'mostrar_panel_principal')) {
            Schema::table('administracion_producto_categoria', function (Blueprint $table) {
                $table->char('mostrar_panel_principal', 1)
                    ->default('N')
                    ->after('Activo')
                    ->comment('S = visible en sección Categorías del home');
            });
        }

        // Mantener visibles en home las categorías que ya existían
        DB::table('administracion_producto_categoria')
            ->whereNull('mostrar_panel_principal')
            ->orWhere('mostrar_panel_principal', '')
            ->update(['mostrar_panel_principal' => 'S']);

        // Por defecto las activas actuales se muestran en el panel
        DB::table('administracion_producto_categoria')
            ->where('Activo', 'S')
            ->update(['mostrar_panel_principal' => 'S']);
    }

    public function down(): void
    {
        if (Schema::hasTable('administracion_producto_categoria')
            && Schema::hasColumn('administracion_producto_categoria', 'mostrar_panel_principal')) {
            Schema::table('administracion_producto_categoria', function (Blueprint $table) {
                $table->dropColumn('mostrar_panel_principal');
            });
        }
    }
};
