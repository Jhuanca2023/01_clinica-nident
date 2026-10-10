<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BalanceVentasMensualExport;
use App\Exports\BalanceVentasTrimestralExport;
use App\Exports\BalanceVentasAnualExport;

class BalanceVentasController extends Controller
{
    /**
     * Obtener balance de ventas mensual
     */
    /**
     * @OA\Get(
     *     path="/balance_ventas/mensual",
     *     tags={"Balance"},
     *     summary="Obtener balance de ventas mensual detallado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="fecha_inicio", in="query", required=true, @OA\Schema(type="string", format="date"), example="2024-06-01"),
     *     @OA\Parameter(name="fecha_fin", in="query", required=true, @OA\Schema(type="string", format="date"), example="2024-06-30"),
     *     @OA\Response(response=200, description="Balance mensual obtenido", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function mensual(Request $request): JsonResponse
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            if (!$fechaInicio || !$fechaFin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas de inicio y fin son requeridas',
                    'result' => null
                ], 400);
            }

           
            $datos = DB::select("
                SELECT 
                    DATE(p.created_at) as fecha,
                    COUNT(DISTINCT p.id_pedido) as cantidad_pedidos,
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(pp.pro_precio * pp.pro_cantidad)
                            ELSE (pp.pro_precio * pp.pro_cantidad)
                        END
                    ), 0) as total_ventas,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio
                FROM ope_pedidos p
                LEFT JOIN ope_pedidos_producto pp ON p.id_pedido = pp.id_pedido
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
                GROUP BY DATE(p.created_at)
                ORDER BY fecha ASC
            ", [$fechaInicio, $fechaFin]);

            // Cast results to numeric types to avoid frontend JS toFixed errors
            foreach ($datos as $dato) {
                $dato->total_ventas = (float)($dato->total_ventas ?? 0);
                $dato->cantidad_pedidos = (int)($dato->cantidad_pedidos ?? 0);
                $dato->promedio = (float)($dato->promedio ?? 0);
            }

            $resumen = DB::selectOne("
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(subtotal_pedido.subtotal)
                            ELSE subtotal_pedido.subtotal
                        END
                    ), 0) as total_ventas,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio_venta
                FROM ope_pedidos p
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
            ", [$fechaInicio, $fechaFin]);

            return response()->json([
                'success' => true,
                'message' => 'Balance mensual obtenido correctamente',
                'result' => [
                    'datos' => $datos,
                    'resumen' => [
                        'total_ventas' => (float)($resumen->total_ventas ?? 0),
                        'total_pedidos' => (int)($resumen->total_pedidos ?? 0),
                        'promedio_venta' => (float)($resumen->promedio_venta ?? 0),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener balance mensual: ' . $e->getMessage(),
                'result' => null
            ], 500);
        }
    }

    /**
     * Obtener balance de ventas trimestral
     */
    /**
     * @OA\Get(
     *     path="/balance_ventas/trimestral",
     *     tags={"Balance"},
     *     summary="Obtener balance de ventas trimestral",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="anio", in="query", required=false, @OA\Schema(type="integer"), example=2024),
     *     @OA\Parameter(name="trimestre", in="query", required=false, @OA\Schema(type="integer", enum={1,2,3,4}), example=1),
     *     @OA\Response(response=200, description="Balance trimestral obtenido", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function trimestral(Request $request): JsonResponse
    {
        try {
            $anio = $request->input('anio', date('Y'));
            $trimestre = $request->input('trimestre', 1);

           
            $mesInicio = (($trimestre - 1) * 3) + 1;
            $mesFin = $trimestre * 3;
            $fechaInicio = "$anio-" . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . "-01";
            $fechaFin = date('Y-m-t', strtotime("$anio-" . str_pad($mesFin, 2, '0', STR_PAD_LEFT) . "-01"));

          
            $datos = DB::select("
                SELECT 
                    MONTH(p.created_at) as mes_numero,
                    CASE MONTH(p.created_at)
                        WHEN 1 THEN 'Enero'
                        WHEN 2 THEN 'Febrero'
                        WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Mayo'
                        WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                    END as mes,
                    COUNT(DISTINCT p.id_pedido) as cantidad_pedidos,
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(pp.pro_precio * pp.pro_cantidad)
                            ELSE (pp.pro_precio * pp.pro_cantidad)
                        END
                    ), 0) as total_ventas,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio
                FROM ope_pedidos p
                LEFT JOIN ope_pedidos_producto pp ON p.id_pedido = pp.id_pedido
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND MONTH(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
                GROUP BY mes_numero, mes
                ORDER BY mes_numero ASC
            ", [$anio, $mesInicio, $mesFin]);

            // Cast results to numeric types
            foreach ($datos as $dato) {
                $dato->total_ventas = (float)($dato->total_ventas ?? 0);
                $dato->cantidad_pedidos = (int)($dato->cantidad_pedidos ?? 0);
                $dato->promedio = (float)($dato->promedio ?? 0);
            }

            $resumen = DB::selectOne("
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(subtotal_pedido.subtotal)
                            ELSE subtotal_pedido.subtotal
                        END
                    ), 0) as total_ventas,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio_venta
                FROM ope_pedidos p
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND MONTH(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
            ", [$anio, $mesInicio, $mesFin]);

            return response()->json([
                'success' => true,
                'message' => 'Balance trimestral obtenido correctamente',
                'result' => [
                    'datos' => $datos,
                    'resumen' => [
                        'total_ventas' => (float)($resumen->total_ventas ?? 0),
                        'total_pedidos' => (int)($resumen->total_pedidos ?? 0),
                        'promedio_venta' => (float)($resumen->promedio_venta ?? 0),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener balance trimestral: ' . $e->getMessage(),
                'result' => null
            ], 500);
        }
    }

    /**
     * Obtener balance de ventas anual
     */
    /**
     * @OA\Get(
     *     path="/balance_ventas/anual",
     *     tags={"Balance"},
     *     summary="Obtener balance de ventas anual",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="anio", in="query", required=false, @OA\Schema(type="integer"), example=2024),
     *     @OA\Response(response=200, description="Balance anual obtenido", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function anual(Request $request): JsonResponse
    {
        try {
            $anio = $request->input('anio', date('Y'));

           
            $datos = DB::select("
                SELECT 
                    MONTH(p.created_at) as mes_numero,
                    CASE MONTH(p.created_at)
                        WHEN 1 THEN 'Enero'
                        WHEN 2 THEN 'Febrero'
                        WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Mayo'
                        WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                    END as mes,
                    COUNT(DISTINCT p.id_pedido) as cantidad_pedidos,
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(pp.pro_precio * pp.pro_cantidad)
                            ELSE (pp.pro_precio * pp.pro_cantidad)
                        END
                    ), 0) as total_ventas,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio
                FROM ope_pedidos p
                LEFT JOIN ope_pedidos_producto pp ON p.id_pedido = pp.id_pedido
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
                GROUP BY mes_numero, mes
                ORDER BY mes_numero ASC
            ", [$anio]);

            // Cast results to numeric types
            foreach ($datos as $dato) {
                $dato->total_ventas = (float)($dato->total_ventas ?? 0);
                $dato->cantidad_pedidos = (int)($dato->cantidad_pedidos ?? 0);
                $dato->promedio = (float)($dato->promedio ?? 0);
            }

            $resumen = DB::selectOne("
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(subtotal_pedido.subtotal)
                            ELSE subtotal_pedido.subtotal
                        END
                    ), 0) as total_ventas,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio_venta
                FROM ope_pedidos p
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
            ", [$anio]);

            
            $mejorMes = null;
            $peorMes = null;
            $maxVenta = 0;
            $minVenta = PHP_INT_MAX;

            foreach ($datos as $mes) {
                if ($mes->total_ventas > $maxVenta) {
                    $maxVenta = $mes->total_ventas;
                    $mejorMes = $mes->mes;
                }
                if ($mes->total_ventas < $minVenta && $mes->total_ventas > 0) {
                    $minVenta = $mes->total_ventas;
                    $peorMes = $mes->mes;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Balance anual obtenido correctamente',
                'result' => [
                    'datos' => $datos,
                    'resumen' => [
                        'total_ventas' => (float)($resumen->total_ventas ?? 0),
                        'total_pedidos' => (int)($resumen->total_pedidos ?? 0),
                        'promedio_venta' => (float)($resumen->promedio_venta ?? 0),
                        'mejor_mes' => $mejorMes ?? '-',
                        'peor_mes' => $peorMes ?? '-',
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener balance anual: ' . $e->getMessage(),
                'result' => null
            ], 500);
        }
    }

    /**
     * Descargar reporte mensual en Excel
     */
    /**
     * @OA\Get(
     *     path="/balance_ventas/descargar_mensual",
     *     tags={"Balance"},
     *     summary="Descargar reporte de balance mensual (Excel)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="fecha_inicio", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="fecha_fin", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Archivo Excel generado")
     * )
     */
    public function descargar_mensual(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            $datos = DB::select("
                SELECT 
                    DATE(p.created_at) as fecha,
                    COUNT(DISTINCT p.id_pedido) as cantidad_pedidos,
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(pp.pro_precio * pp.pro_cantidad)
                            ELSE (pp.pro_precio * pp.pro_cantidad)
                        END
                    ), 0) as total_ventas,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio
                FROM ope_pedidos p
                LEFT JOIN ope_pedidos_producto pp ON p.id_pedido = pp.id_pedido
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
                GROUP BY DATE(p.created_at)
                ORDER BY fecha ASC
            ", [$fechaInicio, $fechaFin]);

            $resumen = DB::selectOne("
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(subtotal_pedido.subtotal)
                            ELSE subtotal_pedido.subtotal
                        END
                    ), 0) as total_ventas,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio_venta
                FROM ope_pedidos p
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
            ", [$fechaInicio, $fechaFin]);

            return Excel::download(
                new BalanceVentasMensualExport($datos, $resumen, $fechaInicio, $fechaFin),
                "balance_ventas_mensual_{$fechaInicio}_{$fechaFin}.xlsx"
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar reporte trimestral en Excel
     */
    /**
     * @OA\Get(
     *     path="/balance_ventas/descargar_trimestral",
     *     tags={"Balance"},
     *     summary="Descargar reporte de balance trimestral (Excel)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="anio", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="trimestre", in="query", required=false, @OA\Schema(type="integer", enum={1,2,3,4})),
     *     @OA\Response(response=200, description="Archivo Excel generado")
     * )
     */
    public function descargar_trimestral(Request $request)
    {
        try {
            $anio = $request->input('anio', date('Y'));
            $trimestre = $request->input('trimestre', 1);

            $mesInicio = (($trimestre - 1) * 3) + 1;
            $mesFin = $trimestre * 3;

            $datos = DB::select("
                SELECT 
                    MONTH(p.created_at) as mes_numero,
                    CASE MONTH(p.created_at)
                        WHEN 1 THEN 'Enero'
                        WHEN 2 THEN 'Febrero'
                        WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Mayo'
                        WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                    END as mes,
                    COUNT(DISTINCT p.id_pedido) as cantidad_pedidos,
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(pp.pro_precio * pp.pro_cantidad)
                            ELSE (pp.pro_precio * pp.pro_cantidad)
                        END
                    ), 0) as total_ventas,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio
                FROM ope_pedidos p
                LEFT JOIN ope_pedidos_producto pp ON p.id_pedido = pp.id_pedido
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND MONTH(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
                GROUP BY mes_numero, mes
                ORDER BY mes_numero ASC
            ", [$anio, $mesInicio, $mesFin]);

            $resumen = DB::selectOne("
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(subtotal_pedido.subtotal)
                            ELSE subtotal_pedido.subtotal
                        END
                    ), 0) as total_ventas,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio_venta
                FROM ope_pedidos p
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND MONTH(p.created_at) BETWEEN ? AND ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
            ", [$anio, $mesInicio, $mesFin]);

            return Excel::download(
                new BalanceVentasTrimestralExport($datos, $resumen, $anio, $trimestre),
                "balance_ventas_trimestral_{$anio}_Q{$trimestre}.xlsx"
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar reporte anual en Excel
     */
    /**
     * @OA\Get(
     *     path="/balance_ventas/descargar_anual",
     *     tags={"Balance"},
     *     summary="Descargar reporte de balance anual (Excel)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="anio", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Archivo Excel generado")
     * )
     */
    public function descargar_anual(Request $request)
    {
        try {
            $anio = $request->input('anio', date('Y'));

            $datos = DB::select("
                SELECT 
                    MONTH(p.created_at) as mes_numero,
                    CASE MONTH(p.created_at)
                        WHEN 1 THEN 'Enero'
                        WHEN 2 THEN 'Febrero'
                        WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Mayo'
                        WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                    END as mes,
                    COUNT(DISTINCT p.id_pedido) as cantidad_pedidos,
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(pp.pro_precio * pp.pro_cantidad)
                            ELSE (pp.pro_precio * pp.pro_cantidad)
                        END
                    ), 0) as total_ventas,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio
                FROM ope_pedidos p
                LEFT JOIN ope_pedidos_producto pp ON p.id_pedido = pp.id_pedido
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
                GROUP BY mes_numero, mes
                ORDER BY mes_numero ASC
            ", [$anio]);

            $resumen = DB::selectOne("
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN p.devolucion = 1 THEN -(subtotal_pedido.subtotal)
                            ELSE subtotal_pedido.subtotal
                        END
                    ), 0) as total_ventas,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    COALESCE(AVG(subtotal_pedido.subtotal), 0) as promedio_venta
                FROM ope_pedidos p
                LEFT JOIN (
                    SELECT 
                        id_pedido,
                        SUM(pro_precio * pro_cantidad) as subtotal
                    FROM ope_pedidos_producto
                    GROUP BY id_pedido
                ) subtotal_pedido ON p.id_pedido = subtotal_pedido.id_pedido
                WHERE YEAR(p.created_at) = ?
                  AND p.estado != 7
                  AND (
                    p.estado >= 2
                    OR (p.estado = 1 AND (p.comprobante_conforme = 1 OR p.pasarela_estado_pago = 'completado'))
                  )
            ", [$anio]);

           
            $mejorMes = null;
            $peorMes = null;
            $maxVenta = 0;
            $minVenta = PHP_INT_MAX;
            foreach ($datos as $mes) {
                $ventas = (float)($mes->total_ventas ?? 0);
                if ($ventas > $maxVenta) {
                    $maxVenta = $ventas;
                    $mejorMes = $mes->mes;
                }
                if ($ventas < $minVenta && $ventas > 0) {
                    $minVenta = $ventas;
                    $peorMes = $mes->mes;
                }
            }

            return Excel::download(
                new BalanceVentasAnualExport($datos, $resumen, $anio, $mejorMes, $peorMes),
                "balance_ventas_anual_{$anio}.xlsx"
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
