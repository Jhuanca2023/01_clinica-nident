<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\ClienteResource;
use App\Models\OpePedidos;
use App\Models\HistorialEstadoPedido;
use App\Models\ProductoImagen;
use App\Models\ProductoFichaTecnica;
use App\Support\CmsStorageUrl;
use App\Support\PedidoProductoRepositorio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use PDO;

/**
 * @OA\Get(
 *     path="/pedidos/listar",
 *     tags={"Pedidos"},
 *     summary="Listar todos los pedidos",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Lista de pedidos",
 *         @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/pedidos/obtener",
 *     tags={"Pedidos"},
 *     summary="Obtener detalle de un pedido por ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_pedido", in="query", required=true, @OA\Schema(type="integer"), example=100),
 *     @OA\Response(response=200, description="Detalle del pedido"),
 *     @OA\Response(response=403, description="No autorizado para ver este pedido"),
 *     @OA\Response(response=404, description="Pedido no encontrado")
 * )
 *
 * @OA\Get(
 *     path="/pedidos/listar_factura",
 *     tags={"Pedidos"},
 *     summary="Listar pedidos con datos de factura",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Lista de pedidos con factura")
 * )
 *
 * @OA\Get(
 *     path="/pedidos/listar_filtro",
 *     tags={"Pedidos"},
 *     summary="Listar pedidos filtrados por estado",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="estado", in="query", @OA\Schema(type="integer", enum={1,2,3,4,5,6,7}), example=1,
 *         description="1=Recibido, 2=Confirmado, 3=Preparando, 4=Listo, 5=En camino, 6=Entregado, 7=Cancelado"),
 *     @OA\Response(response=200, description="Pedidos filtrados",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="pedidos", type="array", @OA\Items(ref="#/components/schemas/Pedido")),
 *                 @OA\Property(property="contadores", type="object",
 *                     @OA\Property(property="total_pendientes", type="integer"),
 *                     @OA\Property(property="total_deliverys", type="integer"),
 *                     @OA\Property(property="total_entregados", type="integer"),
 *                     @OA\Property(property="total_cancelados", type="integer")
 *                 ),
 *                 @OA\Property(property="puede_asignar", type="boolean")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=500, description="Error al obtener pedidos")
 * )
 *
 * @OA\Put(
 *     path="/pedidos/actualizar_estado_pedido",
 *     tags={"Pedidos"},
 *     summary="Actualizar estado del pedido con historial",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\MediaType(mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"id_pedido","estado"},
 *                 @OA\Property(property="id_pedido", type="integer", example=100),
 *                 @OA\Property(property="estado", type="integer", enum={1,2,3,4,5,6,7}, example=2),
 *                 @OA\Property(property="observacion", type="string", example="Pago verificado"),
 *                 @OA\Property(property="evidencia", type="string", format="binary", description="Imagen de evidencia (max 10MB)")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Estado actualizado"),
 *     @OA\Response(response=400, description="Estado igual al actual"),
 *     @OA\Response(response=404, description="Pedido no encontrado"),
 *     @OA\Response(response=500, description="Error al actualizar")
 * )
 *
 * @OA\Put(
 *     path="/pedidos/asignar_usuario",
 *     tags={"Pedidos"},
 *     summary="Asignar repartidor a un pedido",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_pedido"},
 *             @OA\Property(property="id_pedido", type="integer", example=100),
 *             @OA\Property(property="id_usuario", type="integer", nullable=true, example=3)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Pedido asignado"),
 *     @OA\Response(response=403, description="Sin permiso para asignar"),
 *     @OA\Response(response=404, description="Pedido no encontrado")
 * )
 *
 * @OA\Put(
 *     path="/pedidos/actualizar_estado_devolucion",
 *     tags={"Pedidos"},
 *     summary="Actualizar estado de devolución del pedido",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_pedido","devolucion"},
 *             @OA\Property(property="id_pedido", type="integer", example=100),
 *             @OA\Property(property="devolucion", type="string", example="S")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Devolución actualizada")
 * )
 *
 * @OA\Put(
 *     path="/pedidos/actualizar_comprobante_conforme",
 *     tags={"Pedidos"},
 *     summary="Marcar comprobante como conforme",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_pedido","comprobante_conforme"},
 *             @OA\Property(property="id_pedido", type="integer", example=100),
 *             @OA\Property(property="comprobante_conforme", type="string", example="S")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Comprobante actualizado")
 * )
 *
 * @OA\Get(
 *     path="/pedidos/historial_estados",
 *     tags={"Pedidos"},
 *     summary="Obtener historial de cambios de estado del pedido",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_pedido", in="query", required=true, @OA\Schema(type="integer"), example=100),
 *     @OA\Response(response=200, description="Historial obtenido")
 * )
 *
 * @OA\Put(
 *     path="/pedidos/historial_estados/actualizar",
 *     tags={"Pedidos"},
 *     summary="Actualizar observación de un historial",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_historial"},
 *             @OA\Property(property="id_historial", type="integer", example=5),
 *             @OA\Property(property="observacion", type="string", example="Actualizado manualmente")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Historial actualizado"),
 *     @OA\Response(response=404, description="Historial no encontrado")
 * )
 *
 * @OA\Delete(
 *     path="/pedidos/historial_estados/eliminar",
 *     tags={"Pedidos"},
 *     summary="Eliminar un registro del historial",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_historial"},
 *             @OA\Property(property="id_historial", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Historial eliminado"),
 *     @OA\Response(response=404, description="No encontrado")
 * )
 *
 * @OA\Delete(
 *     path="/pedidos/historial_estados/eliminar_por_estado",
 *     tags={"Pedidos"},
 *     summary="Eliminar historial de un estado específico",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_pedido","estado"},
 *             @OA\Property(property="id_pedido", type="integer", example=100),
 *             @OA\Property(property="estado", type="integer", enum={1,2,3,4,5,6,7}, example=2)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Historial del estado eliminado")
 * )
 *
 * @OA\Delete(
 *     path="/pedidos/historial_estados/eliminar_todo",
 *     tags={"Pedidos"},
 *     summary="Eliminar todo el historial de un pedido",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_pedido"},
 *             @OA\Property(property="id_pedido", type="integer", example=100)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Todo el historial eliminado")
 * )
 */
class PedidosController extends Controller
{
    /**
     * Elimina el archivo de evidencia en disco (disco public_imagenes).
     */
    private function eliminarArchivoEvidencia(?string $rutaRelativa): void
    {
        if (!$rutaRelativa) {
            return;
        }
        \App\Support\CmsImageStorage::deleteIfOwned($rutaRelativa);
    }

    /**
     * Permiso front `pedido_listar_todos` ↔ sistema_objetos.nombre = PEDIDO_LISTAR_TODOS.
     */
    private function puedeListarTodosPedidos(): bool
    {
        $uid = Auth::id();
        if (! $uid) {
            return false;
        }
        $id = (int) $uid;

        $user = Auth::user();
        if ($id === 1 || ($user && $user->es_administrador_principal)) {
            return true;
        }

        return Helper::usuarioTieneObjetoNombre($id, 'PEDIDO LISTAR TODOS')
            || Helper::usuarioTieneObjetoNombre($id, 'PEDIDO_LISTAR_TODOS')
            || Helper::usuarioTieneObjetoNombre($id, 'pedido_listar_todos')
            || Helper::usuarioTieneObjetoNombre($id, 'PEDIDO ASIGNAR')
            || Helper::usuarioTieneObjetoNombre($id, 'PEDIDO_ASIGNAR')
            || Helper::usuarioTieneObjetoNombre($id, 'pedido_asignar');
    }

    private function sqlEstadoDescripcion(): string
    {
        return "CASE
            WHEN AE.estado = 1 THEN 'Pedido recibido'
            WHEN AE.estado = 2 THEN 'Pedido confirmado / Pago confirmado'
            WHEN AE.estado = 3 THEN 'Preparando pedido'
            WHEN AE.estado = 4 THEN 'Pedido listo'
            WHEN AE.estado = 5 THEN 'En camino'
            WHEN AE.estado = 6 THEN 'Entregado'
            WHEN AE.estado = 7 THEN 'Cancelado o rechazado'
            ELSE 'Desconocido'
        END";
    }

    /**
     * Listado vía Query Builder (sin SP). Incluye pedidos guest y nombre desde dirección de envío.
     */
    private function listarPedidosConQuery(int $estado, ?string $fechaDesde, ?string $fechaHasta, ?int $idUsuarioFiltro, ?string $codigoPedido = null): array
    {
        $desde = $fechaDesde ?? '1900-01-01';
        $hasta = $fechaHasta ?? '2999-12-31';
        $codigo = trim((string) $codigoPedido);

        $query = DB::table('ope_pedidos as AE')
            ->leftJoin('ope_pedidos_producto as OPP', 'AE.id_pedido', '=', 'OPP.id_pedido')
            ->leftJoin('administracion_cliente as AC', 'AE.id_cliente', '=', 'AC.id_cliente')
            ->leftJoin('users as US', 'AC.id_usuario', '=', 'US.id')
            ->leftJoin('users as REP', 'AE.id_usuario', '=', 'REP.id')
            ->whereBetween(DB::raw('DATE(AE.created_at)'), [$desde, $hasta]);

        if ($idUsuarioFiltro !== null) {
            $query->where('AE.id_usuario', $idUsuarioFiltro);
        }

        if ($codigo !== '') {
            $query->where('AE.codigo_pedido', 'like', '%' . $codigo . '%');
        } elseif (in_array($estado, [1, 2, 3], true)) {
            $query->whereIn('AE.estado', [1, 2, 3]);
        } elseif ($estado === 4) {
            $query->whereIn('AE.estado', [4, 5]);
        } elseif ($estado === 6) {
            $query->where('AE.estado', 6);
        } elseif ($estado === 7) {
            $query->where('AE.estado', 7);
        } elseif ($estado > 0) {
            $query->where('AE.estado', $estado);
        }

        $estadoSql = $this->sqlEstadoDescripcion();
        $rows = $query
            ->groupBy(
                'AE.id_pedido',
                'AE.created_at',
                'AE.codigo_pedido',
                'AE.id_usuario',
                'AE.direccion_envio_ubicacion',
                'AE.estado',
                'AE.direccion_envio_nombre',
                'AE.direccion_envio_apellido',
                'AC.nombre',
                'AC.apellido',
                'US.name',
                'REP.name'
            )
            ->selectRaw("
                AE.id_pedido,
                DATE_FORMAT(AE.created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
                AE.codigo_pedido,
                AE.id_usuario,
                MAX(REP.name) AS nombre_repartidor_asignado,
                COALESCE(
                    NULLIF(TRIM(CONCAT(IFNULL(AC.nombre, ''), ' ', IFNULL(AC.apellido, ''))), ''),
                    NULLIF(TRIM(CONCAT(IFNULL(AE.direccion_envio_nombre, ''), ' ', IFNULL(AE.direccion_envio_apellido, ''))), ''),
                    US.name,
                    'Cliente invitado'
                ) AS cliente_nombre,
                GROUP_CONCAT(DISTINCT OPP.codigo_producto ORDER BY OPP.codigo_producto SEPARATOR ', ') AS codigo_producto,
                AE.direccion_envio_ubicacion,
                COALESCE(SUM(OPP.pro_cantidad), 0) AS pro_cantidad,
                COALESCE(SUM(OPP.pro_precio * OPP.pro_cantidad), 0) AS pro_total,
                {$estadoSql} AS estado_descripcion,
                AE.estado
            ")
            ->orderByDesc('AE.id_pedido')
            ->get();

        $pedidos = [];
        $rowIndex = 1;
        foreach ($rows as $row) {
            $item = (array) $row;
            $item['RowIndex'] = $rowIndex++;
            $pedidos[] = (object) $item;
        }

        return $pedidos;
    }

    private function contadoresPedidosConQuery(?string $fechaDesde, ?string $fechaHasta, ?int $idUsuarioFiltro): object
    {
        $desde = $fechaDesde ?? '1900-01-01';
        $hasta = $fechaHasta ?? '2999-12-31';

        $query = DB::table('ope_pedidos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$desde, $hasta]);

        if ($idUsuarioFiltro !== null) {
            $query->where('id_usuario', $idUsuarioFiltro);
        }

        $row = $query->selectRaw("
            COUNT(CASE WHEN estado IN (1, 2, 3) THEN 1 END) AS total_pendientes,
            COUNT(CASE WHEN estado IN (4, 5) THEN 1 END) AS total_deliverys,
            COUNT(CASE WHEN estado = 6 THEN 1 END) AS total_entregados,
            COUNT(CASE WHEN estado = 7 THEN 1 END) AS total_cancelados
        ")->first();

        return $this->normalizarContadoresDesdeSp($row);
    }

    private function usuarioPuedeVerPedidoModel(OpePedidos $pedido): bool
    {
        if ($this->puedeListarTodosPedidos()) {
            return true;
        }
        $uid = Auth::id();
        if (! $uid) {
            return false;
        }

        return $pedido->id_usuario !== null && (int) $pedido->id_usuario === (int) $uid;
    }

    private function contadoresDesdeListaPedidos(array $pedidos): object
    {
        $tp = 0;
        $td = 0;
        $te = 0;
        $tc = 0;
        foreach ($pedidos as $p) {
            $e = (int) ($p->estado ?? 0);
            if ($e === 1) {
                $tp++;
            } elseif (in_array($e, [2, 3, 4, 5], true)) {
                $td++;
            } elseif ($e === 6) {
                $te++;
            } elseif ($e === 7) {
                $tc++;
            }
        }

        return (object) [
            'total_pendientes' => $tp,
            'total_deliverys' => $td,
            'total_entregados' => $te,
            'total_cancelados' => $tc,
        ];
    }

    /**
     * Contadores globales devueltos por USP_OPE_PEDIDOS_LISTAR_FILTRAR (2.º result set).
     */
    private function normalizarContadoresDesdeSp(?object $row): object
    {
        if (! $row) {
            return (object) [
                'total_pendientes' => 0,
                'total_deliverys' => 0,
                'total_entregados' => 0,
                'total_cancelados' => 0,
            ];
        }

        return (object) [
            'total_pendientes' => (int) ($row->total_pendientes ?? 0),
            'total_deliverys' => (int) ($row->total_deliverys ?? 0),
            'total_entregados' => (int) ($row->total_entregados ?? 0),
            'total_cancelados' => (int) ($row->total_cancelados ?? 0),
        ];
    }

    public function obtener(Request $request) : JsonResponse
    {
        $id_pedido = $request->id_pedido;
        $pedido = OpePedidos::find($id_pedido);
        if (! $pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        }
        if (! $this->usuarioPuedeVerPedidoModel($pedido)) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para ver este pedido',
            ], 403);
        }

        $result = DB::select('CALL USP_OPE_PEDIDOS_OBTENER(?)', [$id_pedido]);

        // Preferir repositorio_ (snapshot de compra); fallback a catálogo vivo solo si no hay repo
        if (Schema::hasTable('repositorio_producto')) {
            $repos = PedidoProductoRepositorio::obtenerPorPedido((int) $id_pedido);
            // Pedidos antiguos sin snapshot: generar desde catálogo actual (mejor esfuerzo)
            if ($repos->isEmpty() && DB::table('ope_pedidos_producto')->where('id_pedido', $id_pedido)->exists()) {
                PedidoProductoRepositorio::snapshotPedidoCompleto((int) $id_pedido);
                $repos = PedidoProductoRepositorio::obtenerPorPedido((int) $id_pedido);
            }

            if ($repos->isNotEmpty()) {
                $reposByLinea = $repos->keyBy('id_pedido_producto');
                $lineasOpp = DB::table('ope_pedidos_producto')
                    ->where('id_pedido', $id_pedido)
                    ->orderBy('id_pedido_producto')
                    ->get();

                $usedOpp = [];
                foreach ($result as $row) {
                    $opp = null;
                    foreach ($lineasOpp as $i => $linea) {
                        if (isset($usedOpp[$i])) {
                            continue;
                        }
                        $sameNombre = (string) ($linea->pro_nombre ?? '') === (string) ($row->pro_nombre ?? '');
                        $samePrecio = abs((float) ($linea->pro_precio ?? 0) - (float) ($row->pro_precio ?? 0)) < 0.001;
                        $sameCant = (int) ($linea->pro_cantidad ?? 0) === (int) ($row->pro_cantidad ?? 0);
                        if ($sameNombre && $samePrecio && $sameCant) {
                            $opp = $linea;
                            $usedOpp[$i] = true;
                            break;
                        }
                    }
                    if ($opp === null) {
                        foreach ($lineasOpp as $i => $linea) {
                            if (! isset($usedOpp[$i])) {
                                $opp = $linea;
                                $usedOpp[$i] = true;
                                break;
                            }
                        }
                    }
                    if ($opp === null) {
                        continue;
                    }

                    $repo = $reposByLinea->get($opp->id_pedido_producto)
                        ?? $repos->firstWhere('id_producto_origen', (int) ($opp->id_producto ?? 0));
                    if (! $repo) {
                        continue;
                    }

                    $row->id_producto = $repo->id_producto_origen;
                    $row->id_repositorio_producto = $repo->id_repositorio_producto;
                    $row->id_pedido_producto = $repo->id_pedido_producto;

                    $codigoCatalogo = trim((string) ($repo->codigo_producto ?? ''));
                    $row->codigo_producto = $codigoCatalogo !== '' ? $codigoCatalogo : null;

                    $color = trim((string) ($repo->color_pedido ?: $repo->color ?: ''));
                    $row->pro_color = $color !== '' ? $color : null;
                    $row->pro_colores_disponibles = $repo->colores_disponibles ?? null;

                    $colorNombre = null;
                    $hexNorm = strtolower(ltrim($color, '#'));
                    $rawColores = $repo->colores_disponibles ?? null;
                    $decoded = is_string($rawColores) ? json_decode($rawColores, true) : (is_array($rawColores) ? $rawColores : null);
                    if (is_array($decoded)) {
                        foreach ($decoded as $cItem) {
                            if (! is_array($cItem)) {
                                continue;
                            }
                            $cHex = strtolower(ltrim((string) ($cItem['hex'] ?? ''), '#'));
                            if ($hexNorm !== '' && $cHex === $hexNorm) {
                                $nom = trim((string) ($cItem['nombre'] ?? ''));
                                if ($nom !== '' && ! preg_match('/^#?[0-9a-f]{3,8}$/i', $nom)) {
                                    $colorNombre = $nom;
                                }
                                break;
                            }
                        }
                    }
                    if ($colorNombre === null && $color !== '' && ! preg_match('/^#?[0-9a-f]{3,8}$/i', $color)) {
                        $colorNombre = $color;
                    }
                    $row->pro_color_nombre = $colorNombre;

                    $imgRaw = trim((string) ($repo->url_imagen ?? $row->pro_url_imagen ?? ''));
                    $row->pro_url_imagen_rel = $imgRaw !== '' ? ltrim(str_replace('\\', '/', $imgRaw), '/') : null;
                    $row->pro_url_imagen = $imgRaw !== '' ? CmsStorageUrl::forApi($imgRaw) : null;

                    $desc = trim((string) ($repo->descripcion ?? $row->pro_descripcion ?? ''));
                    $row->pro_descripcion = $desc !== '' ? $desc : null;

                    $row->pro_nombre_categoria = $repo->nombre_categoria ?? null;
                    $row->pro_nombre_subcategoria = $repo->nombre_subcategoria ?? null;
                    $row->pro_stock_snapshot = $repo->stock ?? null;
                    $row->pro_tallas_disponibles = $repo->tallas_disponibles ?? null;
                    $row->pro_tallas_variantes = $repo->tallas_variantes ?? null;

                    $talla = trim((string) ($repo->talla_pedida ?? ''));
                    if ($talla === '') {
                        $nombreRaw = (string) ($row->pro_nombre ?? $repo->pro_nombre_linea ?? '');
                        if (preg_match('/\s*[·•]\s*Talla\s+(.+)$/iu', $nombreRaw, $mTalla)) {
                            $talla = trim((string) $mTalla[1]);
                        }
                    }
                    $row->pro_talla = $talla !== '' ? $talla : null;
                    $row->pro_nombre_base = trim((string) ($repo->nombre ?? $row->pro_nombre ?? ''));
                    $row->pro_medidas = $talla !== '' ? $talla : null;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Listar detalle del OpePedidos',
                    'result' => $result,
                ]);
            }
        }

        // Fallback legacy: enriquecer desde administracion_producto (catálogo vivo)
        $selectLineas = [
            'OPP.id_producto',
            'OPP.codigo_producto',
            'OPP.pro_nombre',
            'OPP.pro_precio',
            'OPP.pro_cantidad',
            'OPP.pro_url_imagen',
            'AP.codigo_producto as ap_codigo',
        ];
        if (Schema::hasColumn('ope_pedidos_producto', 'pro_descripcion')) {
            $selectLineas[] = 'OPP.pro_descripcion as opp_pro_descripcion';
        }
        if (Schema::hasColumn('administracion_producto', 'codigo_producto_new')) {
            $selectLineas[] = 'AP.codigo_producto_new as ap_codigo_new';
        }
        if (Schema::hasColumn('administracion_producto', 'color')) {
            $selectLineas[] = 'AP.color as ap_color';
        }
        if (Schema::hasColumn('administracion_producto', 'colores_disponibles')) {
            $selectLineas[] = 'AP.colores_disponibles as ap_colores_disponibles';
        }
        if (Schema::hasColumn('administracion_producto', 'url_imagen')) {
            $selectLineas[] = 'AP.url_imagen as ap_url_imagen';
        }
        if (Schema::hasColumn('administracion_producto', 'descripcion')) {
            $selectLineas[] = 'AP.descripcion as ap_descripcion';
        }
        if (Schema::hasColumn('administracion_producto', 'paquete_medidas')) {
            $selectLineas[] = 'AP.paquete_medidas as ap_paquete_medidas';
        }

        $lineas = DB::table('ope_pedidos_producto as OPP')
            ->leftJoin('administracion_producto as AP', 'OPP.id_producto', '=', 'AP.id_producto')
            ->where('OPP.id_pedido', $id_pedido)
            ->select($selectLineas)
            ->orderBy('OPP.id_producto')
            ->get();

        $used = [];
        foreach ($result as $row) {
            $matchIdx = null;
            foreach ($lineas as $i => $linea) {
                if (isset($used[$i])) {
                    continue;
                }
                $sameNombre = (string) ($linea->pro_nombre ?? '') === (string) ($row->pro_nombre ?? '');
                $samePrecio = abs((float) ($linea->pro_precio ?? 0) - (float) ($row->pro_precio ?? 0)) < 0.001;
                $sameCant = (int) ($linea->pro_cantidad ?? 0) === (int) ($row->pro_cantidad ?? 0);
                if ($sameNombre && $samePrecio && $sameCant) {
                    $matchIdx = $i;
                    break;
                }
            }
            if ($matchIdx === null) {
                foreach ($lineas as $i => $linea) {
                    if (! isset($used[$i])) {
                        $matchIdx = $i;
                        break;
                    }
                }
            }
            if ($matchIdx !== null) {
                $used[$matchIdx] = true;
                $linea = $lineas[$matchIdx];
                $row->id_producto = $linea->id_producto;
                $codigoCatalogo = trim((string) ($linea->ap_codigo ?? ''));
                $codigoLinea = trim((string) ($linea->codigo_producto ?? ''));
                if ($codigoCatalogo !== '') {
                    $row->codigo_producto = $codigoCatalogo;
                } elseif ($codigoLinea !== '' && ! ctype_digit($codigoLinea)) {
                    $row->codigo_producto = $codigoLinea;
                } else {
                    $row->codigo_producto = null;
                }

                $color = trim((string) ($linea->ap_color ?? ''));
                $row->pro_color = $color !== '' ? $color : null;
                $row->pro_colores_disponibles = $linea->ap_colores_disponibles ?? null;

                $colorNombre = null;
                $hexNorm = strtolower(ltrim($color, '#'));
                $rawColores = $linea->ap_colores_disponibles ?? null;
                if (is_string($rawColores) && $rawColores !== '') {
                    $decoded = json_decode($rawColores, true);
                } elseif (is_array($rawColores)) {
                    $decoded = $rawColores;
                } else {
                    $decoded = null;
                }
                if (is_array($decoded)) {
                    foreach ($decoded as $cItem) {
                        if (! is_array($cItem)) {
                            continue;
                        }
                        $cHex = strtolower(ltrim((string) ($cItem['hex'] ?? ''), '#'));
                        if ($hexNorm !== '' && $cHex === $hexNorm) {
                            $nom = trim((string) ($cItem['nombre'] ?? ''));
                            if ($nom !== '' && ! preg_match('/^#?[0-9a-f]{3,8}$/i', $nom)) {
                                $colorNombre = $nom;
                            }
                            break;
                        }
                    }
                }
                if ($colorNombre === null && $color !== '' && ! preg_match('/^#?[0-9a-f]{3,8}$/i', $color)) {
                    $colorNombre = $color;
                }
                $row->pro_color_nombre = $colorNombre;

                $imgLinea = trim((string) ($row->pro_url_imagen ?? $linea->pro_url_imagen ?? ''));
                $imgCatalogo = trim((string) ($linea->ap_url_imagen ?? ''));
                $imgRaw = $imgCatalogo !== '' ? $imgCatalogo : $imgLinea;
                $row->pro_url_imagen_rel = $imgRaw !== ''
                    ? ltrim(str_replace('\\', '/', $imgRaw), '/')
                    : null;
                $row->pro_url_imagen = $imgRaw !== '' ? CmsStorageUrl::forApi($imgRaw) : null;

                $descLinea = trim((string) ($linea->opp_pro_descripcion ?? $row->pro_descripcion ?? ''));
                $descCatalogo = trim((string) ($linea->ap_descripcion ?? ''));
                $desc = $descLinea !== '' ? $descLinea : $descCatalogo;
                $row->pro_descripcion = $desc !== '' ? $desc : null;

                $row->pro_medidas = null;
                $row->pro_tallas_lista = null;
            }

            $nombreRaw = (string) ($row->pro_nombre ?? '');
            if (preg_match('/\s*[·•]\s*Talla\s+(.+)$/iu', $nombreRaw, $mTalla)) {
                $row->pro_talla = trim((string) $mTalla[1]);
                $row->pro_nombre_base = trim(preg_replace('/\s*[·•]\s*Talla\s+.+$/iu', '', $nombreRaw));
            } elseif (preg_match('/\bTalla\s+([A-Za-z0-9\/\-]+)\s*$/iu', $nombreRaw, $mTalla2)) {
                $row->pro_talla = trim((string) $mTalla2[1]);
                $row->pro_nombre_base = trim(preg_replace('/\bTalla\s+[A-Za-z0-9\/\-]+\s*$/iu', '', $nombreRaw));
            } else {
                $row->pro_talla = $row->pro_talla ?? null;
                $row->pro_nombre_base = $row->pro_nombre_base ?? $nombreRaw;
            }

            $tallaElegida = trim((string) ($row->pro_talla ?? ''));
            $row->pro_medidas = $tallaElegida !== '' ? $tallaElegida : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Listar detalle del OpePedidos',
            'result' => $result,
        ]);
    }

    public function asignar_pedido_usuario(Request $request): JsonResponse
    {
        if (! $this->puedeListarTodosPedidos()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para asignar pedidos',
            ], 403);
        }

        if (! Schema::hasColumn('ope_pedidos', 'id_usuario')) {
            return response()->json([
                'success' => false,
                'message' => 'Ejecute la migración: columna id_usuario en ope_pedidos',
            ], 500);
        }

        $request->validate([
            'id_pedido' => 'required|integer',
            'id_usuario' => 'nullable|integer|exists:users,id',
        ]);

        $pedido = OpePedidos::find($request->input('id_pedido'));
        if (! $pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        }

        $pedido->id_usuario = $request->input('id_usuario');
        $pedido->updated_at = date('Y-m-d H:i:s');
        $pedido->save();

        return response()->json([
            'success' => true,
            'message' => 'Pedido asignado correctamente',
            'result' => $pedido,
        ]);
    }
    
    public function listar()
    {
        $result = DB::select('CALL USP_OPE_PEDIDOS_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    public function listar_factura()
    {
        $result = DB::select('CALL USP_OPE_PEDIDOS_LISTAR_FACTURA()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }
    
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        
        DB::beginTransaction();
        try {
            
            DB::table('ope_pedidos_producto')->where('id_pedido', $id)->delete();
            DB::table('ope_pedidos_estado_historial')->where('id_pedido', $id)->delete();
            
            
            if (Schema::hasTable('ope_pedidos_ficha_tecnica')) {
                DB::table('ope_pedidos_ficha_tecnica')->where('id_pedido', $id)->delete();
            }

            PedidoProductoRepositorio::eliminarPorPedido((int) $id);

            $result = OpePedidos::where('id_pedido', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado!',
                'result'  => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizar_estado_pedido(Request $request) : JsonResponse
    {
        $request->validate([
            'id_pedido' => 'required|integer',
            'estado' => 'required|integer|between:1,7',
            'observacion' => 'nullable|string|max:1000',
            'evidencia' => 'nullable|file|image|max:10240',
        ]);

        $id_pedido = (int) $request->input('id_pedido');
        $estado_nuevo = (int) $request->input('estado');
        $observacion = $request->input('observacion');

        // Obtener estado anterior
        $pedido = OpePedidos::find($id_pedido);
        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        $estado_anterior = $pedido->estado;

        // Validar que el estado nuevo sea diferente al anterior
        if ($estado_anterior == $estado_nuevo) {
            return response()->json([
                'success' => false,
                'message' => 'El estado nuevo debe ser diferente al estado actual'
            ], 400);
        }

        $evidenciaUrl = null;
        if ($request->hasFile('evidencia')) {
            $file = $request->file('evidencia');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $ext = 'jpg';
            }
            $filename_ = 'ev_' . $id_pedido . '_' . time() . '_' . Str::random(8) . '.' . $ext;
            $evidenciaUrl = \App\Support\CmsImageStorage::store($file, 'pedidos_estado_evidencia');
        }

        DB::beginTransaction();
        try {
            // Actualizar estado del pedido
            $result = OpePedidos::where('id_pedido', $id_pedido)
                ->update([
                    'estado' => $estado_nuevo,
                    'updated_at' => date("Y-m-d H:i:s"),
                    ]);

            // Guardar en historial solo si el estado cambió
            HistorialEstadoPedido::create([
                'id_pedido' => $id_pedido,
                'estado_anterior' => $estado_anterior,
                'estado_nuevo' => $estado_nuevo,
                'observacion' => $observacion,
                'evidencia_url' => $evidenciaUrl,
                'id_usuario' => Auth::id()
            ]);

            DB::commit();
 
        return response()->json([
            'success' => true,
                'message' => 'Estado actualizado correctamente',
                'result' => $result
        ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($evidenciaUrl) {
                $this->eliminarArchivoEvidencia($evidenciaUrl);
            }
            Log::error('Error al actualizar estado del pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function actualizar_estado_devolucion(Request $request) : JsonResponse
    {
        $id_pedido = $request->input('id_pedido');
        $result = OpePedidos::where('id_pedido',$id_pedido)
                ->update([
                        'devolucion'        => $request->devolucion,
                        'updated_at'        => date("Y-m-d H:i:s"),
                    ]);
 
        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $result
        ]);
    }

    public function listar_filtro(Request $request)
    {
        try {
            $estado = (int) $request->input('estado', 1);
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');
            $codigoPedido = trim((string) $request->input('codigo_pedido', ''));

            $puedeListarTodos = $this->puedeListarTodosPedidos();
            $uid = Auth::id();
            $idUsuarioFiltro = $puedeListarTodos ? null : (int) ($uid ?? 0);

            $pedidos = $this->listarPedidosConQuery($estado, $fechaDesde, $fechaHasta, $idUsuarioFiltro, $codigoPedido !== '' ? $codigoPedido : null);
            $contadores = $this->contadoresPedidosConQuery($fechaDesde, $fechaHasta, $idUsuarioFiltro);

            return response()->json([
                'success' => true,
                'data' => [
                    'pedidos' => $pedidos,
                    'contadores' => $contadores,
                    'puede_listar_todos' => $puedeListarTodos,
                    'puede_asignar' => $puedeListarTodos,
                    'filtros_aplicados' => [
                        'estado' => $estado,
                        'fecha_desde' => $fechaDesde,
                        'fecha_hasta' => $fechaHasta,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener los pedidos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pedidos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizar_comprobante_conforme(Request $request) : JsonResponse
    {
        $id_pedido = $request->input('id_pedido');
        $result = OpePedidos::where('id_pedido',$id_pedido)
                ->update([
                        'comprobante_conforme'     => $request->comprobante_conforme,
                        'updated_at'               => date("Y-m-d H:i:s"),
                    ]);
 
        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $result
        ]);
    }

    public function listar_historial_estados(Request $request) : JsonResponse
    {
        $id_pedido = $request->input('id_pedido');
        
        $historial = HistorialEstadoPedido::where('id_pedido', $id_pedido)
            ->with('usuario:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Historial de estados obtenido',
            'result' => $historial
        ]);
    }

    public function actualizar_historial_estado(Request $request) : JsonResponse
    {
        $request->validate([
            'id_historial' => 'required|integer',
            'observacion' => 'nullable|string|max:1000'
        ]);

        $historial = HistorialEstadoPedido::find($request->input('id_historial'));
        
        if (!$historial) {
            return response()->json([
                'success' => false,
                'message' => 'Registro de historial no encontrado'
            ], 404);
        }

        $historial->update([
            'observacion' => $request->input('observacion'),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Observación actualizada',
            'result' => $historial
        ]);
    }

    public function eliminar_historial_estado(Request $request) : JsonResponse
    {
        $id_historial = $request->input('id_historial');
        
        $historial = HistorialEstadoPedido::find($id_historial);
        
        if (!$historial) {
            return response()->json([
                'success' => false,
                'message' => 'Registro de historial no encontrado'
            ], 404);
        }

        $this->eliminarArchivoEvidencia($historial->evidencia_url);
        $historial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado del historial',
            'result' => true
        ]);
    }

    public function eliminar_historial_por_estado(Request $request) : JsonResponse
    {
        $request->validate([
            'id_pedido' => 'required|integer',
            'estado' => 'required|integer|between:1,7'
        ]);

        $id_pedido = $request->input('id_pedido');
        $estado = $request->input('estado');

        $registros = HistorialEstadoPedido::where('id_pedido', $id_pedido)
            ->where('estado_nuevo', $estado)
            ->get();
        foreach ($registros as $r) {
            $this->eliminarArchivoEvidencia($r->evidencia_url);
        }

        $eliminados = HistorialEstadoPedido::where('id_pedido', $id_pedido)
            ->where('estado_nuevo', $estado)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Historial del estado eliminado correctamente',
            'result' => $eliminados
        ]);
    }

    public function eliminar_todo_historial(Request $request) : JsonResponse
    {
        $request->validate([
            'id_pedido' => 'required|integer'
        ]);

        $id_pedido = $request->input('id_pedido');

        $registros = HistorialEstadoPedido::where('id_pedido', $id_pedido)->get();
        foreach ($registros as $r) {
            $this->eliminarArchivoEvidencia($r->evidencia_url);
        }

        $eliminados = HistorialEstadoPedido::where('id_pedido', $id_pedido)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todo el historial ha sido eliminado correctamente',
            'result' => $eliminados
        ]);
    }

}