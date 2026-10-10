<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vigencia del popup promo: el descuento aplicado con "Comprar" dura hasta fecha_fin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_popup_promo')) {
            return;
        }

        Schema::table('web_popup_promo', function (Blueprint $table) {
            if (! Schema::hasColumn('web_popup_promo', 'fecha_inicio')) {
                $table->dateTime('fecha_inicio')->nullable()->after('activo');
            }
            if (! Schema::hasColumn('web_popup_promo', 'fecha_fin')) {
                $table->dateTime('fecha_fin')->nullable()->after('fecha_inicio');
            }
            if (! Schema::hasColumn('web_popup_promo', 'descuento_porcentaje')) {
                $table->decimal('descuento_porcentaje', 5, 2)->nullable()->after('descuento_texto');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_popup_promo')) {
            return;
        }
        Schema::table('web_popup_promo', function (Blueprint $table) {
            if (Schema::hasColumn('web_popup_promo', 'descuento_porcentaje')) {
                $table->dropColumn('descuento_porcentaje');
            }
            if (Schema::hasColumn('web_popup_promo', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
            if (Schema::hasColumn('web_popup_promo', 'fecha_inicio')) {
                $table->dropColumn('fecha_inicio');
            }
        });
    }
};
