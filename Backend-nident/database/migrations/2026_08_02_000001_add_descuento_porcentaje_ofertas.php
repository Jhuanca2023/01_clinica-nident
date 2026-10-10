<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (! Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
                $table->decimal('descuento_porcentaje', 5, 2)->nullable()->after('oferta_maxima_cantidad_por_precio');
            }
        });

        if (Schema::hasTable('web_promo_item')) {
            Schema::table('web_promo_item', function (Blueprint $table) {
                if (! Schema::hasColumn('web_promo_item', 'tipo_oferta')) {
                    $table->string('tipo_oferta', 20)->default('pack')->after('nombre');
                }
                if (! Schema::hasColumn('web_promo_item', 'descuento_porcentaje')) {
                    $table->decimal('descuento_porcentaje', 5, 2)->nullable()->after('precio');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
                $table->dropColumn('descuento_porcentaje');
            }
        });

        if (Schema::hasTable('web_promo_item')) {
            Schema::table('web_promo_item', function (Blueprint $table) {
                if (Schema::hasColumn('web_promo_item', 'descuento_porcentaje')) {
                    $table->dropColumn('descuento_porcentaje');
                }
                if (Schema::hasColumn('web_promo_item', 'tipo_oferta')) {
                    $table->dropColumn('tipo_oferta');
                }
            });
        }
    }
};
