<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalculoEnvioController extends Controller
{
    /**
     * @OA\Post(
     *     path="/calcular-envio",
     *     tags={"Costo Envío"},
     *     summary="Calcular costo total de envío basado en productos y distrito",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"productos","distrito"},
     *         @OA\Property(property="productos", type="array", @OA\Items(
     *             @OA\Property(property="id_producto", type="integer", example=5),
     *             @OA\Property(property="peso", type="number", example=1.5),
     *             @OA\Property(property="cantidad", type="integer", example=1)
     *         )),
     *         @OA\Property(property="distrito", type="string", example="LIMA")
     *     )),
     *     @OA\Response(response=200, description="Cálculo realizado exitosamente"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function calcular(Request $request)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*.peso' => 'required|numeric|min:0.001',
            'distrito' => 'required|string'
        ]);

        $productosJson = json_encode($request->productos);
        $distrito = $request->distrito;

        $result = DB::select('CALL USP_WEB_OFERTAS_CALCULAR_COSTO_TOTAL(?, ?, @subtotal, @costo_envio, @total, @envio_gratis, @bono_oferta)', [
            $productosJson, $distrito
        ]);

        $results = DB::select('SELECT @subtotal as subtotal, @costo_envio as costo_envio, @total as total, @envio_gratis as envio_gratis, @bono_oferta as bono_oferta');

        return response()->json([
            'success' => true,
            'result' => $results[0]
        ]);
    }
}