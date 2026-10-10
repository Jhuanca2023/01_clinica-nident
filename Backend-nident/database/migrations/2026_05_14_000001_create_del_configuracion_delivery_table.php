<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tabla: del_configuracion_delivery
 * Prefijo DEL_ = módulo Delivery
 * Almacena la configuración global de costos y reglas de delivery de la tienda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('del_configuracion_delivery', function (Blueprint $table) {
            $table->id();

            // ── Ubicación de la tienda ─────────────────────────────────────
            $table->decimal('tienda_latitud',  10, 7)->default(-12.1770190);
            $table->decimal('tienda_longitud', 10, 7)->default(-77.0100450);
            $table->string('tienda_direccion', 300)
                  ->default('Jirón Juno Mz. C Lte. 7E, La Campiña, Chorrillos');

            // ── Radio de envío gratis (metros) ─────────────────────────────
            $table->integer('radio_metros_gratis')->default(500);

            // ── Costos de delivery ─────────────────────────────────────────
            $table->decimal('costo_distritos_especiales', 8, 2)->default(10.00);
            $table->decimal('costo_otros_distritos',      8, 2)->default(10.00);

            // ── Compra mínima requerida para desbloquear compra ────────────
            $table->decimal('monto_minimo_compra', 8, 2)->default(100.00);

            // ── Distritos especiales (JSON array de nombres) ───────────────
            $table->json('distritos_especiales')->nullable();

            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });

        // Insertar registro inicial con la configuración base
        DB::table('del_configuracion_delivery')->insert([
            'tienda_latitud'             => -12.1770190,
            'tienda_longitud'            => -77.0100450,
            'tienda_direccion'           => 'Jirón Juno Mz. C Lte. 7E, La Campiña, Chorrillos',
            'radio_metros_gratis'        => 500,
            'costo_distritos_especiales' => 10.00,
            'costo_otros_distritos'      => 10.00,
            'monto_minimo_compra'        => 100.00,
            'distritos_especiales'       => json_encode(['Chorrillos', 'Barranco', 'Santiago de Surco']),
            'activo'                     => 1,
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('del_configuracion_delivery');
    }
};
