<?php

use App\Support\PagoPasarelaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Repara modo / valor_test si la migración 2026_08_06 quedó
 * registrada en `migrations` sin crear las columnas en cPanel.
 */
return new class extends Migration
{
    public function up(): void
    {
        PagoPasarelaSchema::ensureModoColumns();
    }

    public function down(): void
    {
        if (Schema::hasTable('pago_pasarela') && Schema::hasColumn('pago_pasarela', 'modo')) {
            Schema::table('pago_pasarela', function ($table) {
                $table->dropColumn('modo');
            });
        }
        if (Schema::hasTable('pago_pasarela_config') && Schema::hasColumn('pago_pasarela_config', 'valor_test')) {
            Schema::table('pago_pasarela_config', function ($table) {
                $table->dropColumn('valor_test');
            });
        }
    }
};
