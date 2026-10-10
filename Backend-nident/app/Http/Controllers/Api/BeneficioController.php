<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BeneficioResource;
use App\Models\Beneficio;

class BeneficioController extends Controller
{

    /**
     * @OA\Get(
     *     path="/beneficio/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar todos los beneficios (cards de la home)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de beneficios", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(Request $request): JsonResponse
    {
        $result = Beneficio::where('activo', 'S')
            ->orderBy('orden')
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/beneficio/actualizar",
     *     tags={"Sitio Web"},
     *     summary="Actualizar múltiples beneficios a la vez",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"cards"},
     *         @OA\Property(property="cards", type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="icono", type="string", example="truck"),
     *             @OA\Property(property="titulo", type="string", example="Envío Gratis"),
     *             @OA\Property(property="descripcion", type="string", example="En pedidos mayores a S/ 100"),
     *             @OA\Property(property="badge", type="string"),
     *             @OA\Property(property="orden", type="integer", example=1),
     *             @OA\Property(property="activo", type="string", enum={"S","N"}, example="S")
     *         ))
     *     )),
     *     @OA\Response(response=200, description="Beneficios actualizados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request)
    {
        try {
            $cards = $request->input('cards', []);
            foreach ($cards as $card) {
                Beneficio::where('id', $card['id'])
                    ->update([
                        'icono'       => $card['icono'],
                        'titulo'      => $card['titulo'],
                        'descripcion' => $card['descripcion'],
                        'badge'       => $card['badge'] ?? null,
                        'orden'       => $card['orden'],
                        'activo'      => $card['activo'] ?? 'S',
                        'updated_at'  => now(),
                    ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Cards actualizados correctamente'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}