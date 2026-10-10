<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Pasarela" WhatsApp: no cobra en línea. Al activarla, el checkout registra
 * el pedido (con su código) y lo envía al WhatsApp de ventas. Convive con
 * Culqi / Izipay / Stripe / PayPal bajo la misma regla: solo una activa.
 */
return new class extends Migration
{
    private const CONFIGS = [
        ['WHATSAPP_NUMERO', 'Número de WhatsApp de ventas (vacío = el del sitio)'],
        ['WHATSAPP_ENCABEZADO', 'Encabezado del mensaje del pedido'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('pago_pasarela') || ! Schema::hasTable('pago_pasarela_config')) {
            return;
        }

        $now = now();
        $id = DB::table('pago_pasarela')->where('codigo', 'whatsapp')->value('id_pago_pasarela');

        if (! $id) {
            $datos = [
                'codigo'      => 'whatsapp',
                'nombre'      => 'WhatsApp',
                'descripcion' => 'Pedidos sin pago en línea. El cliente completa sus datos, el pedido se registra con su código y se envía a tu WhatsApp de ventas para coordinar el pago y la entrega.',
                'logo_url'    => null,
                'is_activo'   => 0,
                'orden'       => (int) DB::table('pago_pasarela')->max('orden') + 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
            if (Schema::hasColumn('pago_pasarela', 'modo')) {
                $datos['modo'] = 'produccion';
            }
            $id = DB::table('pago_pasarela')->insertGetId($datos);
        }

        foreach (self::CONFIGS as [$clave, $etiqueta]) {
            DB::table('pago_pasarela_config')->updateOrInsert(
                ['id_pago_pasarela' => $id, 'clave' => $clave],
                ['etiqueta' => $etiqueta, 'es_secreto' => 0, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        $id = DB::table('pago_pasarela')->where('codigo', 'whatsapp')->value('id_pago_pasarela');
        if (! $id) {
            return;
        }

        // Si WhatsApp estaba activa, se deja Culqi como pasarela por defecto
        $estabaActiva = (bool) DB::table('pago_pasarela')->where('id_pago_pasarela', $id)->value('is_activo');
        DB::table('pago_pasarela_config')->where('id_pago_pasarela', $id)->delete();
        DB::table('pago_pasarela')->where('id_pago_pasarela', $id)->delete();
        if ($estabaActiva) {
            DB::table('pago_pasarela')->where('codigo', 'culqi')->update(['is_activo' => 1]);
        }
    }
};
