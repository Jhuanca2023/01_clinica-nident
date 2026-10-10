<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El carrito (ShopDetailController::web_shopDetail_agregar) identifica los productos
 * por un código que empieza con "P". Los productos Valento V001…V012 pasan a PV001…PV012.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto')) {
            return;
        }

        DB::table('administracion_producto')
            ->where('codigo_producto', 'like', 'V0%')
            ->update(['codigo_producto' => DB::raw("CONCAT('P', codigo_producto)")]);
    }

    public function down(): void
    {
        //
    }
};
