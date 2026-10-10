<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tarjeta de asesoría por WhatsApp del detalle de producto (web_shopDetail):
 * sus textos pasan a ser administrables desde el módulo WhatsApp del admin.
 * Los valores por defecto son los textos que tenía fijos la plantilla.
 */
return new class extends Migration
{
    private const TABLA = 'web_whatsapp_config';

    /** columna => valor por defecto */
    private const COLUMNAS = [
        'card_activo'    => 'S',
        'card_nombre'    => 'Valento',
        'card_rol'       => 'Asesoría comercial',
        'card_estado'    => 'Disponible ahora · responde en minutos',
        'card_titulo'    => 'Recibe asesoría personalizada y compra con seguridad',
        'card_texto'     => 'Te ayudamos a elegir tallas, colores y cotización mayorista por WhatsApp.',
        'card_boton'     => 'Hablar con un asesor ahora',
        'card_mensaje'   => 'Hola, quiero cotizar el producto: {producto}',
        'card_respuesta' => 'Tiempo promedio de respuesta: 1 minuto',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLA)) {
            return;
        }

        Schema::table(self::TABLA, function (Blueprint $table) {
            foreach (array_keys(self::COLUMNAS) as $col) {
                if (Schema::hasColumn(self::TABLA, $col)) {
                    continue;
                }
                if ($col === 'card_activo') {
                    $table->char($col, 1)->default('S');
                } elseif (in_array($col, ['card_texto', 'card_mensaje'], true)) {
                    $table->text($col)->nullable();
                } else {
                    $table->string($col, 150)->nullable();
                }
            }
        });

        // Rellenar solo lo que esté vacío (no pisa textos ya editados)
        foreach (self::COLUMNAS as $col => $valor) {
            DB::table(self::TABLA)
                ->where(fn ($q) => $q->whereNull($col)->orWhere($col, ''))
                ->update([$col => $valor]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLA)) {
            return;
        }

        Schema::table(self::TABLA, function (Blueprint $table) {
            foreach (array_keys(self::COLUMNAS) as $col) {
                if (Schema::hasColumn(self::TABLA, $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
