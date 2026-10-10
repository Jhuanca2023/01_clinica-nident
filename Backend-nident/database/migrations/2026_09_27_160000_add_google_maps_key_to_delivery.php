<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API key de Google Maps (Maps JavaScript API) para el mapa del módulo Delivery.
 * Es una clave de navegador: se restringe por dominio (HTTP referrer) en Google Cloud.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('del_configuracion_delivery') && ! Schema::hasColumn('del_configuracion_delivery', 'google_maps_api_key')) {
            Schema::table('del_configuracion_delivery', function (Blueprint $table) {
                $table->string('google_maps_api_key', 200)->nullable()->after('tienda_direccion')
                    ->comment('Clave de navegador de Google Maps JavaScript API');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('del_configuracion_delivery') && Schema::hasColumn('del_configuracion_delivery', 'google_maps_api_key')) {
            Schema::table('del_configuracion_delivery', function (Blueprint $table) {
                $table->dropColumn('google_maps_api_key');
            });
        }
    }
};
