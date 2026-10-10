<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OpePedidos;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\HistorialEstadoPedido;
use App\Models\User;
use App\Support\LiamPageData;
use Illuminate\Support\Facades\Schema;

class PedidosController  extends Controller
{

    public function fun_listar_pedidos($page, $page_size)
    {
        $idUsuario =  isset(Auth::user()->id) ? Auth::user()->id : 0;
        $result = DB::select('CALL USP_WEB_OPE_PEDIDO_LISTAR(?,?,?)', [$page, $page_size, $idUsuario]);
        return $this->enriquecerLineasMisCompras($result);
    }

    /**
     * Preferir snapshot repositorio_ (imagen + código) y metadatos de pago del pedido.
     * Evita depender de cambios en USP_WEB_OPE_PEDIDO_LISTAR.
     *
     * @param  array<int, object>  $rows
     * @return array<int, object>
     */
    private function enriquecerLineasMisCompras(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $pedidoIds = collect($rows)->pluck('id_pedido')->filter()->unique()->values()->all();
        $metaByPedido = [];
        if ($pedidoIds !== []) {
            $metaByPedido = DB::table('ope_pedidos')
                ->whereIn('id_pedido', $pedidoIds)
                ->get([
                    'id_pedido',
                    'metodo_pago',
                    'radio_tipo_pago',
                    'pasarela_estado_pago',
                    'direccion_envio_nombre',
                    'direccion_envio_apellido',
                ])
                ->keyBy('id_pedido')
                ->all();
        }

        $repoByPedidoProducto = collect();
        $repoByPedidoOrigen = collect();
        if (Schema::hasTable('repositorio_producto') && $pedidoIds !== []) {
            $repos = DB::table('repositorio_producto')
                ->whereIn('id_pedido', $pedidoIds)
                ->orderByDesc('id_repositorio_producto')
                ->get();
            $repoByPedidoProducto = $repos->keyBy('id_pedido_producto');
            $repoByPedidoOrigen = $repos->groupBy(fn ($r) => $r->id_pedido . ':' . $r->id_producto_origen)
                ->map(fn ($g) => $g->first());
        }

        foreach ($rows as $row) {
            $meta = $metaByPedido[$row->id_pedido] ?? null;
            if ($meta) {
                $row->metodo_pago = $meta->metodo_pago;
                $row->radio_tipo_pago = $meta->radio_tipo_pago;
                if (empty($row->pasarela_estado_pago) && ! empty($meta->pasarela_estado_pago)) {
                    $row->pasarela_estado_pago = $meta->pasarela_estado_pago;
                }
                $dest = trim(((string) ($meta->direccion_envio_nombre ?? '')) . ' ' . ((string) ($meta->direccion_envio_apellido ?? '')));
                $row->nombre_destinatario = $dest !== '' ? $dest : null;
            }

            $repo = null;
            if (! empty($row->id_pedido_producto) && $repoByPedidoProducto->has($row->id_pedido_producto)) {
                $repo = $repoByPedidoProducto->get($row->id_pedido_producto);
            } elseif ($repoByPedidoOrigen->has(($row->id_pedido ?? '') . ':' . ($row->id_producto ?? ''))) {
                $repo = $repoByPedidoOrigen->get(($row->id_pedido ?? '') . ':' . ($row->id_producto ?? ''));
            }

            if ($repo) {
                $codigoRepo = trim((string) ($repo->codigo_producto ?? ''));
                if ($codigoRepo !== '') {
                    $row->codigo_producto = $codigoRepo;
                }
                $imgRepo = trim((string) ($repo->url_imagen ?? ''));
                if ($imgRepo !== '') {
                    $row->pro_url_imagen = LiamPageData::img($imgRepo);
                }
                if (! empty($repo->pro_nombre_linea)) {
                    $row->pro_nombre = $repo->pro_nombre_linea;
                } elseif (! empty($repo->nombre) && empty($row->pro_nombre)) {
                    $row->pro_nombre = $repo->nombre;
                }
            } else {
                $row->pro_url_imagen = LiamPageData::img(
                    (string) ($row->pro_url_imagen ?? ''),
                    'temp02/img/product/product-1.jpg'
                );
                // Código numérico (= id) → buscar código real del catálogo
                $codigo = trim((string) ($row->codigo_producto ?? ''));
                if ($codigo === '' || ctype_digit($codigo)) {
                    $real = DB::table('administracion_producto')
                        ->where('id_producto', (int) ($row->id_producto ?? 0))
                        ->value('codigo_producto');
                    if ($real) {
                        $row->codigo_producto = $real;
                    }
                }
            }

            $row->medio_pago_etiqueta = self::etiquetaMedioPago(
                (string) ($row->radio_tipo_pago ?? ''),
                (string) ($row->metodo_pago ?? '')
            );
        }

        return $rows;
    }

    public static function etiquetaMedioPago(string $radioTipoPago, string $metodoPago): string
    {
        $tipo = strtolower(trim($radioTipoPago));
        $metodo = strtolower(trim($metodoPago));

        if ($tipo === 'whatsapp' || $metodo === 'whatsapp') {
            return 'WhatsApp';
        }
        if ($metodo !== '') {
            if (str_contains($metodo, 'paypal')) {
                return 'PayPal';
            }
            if (str_contains($metodo, 'culqi') || str_contains($metodo, 'tarjeta')) {
                return 'Tarjeta (pasarela)';
            }
            if (str_contains($metodo, 'izipay')) {
                return 'Izipay';
            }
            if (str_contains($metodo, 'stripe')) {
                return 'Stripe';
            }
            if (str_contains($metodo, 'yape') || str_contains($metodo, 'billetera') || str_contains($metodo, 'plin')) {
                return 'Billeteras digitales';
            }
            if (str_contains($metodo, 'pago online') || str_contains($metodo, 'pasarela')) {
                return 'Pasarela de pago';
            }

            return $metodoPago;
        }

        if ($tipo === 'normal' || $tipo === 'pasarela' || $tipo === 'online') {
            return 'Pasarela de pago';
        }

        return 'Por confirmar';
    }

    public function listar_pedidos(Request $request)
    {
        $page = $request->get('page', 1); // Default to page 1 if not set

        $page_size = 6; // Set the number of records per page
        $dataResult = $this->fun_listar_pedidos($page, $page_size);
        $ProductsComprasArrays = [];

        foreach ($dataResult as $user) {
            // Obtener la observaci?n m?s reciente del historial para este pedido
            $ultimaObservacion = HistorialEstadoPedido::where('id_pedido', $user->id_pedido)
                ->whereNotNull('observacion')
                ->where('observacion', '!=', '')
                ->orderBy('created_at', 'desc')
                ->first();

            $key = json_encode([
                'codigo_pedido'             => $user->codigo_pedido,
                'created_at'                => $user->created_at,
                'id_pedido'                 => $user->id_pedido,
                'subido_comprobante_url'    => $user->subido_comprobante_url,
                'comprobante_conforme'      => $user->comprobante_conforme,

                'direccion_envio_ubicacion' => $user->direccion_envio_ubicacion,
                'pasarela_estado_pago'      => $user->pasarela_estado_pago,
                 'estado_en_camino'         => $user->estado_en_camino,
                 'ultima_observacion'       => $ultimaObservacion ? $ultimaObservacion->observacion : null,
                 'fecha_observacion'       => $ultimaObservacion ? $ultimaObservacion->created_at : null,
                 'metodo_pago'             => $user->metodo_pago ?? null,
                 'radio_tipo_pago'         => $user->radio_tipo_pago ?? null,
                 'medio_pago_etiqueta'     => $user->medio_pago_etiqueta ?? self::etiquetaMedioPago(
                     (string) ($user->radio_tipo_pago ?? ''),
                     (string) ($user->metodo_pago ?? '')
                 ),
                 'nombre_destinatario'     => $user->nombre_destinatario ?? null,
            ]);

            $ProductsComprasArrays[$user->id_pedido][$key][] = $user;
        }
        $totalPosts = $dataResult[0]->cantidad ?? null;
        $totalPages = ceil($totalPosts / $page_size);

        return view('web.pages.ope_pedidos')->with(array_merge(
            \App\Support\LiamPageData::layout('cuenta'),
            [
                'metaData' => \App\Models\MetadatosPagina::metaVigente('home'),
            ],
            compact('ProductsComprasArrays', 'page', 'page_size', 'totalPages')
        ));
    }

    function listar_pedidos_paginar(Request $request)
    {
        // Log::channel('stderr')->info($Products);
        if ($request->ajax()) {
            $page = $request->get('page', 1); // Default to page 1 if not set
            $page_size = 6; // Set the number of records per page
            $dataResult = $this->fun_listar_pedidos($page, $page_size);
            $ProductsComprasArrays = [];

            foreach ($dataResult as $user) {
                $key = json_encode([
                    'codigo_pedido' => $user->codigo_pedido,
                    'created_at' => $user->created_at,
                    'id_pedido' => $user->id_pedido,
                    'subido_comprobante_url' => $user->subido_comprobante_url,
                    'comprobante_conforme' => $user->comprobante_conforme ?? null,
                    'direccion_envio_ubicacion' => $user->direccion_envio_ubicacion ?? null,
                    'pasarela_estado_pago' => $user->pasarela_estado_pago ?? null,
                    'estado_en_camino' => $user->estado_en_camino ?? null,
                    'metodo_pago' => $user->metodo_pago ?? null,
                    'radio_tipo_pago' => $user->radio_tipo_pago ?? null,
                    'medio_pago_etiqueta' => $user->medio_pago_etiqueta ?? null,
                    'nombre_destinatario' => $user->nombre_destinatario ?? null,
                ]);

                $ProductsComprasArrays[$user->id_pedido][$key][] = $user;
            }
            $totalPosts = $dataResult[0]->cantidad;
            $totalPages = ceil($totalPosts / $page_size);

            return view('web.pages.pedidos.ajax.open_pedidos', compact('ProductsComprasArrays', 'page', 'page_size', 'totalPages'))->render();
        }
    }

    function listar_pedidos_obtener(Request $request)
    {
        try {
            $id_producto = (int) $request->input('id_producto');
            $id_pedido   = (int) $request->input('id_pedido');

            if ($id_pedido <= 0 || $id_producto <= 0) {
                return response(
                    '<div class="text-center py-5 text-danger"><i class="mdi mdi-alert-circle mr-2"></i>Datos del pedido incompletos.</div>',
                    422
                );
            }

            $obtenerProducto = DB::select('CALL USP_WEB_OPE_PEDIDO_DETALLE(?,?)', [$id_pedido, $id_producto]);

            // Preferir snapshot repositorio_ del pedido
            $repo = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('repositorio_producto')) {
                $repo = DB::table('repositorio_producto')
                    ->where('id_pedido', $id_pedido)
                    ->where('id_producto_origen', $id_producto)
                    ->orderByDesc('id_repositorio_producto')
                    ->first();
                if (! $repo) {
                    \App\Support\PedidoProductoRepositorio::snapshotPedidoCompleto($id_pedido);
                    $repo = DB::table('repositorio_producto')
                        ->where('id_pedido', $id_pedido)
                        ->where('id_producto_origen', $id_producto)
                        ->orderByDesc('id_repositorio_producto')
                        ->first();
                }
            }

            if ($repo) {
                $imgRepo = trim((string) ($repo->url_imagen ?? ''));
                $obtenerProducto = [self::normalizePedidoProductoDetalle((object) [
                    'id_pedido_producto' => $repo->id_pedido_producto,
                    'id_pedido' => $repo->id_pedido,
                    'id_producto' => $repo->id_producto_origen,
                    'codigo_producto' => $repo->codigo_producto,
                    'pro_nombre' => $repo->pro_nombre_linea ?: $repo->nombre,
                    'pro_descripcion' => $repo->descripcion,
                    'pro_precio' => $repo->precio_unitario_pedido ?? $repo->precio,
                    'pro_cantidad' => $repo->cantidad_pedida ?? 1,
                    'pro_url_imagen' => $imgRepo !== ''
                        ? LiamPageData::img($imgRepo)
                        : '',
                    'pro_categoria' => $repo->nombre_categoria,
                    'numero_estrellas' => $repo->numero_estrellas,
                    'slug' => $repo->slug,
                    'pro_precio_old' => $repo->precio_old,
                    'nombre_subcategoria' => $repo->nombre_subcategoria,
                ])];
            } elseif (empty($obtenerProducto)) {
                $fallback = DB::table('ope_pedidos_producto as opp')
                    ->leftJoin('administracion_producto as p', 'opp.id_producto', '=', 'p.id_producto')
                    ->where('opp.id_pedido', $id_pedido)
                    ->where('opp.id_producto', $id_producto)
                    ->select(
                        'opp.id_pedido_producto',
                        'opp.id_pedido',
                        'opp.id_producto',
                        'opp.codigo_producto',
                        'opp.pro_nombre',
                        'opp.pro_descripcion',
                        'opp.pro_precio',
                        'opp.pro_cantidad',
                        'opp.pro_url_imagen',
                        'opp.pro_categoria',
                        'p.numero_estrellas',
                        'p.slug',
                        'p.precio_old as pro_precio_old'
                    )
                    ->first();

                if ($fallback) {
                    $fallback->pro_url_imagen = LiamPageData::img(
                        (string) ($fallback->pro_url_imagen ?? ''),
                        'temp02/img/product/product-1.jpg'
                    );
                    $obtenerProducto = [self::normalizePedidoProductoDetalle($fallback)];
                }
            } else {
                foreach ($obtenerProducto as $p) {
                    $p->id_producto = $id_producto;
                    self::normalizePedidoProductoDetalle($p);
                    $p->pro_url_imagen = LiamPageData::img(
                        (string) ($p->pro_url_imagen ?? ''),
                        'temp02/img/product/product-1.jpg'
                    );
                }
            }

            if (empty($obtenerProducto)) {
                return response(
                    '<div class="text-center py-5 text-muted"><i class="mdi mdi-information-outline mr-2"></i>No se encontró el producto en este pedido.</div>',
                    404
                );
            }

            $obtenerProducto = \App\Models\Producto::enhanceList($obtenerProducto);

            $obtenerFichaTecnica = [];
            if ($repo && \Illuminate\Support\Facades\Schema::hasTable('repositorio_producto_ficha_tecnica')) {
                $obtenerFichaTecnica = DB::table('repositorio_producto_ficha_tecnica')
                    ->where('id_repositorio_producto', $repo->id_repositorio_producto)
                    ->orderBy('orden')
                    ->get()
                    ->all();
            }
            if (empty($obtenerFichaTecnica)) {
                $obtenerFichaTecnica = DB::select(
                    'CALL USP_WEB_OPE_PEDIDO_FICHA_TECNICA(?,?)',
                    [$id_pedido, $id_producto]
                );
            }
            if (empty($obtenerFichaTecnica)) {
                $obtenerFichaTecnica = DB::table('administracion_producto_ficha_tecnica')
                    ->where('id_producto', $id_producto)
                    ->orderBy('orden')
                    ->get()
                    ->all();
            }

            return view(
                'web.pages.pedidos.ajax.open_pedidos_detalle',
                compact('obtenerFichaTecnica', 'obtenerProducto')
            )->render();
        } catch (\Throwable $e) {
            Log::error('[listar_pedidos_obtener] ' . $e->getMessage(), [
                'id_pedido'   => $request->input('id_pedido'),
                'id_producto' => $request->input('id_producto'),
            ]);

            return response(
                '<div class="text-center py-5 text-danger"><i class="mdi mdi-alert-circle mr-2"></i>Error al cargar el detalle del producto.</div>',
                500
            );
        }
    }

    /**
     * El SP USP_WEB_OPE_PEDIDO_DETALLE devuelve nombre/precio; la vista usa pro_nombre/pro_precio.
     */
    private static function normalizePedidoProductoDetalle(object $row): object
    {
        $row->pro_nombre      = $row->pro_nombre      ?? $row->nombre      ?? '';
        $row->pro_descripcion = $row->pro_descripcion ?? $row->descripcion ?? '';
        $row->pro_precio      = (float) ($row->pro_precio ?? $row->precio ?? 0);
        $row->pro_cantidad    = (int) ($row->pro_cantidad ?? $row->cantidad ?? 1);
        $row->pro_url_imagen  = $row->pro_url_imagen  ?? $row->url_imagen  ?? '';

        if (! isset($row->pro_precio_old) && isset($row->precio_old)) {
            $row->pro_precio_old = $row->precio_old;
        }

        return $row;
    }

    public function subir_comprobante_pedido(Request $request, OpePedidos $order)
    {
        $request->validate([
            'subido_comprobante_url' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ], [
            'subido_comprobante_url.required' => 'Debe seleccionar un archivo',
            'subido_comprobante_url.mimes' => 'Solo se permiten archivos JPG, PNG o PDF',
            'subido_comprobante_url.max' => 'El archivo no debe superar 2MB'
        ]);

        try {
            // Eliminar comprobante anterior si existe
            if ($order->subido_comprobante_url) {
                Storage::disk('public')->delete('comprobantes/' . $order->subido_comprobante_url);
            }

            // Guardar nuevo comprobante
            $file = $request->file('subido_comprobante_url');
            $filename = 'payment_' . $order->id_pedido . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Almacenar directamente en el disco public
            $file->storeAs('comprobantes', $filename, 'public');

            // Actualizar registro en BD
            $order->update([
                'subido_comprobante_url' => $filename,
                'subido_comprobante_estatus' => 2,
                'subido_comprobante_fecha' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comprobante subido correctamente',
                'file' => $filename
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al subir comprobante: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al subir el comprobante: ' . $e->getMessage()
            ], 500);
        }
    }

    public function perfil_actualizar(Request $request)
    {
        try {
            $user = Auth::user();
            $cuentaSocial = $this->usuarioTieneLoginSocial($user);

            if ($cuentaSocial) {
                $validator = Validator::make($request->all(), [
                    'telefono' => 'required|string|max:255',
                ], [
                    'telefono.required' => 'El tel?fono es obligatorio',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'nombre' => 'required|string|max:255',
                    'apellido' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'telefono' => 'required|string|max:255',
                ], [
                    'nombre.required' => 'El nombre es obligatorio',
                    'apellido.required' => 'El apellido es obligatorio',
                    'email.required' => 'El email es obligatorio',
                    'email.email' => 'El email no es v?lido',
                    'telefono.required' => 'El tel?fono es obligatorio',
                ]);
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $idUsuario = Auth::id();

            $cliente = DB::table('administracion_cliente')
                ->where('id_usuario', $idUsuario)
                ->first();

            if ($cuentaSocial) {
                if ($cliente) {
                    DB::table('administracion_cliente')
                        ->where('id_usuario', $idUsuario)
                        ->update([
                            'telefono' => $request->telefono,
                            'updated_at' => now(),
                        ]);
                } else {
                    $nombreApellido = $this->nombreApellidoDesdeUsuario($user);
                    DB::table('administracion_cliente')->insertGetId([
                        'id_usuario' => $idUsuario,
                        'nombre' => $nombreApellido['nombre'],
                        'apellido' => $nombreApellido['apellido'],
                        'email' => $user->email,
                        'telefono' => $request->telefono,
                        'razon_social' => trim($nombreApellido['nombre'] . ' ' . $nombreApellido['apellido']),
                        'Activo' => 'S',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Perfil actualizado correctamente',
                ]);
            }

            $datos = [
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'updated_at' => now(),
            ];

            if ($cliente) {
                DB::table('administracion_cliente')
                    ->where('id_usuario', $idUsuario)
                    ->update($datos);

                $mensaje = 'Perfil actualizado correctamente';
            } else {
                $datos['id_usuario'] = $idUsuario;
                $datos['razon_social'] = $request->nombre . ' ' . $request->apellido;
                $datos['Activo'] = 'S';
                $datos['created_at'] = now();

                DB::table('administracion_cliente')->insertGetId($datos);

                $mensaje = 'Perfil creado correctamente';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function perfil_obtener()
    {
        try {
            $user = Auth::user();
            $idUsuario = $user->id;

            $camposIdentidadBloqueados = $this->usuarioTieneLoginSocial($user);

            $cliente = DB::table('administracion_cliente')
                ->where('id_usuario', $idUsuario)
                ->first();

            if ($cliente) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'nombre' => $cliente->nombre,
                        'apellido' => $cliente->apellido,
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono,
                        'campos_identidad_bloqueados' => $camposIdentidadBloqueados,
                    ],
                ]);
            }

            $nombreApellido = $this->nombreApellidoDesdeUsuario($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'nombre' => $nombreApellido['nombre'],
                    'apellido' => $nombreApellido['apellido'],
                    'email' => $user->email ?? '',
                    'telefono' => '',
                    'campos_identidad_bloqueados' => $camposIdentidadBloqueados,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cuenta OAuth (Google): nombre/apellidos/email no editables en Mi perfil.
     * Se consulta users en BD (Auth puede traer un modelo sin google_id actualizado).
     * Sesión login_provider refuerza el flujo tras redirect de Socialite.
     */
    private function usuarioTieneLoginSocial($user): bool
    {
        if (!$user) {
            return false;
        }

        $id = $user->getAuthIdentifier();
        if (!$id) {
            return false;
        }

        $fresh = User::query()->find($id);
        if ($fresh && !empty($fresh->google_id)) {
            return true;
        }

        return Session::get('login_provider') === 'google';
    }

    /**
     * @return array{nombre: string, apellido: string}
     */
    private function nombreApellidoDesdeUsuario($user): array
    {
        $name = trim((string) ($user->name ?? ''));
        if ($name === '') {
            return ['nombre' => '', 'apellido' => ''];
        }
        $parts = preg_split('/\s+/', $name, 2);

        return [
            'nombre' => $parts[0] ?? '',
            'apellido' => $parts[1] ?? '',
        ];
    }

    //=============================START: DIRECCIONES =============================
    /**
     * Listar todas las direcciones del usuario
     */
    public function direcciones_listar()
    {
        try {
            $direcciones = DB::table('administracion_cliente_direcciones')
                ->where('id_usuario', Auth::id())
                ->where('activo', 'S')
                ->orderBy('es_principal', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $direcciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener direcciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva direcci?n
     */
    public function direcciones_crear(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'alias' => 'required|string|max:100',
                'nombre_destinatario' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'departamento' => 'required|string|max:100',
                'provincia' => 'required|string|max:100',
                'distrito' => 'required|string|max:100',
                'direccion' => 'required|string|max:500',
                'referencia' => 'nullable|string|max:500',
                'codigo_postal' => 'nullable|string|max:10',
                'es_principal' => 'boolean'
            ], [
                'alias.required' => 'El alias es obligatorio',
                'nombre_destinatario.required' => 'El nombre del destinatario es obligatorio',
                'telefono.required' => 'El tel?fono es obligatorio',
                'departamento.required' => 'El departamento es obligatorio',
                'provincia.required' => 'La provincia es obligatoria',
                'distrito.required' => 'El distrito es obligatorio',
                'direccion.required' => 'La direcci?n es obligatoria'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $idUsuario = Auth::id();

            // Si es principal, quitar principal de las dem?s
            if ($request->es_principal) {
                DB::table('administracion_cliente_direcciones')
                    ->where('id_usuario', $idUsuario)
                    ->update(['es_principal' => false]);
            }

            $direccionId = DB::table('administracion_cliente_direcciones')->insertGetId([
                'id_usuario' => $idUsuario,
                'alias' => $request->alias,
                'nombre_destinatario' => $request->nombre_destinatario,
                'telefono' => $request->telefono,
                'departamento' => $request->departamento,
                'provincia' => $request->provincia,
                'distrito' => $request->distrito,
                'direccion' => $request->direccion,
                'referencia' => $request->referencia,
                'codigo_postal' => $request->codigo_postal,
                'es_principal' => $request->es_principal ?? false,
                'activo' => 'S',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Direcci?n agregada correctamente',
                'id' => $direccionId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear direcci?n: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar direcci?n existente
     */
    public function direcciones_actualizar(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'alias' => 'required|string|max:100',
                'nombre_destinatario' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'departamento' => 'required|string|max:100',
                'provincia' => 'required|string|max:100',
                'distrito' => 'required|string|max:100',
                'direccion' => 'required|string|max:500',
                'referencia' => 'nullable|string|max:500',
                'codigo_postal' => 'nullable|string|max:10',
                'es_principal' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $idUsuario = Auth::id();

            // Verificar que la direcci?n pertenece al usuario
            $direccion = DB::table('administracion_cliente_direcciones')
                ->where('id', $id)
                ->where('id_usuario', $idUsuario)
                ->first();

            if (!$direccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Direcci?n no encontrada'
                ], 404);
            }

            // Si es principal, quitar principal de las dem?s
            if ($request->es_principal) {
                DB::table('administracion_cliente_direcciones')
                    ->where('id_usuario', $idUsuario)
                    ->where('id', '!=', $id)
                    ->update(['es_principal' => false]);
            }

            DB::table('administracion_cliente_direcciones')
                ->where('id', $id)
                ->update([
                    'alias' => $request->alias,
                    'nombre_destinatario' => $request->nombre_destinatario,
                    'telefono' => $request->telefono,
                    'departamento' => $request->departamento,
                    'provincia' => $request->provincia,
                    'distrito' => $request->distrito,
                    'direccion' => $request->direccion,
                    'referencia' => $request->referencia,
                    'codigo_postal' => $request->codigo_postal,
                    'es_principal' => $request->es_principal ?? false,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Direcci?n actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar direcci?n: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar direcci?n (soft delete)
     */
    public function direcciones_eliminar($id)
    {
        try {
            $idUsuario = Auth::id();

            $direccion = DB::table('administracion_cliente_direcciones')
                ->where('id', $id)
                ->where('id_usuario', $idUsuario)
                ->first();

            if (!$direccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Direcci?n no encontrada'
                ], 404);
            }

            DB::table('administracion_cliente_direcciones')
                ->where('id', $id)
                ->update([
                    'activo' => 'N',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Direcci?n eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar direcci?n: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Establecer como direcci?n principal
     */
    public function direcciones_principal($id)
    {
        try {
            $idUsuario = Auth::id();

            $direccion = DB::table('administracion_cliente_direcciones')
                ->where('id', $id)
                ->where('id_usuario', $idUsuario)
                ->where('activo', 'S')
                ->first();

            if (!$direccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Direcci?n no encontrada'
                ], 404);
            }

            // Quitar principal de todas
            DB::table('administracion_cliente_direcciones')
                ->where('id_usuario', $idUsuario)
                ->update(['es_principal' => false]);

            // Establecer esta como principal
            DB::table('administracion_cliente_direcciones')
                ->where('id', $id)
                ->update(['es_principal' => true, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Direcci?n principal actualizada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer direcci?n principal: ' . $e->getMessage()
            ], 500);
        }
    }
    //============================= END: DIRECCIONES =============================

}
