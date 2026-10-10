<?php

namespace App\Helpers;

use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Log;

class Helper
{
    // ✅ CONFIGURACIÓN: Días hábiles (true = trabaja, false = no trabaja)
    private static $diasHabiles = [
        0 => false, // Domingo
        1 => true,  // Lunes
        2 => true,  // Martes
        3 => true,  // Miércoles
        4 => true,  // Jueves
        5 => true,  // Viernes
        6 => false  // Sábado
    ];

    /**
     * ✅ FUNCIÓN CORREGIDA: Determina si un producto llega hoy, mañana o el lunes
     * Considera días hábiles y fin de semana
     * 
     * @param int $idProducto ID del producto a consultar
     * @return array Array con información de entrega
     */
    public static function corteTiempoPromocion($idProducto)
    {
        $producto = Producto::find($idProducto);
        
        if (!$producto) {
            return [
                'mensaje' => "Producto no encontrado",
                'cutoff_time' => null,
                'llega_hoy' => false,
                'dia_entrega' => null,
                'tipo' => 'error',
                'segundos_restantes' => 0
            ];
        }

        $now = Carbon::now('America/Lima');
        $diaSemana = (int)$now->dayOfWeek; // 0=Domingo, 1=Lunes, ..., 6=Sábado
        
        // Obtener hora de corte del producto (default: 15:00)
        $horaCorteCadena = $producto->corte_tiempo_promocion ?? '15:00:00';
        
        // Limpiar formato de hora si viene con segundos
        if (substr_count($horaCorteCadena, ':') === 2) {
            $partes = explode(':', $horaCorteCadena);
            $horaCorteCadena = $partes[0] . ':' . $partes[1];
        }
        
        $horaCorte = Carbon::today('America/Lima')->setTimeFromTimeString($horaCorteCadena);
        
        // Calcular ventana de 12 horas antes del corte
        $inicioVentana = $horaCorte->copy()->subHours(12);

        // ============================================
        // ✅ CASO 1: ESTAMOS EN FIN DE SEMANA (Sábado o Domingo)
        // ============================================
        if (!self::$diasHabiles[$diaSemana]) {
            $fechaEntrega = self::calcularProximoDiaHabil($now);
            
            return [
                'mensaje' => "Llega el Lunes",
                'cutoff_time' => null,
                'llega_hoy' => false,
                'dia_entrega' => 'lunes',
                'tipo' => 'lunes',
                'fecha_entrega' => $fechaEntrega->format('d \d\e M'),
                'dia' => $fechaEntrega->format('d'),
                'mes' => self::obtenerMesEspanol($fechaEntrega->format('n')),
                'dia_nombre' => self::obtenerDiaSemana($fechaEntrega->dayOfWeek),
                'segundos_restantes' => 0
            ];
        }

        // ============================================
        // ✅ CASO 2: DENTRO DE LA VENTANA DE 12 HORAS (Lunes-Viernes)
        // ============================================
        if ($now->gte($inicioVentana) && $now->lt($horaCorte)) {
            $segundosRestantes = $horaCorte->diffInSeconds($now);
            
            return [
                'mensaje' => "Llega hoy",
                'cutoff_time' => $horaCorte->format('Y-m-d H:i:s'),
                'llega_hoy' => true,
                'dia_entrega' => 'hoy',
                'tipo' => 'hoy',
                'hora_corte' => $horaCorte->format('H:i'),
                'horas_restantes' => floor($segundosRestantes / 3600),
                'minutos_restantes' => floor(($segundosRestantes % 3600) / 60),
                'segundos_restantes' => $segundosRestantes,
                'timestamp_corte' => $horaCorte->timestamp
            ];
        }

        // ============================================
        // ✅ CASO 3: FUERA DE LA VENTANA (Después de las 3pm)
        // ============================================
        $fechaEntrega = $now->copy()->addDay();
        
        // Validar si mañana cae en fin de semana
        $fechaEntrega = self::calcularProximoDiaHabil($fechaEntrega);
        
        // Determinar el tipo según el día de entrega
        $diaEntrega = (int)$fechaEntrega->dayOfWeek;
        $tipo = ($diaEntrega === 1) ? 'lunes' : 'manana'; // Si es lunes, tipo especial
        
        $mensaje = ($tipo === 'lunes') ? "Llega el Lunes" : "Llega mañana";
        
        return [
            'mensaje' => $mensaje,
            'cutoff_time' => $horaCorte->format('Y-m-d H:i:s'),
            'llega_hoy' => false,
            'dia_entrega' => $tipo === 'lunes' ? 'lunes' : 'mañana',
            'tipo' => $tipo,
            'fecha_entrega' => $fechaEntrega->format('d \d\e M'),
            'dia' => $fechaEntrega->format('d'),
            'mes' => self::obtenerMesEspanol($fechaEntrega->format('n')),
            'dia_nombre' => self::obtenerDiaSemana($fechaEntrega->dayOfWeek),
            'segundos_restantes' => 0
        ];
    }

    /**
     * ✅ NUEVA FUNCIÓN: Calcula el próximo día hábil
     * Si la fecha cae en sábado/domingo, retorna el siguiente lunes
     * 
     * @param Carbon $fecha Fecha a validar
     * @return Carbon Próximo día hábil
     */
    private static function calcularProximoDiaHabil(Carbon $fecha)
    {
        $fechaEntrega = $fecha->copy();
        $diasSumados = 0;
        $maxIteraciones = 7; // Seguridad para evitar loops infinitos

        // Buscar el próximo día hábil
        while ($diasSumados < $maxIteraciones) {
            $diaSemana = (int)$fechaEntrega->dayOfWeek;
            
            // Si es día hábil, retornar
            if (self::$diasHabiles[$diaSemana]) {
                return $fechaEntrega;
            }

            // Sino, sumar un día y continuar
            $fechaEntrega->addDay();
            $diasSumados++;
        }

        // Fallback: Si no encuentra día hábil en 7 días, retornar fecha original
        return $fechaEntrega;
    }

    /**
     * ✅ Helper: Obtener nombre del día en español
     */
    private static function obtenerDiaSemana($numeroDia)
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
     * ✅ Helper: Obtener mes en español (abreviado)
     */
    private static function obtenerMesEspanol($numeroMes)
    {
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];
        return $meses[(int)$numeroMes] ?? 'Ene';
    }

    // ============================================
    // ✅ FUNCIONES ORIGINALES (SIN CAMBIOS)
    // ============================================
    
    static function footer_()
    {
        $footerData = DB::table('web_footer')->whereIn('id_footer', array(1))->get();
        return $footerData;
    }

    /** Mensaje predeterminado de WhatsApp (admin: Información corporativa). */
    static function whatsappDefaultMessage(?object $footer = null): string
    {
        if ($footer === null) {
            $rows = self::footer_();
            $footer = $rows[0] ?? null;
        }
        $msg = trim((string) ($footer->whatsapp_mensaje ?? ''));
        if ($msg !== '') {
            return $msg;
        }

        return '¡Hola! Les escribo desde la web de Valento. '
            . 'Me gustaría recibir información y asesoría sobre sus productos y servicios. ¡Muchas gracias!';
    }

    /** Número WhatsApp en formato internacional sin + (ej. 51981629466). */
    static function whatsappPhoneDigits(?object $footer = null): string
    {
        if ($footer === null) {
            $rows = self::footer_();
            $footer = $rows[0] ?? null;
        }

        $raw = '';
        if ($footer) {
            if (!empty($footer->contacto_telefono)) {
                $raw = (string) $footer->contacto_telefono;
            } elseif (!empty($footer->footer_telefonos)) {
                $lineas = preg_split('/\r\n|\r|\n/', (string) $footer->footer_telefonos);
                $raw = trim($lineas[0] ?? '');
            } elseif (!empty($footer->url_whatsapp)) {
                if (preg_match('/(?:phone=|wa\.me\/)(\d{9,15})/', $footer->url_whatsapp, $m)) {
                    return $m[1];
                }
            }
        }

        $phone = preg_replace('/\D/', '', $raw);
        if (strlen($phone) === 9) {
            $phone = '51' . $phone;
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            $phone = '51' . substr($phone, 1);
        } elseif ($phone !== '' && !str_starts_with($phone, '51')) {
            $phone = '51' . ltrim($phone, '0');
        }

        return $phone !== '' ? $phone : '51981629466';
    }

    /**
     * Enlace WhatsApp con mensaje (siempre incluye text=).
     */
    static function whatsappUrl(?object $footer = null, ?string $mensaje = null, ?string $phone = null): string
    {
        if ($footer === null) {
            $rows = self::footer_();
            $footer = $rows[0] ?? null;
        }

        $digits = $phone ? preg_replace('/\D/', '', $phone) : self::whatsappPhoneDigits($footer);
        $texto = $mensaje ?? self::whatsappDefaultMessage($footer);

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($texto);
    }

    /**
     * URL de WhatsApp para CTAs del landing (alias de whatsappUrl).
     */
    static function landingWhatsappUrl(?object $footer = null, ?string $mensaje = null): string
    {
        return self::whatsappUrl($footer, $mensaje);
    }

    /**
     * Normaliza btn_url: si es WhatsApp sin mensaje o vacío, usa el enlace corporativo.
     */
    static function whatsappBtnUrl(?string $url, ?object $footer = null): string
    {
        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return self::whatsappUrl($footer);
        }

        if (preg_match('#(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)#i', $url)) {
            $hasText = false;
            if (preg_match('/[?&]text=([^&]*)/', $url, $m)) {
                $hasText = trim(urldecode($m[1] ?? '')) !== '';
            }
            if (!$hasText) {
                return self::whatsappUrl($footer);
            }
        }

        return $url;
    }

    /** URL pública para imágenes CMS (temp02 o storage_). */
    static function cmsAssetUrl(?string $path): string
    {
        if (empty(trim((string) $path))) {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset($path);
    }

    /** URL pública para imágenes CMS (temp02 o storage_). */
    static function cmsBannerUrl(?string $path, array $fallbacks = []): string
    {
        $candidates = array_filter(array_merge(
            [$path],
            $fallbacks,
            [
                'temp02/assets/img/jh_importaciones/inicio/seccion 2.jpg',
                'temp02/assets/img/jh_importaciones/inicio/jh_importaciones_img_5.jpg',
                'temp02/assets/img/banner/2.jpg',
            ]
        ));

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }
            if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
                return $candidate;
            }
            $relative = ltrim(str_replace('\\', '/', $candidate), '/');
            if (file_exists(public_path($relative))) {
                return asset($relative);
            }
        }

        return asset('temp02/assets/img/banner/2.jpg');
    }

    /** ID de embed de YouTube a partir de URL watch, youtu.be o embed. */
    static function youtubeEmbedId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }

        return null;
    }
    
    static function slider_()
    {
        $dataRow = DB::table('web_slider')->whereIn('id_slider', array(4))->get();
        return $dataRow;
    }
    
    static function cart_heder()
    {
        $hederData = Cart::content();
        return $hederData;
    }

    static function cantidadProducto($codigo_pedido)
    {
        $cantidad = DB::table('ope_pedidos AS PED')
            ->join('ope_pedidos_producto AS PRO', 'PED.id_pedido', '=', 'PRO.id_pedido')
            ->select('PRO.pro_cantidad')
            ->where('PED.codigo_pedido', $codigo_pedido)
            ->get()
            ->sum(function ($item) {
                return $item->pro_cantidad;
            });

        $total = DB::table('ope_pedidos AS PED')
            ->join('ope_pedidos_producto AS PRO', 'PED.id_pedido', '=', 'PRO.id_pedido')
            ->select('PRO.pro_precio', 'PRO.pro_cantidad')
            ->where('PED.codigo_pedido', $codigo_pedido)
            ->get()
            ->sum(function ($item) {
                return $item->pro_precio * $item->pro_cantidad;
            });

        return ['cantidad' => $cantidad, 'total' => $total];
    }

    static function sumaTotalProducto()
    {
        $hederData = Cart::content();
        return $hederData;
    }

    public static function getPublicHtmlPath($relativePath = '')
    {
        $root = env('APP_PUBLIC_PATH') ?: public_path();
        if (! is_dir($root) && is_dir(base_path('../public_html'))) {
            $root = base_path('../public_html');
        }

        $relative = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $relativePath), '/\\');

        return rtrim($root, '/\\') . ($relative !== '' ? DIRECTORY_SEPARATOR . $relative : '');
    }


    static function validateIsMayorista($id_usuario)
    {
        if (!$id_usuario) {
            return false;
        }

        $dataRow = DB::table('users AS U')
            ->join('seguridad_perfil_users AS SP', 'U.id', '=', 'SP.id_usuario')
            ->join('seguridad_perfil AS SPE', 'SP.id_perfil', '=', 'SPE.id_perfil')
            ->select('SPE.id_perfil', 'SPE.nombre')
            ->where('U.id', $id_usuario)
            ->where('SPE.nombre', 'MAYORISTA')
            ->first();

        return $dataRow && $dataRow->nombre === 'MAYORISTA';
    }

    /**
     * Verifica si el usuario tiene el objeto de seguridad (sistema_objetos.nombre) vía menú o módulo.
     * Ej.: nombre "PEDIDO ASIGNAR" → permiso front `pedido_asignar`.
     */
    public static function usuarioTieneObjetoNombre(int $userId, string $nombreObjeto): bool
    {
        if ($userId <= 0) {
            return false;
        }
        if ($userId === 1) {
            return true;
        }

        $objRow = DB::table('sistema_objetos')->where('nombre', $nombreObjeto)->first();
        if (! $objRow) {
            return false;
        }
        $idObjetos = (int) $objRow->id_objetos;

        $cnt = DB::table('seguridad_perfil_users as SPU')
            ->join('seguridad_roles_perfil as SRP', 'SPU.id_perfil', '=', 'SRP.id_perfil')
            ->join('seguridad_menu_objetos_roles as SMOR', 'SMOR.id_roles', '=', 'SRP.id_roles')
            ->join('sistema_menu_objetos as SMO', 'SMO.id_menu_objetos', '=', 'SMOR.id_menu_objetos')
            ->where('SPU.id_usuario', $userId)
            ->where('SMO.id_objetos', $idObjetos)
            ->count();

        if ($cnt > 0) {
            return true;
        }

        if (Schema::hasTable('seguridad_modulo_objetos_roles')) {
            $cnt2 = DB::table('seguridad_perfil_users as SPU')
                ->join('seguridad_roles_perfil as SRP', 'SPU.id_perfil', '=', 'SRP.id_perfil')
                ->join('seguridad_modulo_objetos_roles as SMOR', 'SMOR.id_roles', '=', 'SRP.id_roles')
                ->join('sistema_modulo_objetos as SMO', 'SMO.id_modulo_objetos', '=', 'SMOR.id_modulo_objetos')
                ->where('SPU.id_usuario', $userId)
                ->where('SMO.id_objetos', $idObjetos)
                ->count();

            return $cnt2 > 0;
        }

        return false;
    }

    /**
     * Cantidad total en carrito para un id_producto (todas las líneas; mismo producto, distintas variantes).
     */
    public static function cantidadEnCarritoPorIdProducto(int $idProducto): int
    {
        $total = 0;
        foreach (Cart::content() as $item) {
            $pid = (int) ($item->options['id_producto'] ?? $item->options->id_producto ?? 0);
            if ($pid === $idProducto) {
                $total += (int) $item->qty;
            }
        }

        return $total;
    }

    /** Cantidad en carrito de un producto filtrada por talla. */
    public static function cantidadEnCarritoPorProductoYTalla(int $idProducto, string $talla): int
    {
        $talla = strtoupper(trim($talla));
        $total = 0;
        foreach (Cart::content() as $item) {
            $pid = (int) ($item->options['id_producto'] ?? $item->options->id_producto ?? 0);
            $itemTalla = strtoupper(trim((string) ($item->options['talla'] ?? $item->options->talla ?? '')));
            if ($pid === $idProducto && $itemTalla === $talla) {
                $total += (int) $item->qty;
            }
        }

        return $total;
    }

    /**
     * Igual que cantidadEnCarritoPorIdProducto pero excluye una fila (al cambiar cantidad de una línea).
     */
    public static function cantidadEnCarritoPorIdProductoExcluyendoFila(string $rowIdExcluir, int $idProducto): int
    {
        $total = 0;
        foreach (Cart::content() as $item) {
            if ($item->rowId === $rowIdExcluir) {
                continue;
            }
            $pid = (int) ($item->options['id_producto'] ?? $item->options->id_producto ?? 0);
            if ($pid === $idProducto) {
                $total += (int) $item->qty;
            }
        }

        return $total;
    }

    public static function obtenerStockProducto(int $idProducto): int
    {
        $stock = DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->value('stock');

        return max(0, (int) $stock);
    }

    /**
     * Valida que las cantidades del carrito no superen el stock en administracion_producto.
     *
     * @return array{ok:bool,message?:string}
     */
    public static function validarStockCarritoCompleto(): array
    {
        $agrupado = [];
        foreach (Cart::content() as $item) {
            $id = (int) ($item->options['id_producto'] ?? $item->options->id_producto ?? 0);
            if ($id < 1) {
                continue;
            }
            $agrupado[$id] = ($agrupado[$id] ?? 0) + (int) $item->qty;
        }

        if ($agrupado === []) {
            return ['ok' => true];
        }

        $ids = array_keys($agrupado);
        $productos = DB::table('administracion_producto')
            ->whereIn('id_producto', $ids)
            ->get(['id_producto', 'nombre', 'stock']);

        $byId = $productos->keyBy('id_producto');

        foreach ($agrupado as $idProd => $qtyPedida) {
            $row = $byId[$idProd] ?? null;
            $stock = $row ? max(0, (int) $row->stock) : 0;
            $nombre = $row ? $row->nombre : 'Producto';

            if ($qtyPedida > $stock) {
                $msg = $stock <= 0
                    ? "No hay stock disponible para «{$nombre}». Quite el producto del carrito o actualice la cantidad."
                    : "Stock insuficiente para «{$nombre}». Disponible: {$stock} unidad(es); en el carrito: {$qtyPedida}.";

                return ['ok' => false, 'message' => $msg];
            }
        }

        return ['ok' => true];
    }

    /**
     * Oferta tipo pack (xN): N unidades cuestan $packPrecio.
     * Cantidad = packs*N + resto → total = packs*packPrecio + resto*precioBase.
     * Ej. 2x70 c/u 40: 1=40, 2=70, 3=70+40, 4=140, 5=140+40.
     *
     * @return array{unit_price: float, line_total: float, packs: int, remainder: int, discount: float, has_pack: bool, pack_qty: int, pack_price: float}
     */
    public static function calcularOfertaPackLinea(
        $packCantidad,
        $packPrecio,
        float $precioUnitarioBase,
        int $cantidad
    ): array {
        $cantidad = max(0, $cantidad);
        $base = max(0.0, $precioUnitarioBase);
        $resultado = [
            'unit_price' => $base,
            'line_total' => round($base * $cantidad, 2),
            'packs' => 0,
            'remainder' => $cantidad,
            'discount' => 0.0,
            'has_pack' => false,
            'pack_qty' => 0,
            'pack_price' => 0.0,
        ];

        if ($cantidad < 1 || $base <= 0) {
            return $resultado;
        }
        if ($packCantidad === null || $packPrecio === null || $packCantidad === '' || $packPrecio === '') {
            return $resultado;
        }

        $n = (int) round((float) $packCantidad);
        $packPrice = (float) $packPrecio;
        if ($n < 1 || $packPrice <= 0) {
            return $resultado;
        }

        $packs = intdiv($cantidad, $n);
        $remainder = $cantidad % $n;
        $lineTotal = round(($packs * $packPrice) + ($remainder * $base), 2);
        $fullPrice = round($cantidad * $base, 2);
        // Precisión completa: evita 36.67*3=110.01 / dto 9.99
        $unit = $cantidad > 0 ? ($lineTotal / $cantidad) : $base;

        return [
            'unit_price' => $unit,
            'line_total' => $lineTotal,
            'packs' => $packs,
            'remainder' => $remainder,
            'discount' => max(0.0, round($fullPrice - $lineTotal, 2)),
            'has_pack' => $packs > 0,
            'pack_qty' => $n,
            'pack_price' => $packPrice,
        ];
    }

    /**
     * Precio unitario efectivo con oferta pack (xN).
     * Compatible con carrito: Cart guarda unitario y multiplica por qty.
     */
    public static function aplicarPrecioOfertaPorCantidad(
        $ofertaCantidadUmbral,
        $ofertaPrecioPack,
        float $precioBase,
        int $cantidad
    ): float {
        $calc = self::calcularOfertaPackLinea(
            $ofertaCantidadUmbral,
            $ofertaPrecioPack,
            $precioBase,
            $cantidad
        );

        return (float) $calc['unit_price'];
    }

    /**
     * URL pública del sitio. Si APP_URL apunta a localhost en producción,
     * usa el host real de la petición (evita og:url roto en WhatsApp).
     */
    public static function publicBaseUrl(): string
    {
        $configured = rtrim((string) config('app.url'), '/');

        if (
            $configured !== ''
            && ! str_contains($configured, 'localhost')
            && ! str_contains($configured, '127.0.0.1')
        ) {
            $base = $configured;
        } else {
            $host = request()->getHttpHost();
            if ($host !== '' && ! str_contains($host, 'localhost') && ! str_starts_with($host, '127.0.0.1')) {
                $scheme = request()->isSecure() ? 'https' : 'http';
                $base = $scheme . '://' . $host;
            } elseif ($configured !== '') {
                $base = $configured;
            } else {
                $base = rtrim(url('/'), '/');
            }
        }

        if (str_starts_with((string) config('app.url'), 'https://') || request()->isSecure()) {
            $base = preg_replace('#^http://#i', 'https://', $base);
        }

        return rtrim($base, '/');
    }

    /** URL absoluta HTTPS (requerida por WhatsApp / Open Graph). */
    public static function cmsAbsoluteUrl(?string $path): string
    {
        if (empty(trim((string) $path))) {
            return '';
        }

        $url = self::cmsAssetUrl($path);

        if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
            $relative = ltrim((string) parse_url($url, PHP_URL_PATH), '/');
            $url = self::publicBaseUrl() . '/' . $relative;
        }

        if (str_starts_with((string) config('app.url'), 'https://') || request()->isSecure()) {
            $url = preg_replace('#^http://#i', 'https://', $url);
        }

        return $url;
    }

    /**
     * URL canónica para compartir (sin barra final en la raíz).
     */
    public static function canonicalShareUrl(?string $path = null): string
    {
        if ($path === null) {
            return self::sharePageUrl();
        }

        $base = self::publicBaseUrl();
        $path = trim((string) $path, '/');

        return $path === '' ? $base : $base . '/' . $path;
    }

    /** Imagen OG cuadrada — optimizada para miniatura WhatsApp. */
    public const LIAM_OG_IMAGE = 'wa-og/valento-square.jpg';
    public const LIAM_OG_IMAGE_WIDTH = 1200;
    public const LIAM_OG_IMAGE_HEIGHT = 1200;

    /** @deprecated Restos Royal Masajes — usar LIAM_OG_IMAGE */
    public const ROYAL_OG_IMAGE = self::LIAM_OG_IMAGE;

    /** Imagen por defecto al compartir el sitio (WhatsApp, Facebook, etc.). */
    public static function siteOgImageUrl(?string $override = null, ?string $preferredPath = null): string
    {
        if (! empty($override)) {
            return self::cmsAbsoluteUrl($override);
        }

        $candidates = array_values(array_filter([
            $preferredPath,
            self::LIAM_OG_IMAGE,
            'wa-og/valento-wide.jpg',
            'imagenes/valento-share-og.jpg',
            'og-share.jpg',
        ]));

        foreach ($candidates as $path) {
            if (is_file(public_path($path))) {
                return self::cmsAbsoluteUrl($path);
            }
        }

        return self::cmsAbsoluteUrl(self::LIAM_OG_IMAGE);
    }

    /** URL canónica del sitio (home sin barra final). */
    public static function sharePageUrl(): string
    {
        $base = self::publicBaseUrl();
        $path = trim((string) request()->path(), '/');

        return $path === '' ? $base : $base . '/' . $path;
    }

    /** Favicon Valento — sitio web público. */
    public static function siteFaviconUrl(string $file = 'favicon.ico'): string
    {
        return self::cmsAbsoluteUrl($file);
    }
}