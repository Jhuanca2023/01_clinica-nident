<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tabla: web_carrito_config
 * Configuración de textos y variables de la página "Lista Detalle" (carrito):
 * títulos, etiquetas, IGV, umbral de envío gratis, nota importante, botones
 * y la tarjeta de "Asesor en línea".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_carrito_config', function (Blueprint $table) {
            $table->id();

            // ── Resumen ─────────────────────────────────────────────
            $table->string('resumen_titulo', 120)->default('Resumen de su compra');
            $table->string('label_subtotal', 60)->default('Subtotal');
            $table->string('label_igv', 60)->default('IGV (18%)');
            $table->decimal('igv_porcentaje', 5, 2)->default(18.00);
            $table->string('label_descuento', 60)->default('Descuento oferta');
            $table->string('label_total', 60)->default('Total');

            // ── Envío gratis ────────────────────────────────────────
            $table->decimal('umbral_envio_gratis', 10, 2)->default(199.00);
            $table->string('envio_gratis_texto', 120)->default('¡Envío GRATIS!');
            // {monto} se reemplaza por el faltante en soles
            $table->string('envio_falta_texto', 160)->default('Te faltan {monto} para envío gratis');

            // ── Nota importante ─────────────────────────────────────
            $table->string('nota_titulo', 60)->default('IMPORTANTE:');
            $table->text('nota_texto')->nullable();
            $table->char('nota_activa', 1)->default('S');

            // ── Botones ─────────────────────────────────────────────
            $table->string('btn_continuar_texto', 60)->default('Continuar');
            $table->string('btn_seguir_texto', 60)->default('Seguir comprando');

            // ── Tarjeta Asesor en línea (WhatsApp) ──────────────────
            $table->char('asesor_activo', 1)->default('S');
            $table->string('asesor_label', 60)->default('Asesor en línea');
            $table->string('asesor_titulo', 80)->default('¿Necesitas ayuda?');
            $table->string('asesor_subtitulo', 120)->default('Chatea con un asesor ahora');
            $table->text('asesor_mensaje')->nullable();

            $table->char('Activo', 1)->default('S');
            $table->timestamps();
        });

        // Fila única por defecto
        DB::table('web_carrito_config')->insert([
            'id'              => 1,
            'nota_texto'      => 'Asegúrese que su carrito incluye TERNO + CAMISA + CORBATA en la promoción de ARMA TU PACK',
            'asesor_mensaje'  => 'Hola! Estoy en el carrito de Liam Vercceli y me gustaría recibir asesoría para completar mi compra.',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_carrito_config');
    }
};
