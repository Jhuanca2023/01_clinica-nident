<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * tipo: tabla (especificaciones) | html (TinyMCE).
 * Permite N módulos de ficha técnica por producto.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto_ficha_tecnica')) {
            return;
        }

        if (! Schema::hasColumn('administracion_producto_ficha_tecnica', 'tipo')) {
            Schema::table('administracion_producto_ficha_tecnica', function (Blueprint $table) {
                $table->string('tipo', 20)->default('tabla')->after('orden')
                    ->comment('tabla | html');
            });
        }

        // Migrar semántica antigua: orden 3 = html, resto = tabla
        DB::table('administracion_producto_ficha_tecnica')
            ->where('orden', 3)
            ->update(['tipo' => 'html']);

        DB::table('administracion_producto_ficha_tecnica')
            ->where('orden', '!=', 3)
            ->where(function ($q) {
                $q->whereNull('tipo')->orWhere('tipo', '');
            })
            ->update(['tipo' => 'tabla']);

        // Si tiene HTML y no tiene características → html
        DB::table('administracion_producto_ficha_tecnica')
            ->whereNotNull('descripcion')
            ->where('descripcion', '!=', '')
            ->where(function ($q) {
                $q->whereNull('nuevo_caracteristicas')
                    ->orWhere('nuevo_caracteristicas', '')
                    ->orWhere('nuevo_caracteristicas', '[]');
            })
            ->update(['tipo' => 'html']);
    }

    public function down(): void
    {
        if (Schema::hasTable('administracion_producto_ficha_tecnica')
            && Schema::hasColumn('administracion_producto_ficha_tecnica', 'tipo')) {
            Schema::table('administracion_producto_ficha_tecnica', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
    }
};
