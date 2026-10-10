<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el campo monto_pagado_cliente a ope_pedidos.
 * Almacena cuánto paga el cliente en efectivo para calcular el vuelto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ope_pedidos', function (Blueprint $table) {
            $table->decimal('monto_pagado_cliente', 10, 2)
                  ->nullable()
                  ->comment('Monto en efectivo con que paga el cliente — permite calcular vuelto');
        });
    }

    public function down(): void
    {
        Schema::table('ope_pedidos', function (Blueprint $table) {
            $table->dropColumn('monto_pagado_cliente');
        });
    }
};
