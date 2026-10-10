<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Agrega costo_envio_baja_compra a del_configuracion_delivery.
 * Cuando el subtotal del carrito es menor al monto_minimo_compra,
 * en lugar de bloquear, se cobra este costo diferenciado (ej. S/5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('del_configuracion_delivery', function (Blueprint $table) {
            $table->decimal('costo_envio_baja_compra', 8, 2)
                  ->default(5.00)
                  ->after('monto_minimo_compra')
                  ->comment('Costo de envío cuando el subtotal es menor al monto mínimo');
        });

        // Actualizar el registro existente con el valor por defecto
        DB::table('del_configuracion_delivery')
          ->where('activo', 1)
          ->update(['costo_envio_baja_compra' => 5.00]);
    }

    public function down(): void
    {
        Schema::table('del_configuracion_delivery', function (Blueprint $table) {
            $table->dropColumn('costo_envio_baja_compra');
        });
    }
};
