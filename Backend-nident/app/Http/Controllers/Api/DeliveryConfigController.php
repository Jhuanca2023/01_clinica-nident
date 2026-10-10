<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DelConfiguracionDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * DeliveryConfigController — módulo "Delivery" del admin.
 * Gestiona la configuración global de delivery (del_configuracion_delivery)
 * y centraliza el cálculo del costo que usan el checkout y el pedido.
 */
class DeliveryConfigController extends Controller
{
    /**
     * GET /api/delivery-config/obtener
     * Devuelve la configuración activa de delivery.
     */
    public function obtener()
    {
        $config = DelConfiguracionDelivery::obtenerActivo();
        $data = $config->toArray();
        $data['mensaje_delivery_deshabilitado_defecto'] = DelConfiguracionDelivery::MENSAJE_SIN_DELIVERY;

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * PUT /api/delivery-config/actualizar
     * Actualiza (o crea) la configuración de delivery.
     */
    public function actualizar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_habilitado'             => 'required|boolean',
            'mensaje_delivery_deshabilitado'  => 'nullable|string|max:300',
            'tienda_latitud'                  => 'required|numeric|between:-90,90',
            'tienda_longitud'                 => 'required|numeric|between:-180,180',
            'tienda_direccion'                => 'required|string|max:300',
            'google_maps_api_key'             => 'nullable|string|max:200',
            'radio_metros_gratis'             => 'required|integer|min:0|max:50000',
            'monto_minimo_radio'              => 'required|numeric|min:0',
            'costo_distritos_especiales'      => 'required|numeric|min:0',
            'distritos_especiales_sin_minimo' => 'required|boolean',
            'costo_otros_distritos'           => 'required|numeric|min:0',
            'monto_minimo_compra'             => 'required|numeric|min:0',
            'costo_envio_baja_compra'         => 'required|numeric|min:0',
            'bloquear_bajo_minimo'            => 'required|boolean',
            'distritos_especiales'            => 'nullable|array',
            'distritos_especiales.*'          => 'string|max:100',
            'distritos_gratis'                => 'nullable|array',
            'distritos_gratis.*'              => 'string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $gratis = $this->limpiarDistritos($request->distritos_gratis ?? []);
        // Un distrito no puede ser "gratis" y "especial" a la vez: gana "gratis"
        $especiales = array_values(array_filter(
            $this->limpiarDistritos($request->distritos_especiales ?? []),
            fn ($d) => ! in_array(self::normalizar($d), array_map([self::class, 'normalizar'], $gratis), true)
        ));

        $datos = [
            'delivery_habilitado'             => (bool) $request->delivery_habilitado,
            'mensaje_delivery_deshabilitado'  => trim((string) $request->mensaje_delivery_deshabilitado) ?: null,
            'tienda_latitud'                  => $request->tienda_latitud,
            'tienda_longitud'                 => $request->tienda_longitud,
            'tienda_direccion'                => $request->tienda_direccion,
            'google_maps_api_key'             => trim((string) $request->google_maps_api_key) ?: null,
            'radio_metros_gratis'             => $request->radio_metros_gratis,
            'monto_minimo_radio'              => $request->monto_minimo_radio,
            'costo_distritos_especiales'      => $request->costo_distritos_especiales,
            'distritos_especiales_sin_minimo' => (bool) $request->distritos_especiales_sin_minimo,
            'costo_otros_distritos'           => $request->costo_otros_distritos,
            'monto_minimo_compra'             => $request->monto_minimo_compra,
            'costo_envio_baja_compra'         => $request->costo_envio_baja_compra,
            'bloquear_bajo_minimo'            => (bool) $request->bloquear_bajo_minimo,
            'distritos_especiales'            => $especiales,
            'distritos_gratis'                => $gratis,
            'activo'                          => 1,
        ];

        $config = DelConfiguracionDelivery::where('activo', 1)->first();
        if ($config) {
            $config->update($datos);
        } else {
            $config = DelConfiguracionDelivery::create($datos);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Configuración de delivery actualizada correctamente.',
            'data'    => $config->fresh(),
        ]);
    }

    /**
     * PUT /api/delivery-config/estado
     * Activa o desactiva el delivery sin tocar el resto de la configuración.
     */
    public function cambiarEstado(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_habilitado' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $config = DelConfiguracionDelivery::where('activo', 1)->first();
        if (! $config) {
            $config = DelConfiguracionDelivery::obtenerActivo();
            $config->save();
        }

        $config->update(['delivery_habilitado' => (bool) $request->delivery_habilitado]);

        return response()->json([
            'status'  => 'success',
            'message' => $config->delivery_habilitado ? 'Delivery activado.' : 'Delivery desactivado.',
            'data'    => $config->fresh(),
        ]);
    }

    /**
     * POST /api/delivery-config/calcular
     * Calcula el costo de delivery dado un distrito y lat/lng del cliente (opcional).
     */
    public function calcular(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distrito'         => 'required|string|max:100',
            'lat_cliente'      => 'nullable|numeric|between:-90,90',
            'lng_cliente'      => 'nullable|numeric|between:-180,180',
            'subtotal_carrito' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $resultado = self::resolverCostoDelivery(
            DelConfiguracionDelivery::obtenerActivo(),
            trim($request->distrito),
            $request->lat_cliente ? (float) $request->lat_cliente : null,
            $request->lng_cliente ? (float) $request->lng_cliente : null,
            (float) ($request->subtotal_carrito ?? 0)
        );

        return response()->json(['status' => 'success', 'data' => $resultado]);
    }

    /**
     * Lógica central de delivery — todo configurable desde el módulo Delivery.
     *
     * Orden de prioridad:
     *  0. Delivery desactivado → sin costo; se coordina entrega o recojo.
     *  1. Radio cercano a la tienda → GRATIS desde monto_minimo_radio.
     *  2. Distritos gratis → GRATIS desde monto_minimo_compra.
     *  3. Distritos especiales → costo fijo (siempre, o desde el mínimo).
     *  4. Otros distritos → costo desde el mínimo.
     *
     * Bajo el mínimo (casos 1-2 siempre; 3-4 si no son "sin mínimo") se cobra
     * costo_envio_baja_compra, o se bloquea la compra si bloquear_bajo_minimo = 1
     * (el bloqueo aplica solo a los casos 3-4: los gratis nunca bloquean).
     */
    public static function resolverCostoDelivery(
        DelConfiguracionDelivery $config,
        string $distrito,
        ?float $latCliente,
        ?float $lngCliente,
        float $subtotal
    ): array {
        $montoMinimo      = (float) $config->monto_minimo_compra;
        $montoMinimoRadio = (float) ($config->monto_minimo_radio ?? 0);
        $costoBajaCompra  = (float) ($config->costo_envio_baja_compra ?? 5);
        $bloquear         = (bool) ($config->bloquear_bajo_minimo ?? false);

        $distritoNorm = self::normalizar($distrito);
        $esGratis   = in_array($distritoNorm, array_map([self::class, 'normalizar'], $config->distritos_gratis ?? []), true);
        $esEspecial = ! $esGratis
            && in_array($distritoNorm, array_map([self::class, 'normalizar'], $config->distritos_especiales ?? []), true);
        $nombre = self::nombreVisible($distrito, array_merge($config->distritos_gratis ?? [], $config->distritos_especiales ?? []));

        $base = [
            'costo_envio'        => 0.00,
            'envio_gratis'       => false,
            'compra_baja'        => false,
            'bloqueado'          => false,
            'deshabilitado'      => false,
            'razon'              => '',
            'mensaje'            => '',
            'mensaje_secundario' => null,
            'distancia_metros'   => null,
            'monto_minimo'       => $montoMinimo,
            'subtotal'           => $subtotal,
            'falta_para_minimo'  => 0,
            'distrito_especial'  => $esEspecial || $esGratis,
        ];

        // ── 0. Delivery desactivado ──────────────────────────────────────
        if (! $config->deliveryHabilitado()) {
            return array_merge($base, [
                'deshabilitado' => true,
                'razon'         => 'delivery_deshabilitado',
                'mensaje'       => $config->mensajeSinDelivery(),
            ]);
        }

        // ── 1. Radio cercano a la tienda (nunca bloquea) ─────────────────
        if ($latCliente !== null && $lngCliente !== null && (int) $config->radio_metros_gratis > 0) {
            $distancia = DelConfiguracionDelivery::calcularDistanciaMetros(
                $config->tienda_latitud, $config->tienda_longitud, $latCliente, $lngCliente
            );

            if ($distancia <= $config->radio_metros_gratis) {
                $base['distancia_metros'] = round($distancia);
                if ($subtotal >= $montoMinimoRadio || $subtotal == 0) {
                    return array_merge($base, [
                        'envio_gratis' => true,
                        'razon'        => 'dentro_radio',
                        'mensaje'      => '¡Envío GRATIS! Estás a ' . round($distancia) . ' m de nuestra tienda.',
                        'monto_minimo' => $montoMinimoRadio,
                    ]);
                }

                $falta = $montoMinimoRadio - $subtotal;
                return array_merge($base, [
                    'costo_envio'        => $costoBajaCompra,
                    'compra_baja'        => true,
                    'razon'              => 'radio_compra_baja',
                    'mensaje'            => 'Delivery S/ ' . self::s($costoBajaCompra) . ' — Agrega S/ ' . self::s($falta)
                                            . ' más y el envío es GRATIS (estás a ' . round($distancia) . ' m de la tienda)',
                    'mensaje_secundario' => 'Zona cercana: envío GRATIS desde S/ ' . self::s($montoMinimoRadio),
                    'monto_minimo'       => $montoMinimoRadio,
                    'falta_para_minimo'  => round($falta, 2),
                ]);
            }
        }

        // ── 2. Distritos gratis (nunca bloquean) ─────────────────────────
        if ($esGratis) {
            if ($subtotal >= $montoMinimo || $subtotal == 0) {
                return array_merge($base, [
                    'envio_gratis' => true,
                    'razon'        => 'distrito_gratis',
                    'mensaje'      => '¡Envío GRATIS a ' . $nombre . '!'
                                      . ($montoMinimo > 0 ? ' Tu compra supera S/ ' . self::s($montoMinimo) : ''),
                ]);
            }

            $falta = $montoMinimo - $subtotal;
            return array_merge($base, [
                'costo_envio'        => $costoBajaCompra,
                'compra_baja'        => true,
                'razon'              => 'distrito_gratis_compra_baja',
                'mensaje'            => 'Delivery S/ ' . self::s($costoBajaCompra) . ' — Agrega S/ ' . self::s($falta)
                                        . ' más y el envío a ' . $nombre . ' es GRATIS',
                'mensaje_secundario' => $nombre . ': envío GRATIS desde S/ ' . self::s($montoMinimo),
                'falta_para_minimo'  => round($falta, 2),
            ]);
        }

        // ── 3 y 4. Distritos especiales / otros distritos ────────────────
        $costo = (float) ($esEspecial ? $config->costo_distritos_especiales : $config->costo_otros_distritos);
        $sinMinimo = $esEspecial && (bool) ($config->distritos_especiales_sin_minimo ?? false);
        $razon = $esEspecial ? 'distrito_especial' : 'otro_distrito';

        if (! $sinMinimo && $subtotal > 0 && $subtotal < $montoMinimo) {
            $falta = $montoMinimo - $subtotal;

            if ($bloquear) {
                return array_merge($base, [
                    'compra_baja'        => true,
                    'bloqueado'          => true,
                    'razon'              => $razon . '_bloqueado',
                    'mensaje'            => 'Agrega S/ ' . self::s($falta) . ' más en productos para continuar con el delivery',
                    'mensaje_secundario' => 'Compra mínima para delivery a ' . $nombre . ': S/ ' . self::s($montoMinimo),
                    'falta_para_minimo'  => round($falta, 2),
                ]);
            }

            // "Agrega y paga solo" únicamente si comprar más abarata el delivery
            $mensaje = 'Delivery S/ ' . self::s($costoBajaCompra);
            $secundario = 'Tarifa para compras menores a S/ ' . self::s($montoMinimo) . '.';
            if ($costo < $costoBajaCompra) {
                $mensaje .= ' — Agrega S/ ' . self::s($falta) . ' más y paga solo S/ ' . self::s($costo);
                $secundario = 'Con compras desde S/ ' . self::s($montoMinimo) . ' el delivery es S/ ' . self::s($costo);
            }

            return array_merge($base, [
                'costo_envio'        => $costoBajaCompra,
                'compra_baja'        => true,
                'razon'              => 'compra_baja',
                'mensaje'            => $mensaje,
                'mensaje_secundario' => $secundario,
                'falta_para_minimo'  => round($falta, 2),
            ]);
        }

        return array_merge($base, [
            'costo_envio'  => $costo,
            'envio_gratis' => $costo <= 0,
            'razon'        => $razon,
            'mensaje'      => $costo > 0
                ? 'Delivery a ' . $nombre . ': S/ ' . self::s($costo)
                : '¡Envío GRATIS a ' . $nombre . '!',
        ]);
    }

    /** Compara distritos sin tildes ni mayúsculas ("Jesús María" = "jesus maria"). */
    public static function normalizar(?string $texto): string
    {
        $texto = mb_strtolower(trim((string) $texto), 'UTF-8');
        $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);

        return preg_replace('/\s+/', ' ', $texto);
    }

    /** Nombre tal como está escrito en la configuración ("Santiago de Surco"), o el recibido. */
    private static function nombreVisible(string $distrito, array $configurados): string
    {
        $clave = self::normalizar($distrito);
        foreach ($configurados as $d) {
            if (self::normalizar($d) === $clave) {
                return trim($d);
            }
        }

        return trim($distrito);
    }

    private static function s(float $monto): string
    {
        return number_format($monto, 2);
    }

    /** @return list<string> */
    private function limpiarDistritos(array $lista): array
    {
        $vistos = [];
        $salida = [];
        foreach ($lista as $d) {
            $d = trim((string) $d);
            $clave = self::normalizar($d);
            if ($d === '' || isset($vistos[$clave])) {
                continue;
            }
            $vistos[$clave] = true;
            $salida[] = $d;
        }

        return $salida;
    }
}
