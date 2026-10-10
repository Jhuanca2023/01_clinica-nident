<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: DelConfiguracionDelivery
 * Tabla: del_configuracion_delivery
 *
 * Gestiona la configuración global de delivery:
 *  - Ubicación de la tienda (lat/lng) y radio gratis (metros)
 *  - Distritos especiales y sus costos
 *  - Monto mínimo de compra para desbloquear la compra
 */
class DelConfiguracionDelivery extends Model
{
    use HasFactory;

    protected $table      = 'del_configuracion_delivery';
    protected $primaryKey = 'id';
    protected $guarded    = [];

    protected $casts = [
        'distritos_especiales'       => 'array',
        'distritos_gratis'           => 'array',
        'delivery_habilitado'        => 'boolean',
        'distritos_especiales_sin_minimo' => 'boolean',
        'bloquear_bajo_minimo'       => 'boolean',
        'monto_minimo_radio'         => 'float',
        'tienda_latitud'             => 'float',
        'tienda_longitud'            => 'float',
        'radio_metros_gratis'        => 'integer',
        'costo_distritos_especiales' => 'float',
        'costo_otros_distritos'      => 'float',
        'monto_minimo_compra'        => 'float',
        'costo_envio_baja_compra'    => 'float',
        'activo'                     => 'boolean',
    ];

    public const MENSAJE_SIN_DELIVERY = 'Por ahora no realizamos delivery. Coordinaremos contigo la entrega o el recojo de tu pedido.';

    /** ¿La tienda hace delivery? (interruptor del módulo Delivery). */
    public function deliveryHabilitado(): bool
    {
        return (bool) ($this->delivery_habilitado ?? true);
    }

    /** Texto que ve el cliente cuando el delivery está desactivado. */
    public function mensajeSinDelivery(): string
    {
        $texto = trim((string) ($this->mensaje_delivery_deshabilitado ?? ''));

        return $texto !== '' ? $texto : self::MENSAJE_SIN_DELIVERY;
    }

    /**
     * Devuelve el registro de configuración activo (singleton).
     * Si no existe ninguno, retorna valores por defecto en memoria.
     */
    public static function obtenerActivo(): self
    {
        $config = self::where('activo', 1)->first();

        if (!$config) {
            $config = new self([
                'tienda_latitud'             => -12.1770190,
                'tienda_longitud'            => -77.0100450,
                'tienda_direccion'           => 'Jirón Juno Mz. C Lte. 7E, La Campiña, Chorrillos',
                'radio_metros_gratis'        => 500,
                'costo_distritos_especiales' => 10.00,
                'costo_otros_distritos'      => 10.00,
                'monto_minimo_compra'        => 100.00,
                'costo_envio_baja_compra'    => 5.00,
                'distritos_especiales'       => ['Chorrillos', 'Barranco', 'Santiago de Surco'],
                'distritos_gratis'           => [],
                'delivery_habilitado'        => true,
                'monto_minimo_radio'         => 100.00,
                'distritos_especiales_sin_minimo' => false,
                'bloquear_bajo_minimo'       => false,
                'activo'                     => true,
            ]);
        }

        return $config;
    }

    /**
     * Calcula la distancia en metros entre dos coordenadas (Haversine).
     */
    public static function calcularDistanciaMetros(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $radioTierra = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $radioTierra * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
