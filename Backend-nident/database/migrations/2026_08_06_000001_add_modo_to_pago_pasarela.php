<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modo Test / Producción por pasarela.
 *  - pago_pasarela.modo:        'produccion' | 'test' (modo activo que usa el checkout)
 *  - pago_pasarela_config.valor_test: claves de PRUEBA (las de producción quedan en `valor`)
 */
return new class extends Migration
{
    public function up(): void
    {
        // hasColumn debe ir FUERA de Schema::table: dentro del closure
        // Laravel a veces no aplica el ADD y artisan-clear marca la migración como ran.
        if (Schema::hasTable('pago_pasarela') && ! Schema::hasColumn('pago_pasarela', 'modo')) {
            Schema::table('pago_pasarela', function (Blueprint $table) {
                $table->string('modo', 20)->default('produccion')->after('is_activo');
            });
        }

        if (Schema::hasTable('pago_pasarela_config') && ! Schema::hasColumn('pago_pasarela_config', 'valor_test')) {
            Schema::table('pago_pasarela_config', function (Blueprint $table) {
                $table->text('valor_test')->nullable()->after('valor');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pago_pasarela', function (Blueprint $table) {
            if (Schema::hasColumn('pago_pasarela', 'modo')) {
                $table->dropColumn('modo');
            }
        });
        Schema::table('pago_pasarela_config', function (Blueprint $table) {
            if (Schema::hasColumn('pago_pasarela_config', 'valor_test')) {
                $table->dropColumn('valor_test');
            }
        });
    }
};
