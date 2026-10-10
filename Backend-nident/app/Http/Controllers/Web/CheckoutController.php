<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EnvioService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Lang;
use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\CulqiPaymentRejectedException;
use App\Services\CulqiService;
use App\Services\IzipayService;
use App\Services\PaymentGatewayManager;
use App\Services\StripeService;
use App\Services\PaypalService;
use Illuminate\Support\Facades\Cache;
use App\Support\ContactoValento;
use App\Support\LiamPageData;
use App\Support\UbigeoQuery;
use App\Support\MysqlTimezone;
use App\Support\ProductoImagePath;
use App\Support\PedidoProductoRepositorio;
use App\Support\ProductoStockService;
use App\Support\ClienteSesion;

class CheckoutController extends Controller
{
    protected $envioService;

    /** Cache por request del resultado 4 casos de delivery */
    private ?array $deliveryResultado = null;

    public function __construct(EnvioService $envioService)
    {
        $this->envioService = $envioService;
    }

    public function index()
    {
        if (Cart::count() === 0) {
            return redirect('/tienda');
        }

        $listarDepartamentos = DB::table('ubigeo_peru_departments')->get();
        $listaDepartamentos = $this->envioService->listarDepartamentosEnvio();

        // Obtener los IDs de productos del carrito
        // Obtener los IDs de productos del carrito desde las opciones
        $productosEnCarrito = Cart::content()->pluck('options.id_producto')->toArray();



        // Buscar el producto con mayor peso_kilogramo
        $utimoMayorPeso = DB::table('administracion_producto')
            ->select('peso_kilogramo', 'paquete_dimencion', 'paquete_medidas', 'id_producto')
            ->whereIn('id_producto', $productosEnCarrito)
            ->orderBy('peso_kilogramo', 'DESC')
            ->first();

        // dd($utimoMayorPeso);

        $pasarelaActiva = \App\Services\PaymentGatewayManager::getActiveCodigo();
        $izipayPublicKey = null;
        $paypalClientId = null;

        if ($pasarelaActiva === 'izipay') {
            $izipay = new \App\Services\IzipayService();
            $izipayPublicKey = $izipay->getPublicKey();
            } elseif ($pasarelaActiva === 'paypal') {
            $paypalClientId = \App\Services\PaymentGatewayManager::getConfig('PAYPAL_CLIENT_ID');
        }
        $stripePublicKey = null;
        if ($pasarelaActiva === 'stripe') {
            $stripe               = new \App\Services\StripeService();
            $stripePublicKey = $stripe->getPublicKey();
        }
        // Filtrar solo Lima Metropolitana si es necesario
        // (listaDepartamentos_envio se mantiene por compatibilidad; el checkout usa ubigeo completo)
        $listaDepartamentos = $listaDepartamentos->filter(function ($item) {
            return $item->address_departamento === 'Lima Metropolitana';
        })->values();

        // Ubigeo: ordenar departamentos y dejar Lima primero para el combo
        $listarDepartamentos = $listarDepartamentos
            ->map(function ($d) {
                $d->name = trim((string) ($d->name ?? ''));
                return $d;
            })
            ->sortBy(function ($d) {
                return strcasecmp($d->name, 'Lima') === 0 ? '0' : ('1' . $d->name);
            })
            ->values();

        // ── Lógica Yape ──────────────────────────────────────────────────────
        $cartItemsData = Cart::content();
        $totalNormal = floatval(str_replace(',', '', Cart::total()));
        $totalYape = $totalNormal;
        $yapeDisponible = false;

        $idsCarrito = $cartItemsData->pluck('options.id_producto')->filter()->unique()->values()->toArray();
        $preciosYape = collect();

        if (!empty($idsCarrito)) {
            $preciosYape = DB::table('administracion_producto')
                ->whereIn('id_producto', $idsCarrito)
                ->whereNotNull('precio_yape')
                ->where('precio_yape', '>', 0)
                ->pluck('precio_yape', 'id_producto');

            if ($preciosYape->count() === count($idsCarrito)) {
                $yapeDisponible = true;
                $totalYape = 0;
                foreach ($cartItemsData as $item) {
                    $idProd = $item->options->id_producto ?? null;
                    $precioYp = $idProd ? ($preciosYape[$idProd] ?? $item->price) : $item->price;
                    $totalYape += $precioYp * $item->qty;
                }
                $totalYape = round($totalYape, 2);
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        return view('web.pages.web_checkout', array_merge(LiamPageData::layout('checkout'), [
            'utimoMayorPeso' => $utimoMayorPeso,
            'cartItemsListaCheckout' => Cart::content(),
            'listarDepartamentos_simple' => $listarDepartamentos,
            'listaDepartamentos_envio' => $listaDepartamentos,
            'pasarela_activa' => $pasarelaActiva,
            'izipay_public_key' => $izipayPublicKey,
            'paypal_client_id' => $paypalClientId,
            'yapeDisponible' => $yapeDisponible,
            'totalNormal' => $totalNormal,
            'totalYape' => $totalYape,
            'preciosYape' => $preciosYape,
            'stripe_public_key'             => $stripePublicKey,
        ]));
    }

    /**
     * Obtener provincias por departamento
     */
    public function getProvinciasByDepartamento(Request $request)
    {
        try {
            $departmentId = $request->input('department_id');

            if (!$departmentId) {
                return response()->json(['error' => 'Department ID requerido'], 400);
            }

            $provincias = UbigeoQuery::provincias($departmentId);

            return response()->json([
                'success' => true,
                'provincias' => $provincias,
            ]);
        } catch (\Exception $e) {
            Log::warning('Checkout ubigeo provincias: ' . $e->getMessage(), [
                'department_id' => $request->input('department_id'),
            ]);
            return response()->json([
                'error' => 'Error al obtener provincias: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener distritos por provincia
     */
    public function getDistritosByProvincia(Request $request)
    {
        try {
            $provinceId = $request->input('province_id');
            $departmentId = $request->input('department_id');

            if (!$provinceId || !$departmentId) {
                return response()->json(['error' => 'Province ID y Department ID requeridos'], 400);
            }

            $distritos = UbigeoQuery::distritos($departmentId, $provinceId);

            return response()->json([
                'success' => true,
                'distritos' => $distritos,
            ]);
        } catch (\Exception $e) {
            Log::warning('Checkout ubigeo distritos: ' . $e->getMessage(), [
                'department_id' => $request->input('department_id'),
                'province_id' => $request->input('province_id'),
            ]);
            return response()->json([
                'error' => 'Error al obtener distritos: ' . $e->getMessage(),
            ], 500);
        }
    }

   
    private function requestHasFormDataForPasarela(Request $request): bool
    {
        if ($request->has('form_data')) {
            return true;
        }

        return is_array($request->input('form_data'));
    }

    private function obtener_id_cliente()
    {
        return ClienteSesion::idCliente();
    }




    private function validar_codigo_pedido($tipo = 'B')
    {
        $count_ = DB::table('ope_pedidos')->count() + 1; // +1 para el siguiente correlativo

        // Generar código de pedido único
        do {
            $codigo_pedido = 'PED-' . Str::upper(Str::random(6));
        } while (DB::table('ope_pedidos')->where('codigo_pedido', $codigo_pedido)->exists());

        // Generar código de boleta o factura (B001 para Boleta, F001 para Factura)
        $prefijo = ($tipo === 'F') ? 'F001' : 'B001';
        $codigo_boleta_o_factura = $prefijo . '-' . str_pad($count_, 8, '0', STR_PAD_LEFT);

        return [
            'codigo_pedido' => $codigo_pedido,
            'codigo_boleta_o_factura' => $codigo_boleta_o_factura
        ];
    }

    public function generarFormularioCulqi(Request $request)
    {
        Log::info("CULQI_GENERATE_START", ['request' => $request->all()]);

        $this->validate($request, [
            'amount' => 'required|numeric|min:100',
            'email' => 'required|email'
        ]);

        $formData = $this->extraerFormDataArray($request);
        $precioEnvio = $this->calcularCostoEnvio($this->requestParaCalculoEnvio($request));
        $montoTotal = $this->calcularMontoTotalConEnvio((int) $request->amount, $request);

        $culqi = new CulqiService();
        $codigoPedido = $culqi->generateOrderId();
        session([
            'culqi_order_temp' => $codigoPedido,
            'culqi_form_data' => $formData,
            'culqi_amount' => $montoTotal,
        ]);

        Log::info('CULQI_GENERATE_AMOUNT', [
            'monto_base' => (int) $request->amount,
            'costo_envio' => $precioEnvio,
            'monto_total' => $montoTotal,
        ]);

        return response()->json([
            'publicKey' => $culqi->getPublicKey(),
            'amount' => $montoTotal,
            'currency' => 'PEN',
            'order' => $codigoPedido,
            'metadata' => [
                'origen' => 'web_checkout',
                'user_id' => auth()->check() ? auth()->id() : null,
                'user_email' => $request->email,
                'costo_envio' => $precioEnvio,
            ],
        ]);
    }

    /**
     * Procesar pago con Culqi - Recibe FormData
     */
    public function enviar_pago_con_culqi(Request $request)
    {
        Log::info("CULQI_CAPTURE_START", ['request' => $request->all(), 'session_data' => session('culqi_form_data')]);
        // Validación específica para Culqi (FormData)
        if (!session()->has('culqi_order_temp')) {
            return response()->json([
                'success' => false,
                'message' => "Sesión expirada. Por favor recarga la página e intenta nuevamente.",
                'result' => null,
            ]);
        }

        $this->validate($request, [
            'token' => 'required|string',
            'amount' => 'required|numeric',
            'email' => 'required|email',
            'metadata' => 'nullable|array',
        ]);

        $estado = 1; // Pedido recibido (con Culqi)

        // Asegurar que form_data esté en el request si está en la sesión
        if (! $request->has('form_data') && session()->has('culqi_form_data')) {
            $request->merge(['form_data' => session('culqi_form_data')]);
        }

        // El token Yape/Culqi se genera con el monto total (carrito + envío).
        // Debe coincidir exactamente al capturar el cargo.
        $montoCaptura = session('culqi_amount');
        if (! $montoCaptura) {
            $montoCaptura = $this->calcularMontoTotalConEnvio((int) $request->amount, $request);
        }
        $request->merge(['amount' => (int) $montoCaptura]);

        Log::info('CULQI_CAPTURE_AMOUNT', [
            'amount' => (int) $montoCaptura,
            'from_session' => session()->has('culqi_amount'),
        ]);

        return $this->procesarPedido($request, true, $estado);
    }

    /**
     * Generar orden de PayPal
     */
    public function generarFormularioPaypal(Request $request)
    {
        try {
            $totalAmount = (float) Cart::total();

            // Si viene el costo de envío, sumarlo
            $precioEnvio = $this->calcularCostoEnvio($request);
            if (is_numeric($precioEnvio)) {
                $totalAmount += $precioEnvio;
            }

            $paypal = new PaypalService();
            $codigo_ = $this->validar_codigo_pedido();

            $order = $paypal->createOrder($totalAmount, 'PEN');

            if ($order && isset($order['id'])) {
                // Guardar datos del formulario en sesión
                session(['paypal_form_data' => $request->input('form_data') ?? $request->all()]);
                
                return response()->json([
                    'success' => true,
                    'order_id' => $order['id']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al generar PayPal Order: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesar pago con PayPal (Captura)
     */
    public function enviar_pago_con_paypal(Request $request)
    {
        Log::info("PAYPAL_CAPTURE_START", [
            'order_id' => $request->paypal_order_id,
            'all_data' => $request->all()
        ]);
        try {
            // Asegurar que form_data esté en el request si está en la sesión
            if (! $request->has('form_data') && session()->has('paypal_form_data')) {
                $request->merge(['form_data' => session('paypal_form_data')]);
            }

            // Se envía 1 como estado inicial y 'paypal' como pasarela
            $response = $this->procesarPedido($request, false, 1, 'paypal');
            Log::info("PAYPAL_CAPTURE_SUCCESS", ['response' => $response]);
            return $response;
        } catch (\Exception $e) {
            Log::error("PAYPAL_CAPTURE_EXCEPTION", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => "Error al capturar el pago: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar pago sin Culqi (contraentrega) - Recibe FormData
     */
    public function enviar_pago_sin_culqi(Request $request)
    {

        // Validación para pago normal (FormData)
        $this->validate($request, [
            // Validaciones para FormData
            'txt_nombre' => 'required|string|max:255',
            'txt_apellido' => 'required|string|max:255',
            'txt_email' => 'required|email',
            'txt_direccion' => 'required|string|max:500',
            'txt_telefono' => 'required|string|max:20',
            'txt_dni' => 'required|string|max:20',
            'txt_ruc' => 'nullable|string|max:20',

            'txt_listar_departamentos' => 'required|string',
            'txt_listar_provincias' => 'required|string',
            'txt_listar_distritos' => 'required|string',

            'radio_metodo_pago' => 'required|in:1,2',
            'check_terminos' => 'required'
        ]);

        $estado = 1; // Pedido recibido (sin Culqi - contraentrega)
        return $this->procesarPedido($request, false, $estado);
    }

    /**
     * Pedido por WhatsApp (pasarela "whatsapp" activa en el admin).
     * Registra el pedido con su código, sin cobro ni descuento de stock,
     * y devuelve el enlace wa.me con el detalle para el asesor de ventas.
     */
    public function enviar_pedido_whatsapp(Request $request)
    {
        if (! \App\Services\PaymentGatewayManager::isWhatsapp()) {
            return response()->json([
                'success' => false,
                'message' => 'Los pedidos por WhatsApp no están habilitados. Recarga la página.',
                'result'  => null,
            ], 422);
        }

        $this->validate($request, [
            'txt_nombre'               => 'required|string|max:255',
            'txt_apellido'             => 'required|string|max:255',
            'txt_email'                => 'required|email',
            'txt_direccion'            => 'required|string|max:500',
            'txt_telefono'             => 'required|string|max:20',
            'txt_dni'                  => 'required|string|max:20',
            'txt_ruc'                  => 'nullable|string|max:20',
            'txt_listar_departamentos' => 'required|string',
            'txt_listar_provincias'    => 'required|string',
            'txt_listar_distritos'     => 'required|string',
            'txt_observaciones'        => 'nullable|string|max:1000',
            'check_terminos'           => 'required',
        ]);

        // Sin pago en línea: mismo registro que un pedido offline, marcado como WhatsApp
        $request->merge(['radio_metodo_pago' => 0, 'radio_tipo_pago' => 'whatsapp']);

        // El carrito se vacía al crear el pedido: se toma antes para el mensaje
        $items = Cart::content()->map(fn ($item) => [
            'nombre'   => (string) $item->name,
            'talla'    => trim((string) ($item->options->talla ?? '')),
            'color'    => trim((string) ($item->options->color ?? '')),
            'cantidad' => (int) $item->qty,
            'subtotal' => (float) $item->price * (int) $item->qty,
        ])->values();

        $respuesta = $this->procesarPedido($request, false, 1, 'whatsapp');
        $datos = $respuesta->getData(true);

        if (empty($datos['success'])) {
            return $respuesta;
        }

        [$codigoPedido, $total] = $datos['result'];
        $mensaje = $this->mensajePedidoWhatsapp($request, $codigoPedido, $items, (float) str_replace(',', '', (string) $total));
        $numero = \App\Services\PaymentGatewayManager::getWhatsappNumero();

        $datos['whatsapp_url'] = 'https://wa.me/' . $numero . '?text=' . rawurlencode($mensaje);
        $datos['redirect'] = url('/thank-you');

        return response()->json($datos);
    }

    /**
     * Mensaje del pedido para WhatsApp (texto plano con formato de WhatsApp).
     */
    private function mensajePedidoWhatsapp(Request $request, string $codigoPedido, $items, float $subtotal): string
    {
        $encabezado = trim((string) \App\Services\PaymentGatewayManager::getConfig('WHATSAPP_ENCABEZADO'));
        $delivery = $this->resolverDeliveryCompleto($request);
        $envio = $delivery['envio_gratis'] ? 0.0 : (float) ($delivery['costo_envio'] ?? 0);

        $lineas = [
            '*' . ($encabezado !== '' ? $encabezado : 'NUEVO PEDIDO — rossety') . '*',
            '*N° de pedido:* ' . $codigoPedido,
            '',
            '*DATOS DEL CLIENTE*',
            '• Nombre: ' . trim($request->input('txt_nombre') . ' ' . $request->input('txt_apellido')),
            '• ' . ($request->input('txt_tipo_documento') ?: 'Documento') . ': ' . $request->input('txt_dni'),
        ];
        if ($request->filled('txt_ruc')) {
            $lineas[] = '• RUC: ' . $request->input('txt_ruc')
                . ($request->filled('txt_razon_social') ? ' — ' . $request->input('txt_razon_social') : '');
        }
        $lineas[] = '• Teléfono: ' . $request->input('txt_telefono');
        $lineas[] = '• Correo: ' . $request->input('txt_email');
        $lineas[] = '';
        $lineas[] = '*ENTREGA*';
        $lineas[] = '• Dirección: ' . $request->input('txt_direccion');
        $lineas[] = '• ' . collect([
            $request->input('txt_listar_distritos'),
            $request->input('txt_listar_provincias'),
            $request->input('txt_listar_departamentos'),
        ])->filter()->implode(', ');
        $lineas[] = '';
        $lineas[] = '*PRODUCTOS*';

        foreach ($items as $i => $item) {
            $detalle = collect([
                $item['talla'] !== '' && ! preg_match('/\bTalla\s+/iu', $item['nombre']) ? 'Talla ' . $item['talla'] : null,
                $item['color'] !== '' ? 'Color ' . $item['color'] : null,
            ])->filter()->implode(' · ');

            $lineas[] = ($i + 1) . '. ' . $item['nombre'] . ($detalle !== '' ? ' (' . $detalle . ')' : '')
                . ' — Cant: ' . $item['cantidad'] . ' — S/ ' . number_format($item['subtotal'], 2);
        }

        $lineas[] = '';
        $lineas[] = '• Subtotal: S/ ' . number_format($subtotal, 2);
        $porCoordinar = in_array($delivery['razon'], ['sin_distrito', 'delivery_deshabilitado'], true);
        $lineas[] = '• Envío: ' . ($porCoordinar ? 'Por coordinar (sin delivery)' : ($envio > 0 ? 'S/ ' . number_format($envio, 2) : 'Gratis'));
        $lineas[] = '*• Total: S/ ' . number_format($subtotal + $envio, 2) . '*';

        if ($request->filled('txt_observaciones')) {
            $lineas[] = '';
            $lineas[] = '*OBSERVACIONES*';
            $lineas[] = trim((string) $request->input('txt_observaciones'));
        }

        $lineas[] = '';
        $lineas[] = 'Quedo atento(a) para coordinar el pago y la entrega. ¡Gracias!';

        return implode("\n", $lineas);
    }

    /**
     * Método principal para procesar pedidos.
     *
     * Orden crítico (pago online):
     * 1) Validar todo (carrito, stock, formulario) — sin cobro
     * 2) Cobrar en la pasarela — solo si las validaciones pasaron
     * 3) Guardar pedido en BD
     * 4) Enviar correos (nunca aborta el pedido si el mail falla)
     *
     * Antes el mail iba dentro de la misma transacción que el cobro Culqi:
     * si el correo fallaba se hacía rollback del pedido pero el cargo en Culqi
     * ya era irreversible (el comportamiento que reportaste).
     *
     * @param string $pasarela  'culqi' | 'izipay' | 'contraentrega' | 'stripe' | 'paypal'
     */
    private function procesarPedido(Request $request, $usarCulqi = false, $estado = 0, string $pasarela = 'culqi')
    {
        Log::info("PROCESAR_PEDIDO_START", [
            'pasarela' => $pasarela,
            'request_data' => $request->all(),
            'has_form_data' => $request->has('form_data'),
            'form_data_is_array' => is_array($request->input('form_data')),
        ]);

        // ── 1. Validaciones previas (sin efectos de cobro) ─────────────────
        if (Cart::total() <= 0) {
            return response()->json([
                'success' => false,
                'message' => "El carrito está vacío. Seleccione al menos un producto",
                'result' => null,
            ], 422);
        }

        $stockCheck = \App\Helpers\Helper::validarStockCarritoCompleto();
        if (!$stockCheck['ok']) {
            return response()->json([
                'success' => false,
                'message' => $stockCheck['message'],
                'result' => null,
            ], 422);
        }

        $errorFormulario = $this->validarDatosCheckout($request, $usarCulqi, $pasarela);

        // Módulo Delivery con "bloquear bajo el mínimo": no se acepta el pedido (ni se cobra)
        $delivery = $this->resolverDeliveryCompleto($request);
        if ($errorFormulario === null && !empty($delivery['bloqueado'])) {
            $errorFormulario = trim(($delivery['mensaje'] ?? '') . '. ' . ($delivery['mensaje_secundario'] ?? ''), '. ') . '.';
        }

        if ($errorFormulario !== null) {
            return response()->json([
                'success' => false,
                'message' => $errorFormulario,
                'result' => null,
            ], 422);
        }

        // Determinar tipo de comprobante (B: Boleta, F: Factura)
        $tipoComprobante = 'B';
        if ($request->has('cb_metodo_pago')) {
            $tipoComprobante = $request->input('cb_metodo_pago');
        } elseif ($this->requestHasFormDataForPasarela($request)) {
            $formData = $request->input('form_data');
            if (is_string($formData)) {
                $formData = json_decode($formData, true);
            }
            $tipoComprobante = is_array($formData) ? ($formData['cb_metodo_pago'] ?? 'B') : 'B';
        }

        $codigo_ = $this->validar_codigo_pedido($tipoComprobante);
        $id_cliente_ = $this->obtener_id_cliente();
        $pagoInfo = null;

        // ── 2. Cobro en pasarela ANTES de persistir (si falla, no hay pedido) ─
        try {
            if ($pasarela === 'izipay') {
                $pagoInfo = $this->procesarPagoIzipay($request);
            } elseif ($usarCulqi) {
                $pagoInfo = $this->procesarPagoCulqi($request, $codigo_);
            } elseif ($pasarela === 'stripe') {
                $pagoInfo = $this->procesarPagoStripe($request);
            } elseif ($pasarela === 'paypal') {
                $pagoInfo = $this->procesarPagoPaypal($request);
            } elseif ($pasarela === 'whatsapp') {
                $pagoInfo = $this->procesarPagoWhatsapp();
            } else {
                $pagoInfo = $this->procesarPagoContraentrega();
            }

            if (($pagoInfo['estado'] ?? '') === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago no fue aprobado. No se registró ningún pedido.',
                    'result' => null,
                ], 422);
            }
        } catch (CulqiPaymentRejectedException $e) {
            Log::info('Culqi rechazó el pago', [
                'code' => $e->getErrorCode(),
                'message' => $e->getUserMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage(),
                'error_code' => $e->getErrorCode(),
                'result' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en pasarela (sin pedido creado): ' . $e->getMessage(), [
                'pasarela' => $pasarela,
                'trace' => $e->getTraceAsString(),
            ]);

            $culqiMsg = null;
            if ($pasarela === 'culqi' || $usarCulqi) {
                $parsed = CulqiService::messageFromCulqiPayload($e->getMessage());
                if (($parsed['code'] ?? 'unknown') !== 'unknown') {
                    $culqiMsg = $parsed['user_message'];
                }
            }

            return response()->json([
                'success' => false,
                'message' => $culqiMsg ?? 'No se pudo procesar el pago. No se realizó ningún cargo ni pedido.',
                'result' => null,
            ], 422);
        }

        // ── 3. Persistir pedido (el cobro online ya ocurrió si aplica) ───────
        DB::beginTransaction();

        try {
            $id_pedido = $this->crearPedido(
                $request,
                $codigo_,
                $id_cliente_,
                $usarCulqi || in_array($pasarela, ['izipay', 'stripe', 'paypal'], true),
                $pasarela
            );

            $debeDescontarStock = ($pagoInfo['metodo'] !== 'contra entrega' && ($pagoInfo['estado'] ?? '') === 'completado');
            $this->procesarProductosCarrito($id_pedido, $debeDescontarStock);
            $this->actualizarInformacionPago($id_pedido, $pagoInfo, $usarCulqi, $request);
            $total = Cart::total();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::critical('Pago capturado pero falló al guardar el pedido', [
                'pasarela' => $pasarela,
                'codigo_pedido' => $codigo_['codigo_pedido'] ?? null,
                'transaccion_id' => $pagoInfo['transaccion_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $tx = $pagoInfo['transaccion_id'] ?? null;
            $msg = $tx
                ? "Tu pago fue recibido (ref: {$tx}) pero hubo un error al registrar el pedido. Contáctanos de inmediato con esa referencia."
                : "Ocurrió un error al registrar tu pedido. Por favor contacta a soporte.";

            return response()->json([
                'success' => false,
                'message' => $msg,
                'result' => null,
                'pago_capturado' => true,
                'transaccion_id' => $tx,
            ], 500);
        }

        // ── 4. Correos: nunca revierten el pedido ni el cobro ───────────────
        try {
            $this->enviarCorreosConfirmacion($request, $codigo_, $pagoInfo, $estado, $usarCulqi);
        } catch (\Throwable $e) {
            Log::error('Pedido OK pero falló el envío de correos: ' . $e->getMessage(), [
                'codigo_pedido' => $codigo_['codigo_pedido'] ?? null,
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
        }

        Cart::destroy();
        $this->limpiarDatosTemporales();

        $deliveryFull = $this->resolverDeliveryCompleto($request);
        session(['order_confirmation' => [
            'codigo_pedido'    => $codigo_["codigo_pedido"],
            'total'            => $total,
            'expires_at'       => now()->addMinutes(15)->toDateTimeString(),
            'delivery_razon'   => $deliveryFull['razon']   ?? null,
            'delivery_mensaje' => $deliveryFull['mensaje']  ?? null,
            'monto_pagado'     => $this->extraerMontoPagado($request),
            'origen'           => $pasarela === 'whatsapp' ? 'whatsapp' : null,
        ]]);

        return response()->json([
            'success' => true,
            'message' => "Pedido creado correctamente",
            'result' => [$codigo_["codigo_pedido"], $total],
        ]);
    }

    /**
     * Valida datos del checkout antes de cobrar.
     * @return string|null Mensaje de error o null si todo está bien
     */
    private function validarDatosCheckout(Request $request, bool $usarCulqi, string $pasarela): ?string
    {
        $esOnline = $usarCulqi || in_array($pasarela, ['izipay', 'stripe', 'paypal'], true);

        if ($esOnline && $this->requestHasFormDataForPasarela($request)) {
            $formData = $request->input('form_data');
            if (is_string($formData)) {
                $formData = json_decode($formData, true);
            }
            if (!is_array($formData)) {
                return 'No se recibieron los datos del formulario. Recarga la página e intenta nuevamente.';
            }

            $requeridos = [
                'txt_nombre' => 'nombre',
                'txt_apellido' => 'apellido',
                'txt_email' => 'correo electrónico',
                'txt_direccion' => 'dirección',
                'txt_telefono' => 'teléfono',
                'txt_dni' => 'DNI',
            ];

            foreach ($requeridos as $campo => $etiqueta) {
                $valor = trim((string) ($formData[$campo] ?? ''));
                if ($valor === '') {
                    return "Completa el campo {$etiqueta} antes de pagar.";
                }
            }

            $departamento = trim((string) ($formData['txt_listar_departamentos'] ?? $formData['cb_departamento'] ?? ''));
            $provincia = trim((string) ($formData['txt_listar_provincias'] ?? $formData['cb_provincia'] ?? ''));
            $distrito = trim((string) ($formData['txt_listar_distritos'] ?? $formData['cb_distrito'] ?? ''));

            // Compat formulario antiguo (solo "provincia" shipping + distrito)
            if ($provincia === '' && $departamento !== '' && stripos($departamento, 'Metropolitana') !== false) {
                $provincia = $departamento;
            }

            if ($departamento === '') {
                return 'Completa el campo departamento antes de pagar.';
            }
            if ($provincia === '') {
                return 'Completa el campo provincia antes de pagar.';
            }
            if ($distrito === '') {
                return 'Completa el campo distrito antes de pagar.';
            }

            $emailForm = trim((string) ($formData['txt_email'] ?? ''));
            if (!filter_var($emailForm, FILTER_VALIDATE_EMAIL)) {
                return 'El correo electrónico del formulario no es válido.';
            }

            if (empty($formData['check_terminos'])) {
                return 'Debes aceptar los términos y condiciones.';
            }
        } elseif (!$esOnline) {
            $email = trim((string) $request->input('txt_email', ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 'El correo electrónico no es válido.';
            }
        }

        if ($usarCulqi) {
            $emailPago = trim((string) $request->input('email', ''));
            if ($emailPago === '' || !filter_var($emailPago, FILTER_VALIDATE_EMAIL)) {
                return 'El correo del pago no es válido. Intenta nuevamente.';
            }
            if (!$request->filled('token') || !$request->filled('amount')) {
                return 'Faltan datos del pago. Intenta nuevamente.';
            }
        }

        return null;
    }


    /**
     * Resuelve nombres de ubigeo (texto) desde el formulario.
     * No usa códigos ubigeo — solo letras, para no romper pedidos/pasarela.
     *
     * @return array{departamento:string,provincia:string,distrito:string}
     */
    private function resolverUbigeoNombres(array $formData): array
    {
        $departamento = trim((string) ($formData['txt_listar_departamentos'] ?? $formData['cb_departamento'] ?? ''));
        $provincia = trim((string) ($formData['txt_listar_provincias'] ?? ''));
        $distrito = trim((string) ($formData['txt_listar_distritos'] ?? $formData['cb_distrito'] ?? ''));

        // Formulario antiguo: txt_listar_departamentos = "Lima Metropolitana" (se guardaba como provincia)
        if ($provincia === '' && $departamento !== '') {
            $legacyProvincia = trim((string) ($formData['cb_provincia'] ?? ''));
            if ($legacyProvincia !== '' && strcasecmp($legacyProvincia, $departamento) !== 0) {
                $provincia = $legacyProvincia;
            } elseif (stripos($departamento, 'Metropolitana') !== false) {
                $provincia = $departamento;
                $departamento = 'Lima';
            } else {
                // Sin combo provincia: el valor de "departamentos" era en realidad la provincia de envío
                $provincia = trim((string) ($formData['cb_provincia'] ?? $departamento));
                if (stripos($provincia, 'Lima') !== false) {
                    $departamento = 'Lima';
                }
            }
        }

        return [
            'departamento' => $departamento,
            'provincia' => $provincia,
            'distrito' => $distrito,
        ];
    }

    /**
     * Crear el pedido en la base de datos
     */
    private function crearPedido($request, $codigo_, $id_cliente_, $usarCulqi = false, $pasarela = 'culqi')
    {
        // Manejar los datos según el formato recibido
        if (($usarCulqi || $pasarela === 'stripe' || $pasarela === 'paypal' || $pasarela === 'izipay') && $this->requestHasFormDataForPasarela($request)) {
            // Datos vienen en form_data (JSON o Array desde pasarelas online)
            $formData = $request->input('form_data');

            if (is_string($formData)) {
                $formData = json_decode($formData, true);
            }

            // ASEGURAR QUE LOS CAMPOS BÁSICOS EXISTAN EN EL ARRAY
            $formData['txt_nombre'] = $formData['txt_nombre'] ?? '';
            $formData['txt_apellido'] = $formData['txt_apellido'] ?? '';
            $formData['txt_email'] = $formData['txt_email'] ?? '';
            $formData['txt_direccion'] = $formData['txt_direccion'] ?? '';
            $formData['txt_telefono'] = $formData['txt_telefono'] ?? $request->input('txt_telefono', '');
            $formData['txt_dni'] = $formData['txt_dni'] ?? '';
            $formData['txt_ruc'] = $formData['txt_ruc'] ?? null;
            $formData['txt_hora_regresiva_descripcion'] = $formData['txt_hora_regresiva_descripcion'] ?? '';

            $formData['txt_listar_departamentos'] = $formData['txt_listar_departamentos'] ?? '';
            $formData['txt_listar_provincias'] = $formData['txt_listar_provincias'] ?? '';
            $formData['txt_listar_distritos'] = $formData['txt_listar_distritos'] ?? $formData['cb_distrito'] ?? '';

            // GREGAR VALORES POR DEFECTO PARA LOS CHECKBOXES
            $formData['check_terminos'] = $formData['check_terminos'] ?? 0;
            $formData['check_direccion_envio'] = $formData['check_direccion_envio'] ?? 0;
            $formData['check_privacidad_cliente'] = $formData['check_privacidad_cliente'] ?? 0;

            $ubi = $this->resolverUbigeoNombres($formData);
            $formData['cb_departamento'] = $ubi['departamento'];
            $formData['cb_provincia'] = $ubi['provincia'];
            $formData['cb_distrito'] = $ubi['distrito'];
            
            $formData['radio_metodo_pago'] = 1;
            $formData['radio_tipo_pago'] = $formData['radio_tipo_pago'] ?? 'normal';
        } else {

            Log::channel('stderr')->info("test--->pasaa Sin Culqui");

            Log::channel('stderr')->info("test-->mas:" . json_encode($request->input('txt_hora_regresiva_descripcion')));
            // Datos vienen directamente en el request (FormData normal)
            $formData = [
                'txt_nombre' => $request->input('txt_nombre'),
                'txt_apellido' => $request->input('txt_apellido'),
                'txt_email' => $request->input('txt_email'),
                'txt_direccion' => $request->input('txt_direccion'),
                'txt_telefono' => $request->input('txt_telefono'),
                'txt_dni' => $request->input('txt_dni'),
                'txt_ruc' => $request->input('txt_ruc'),

                'txt_listar_departamentos' => $request->input('txt_listar_departamentos'),
                'txt_listar_provincias' => $request->input('txt_listar_provincias'),
                'txt_listar_distritos' => $request->input('txt_listar_distritos'),

                'radio_metodo_pago' => ($pasarela === 'paypal' || $request->has('paypal_order_id')) ? 1 : $request->input('radio_metodo_pago'),
                'radio_tipo_pago' => ($pasarela === 'paypal' || !$request->has('radio_tipo_pago')) ? 'normal' : $request->input('radio_tipo_pago'),
                'check_terminos' => $request->has('check_terminos') ? 1 : 0,
                'check_direccion_envio' => $request->has('check_direccion_envio') ? 1 : 0,
                'check_privacidad_cliente' => $request->has('check_privacidad_cliente') ? 1 : 0,

                'txt_hora_regresiva_descripcion' => $request->input('txt_hora_regresiva_descripcion'),
            ];

            $ubi = $this->resolverUbigeoNombres($formData);
            $formData['cb_departamento'] = $ubi['departamento'];
            $formData['cb_provincia'] = $ubi['provincia'];
            $formData['cb_distrito'] = $ubi['distrito'];
        }

        //=============START: OPERACION DE METODO ENVIO PAGO =============
        $precioEnvio = $this->calcularCostoEnvio($request);
        //=============END: OPERACION DE METODO ENVIO PAGO =============

        // Log de depuración antes de llamar al SP
        Log::info("SP_PEDIDOS_PARAMS_DEBUG", [
            'nombre' => $formData['txt_nombre'],
            'apellido' => $formData['txt_apellido'],
            'telefono' => $formData['txt_telefono'] ?? $request->input('txt_telefono'),
            'dni' => $formData['txt_dni'],
            'departamento' => $formData['cb_departamento'] ?? '',
            'provincia' => $formData['cb_provincia'],
            'distrito' => $formData['cb_distrito']
        ]);

        MysqlTimezone::applySession();

        $result = DB::select('CALL USP_WEB_OPE_PEDIDOS_CREAR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $formData['txt_nombre'] ?? '',
            $formData['txt_apellido'] ?? '',
            $formData['txt_email'] ?? '',
            $formData['txt_direccion'] ?? '',
            $formData['txt_telefono'] ?? $request->input('txt_telefono', '999999999'), // Valor por defecto si falla
            $formData['txt_dni'] ?? '',
            $formData['txt_ruc'] ?? null,

            null, // Pais
            $formData['cb_departamento'] ?? '', // Dept → direccion_envio_ciudad
            $formData['cb_provincia'] ?? '',
            $formData['cb_distrito'] ?? '',

            $codigo_["codigo_pedido"],
            $codigo_["codigo_boleta_o_factura"],
            $id_cliente_,
            $formData['check_terminos'] ? 1 : 0,
            $formData['check_direccion_envio'] ? 1 : 0,
            $formData['check_privacidad_cliente'] ? 1 : 0,
            $formData['radio_metodo_pago'] ?? 0,
            $precioEnvio,
            $formData['txt_hora_regresiva_descripcion'] ?? '',
            $formData['radio_tipo_pago'] ?? '',
        ]);

        Log::channel('stderr')->info("DEBUG PAYPAL - Pedido: " . $codigo_["codigo_pedido"] . " | Metodo: " . ($formData['radio_metodo_pago'] ?? 'N/A') . " | Pasarela: " . $pasarela);

        return $result[0]->id_pedido;
    }


    /**
     * Delega la creación del charge a CulqiService.
     * Prefiere el email del formulario (cliente real) sobre el del token de prueba.
     */
    private function procesarPagoCulqi($request, $codigo_): array
    {
        $email = trim((string) $request->input('email', ''));

        if ($request->has('form_data')) {
            $fd = $request->input('form_data');
            if (is_string($fd)) {
                $fd = json_decode($fd, true);
            }
            if (is_array($fd)) {
                $emailForm = trim((string) ($fd['txt_email'] ?? ''));
                if ($emailForm !== '' && filter_var($emailForm, FILTER_VALIDATE_EMAIL)) {
                    $email = $emailForm;
                }
            }
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Correo electrónico inválido para procesar el pago.');
        }

        $culqi = new CulqiService();

        return $culqi->createCharge([
            'token' => $request->token,
            'amount' => $request->amount,
            'email' => $email,
            'codigo_pedido' => $codigo_['codigo_pedido'],
            'metadata' => $request->metadata ?? [],
            'order_temp' => session('culqi_order_temp'),
        ]);
    }

    /**
     * Procesar pago con PayPal (Captura)
     */
    private function procesarPagoPaypal($request): array
    {
        $paypal = new PaypalService();
        $response = $paypal->captureOrder($request->paypal_order_id);

        // LOG DIRECTO AL ARCHIVO - Confirmado el formato
        Log::info("PAYPAL_DEBUG_RESPONSE", ['data' => $response]);

        // Si ya viene mapeado (estado: completado), lo retornamos directamente
        if (isset($response['estado'])) {
            return $response;
        }

        // Si por alguna razón viene el formato original de PayPal
        $status = strtoupper($response['status'] ?? '');
        $esExitoso = ($status === 'COMPLETED' || $status === 'APPROVED');

        return [
            'estado' => $esExitoso ? 'completado' : 'error',
            'metodo' => 'paypal',
            'transaccion_id' => $response['id'] ?? null,
            'fecha_pago' => now()
        ];
    }

    /**
     * Procesar pago contraentrega
     */
    private function procesarPagoContraentrega()
    {
        return [
            'metodo' => 'contra entrega',
            'estado' => 'pendiente',
            'transaccion_id' => null,
            'fecha_pago' => null,
            'codigo_culqi' => null
        ];
    }

    /** Pedido por WhatsApp: el pago se coordina con el asesor (sin cobro ni stock). */
    private function procesarPagoWhatsapp(): array
    {
        return [
            'metodo' => 'whatsapp',
            'estado' => 'pendiente',
            'transaccion_id' => null,
            'fecha_pago' => null,
            'codigo_culqi' => null,
        ];
    }

    /**
     * Procesar productos del carrito
     */
    private function procesarProductosCarrito($id_pedido, $descontarStock = true)
    {
        $cartItems = Cart::content();

        foreach ($cartItems as $cartItem) {
            $this->copiarImagenProducto($cartItem->options["img"]);

            DB::select('call USP_WEB_OPE_PEDIDOS_PRODUCTO_CREAR(?,?,?,?,?,?,?,?)', [
                $id_pedido,
                $cartItem->id,
                $cartItem->qty,
                $cartItem->options["id_producto"],
                $cartItem->options["img"],
                $cartItem->name,
                $cartItem->price,
                $cartItem->options["descripcion"],
            ]);

            $idProducto = (int) ($cartItem->options['id_producto'] ?? $cartItem->id);
            $tallaItem = strtoupper(trim((string) ($cartItem->options['talla'] ?? $cartItem->options->talla ?? '')));
            $colorItem = trim((string) ($cartItem->options['color'] ?? $cartItem->options->color ?? ''));
            PedidoProductoRepositorio::snapshotTrasCrearLinea($id_pedido, $idProducto, [
                'talla' => $tallaItem,
                'color' => $colorItem,
                'nombre_linea' => $cartItem->name,
                'precio' => $cartItem->price,
                'cantidad' => $cartItem->qty,
                'url_imagen' => $cartItem->options['img'] ?? null,
                'descripcion' => $cartItem->options['descripcion'] ?? null,
            ]);

            // Descontar stock si el pago es online y exitoso
            // Con medidas: resta stock de la talla y sincroniza stock general = suma
            // Sin medidas: resta el stock general
            if ($descontarStock) {
                $qty = (int) $cartItem->qty;
                $idP = (int) ($cartItem->options['id_producto'] ?? $cartItem->id);
                $talla = strtoupper(trim((string) (
                    $cartItem->options['talla']
                    ?? $cartItem->options->talla
                    ?? ''
                )));
                if ($talla === '' && preg_match('/\s*[·•]\s*Talla\s+(.+)$/iu', (string) $cartItem->name, $m)) {
                    $talla = strtoupper(trim((string) $m[1]));
                }
                ProductoStockService::descontarVenta($idP, $qty, $talla !== '' ? $talla : null);
            }
        }
    }

    /**
     * Copiar imagen del producto bajo storage_/ (no en carpetas sueltas).
     */
    private function copiarImagenProducto($source)
    {
        if (empty($source)) {
            return;
        }

        $relative = ProductoImagePath::normalize((string) $source);
        if ($relative === null) {
            return;
        }

        if (preg_match('#^https?://#i', $relative)) {
            $path = parse_url($relative, PHP_URL_PATH);
            $relative = ProductoImagePath::normalize(ltrim((string) $path, '/'));
            if ($relative === null || preg_match('#^https?://#i', $relative)) {
                return;
            }
        }

        $diskRoot = rtrim((string) (config('filesystems.disks.public_imagenes.root') ?: public_path()), '/\\');
        $srcPath  = $diskRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);

        if (! is_file($srcPath)) {
            return;
        }

        $destinationDir = public_path(str_replace('/', DIRECTORY_SEPARATOR, ProductoImagePath::DIR_CLIENTE));
        if (! is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . basename($srcPath);
        @copy($srcPath, $destination);
    }

    /**
     * Actualizar información de pago en la base de datos
     */
    private function actualizarInformacionPago($id_pedido, $pagoInfo, $usarCulqi, $request)
    {
        // Pedido por WhatsApp: mismas marcas que el botón "Pedir por WhatsApp" del carrito
        if ($pagoInfo['metodo'] === 'whatsapp') {
            DB::table('ope_pedidos')
                ->where('id_pedido', $id_pedido)
                ->update([
                    'metodo_pago'     => 'WhatsApp',
                    'radio_tipo_pago' => 'whatsapp',
                    'updated_at'      => now(),
                ]);
        }

        // Solo forzar 'Pago Online' si NO es un pedido de contra entrega ni WhatsApp
        if (! in_array($pagoInfo['metodo'], ['contra entrega', 'whatsapp'], true)) {
            DB::statement("UPDATE ope_pedidos
                           SET radio_metodo_pago = 1,
                               metodo_pago = 'Pago Online',
                               radio_tipo_pago = ?,
                               updated_at = ?
                           WHERE id_pedido = ?", [
                ($pagoInfo['metodo'] === 'paypal' ? 'normal' : $request->input('radio_tipo_pago', 'normal')),
                now(),
                $id_pedido
            ]);
        }

        // Guardar datos de transacción adicionales para todas las pasarelas (Bloque Original Estable)
        if (!empty($pagoInfo['transaccion_id']) || !empty($pagoInfo['estado'])) {
            DB::table('ope_pedidos')
                ->where('id_pedido', $id_pedido)
                ->update([
                    'pasarela_estado_pago'    => $pagoInfo['estado'] ?? 'pendiente',
                    'pasarela_metodo'         => $pagoInfo['metodo'] ?? 'desconocido',
                    'pasarela_id_transaccion' => $pagoInfo['transaccion_id'] ?? null,
                    'pasarela_fecha_pago'     => $pagoInfo['fecha_pago'] ?? now(),
                    'pasarela_codigo_culqi'   => $pagoInfo['codigo_culqi'] ?? null,
                ]);
        }

        // Guardar monto_pagado_cliente (campo para vuelto en contra entrega)
        $montoPagado = $this->extraerMontoPagado($request);
        if ($montoPagado !== null) {
            DB::table('ope_pedidos')
                ->where('id_pedido', $id_pedido)
                ->update(['monto_pagado_cliente' => $montoPagado]);
        }
    }

    /**
     * Enviar correos de confirmación
     */
    private function enviarCorreosConfirmacion($request, $codigo_, $pagoInfo, $estado, $usarCulqi = false)
    {

        $cartItems = Cart::content();
        $total = Cart::total();

        // Obtener datos según el formato
        if (($usarCulqi || $pagoInfo['metodo'] === 'stripe' || $pagoInfo['metodo'] === 'paypal' || $pagoInfo['metodo'] === 'izipay') && $this->requestHasFormDataForPasarela($request)) {
            $formData = $request->input('form_data');
            if (is_string($formData)) {
                $formData = json_decode($formData, true);
            }
            $formData['txt_nombre'] = $formData['txt_nombre'] ?? '';
            $formData['txt_apellido'] = $formData['txt_apellido'] ?? '';
            $formData['txt_email'] = $formData['txt_email'] ?? ($request->email ?? '');
            $formData['txt_direccion'] = $formData['txt_direccion'] ?? '';
            $formData['txt_telefono'] = $formData['txt_telefono'] ?? '';
            $formData['txt_dni'] = $formData['txt_dni'] ?? '';
            $formData['txt_ruc'] = $formData['txt_ruc'] ?? '';
            $formData['txt_listar_departamentos'] = $formData['txt_listar_departamentos'] ?? '';
            $formData['txt_listar_provincias'] = $formData['txt_listar_provincias'] ?? '';
            $formData['txt_listar_distritos'] = $formData['txt_listar_distritos'] ?? $formData['cb_distrito'] ?? '';
            $ubi = $this->resolverUbigeoNombres($formData);
            $formData['cb_departamento'] = $ubi['departamento'];
            $formData['cb_provincia'] = $ubi['provincia'];
            $formData['cb_distrito'] = $ubi['distrito'];
            $formData['radio_metodo_pago'] = 1;
        } else {
            $formData = [
                'txt_nombre' => $request->input('txt_nombre'),
                'txt_apellido' => $request->input('txt_apellido'),
                'txt_email' => $request->input('txt_email'),
                'txt_direccion' => $request->input('txt_direccion'),
                'txt_telefono' => $request->input('txt_telefono'),
                'txt_dni' => $request->input('txt_dni'),
                'txt_ruc' => $request->input('txt_ruc'),

                'txt_listar_departamentos' => $request->input('txt_listar_departamentos'),
                'txt_listar_provincias' => $request->input('txt_listar_provincias'),
                'txt_listar_distritos' => $request->input('txt_listar_distritos'),

                'radio_metodo_pago' => $request->input('radio_metodo_pago'),
            ];
            $ubi = $this->resolverUbigeoNombres($formData);
            $formData['cb_departamento'] = $ubi['departamento'];
            $formData['cb_provincia'] = $ubi['provincia'];
            $formData['cb_distrito'] = $ubi['distrito'];
        }

        // Delivery 4 casos: costo + mensaje + razon
        $deliveryFull = $this->resolverDeliveryCompleto($request);
        $precioEnvio  = $deliveryFull['envio_gratis'] ? 'gratis' : ($deliveryFull['costo_envio'] ?? 0);
        $montoPagado  = $this->extraerMontoPagado($request);

        $datosCorreo = [
            'codigo_pedido'           => $codigo_["codigo_pedido"],
            'codigo_boleta_o_factura' => $codigo_["codigo_boleta_o_factura"],
            'created_at'              => now(),
            'estado'                  => $estado,
            'radio_metodo_pago'       => $formData['radio_metodo_pago'],
            'pasarela_metodo'         => $pagoInfo['metodo'] ?? null,
            'direccion_envio_nombre'  => $formData['txt_nombre']    ?? '',
            'direccion_envio_apellido'=> $formData['txt_apellido']  ?? '',
            'direccion_envio_correo'  => $formData['txt_email']     ?? '',
            'direccion_envio_ubicacion'=> $formData['txt_direccion'] ?? '',
            'direccion_envio_telefono'=> $formData['txt_telefono']  ?? '',
            'direccion_envio_dni'     => $formData['txt_dni']       ?? '',
            'direccion_envio_ruc'     => $formData['txt_ruc']       ?? '',

            'direccion_envio_ciudad'    => $formData['cb_departamento'] ?? '',
            'direccion_envio_provincia' => $formData['cb_provincia'],
            'direccion_envio_distrito'  => $formData['cb_distrito'],

            'productos'               => $cartItems,
            'total'                   => $total,
            'costo_envio'             => $precioEnvio,

            // Delivery 4 casos
            'delivery_razon'          => $deliveryFull['razon']    ?? null,
            'delivery_mensaje'        => $deliveryFull['mensaje']   ?? null,
            'monto_pagado_cliente'    => $montoPagado,
        ];

        // Agregar ID de transacción solo si existe
        if ($pagoInfo['transaccion_id']) {
            $datosCorreo['id_transaccion'] = $pagoInfo['transaccion_id'];
        }

        $mailHost = (string) config('mail.mailers.smtp.host', '');
        $codigoPedido = $codigo_['codigo_pedido'] ?? '';

        // Correo del cliente: formulario → request → datos del pedido
        $emailCliente = trim((string) ($formData['txt_email'] ?? ''));
        if ($emailCliente === '' || !filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
            $emailCliente = trim((string) $request->input('email', ''));
        }
        if ($emailCliente === '' || !filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
            $emailCliente = trim((string) ($datosCorreo['direccion_envio_correo'] ?? ''));
        }

        $datosCorreo['direccion_envio_correo'] = $emailCliente;

        // Cada correo es independiente: un fallo de SMTP NO tumba el pedido.
        // Pausa entre envíos para evitar límites del servidor de correo.
        $envios = [
            [
                'label' => 'cliente',
                'to' => [$emailCliente],
                'mailable' => new \App\Mail\OrderConfirmationMail($datosCorreo),
            ],
            [
                'label' => 'ventas',
                'to' => [ContactoValento::ventasEmail()],
                'mailable' => new \App\Mail\NotificationVentasMail($datosCorreo),
            ],
        ];

        foreach ($envios as $i => $envio) {
            $to = array_values(array_filter($envio['to'], function ($addr) {
                return is_string($addr) && filter_var($addr, FILTER_VALIDATE_EMAIL);
            }));
            if ($to === []) {
                Log::warning('Correo de pedido omitido: destinatario vacío o inválido', [
                    'codigo_pedido' => $codigoPedido,
                    'destinatario' => $envio['label'],
                ]);
                continue;
            }
            if ($i > 0) {
                usleep(700000); // 0.7s entre correos
            }
            try {
                Mail::to($to)->send($envio['mailable']);
                Log::info('Correo de pedido enviado', [
                    'codigo_pedido' => $codigoPedido,
                    'destinatario' => $envio['label'],
                    'to' => $to,
                    'mail_host' => $mailHost,
                    'from' => config('mail.from.address'),
                ]);
            } catch (\Throwable $e) {
                Log::error('Fallo envío correo pedido (pedido ya guardado)', [
                    'codigo_pedido' => $codigoPedido,
                    'destinatario' => $envio['label'],
                    'to' => $to,
                    'mail_host' => $mailHost,
                    'from' => config('mail.from.address'),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Limpiar datos temporales de la sesión
     */
    private function limpiarDatosTemporales()
    {
        session()->forget(['culqi_order_temp', 'culqi_form_data', 'culqi_amount', 'izipay_order_temp', 'izipay_form_data', 'izipay_email', 'izipay_amount', 'stripe_order_temp',  'stripe_form_data',  'stripe_email',  'stripe_amount','id_producto_temp']);
    }

    // =========================================================================
    // IZIPAY
    // =========================================================================

    /**
     * Genera el formToken de Izipay para inicializar el SDK Krypton.js.
     * El frontend llama a este endpoint ANTES de mostrar el formulario Izipay.
     */
    public function generarFormularioIzipay(Request $request)
    {
        Log::info("IZIPAY_GENERATE_START", ['request' => $request->all()]);
        // ── Extraer form_data si viene como JSON string ──────────────────
        $formData = $request->input('form_data');
        if (is_string($formData)) {
            $formData = json_decode($formData, true);
        }

        $requestParaEnvio = $formData
            ? new Request([
                'txt_listar_departamentos' => $formData['txt_listar_departamentos'] ?? null,
                'txt_listar_provincias' => $formData['txt_listar_provincias'] ?? null,
                'txt_listar_distritos' => $formData['txt_listar_distritos'] ?? null,
                'txt_peso_kilogramo' => $formData['txt_peso_kilogramo'] ?? null,
                'txt_paqueta_medidas' => $formData['txt_paqueta_medidas'] ?? null,
                'txt_paquete_dimencion' => $formData['txt_paquete_dimencion'] ?? null,
            ])
            : $request;

        $this->validate($request, [
            'amount' => 'required|numeric|min:100',
            'email' => 'required|email',
        ]);

        // ── Calcular costo de envío ──────────────────────────────────────
        $precioEnvio = $this->calcularCostoEnvio($requestParaEnvio);
        $montoBase = (int) $request->amount;
        $montoTotal = $montoBase;

        if ($precioEnvio !== 'gratis' && is_numeric($precioEnvio)) {
            $montoTotal = $montoBase + (int) ($precioEnvio * 100);
        }

        // ── Generar formToken con Izipay API ─────────────────────────────
        $orderId = 'IZP-' . time() . '-' . bin2hex(random_bytes(3));
        session([
            'izipay_order_temp' => $orderId,
            'izipay_form_data' => $request->input('form_data'),
            'izipay_email' => $request->email,
            'izipay_amount' => $montoTotal,
        ]);

        $izipay = new IzipayService();

        $formToken = $izipay->createPayment([
            'amount' => $montoTotal,
            'currency' => 'PEN',
            'order_id' => $orderId,
            'email' => $request->email,
            'user_id' => auth()->check() ? auth()->id() : null,
        ]);

        return response()->json([
            'formToken' => $formToken,
            'publicKey' => $izipay->getPublicKey(),
            'amount' => $montoTotal,
            'order' => $orderId,
            'metadata' => [
                'origen' => 'web_checkout',
                'costo_envio' => $precioEnvio,
            ],
        ]);
    }

    /**
     * Muestra la página con el formulario de pago Izipay (Krypton.js Pop-In).
     * formToken en query; form_data, email, amount desde sesión (guardados en generarFormularioIzipay).
     */
    public function formularioIzipay(Request $request)
    {
        $formToken = $request->query('formToken');
        if (!$formToken || !session()->has('izipay_order_temp')) {
            return redirect('/web_checkout/index')->with('error', 'Sesión de pago expirada. Vuelve a intentar.');
        }

        $formData = session('izipay_form_data');
        if (is_string($formData)) {
            $formData = json_decode($formData, true) ?? [];
        }

        return view('web.pages.web_checkout_izipay_form', [
            'formToken' => $formToken,
            'form_data' => $formData,
            'email' => session('izipay_email'),
            'amount' => session('izipay_amount'),
            'publicKey' => (new IzipayService())->getPublicKey(),
        ]);
    }

    /**
     * Recibe el resultado de pago de Krypton.js (Izipay).
     * Valida la firma HMAC, luego procesa el pedido.
     *
     * El frontend debe enviar:
     *   kr-answer          → JSON del resultado de pago
     *   kr-hash            → firma HMAC-SHA256
     *   kr-hash-algorithm  → "sha256_hmac"
     *   form_data          → datos del formulario de checkout (JSON o array)
     *   amount             → monto total en céntimos
     *   email              → correo del comprador
     */
    public function enviar_pago_con_izipay(Request $request)
    {
        Log::info("IZIPAY_CAPTURE_START", ['request' => $request->all(), 'session_data' => session('izipay_form_data')]);
        if (!session()->has('izipay_order_temp')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada. Recarga la página e intenta nuevamente.',
                'result' => null,
            ]);
        }

        $this->validate($request, [
            'kr-answer' => 'required|string',
            'kr-hash' => 'required|string',
            'kr-hash-algorithm' => 'nullable|string',
            'email' => 'required|email',
            'amount' => 'required|numeric',
        ]);

        $estado = 1; // Pago completado
        return $this->procesarPedido($request, false, $estado, 'izipay');
    }

    /**
     * Valida la firma Izipay y extrae la información del pago.
     * Usado internamente por procesarPedido().
     */
    private function procesarPagoIzipay(Request $request)
    {
        $krAnswer = $request->input('kr-answer');
        $krHash = $request->input('kr-hash');
        $krHashAlgorithm = $request->input('kr-hash-algorithm', 'sha256_hmac');

        $izipay = new IzipayService();

        if (!$izipay->validarFirma($krAnswer, $krHash, $krHashAlgorithm)) {
            throw new \Exception('Firma Izipay inválida. Posible fraude.');
        }

        $answer = json_decode($krAnswer, true);
        $transactions = $answer['transactions'] ?? [];
        $transaction = $transactions[0] ?? [];
        $uuid = $transaction['uuid'] ?? null;
        $orderStatus = $answer['orderStatus'] ?? 'UNKNOWN';

        if ($orderStatus !== 'PAID') {
            throw new \Exception("Pago Izipay no completado. Estado: {$orderStatus}");
        }

        return [
            'metodo' => 'izipay',
            'estado' => 'completado',
            'transaccion_id' => $uuid,
            'fecha_pago' => now(),
            'codigo_culqi' => session('izipay_order_temp'), // reusa el mismo campo DB
        ];
    }

    // =========================================================================
    // DELIVERY 4 CASOS — helpers y cálculo
    // =========================================================================

    /**
     * Calcula el delivery usando la lógica de 4 casos (cacheado por request).
     */
    private function resolverDeliveryCompleto(Request $request): array
    {
        if ($this->deliveryResultado !== null) {
            return $this->deliveryResultado;
        }

        $distrito = $this->extraerDistritoDeRequest($request);
        $config   = \App\Models\DelConfiguracionDelivery::obtenerActivo();

        // Delivery desactivado en el módulo Delivery: el resultado no depende del distrito
        if (!$distrito && $config->deliveryHabilitado()) {
            $this->deliveryResultado = [
                'costo_envio'        => 0,
                'envio_gratis'       => false,
                'compra_baja'        => false,
                'bloqueado'          => false,
                'deshabilitado'      => false,
                'razon'              => 'sin_distrito',
                'mensaje'            => '',
                'mensaje_secundario' => null,
                'distancia_metros'   => null,
                'monto_minimo'       => 0,
                'subtotal'           => 0,
                'falta_para_minimo'  => 0,
                'distrito_especial'  => false,
            ];
            return $this->deliveryResultado;
        }

        [$latCliente, $lngCliente] = $this->extraerLatLngDeRequest($request);
        $subtotal = floatval(str_replace(',', '', Cart::total()));

        $this->deliveryResultado = \App\Http\Controllers\Api\DeliveryConfigController::resolverCostoDelivery(
            $config, trim($distrito), $latCliente, $lngCliente, $subtotal
        );

        return $this->deliveryResultado;
    }

    private function extraerDistritoDeRequest(Request $request): string
    {
        $distrito = $request->input('txt_listar_distritos')
            ?? $request->input('address_distrito')
            ?? '';

        if (!$distrito && $request->has('form_data')) {
            $fd = $request->input('form_data');
            if (is_string($fd)) $fd = json_decode($fd, true);
            if (is_array($fd)) {
                $distrito = $fd['txt_listar_distritos'] ?? $fd['cb_distrito'] ?? '';
            }
        }

        return trim((string) $distrito);
    }

    private function extraerLatLngDeRequest(Request $request): array
    {
        $lat = $request->input('del_lat_cliente');
        $lng = $request->input('del_lng_cliente');

        if ((!$lat || !$lng) && $request->has('form_data')) {
            $fd = $request->input('form_data');
            if (is_string($fd)) $fd = json_decode($fd, true);
            if (is_array($fd)) {
                $lat = $lat ?: ($fd['del_lat_cliente'] ?? null);
                $lng = $lng ?: ($fd['del_lng_cliente'] ?? null);
            }
        }

        return [
            ($lat !== null && $lat !== '') ? (float) $lat : null,
            ($lng !== null && $lng !== '') ? (float) $lng : null,
        ];
    }

    private function extraerMontoPagado(Request $request): ?float
    {
        $valor = $request->input('del_monto_pagar');
        if ($valor !== null && $valor !== '') {
            return (float) $valor;
        }

        if ($request->has('form_data')) {
            $fd = $request->input('form_data');
            if (is_string($fd)) $fd = json_decode($fd, true);
            if (is_array($fd) && isset($fd['del_monto_pagar']) && $fd['del_monto_pagar'] !== '') {
                return (float) $fd['del_monto_pagar'];
            }
        }

        return null;
    }

    /**
     * Calcula el costo de envío usando la lógica de 4 casos.
     * Retorna 'gratis' o el costo numérico.
     */
    private function calcularCostoEnvio(Request $request)
    {
        $delivery = $this->resolverDeliveryCompleto($request);
        if ($delivery['envio_gratis']) {
            return 'gratis';
        }
        return $delivery['costo_envio'];
    }

    /**
     * Monto en céntimos: base del carrito + costo de envío (misma lógica en generate y capture).
     */
    private function calcularMontoTotalConEnvio(int $montoBaseCentavos, Request $request): int
    {
        $precioEnvio = $this->calcularCostoEnvio($this->requestParaCalculoEnvio($request));
        $montoTotal = $montoBaseCentavos;

        if ($precioEnvio !== 'gratis' && is_numeric($precioEnvio)) {
            $montoTotal = $montoBaseCentavos + (int) round($precioEnvio * 100);
        }

        return $montoTotal;
    }

    private function requestParaCalculoEnvio(Request $request): Request
    {
        $formData = $this->extraerFormDataArray($request);

        if ($formData !== null) {
            return new Request([
                'txt_listar_departamentos' => $formData['txt_listar_departamentos'] ?? null,
                'txt_listar_provincias'    => $formData['txt_listar_provincias'] ?? null,
                'txt_listar_distritos'     => $formData['txt_listar_distritos'] ?? null,
                'txt_peso_kilogramo'       => $formData['txt_peso_kilogramo'] ?? null,
                'txt_paqueta_medidas'      => $formData['txt_paqueta_medidas'] ?? null,
                'txt_paquete_dimencion'    => $formData['txt_paquete_dimencion'] ?? null,
                'del_lat_cliente'          => $formData['del_lat_cliente'] ?? null,
                'del_lng_cliente'          => $formData['del_lng_cliente'] ?? null,
                'form_data'                => $formData,
            ]);
        }

        return $request;
    }

    /** @return array<string, mixed>|null */
    private function extraerFormDataArray(Request $request): ?array
    {
        if (! $request->has('form_data')) {
            return null;
        }

        $formData = $request->input('form_data');
        if (is_string($formData)) {
            $formData = json_decode($formData, true);
        }

        return is_array($formData) ? $formData : null;
    }

    public function generarFormularioStripe(Request $request)
    {
        // ── Extraer form_data si viene como JSON string ──────────────────────
        $formData = $request->input('form_data');
        if (is_string($formData)) {
            $formData = json_decode($formData, true);
        }
    
        $requestParaEnvio = $formData
            ? new Request([
                'txt_listar_departamentos' => $formData['txt_listar_departamentos'] ?? null,
                'txt_listar_provincias'    => $formData['txt_listar_provincias']    ?? null,
                'txt_listar_distritos'     => $formData['txt_listar_distritos']     ?? null,
                'txt_peso_kilogramo'       => $formData['txt_peso_kilogramo']       ?? null,
                'txt_paqueta_medidas'      => $formData['txt_paqueta_medidas']      ?? null,
                'txt_paquete_dimencion'    => $formData['txt_paquete_dimencion']    ?? null,
            ])
            : $request;
    
        $this->validate($request, [
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
        ]);
    
        $precioEnvio = $this->calcularCostoEnvio($requestParaEnvio);
        $montoBase   = (int) $request->amount;
        $montoTotal  = $montoBase;
    
        if ($precioEnvio !== 'gratis' && is_numeric($precioEnvio)) {
            $montoTotal = $montoBase + (int) ($precioEnvio * 100);
        }
    
        // ── Crear PaymentIntent en Stripe ────────────────────────────────────
        $orderId = 'STR-' . time() . '-' . bin2hex(random_bytes(3));

        $formParaGuardar = $request->input('form_data');
        if (is_string($formParaGuardar)) {
            $formParaGuardar = json_decode($formParaGuardar, true) ?? [];
        }
        if (!is_array($formParaGuardar)) {
            $formParaGuardar = [];
        }

        session([
            'stripe_order_temp' => $orderId,
            'stripe_form_data'  => $formParaGuardar,
            'stripe_email'      => $request->email,
            'stripe_amount'     => $montoTotal,
        ]);

        // Respaldo sin depender de cookies/sesión (balanceadores, dominios, SameSite)
        Cache::put(
            'stripe_checkout_' . $orderId,
            [
                'form_data' => $formParaGuardar,
                'email'     => $request->email,
                'amount'    => $montoTotal,
            ],
            now()->addMinutes(60)
        );
    
        $stripe = new StripeService();
    
        if (!$stripe->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe no está configurado. Contacta al administrador.',
            ], 422);
        }
    
        $result = $stripe->createPaymentIntent([
            'amount'        => $montoTotal,
            'email'         => $request->email,
            'codigo_pedido' => $orderId,
            'metadata'      => [
                'origen'      => 'web_checkout',
                'costo_envio' => $precioEnvio,
                'user_id'     => auth()->check() ? auth()->id() : null,
            ],
        ]);
    
        return response()->json([
            'clientSecret'    => $result['client_secret'],
            'publicKey'       => $result['public_key'],
            'amount'          => $montoTotal,
            'order'           => $orderId,
            'metadata'        => [
                'origen'      => 'web_checkout',
                'costo_envio' => $precioEnvio,
            ],
        ]);
    }
    public function enviar_pago_con_stripe(Request $request)
    {
        $stripeOrderId = $request->input('stripe_order_id');
        $cacheKey = is_string($stripeOrderId) && str_starts_with($stripeOrderId, 'STR-')
            ? 'stripe_checkout_' . $stripeOrderId
            : null;
        $cachePayload = $cacheKey ? Cache::get($cacheKey) : null;

        $sessionOk = session()->has('stripe_order_temp');
        $cacheOk = is_array($cachePayload);

        if (!$sessionOk && !$cacheOk) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada o datos de checkout no encontrados. Recarga la página e intenta nuevamente.',
                'result'  => null,
            ], 422);
        }

        // Datos del checkout: caché (producción) + sesión + POST
        $this->mergeStripeFormDataWithSession($request, $cachePayload);

        $this->validate($request, [
            'payment_intent_id' => 'required|string|starts_with:pi_',
            'email'             => 'required|email',
            'amount'            => 'required|numeric',
        ]);

        $estado = 1; // Pedido recibido con pago completado
        return $this->procesarPedido($request, false, $estado, 'stripe');
    }

    /**
     * Combina form_data: caché por STR-*, sesión Laravel y cuerpo del POST.
     * Orden: caché → sesión → request (el POST gana en conflictos).
     */
    private function mergeStripeFormDataWithSession(Request $request, ?array $cachePayload = null): void
    {
        $fromCache = [];
        if (is_array($cachePayload) && isset($cachePayload['form_data']) && is_array($cachePayload['form_data'])) {
            $fromCache = $cachePayload['form_data'];
        }

        $fromSession = session('stripe_form_data');
        if (is_string($fromSession)) {
            $fromSession = json_decode($fromSession, true) ?? [];
        }
        if (!is_array($fromSession)) {
            $fromSession = [];
        }

        $fromRequest = $request->input('form_data');
        if (is_string($fromRequest)) {
            $fromRequest = json_decode($fromRequest, true) ?? [];
        }
        if (!is_array($fromRequest)) {
            $fromRequest = [];
        }

        $merged = array_merge($fromCache, $fromSession, $fromRequest);
        $request->merge(['form_data' => $merged]);

        if (!$request->filled('email')) {
            if (session()->has('stripe_email')) {
                $request->merge(['email' => session('stripe_email')]);
            } elseif (is_array($cachePayload) && !empty($cachePayload['email'])) {
                $request->merge(['email' => $cachePayload['email']]);
            }
        }
    }

    private function procesarPagoStripe(Request $request): array
    {
        $stripe = new StripeService();
    
        // Confirmar que el PaymentIntent fue exitoso en Stripe
        return $stripe->confirmPayment($request->input('payment_intent_id'));
        // Retorna: ['metodo'=>'stripe','estado'=>'completado','transaccion_id'=>'pi_...','fecha_pago'=>now(),'codigo_culqi'=>null]
    }
 
}
