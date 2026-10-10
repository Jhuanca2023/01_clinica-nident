<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BADGES = ['NUEVO', 'POPULAR', 'LIMITADO'];

    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto')) {
            return;
        }

        if (! Schema::hasColumn('administracion_producto', 'etiqueta_badge')) {
            Schema::table('administracion_producto', function (Blueprint $table) {
                $table->string('etiqueta_badge', 30)->nullable()->after('codigo_barra');
            });
        }

        foreach (self::BADGES as $badge) {
            DB::table('administracion_producto')
                ->where('codigo_barra', $badge)
                ->update([
                    'etiqueta_badge' => $badge,
                    'codigo_barra'   => null,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('administracion_producto')) {
            return;
        }

        if (Schema::hasColumn('administracion_producto', 'etiqueta_badge')) {
            foreach (self::BADGES as $badge) {
                DB::table('administracion_producto')
                    ->where('etiqueta_badge', $badge)
                    ->whereNull('codigo_barra')
                    ->update(['codigo_barra' => $badge]);
            }

            Schema::table('administracion_producto', function (Blueprint $table) {
                $table->dropColumn('etiqueta_badge');
            });
        }
    }
};
