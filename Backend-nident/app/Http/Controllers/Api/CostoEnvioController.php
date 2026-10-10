<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CostoEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CostoEnvioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/costo-envio/listar",
     *     tags={"Configuración Envíos"},
     *     summary="Listar todos los costos de envío por distrito",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de costos de envío", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $costos = CostoEnvio::orderBy('distrito', 'asc')->get();
        return response()->json(['success' => true, 'result' => $costos]);
    }

    /**
     * @OA\Post(
     *     path="/costo-envio/crear",
     *     tags={"Configuración Envíos"},
     *     summary="Crear nuevo costo de envío por distrito",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"distrito","costo_base","costo_por_kg","monto_minimo_envio_gratis"},
     *         @OA\Property(property="distrito", type="string", example="Miraflores"),
     *         @OA\Property(property="costo_base", type="number", example=10.00),
     *         @OA\Property(property="costo_por_kg", type="number", example=2.50),
     *         @OA\Property(property="monto_minimo_envio_gratis", type="number", example=200.00),
     *         @OA\Property(property="activo", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=200, description="Costo de envío creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distrito' => 'required|string|max:100|unique:costo_envios,distrito',
            'costo_base' => 'required|numeric|min:0',
            'costo_por_kg' => 'required|numeric|min:0',
            'monto_minimo_envio_gratis' => 'required|numeric|min:0',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::select('CALL USP_WEB_OFERTAS_GESTION_COSTO_ENVIOS(?, ?, ?, ?, ?, ?, ?)', [
            'INSERT',
            null,
            $request->distrito,
            $request->costo_base,
            $request->costo_por_kg,
            $request->monto_minimo_envio_gratis,
            $request->activo ?? true
        ]);

        return response()->json(['success' => true, 'message' => 'Costo de envío creado']);
    }

    /**
     * @OA\Put(
     *     path="/costo-envio/actualizar/{id}",
     *     tags={"Configuración Envíos"},
     *     summary="Actualizar costo de envío existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"distrito","costo_base","costo_por_kg","monto_minimo_envio_gratis"},
     *         @OA\Property(property="distrito", type="string"),
     *         @OA\Property(property="costo_base", type="number"),
     *         @OA\Property(property="costo_por_kg", type="number"),
     *         @OA\Property(property="monto_minimo_envio_gratis", type="number"),
     *         @OA\Property(property="activo", type="boolean")
     *     )),
     *     @OA\Response(response=200, description="Costo de envío actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'distrito' => 'required|string|max:100|unique:costo_envios,distrito,'.$id,
            'costo_base' => 'required|numeric|min:0',
            'costo_por_kg' => 'required|numeric|min:0',
            'monto_minimo_envio_gratis' => 'required|numeric|min:0',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::select('CALL USP_WEB_OFERTAS_GESTION_COSTO_ENVIOS(?, ?, ?, ?, ?, ?, ?)', [
            'UPDATE',
            $id,
            $request->distrito,
            $request->costo_base,
            $request->costo_por_kg,
            $request->monto_minimo_envio_gratis,
            $request->activo
        ]);

        return response()->json(['success' => true, 'message' => 'Costo de envío actualizado']);
    }

    /**
     * @OA\Delete(
     *     path="/costo-envio/eliminar/{id}",
     *     tags={"Configuración Envíos"},
     *     summary="Eliminar costo de envío",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Costo de envío eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar($id)
    {
        DB::select('CALL USP_WEB_OFERTAS_GESTION_COSTO_ENVIOS(?, ?, ?, ?, ?, ?, ?)', [
            'DELETE',
            $id,
            null,
            null,
            null,
            null,
            null
        ]);

        return response()->json(['success' => true, 'message' => 'Costo de envío eliminado']);
    }
}