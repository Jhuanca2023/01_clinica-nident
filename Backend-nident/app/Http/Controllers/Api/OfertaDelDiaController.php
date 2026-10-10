<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfertaDelDia;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Get(
 *     path="/ofertas/listar",
 *     tags={"Sitio Web"},
 *     summary="Listar todas las ofertas",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Lista de ofertas",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OfertaDelDia"))
 *         )
 *     )
 * )
 *
 * @OA\Get(
 *     path="/ofertas/obtener",
 *     tags={"Sitio Web"},
 *     summary="Obtener oferta por ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_oferta_dia", in="query", required=true, @OA\Schema(type="integer"), example=1),
 *     @OA\Response(response=200, description="Oferta encontrada"),
 *     @OA\Response(response=422, description="Error de validación")
 * )
 *
 * @OA\Get(
 *     path="/ofertas/listar-productos",
 *     tags={"Sitio Web"},
 *     summary="Listar productos disponibles para asignar a ofertas",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Lista de productos activos")
 * )
 *
 * @OA\Post(
 *     path="/ofertas/crear",
 *     tags={"Sitio Web"},
 *     summary="Crear nueva oferta del día",
 *     description="Al crear, desactiva todas las ofertas previas automáticamente",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_producto","nombre_oferta","precio_original","precio_oferta","start_time","end_time","cantidad_disponible","Activo"},
 *             @OA\Property(property="id_producto", type="integer", example=5),
 *             @OA\Property(property="nombre_oferta", type="string", example="Oferta de la semana"),
 *             @OA\Property(property="precio_original", type="number", example=99.90),
 *             @OA\Property(property="precio_oferta", type="number", example=79.90),
 *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-06-01 08:00:00"),
 *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-06-02 08:00:00"),
 *             @OA\Property(property="cantidad_disponible", type="integer", minimum=1, example=100),
 *             @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
 *             @OA\Property(property="segundo_id_producto", type="integer", nullable=true),
 *             @OA\Property(property="segundo_nombre_oferta", type="string", nullable=true),
 *             @OA\Property(property="segundo_precio_oferta", type="number", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Oferta creada exitosamente"),
 *     @OA\Response(response=422, description="Error de validación"),
 *     @OA\Response(response=500, description="Error interno")
 * )
 *
 * @OA\Put(
 *     path="/ofertas/actualizar",
 *     tags={"Sitio Web"},
 *     summary="Actualizar oferta existente",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_oferta_dia","id_producto","nombre_oferta","precio_original","precio_oferta","start_time","end_time","cantidad_disponible","Activo"},
 *             @OA\Property(property="id_oferta_dia", type="integer", example=1),
 *             @OA\Property(property="id_producto", type="integer", example=5),
 *             @OA\Property(property="nombre_oferta", type="string", example="Oferta actualizada"),
 *             @OA\Property(property="precio_original", type="number", example=99.90),
 *             @OA\Property(property="precio_oferta", type="number", example=69.90),
 *             @OA\Property(property="start_time", type="string", format="date-time"),
 *             @OA\Property(property="end_time", type="string", format="date-time"),
 *             @OA\Property(property="cantidad_disponible", type="integer", example=80),
 *             @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Oferta actualizada"),
 *     @OA\Response(response=422, description="Error de validación"),
 *     @OA\Response(response=500, description="Error interno")
 * )
 *
 * @OA\Delete(
 *     path="/ofertas/eliminar",
 *     tags={"Sitio Web"},
 *     summary="Eliminar oferta",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_oferta_dia"},
 *             @OA\Property(property="id_oferta_dia", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Oferta eliminada"),
 *     @OA\Response(response=422, description="ID no válido"),
 *     @OA\Response(response=500, description="Error interno")
 * )
 *
 * @OA\Put(
 *     path="/ofertas/desactivar",
 *     tags={"Sitio Web"},
 *     summary="Desactivar oferta (cambiar Activo a N)",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_oferta_dia"},
 *             @OA\Property(property="id_oferta_dia", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Oferta desactivada"),
 *     @OA\Response(response=422, description="ID no válido")
 * )
 */
class OfertaDelDiaController extends Controller
{
    /**
     * Obtener una oferta específica
     */
    public function obtener(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_oferta_dia' => 'required|integer|exists:oferta_del_dia,id_oferta_dia'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $id = $request->id_oferta_dia;
        $result = DB::select('CALL USP_WEB_OFERTAS_DEL_DIA_OBTENER(?)', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'data' => $result
        ]);
    }

    /**
     * Listar todas las ofertas
     */
    public function listar()
    {
        $ofertas = OfertaDelDia::with('producto')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ofertas
        ]);
    }

    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_producto' => 'required|exists:administracion_producto,id_producto',
            'nombre_oferta' => 'required|string|max:255',
            'precio_original' => 'required|numeric|min:0',
            'precio_oferta' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'cantidad_disponible' => 'required|integer|min:1',
            'Activo' => 'required|in:S,N'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Convertir las fechas al formato de la aplicación (timezone)
            $startTime = \Carbon\Carbon::parse($request->start_time)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $endTime = \Carbon\Carbon::parse($request->end_time)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');

            DB::select('CALL USP_WEB_OFERTAS_DEL_DIA(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                'INSERT',
                null,
                $request->id_producto,
                $request->nombre_oferta,
                $request->precio_oferta,
                $request->precio_original,
                $startTime,
                $endTime,
                $request->cantidad_disponible,
                $request->Activo,
                $request->id_membresia ?? null,

                $request->segundo_id_producto ?? null,
                $request->segundo_nombre_oferta ?? null,
                $request->segundo_precio_oferta ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Oferta creada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::channel('stderr')->error("Error al crear oferta: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la oferta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una oferta existente
     */
    public function actualizar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_oferta_dia' => 'required|integer|exists:oferta_del_dia,id_oferta_dia',
            'id_producto' => 'required|exists:administracion_producto,id_producto',
            'nombre_oferta' => 'required|string|max:255',
            'precio_original' => 'required|numeric|min:0',
            'precio_oferta' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'cantidad_disponible' => 'required|integer|min:1',
            'Activo' => 'required|in:S,N'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Convertir las fechas al formato de la aplicación (timezone)
            $startTime = \Carbon\Carbon::parse($request->start_time)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $endTime = \Carbon\Carbon::parse($request->end_time)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');

            DB::select('CALL USP_WEB_OFERTAS_DEL_DIA(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                'UPDATE',
                $request->id_oferta_dia,
                $request->id_producto,
                $request->nombre_oferta,
                $request->precio_oferta,
                $request->precio_original,
                $startTime,
                $endTime,
                $request->cantidad_disponible,
                $request->Activo,
                $request->id_membresia ?? null,

                $request->segundo_id_producto ?? null,
                $request->segundo_nombre_oferta ?? null,
                $request->segundo_precio_oferta ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Oferta actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la oferta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una oferta
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_oferta_dia' => 'required|integer|exists:oferta_del_dia,id_oferta_dia'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::select('CALL USP_WEB_OFERTAS_DEL_DIA(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                'DELETE',
                $request->id_oferta_dia,

                null,
                null,
                null,
                null,
                null,

                null,
                null,
                null,
                null,
                null,
                null,

                null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Oferta eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la oferta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar una oferta
     */
    public function desactivar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_oferta_dia' => 'required|integer|exists:oferta_del_dia,id_oferta_dia'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::select('CALL USP_WEB_OFERTAS_DEL_DIA(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)', [
                'DEACTIVATE',
                $request->id_oferta_dia,
                null,
                null,
                null,

                null,
                null,
                null,
                null,
                null,

                null,
                null,
                null,
                null

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Oferta desactivada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar la oferta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar productos disponibles para ofertas
     */
    public function listarProductos()
    {
        $productos = Producto::where('Activo', 'S')
            ->select('id_producto', 'nombre', 'precio', 'url_imagen')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }
}
