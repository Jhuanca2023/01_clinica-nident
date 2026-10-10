<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Garantiza columnas de modo test/producción en pasarelas.
 * Idempotente: seguro en cPanel aunque la migración original
 * haya quedado marcada como ejecutada sin crear las columnas.
 */
class PagoPasarelaSchema
{
    /**
     * @return list<string> columnas creadas en esta llamada
     */
    public static function ensureModoColumns(): array
    {
        $created = [];

        if (Schema::hasTable('pago_pasarela') && ! Schema::hasColumn('pago_pasarela', 'modo')) {
            Schema::table('pago_pasarela', function (Blueprint $table) {
                $table->string('modo', 20)->default('produccion')->after('is_activo');
            });
            $created[] = 'pago_pasarela.modo';
        }

        if (Schema::hasTable('pago_pasarela_config') && ! Schema::hasColumn('pago_pasarela_config', 'valor_test')) {
            Schema::table('pago_pasarela_config', function (Blueprint $table) {
                $table->text('valor_test')->nullable()->after('valor');
            });
            $created[] = 'pago_pasarela_config.valor_test';
        }

        return $created;
    }

    public static function estado(): array
    {
        return [
            'pago_pasarela' => Schema::hasTable('pago_pasarela'),
            'pago_pasarela_config' => Schema::hasTable('pago_pasarela_config'),
            'modo' => Schema::hasTable('pago_pasarela') && Schema::hasColumn('pago_pasarela', 'modo'),
            'valor_test' => Schema::hasTable('pago_pasarela_config') && Schema::hasColumn('pago_pasarela_config', 'valor_test'),
        ];
    }
}
