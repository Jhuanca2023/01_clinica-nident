<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Session;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\EnvioDepartment;
use App\Models\EnvioDistrict;
use App\Models\EnvioShippingAgency;
use App\Models\EnvioShippingSetting;
use App\Models\DelConfiguracionDelivery;
use App\Services\EnvioService;
use App\Http\Controllers\Api\DeliveryConfigController;
use Illuminate\Support\Facades\DB;

class CalcularEnvioController extends Controller
{
    // ✅ CONFIGURACIÓN: Días hábiles (true = trabaja, false = no trabaja)
    private $diasHabiles = [
        0 => false, // Domingo
        1 => true, // Lunes
        2 => true, // Martes
        3 => true, // Miércoles
        4 => true, // Jueves
        5 => true, // Viernes
        6 => false // Sábado
    ];

    protected $envioService;

    public function __construct(EnvioService $envioService)
    {
        $this->envioService = $envioService;
    }

    public function lista_distritos($idDepartamento)
    {
        $districts = $this->envioService->listarDistritosEnvio($idDepartamento);
        return response()->json($districts);
    }

    public function calcular_envio(Request $request)
    {
        $request->merge([
            'txt_peso_kilogramo'  => $request->input('txt_peso_kilogramo') ?? 0,
            'txt_paqueta_medidas' => filled($request->input('txt_paqueta_medidas'))
                ? (string) $request->input('txt_paqueta_medidas')
                : '0',
        ]);

        $request->validate([
            'address_departamento' => 'nullable|string|max:100',
            'address_distrito'     => 'nullable|string|max:100',
            'txt_paqueta_medidas'  => 'required|string',
            'txt_peso_kilogramo'   => 'nullable|numeric',

            'txt_id_producto'      => 'nullable|numeric',
            // Coordenadas opcionales del cliente (Case 3 — radar 500 m)
            'lat_cliente'          => 'nullable|numeric|between:-90,90',
            'lng_cliente'          => 'nullable|numeric|between:-180,180',
            // Subtotal del carrito (para validar compra mínima)
            'subtotal_carrito'     => 'nullable|numeric|min:0',
        ]);

        $result = $this->envioService->getEnvioData($request, true);

        // ── Pago contra entrega solo para Lima Metropolitana ──────────────
        $departamento = $request->address_departamento ?? $request->txt_listar_departamentos ?? '';
        if ($result && $departamento === 'Lima Metropolitana') {
            $result->pago_contra_entrega = 1;
            if (!isset($result->precio_envio) || !is_numeric($result->precio_envio)) {
                $result->precio_envio = 0;
            }
        }

        // ── Hora de corte ─────────────────────────────────────────────────
        if ($result && isset($result->hora_regresiva)) {
            $result->hora_corte_procesada = $this->procesarHoraCorte($result->hora_regresiva);
        }

        if (!$result) {
            return response()->json(['error' => 'No se pudo calcular el envío o faltan datos.'], 400);
        }

        // ── Lógica de los 4 casos de delivery ─────────────────────────────
        $distrito     = $request->address_distrito ?? $request->txt_listar_distritos ?? '';
        $latCliente   = $request->lat_cliente  ? (float) $request->lat_cliente  : null;
        $lngCliente   = $request->lng_cliente  ? (float) $request->lng_cliente  : null;
        $subtotal     = (float) ($request->subtotal_carrito ?? 0);

        $configDelivery = DelConfiguracionDelivery::obtenerActivo();
        $delivery       = DeliveryConfigController::resolverCostoDelivery(
            $configDelivery, $distrito, $latCliente, $lngCliente, $subtotal
        );

        // Inyectar la info de delivery en el resultado existente del SP
        $result->delivery_costo        = $delivery['costo_envio'];
        $result->delivery_gratis       = $delivery['envio_gratis'];
        $result->delivery_compra_baja  = $delivery['compra_baja'];
        $result->delivery_razon        = $delivery['razon'];
        $result->delivery_mensaje      = $delivery['mensaje'];
        $result->delivery_mensaje2     = $delivery['mensaje_secundario'];
        $result->delivery_monto_minimo = $delivery['monto_minimo'];
        $result->delivery_falta        = $delivery['falta_para_minimo'];
        $result->delivery_distancia_m  = $delivery['distancia_metros'];
        $result->delivery_bloqueado    = $delivery['bloqueado'] ?? false;
        $result->delivery_deshabilitado = $delivery['deshabilitado'] ?? false;

        return response()->json($result);
    }

    /**
     * ✅ FUNCIÓN PRINCIPAL: Procesa la hora de corte considerando días hábiles
     */
    private function procesarHoraCorte($horaCorte)
    {
        try {
            $now = new \DateTime('now', new \DateTimeZone('America/Lima'));

            // Limpiar formato de hora
            $horaLimpia = $horaCorte;
            if (substr_count($horaCorte, ':') === 2) {
                $partes = explode(':', $horaCorte);
                $horaLimpia = $partes[0] . ':' . $partes[1];
            }

            // Crear hora de corte de hoy
            $horaCorteHoy = \DateTime::createFromFormat('H:i', $horaLimpia, new \DateTimeZone('America/Lima'));

            if (!$horaCorteHoy) {
                Log::error('Error al parsear hora de corte: ' . $horaCorte);
                return null;
            }

            $horaCorteHoy->setDate($now->format('Y'), $now->format('m'), $now->format('d'));

            // Calcular ventana de 12 horas antes de la hora de corte
            $inicioVentana = clone $horaCorteHoy;
            $inicioVentana->modify('-12 hours');

            $diaActual = (int)$now->format('w'); // 0=Domingo, 1=Lunes, ..., 6=Sábado

            // ✅ CASO 1: Estamos en FIN DE SEMANA (Sábado o Domingo)
            if (!$this->diasHabiles[$diaActual]) {
                $fechaEntrega = $this->calcularProximoDiaHabil($now);

                return [
                    'tipo' => 'lunes', // ✅ Nuevo tipo específico
                    'fecha_entrega' => $fechaEntrega->format('d \d\e M'),
                    'dia' => $fechaEntrega->format('d'),
                    'mes' => $this->obtenerMesEspanol($fechaEntrega->format('n')),
                    'dia_nombre' => $this->obtenerDiaSemana($fechaEntrega->format('w')),
                    'timestamp_proxima_ventana' => null,
                    'hora_corte' => $horaCorteHoy->format('H:i')
                ];
            }

            // ✅ CASO 2: Dentro de la ventana de 12 horas (Lunes-Viernes)
            if ($now >= $inicioVentana && $now < $horaCorteHoy) {
                $diferencia = $now->diff($horaCorteHoy);

                return [
                    'tipo' => 'hoy',
                    'horas_restantes' => (int)$diferencia->format('%h'),
                    'minutos_restantes' => (int)$diferencia->format('%i'),
                    'segundos_restantes' => (int)$diferencia->format('%s'),
                    'hora_corte' => $horaCorteHoy->format('H:i'),
                    'timestamp_corte' => $horaCorteHoy->getTimestamp()
                ];
            }

            // ✅ CASO 3: Fuera de la ventana (después de las 3pm)
            $fechaEntrega = clone $now;
            $fechaEntrega->modify('+1 day');

            // ✅ VALIDAR: Si mañana cae en fin de semana, calcular próximo día hábil
            $fechaEntrega = $this->calcularProximoDiaHabil($fechaEntrega);

            // ✅ DETERMINAR EL TIPO SEGÚN EL DÍA
            $diaEntrega = (int)$fechaEntrega->format('w');
            $tipo = ($diaEntrega === 1) ? 'lunes' : 'manana'; // Si es lunes, tipo especial

            return [
                'tipo' => $tipo,
                'fecha_entrega' => $fechaEntrega->format('d \d\e M'),
                'dia' => $fechaEntrega->format('d'),
                'mes' => $this->obtenerMesEspanol($fechaEntrega->format('n')),
                'dia_nombre' => $this->obtenerDiaSemana($fechaEntrega->format('w')),
                'timestamp_proxima_ventana' => null,
                'hora_corte' => $horaCorteHoy->format('H:i')
            ];

        }
        catch (\Exception $e) {
            Log::error('Error procesando hora de corte: ' . $e->getMessage() . ' - Hora recibida: ' . $horaCorte);
            return null;
        }
    }

    /**
     * ✅ NUEVA FUNCIÓN: Calcula el próximo día hábil
     * Si la fecha cae en sábado/domingo, retorna el siguiente lunes
     */
    private function calcularProximoDiaHabil(\DateTime $fecha)
    {
        $fechaEntrega = clone $fecha;
        $diasSumados = 0;
        $maxIteraciones = 7; // Seguridad para evitar loops infinitos

        // Buscar el próximo día hábil
        while ($diasSumados < $maxIteraciones) {
            $diaSemana = (int)$fechaEntrega->format('w');

            // Si es día hábil, retornar
            if ($this->diasHabiles[$diaSemana]) {
                return $fechaEntrega;
            }

            // Sino, sumar un día y continuar
            $fechaEntrega->modify('+1 day');
            $diasSumados++;
        }

        // Fallback: Si no encuentra día hábil en 7 días, retornar fecha original
        return $fechaEntrega;
    }

    /**
     * ✅ Helper: Obtener nombre del día en español
     */
    private function obtenerDiaSemana($numeroDia)
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
        return $dias[(int)$numeroDia] ?? 'Lunes';
    }

    /**
     * ✅ Helper: Obtener mes en español
     */
    private function obtenerMesEspanol($numeroMes)
    {
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];
        return $meses[(int)$numeroMes] ?? 'Ene';
    }
}