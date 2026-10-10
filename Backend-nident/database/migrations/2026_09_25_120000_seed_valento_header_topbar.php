<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Header: guarda en web_header lo que la web mostraba con valores fijos
 * (teléfono, horario, avisos y botón), para que el admin los edite.
 * Correo unificado con contacto y footer: ventas@rossety.pe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_header')) {
            return;
        }

        $datos = [
            'top_telefonos'    => '+51 964 381 275',
            'top_email'        => 'ventas@rossety.pe',
            'topbar_promo'     => 'Lun - Sab: 9:00 - 18:00',
            'topbar_cta_texto' => 'Cotiza por WhatsApp',
            'ticker_items'     => json_encode([
                ['icon' => 'temp02/img/icons/delivery.png', 'texto' => 'Venta mayorista para profesionales'],
                ['icon' => 'temp02/img/icons/voucher.png',  'texto' => 'Stock permanente y despacho agil'],
                ['icon' => 'temp02/img/icons/sales.png',    'texto' => 'Personaliza con el logo de tu empresa'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at'       => now(),
        ];

        $datos = array_filter(
            $datos,
            fn ($col) => Schema::hasColumn('web_header', $col),
            ARRAY_FILTER_USE_KEY
        );

        DB::table('web_header')->where('id_header', 1)->update($datos);
    }

    public function down(): void
    {
        // Datos de contenido: se editan desde el admin; no se revierte automáticamente.
    }
};
