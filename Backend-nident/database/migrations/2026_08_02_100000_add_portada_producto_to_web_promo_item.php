<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_promo_item')) {
            return;
        }

        Schema::table('web_promo_item', function (Blueprint $table) {
            if (! Schema::hasColumn('web_promo_item', 'mostrar_precio_portada')) {
                $table->char('mostrar_precio_portada', 1)->default('N')->after('descuento_porcentaje');
            }
            if (! Schema::hasColumn('web_promo_item', 'id_producto_portada')) {
                $table->unsignedInteger('id_producto_portada')->nullable()->after('mostrar_precio_portada');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_promo_item')) {
            return;
        }

        Schema::table('web_promo_item', function (Blueprint $table) {
            if (Schema::hasColumn('web_promo_item', 'id_producto_portada')) {
                $table->dropColumn('id_producto_portada');
            }
            if (Schema::hasColumn('web_promo_item', 'mostrar_precio_portada')) {
                $table->dropColumn('mostrar_precio_portada');
            }
        });
    }
};
