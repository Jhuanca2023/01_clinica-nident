<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Página "Mi Carrito" como la plantilla temp02 (shopping-cart.html):
 * nota IMPORTANTE visible y sin la tarjeta del asesor (se reactiva desde el admin "Lista Detalle").
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_carrito_config')) {
            return;
        }

        DB::table('web_carrito_config')->update([
            'nota_activa'   => 'S',
            'asesor_activo' => 'N',
        ]);
    }

    public function down(): void
    {
        //
    }
};
