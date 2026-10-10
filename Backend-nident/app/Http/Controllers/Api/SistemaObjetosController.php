<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SistemaObjetosController extends Controller
{
    /**
     * @OA\Get(
     *     path="/objetos/listar",
     *     tags={"Objetos"},
     *     summary="Listar objetos base del sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de objetos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(Request $request): JsonResponse
    {
        $items = DB::table('sistema_objetos')
            ->orderBy('id_objetos')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $items,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/objetos/crear",
     *     tags={"Objetos"},
     *     summary="Crear un nuevo objeto base",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre"},
     *         @OA\Property(property="nombre", type="string", example="BOTÓN GUARDAR")
     *     )),
     *     @OA\Response(response=200, description="Objeto creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request): JsonResponse
    {
        $id = DB::table('sistema_objetos')->insertGetId([
            'nombre' => $request->nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' => ['id_objetos' => $id],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/objetos/actualizar",
     *     tags={"Objetos"},
     *     summary="Actualizar un objeto base existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_objetos","nombre"},
     *         @OA\Property(property="id_objetos", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string")
     *     )),
     *     @OA\Response(response=200, description="Objeto actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request): JsonResponse
    {
        $id = $request->input('id_objetos');
        DB::table('sistema_objetos')->where('id_objetos', $id)->update([
            'nombre' => $request->nombre,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result' => 1,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/objetos/eliminar",
     *     tags={"Objetos"},
     *     summary="Eliminar un objeto base",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_objetos"},
     *         @OA\Property(property="id_objetos", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Objeto eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request): JsonResponse
    {
        $id = $request->input('id_objetos');
        DB::table('sistema_menu_objetos')->where('id_objetos', $id)->delete();
        DB::table('sistema_objetos')->where('id_objetos', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado',
            'result' => 1,
        ]);
    }
}
