<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\ProductoCategoriaSub;
use Illuminate\Support\Facades\DB;

class ProductoCategoriaSubController extends Controller
{

    /**
     * @OA\Get(
     *     path="/producto_categoria_sub/obtener",
     *     tags={"Categorías"},
     *     summary="Obtener subcategoría de producto por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Subcategoría encontrada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request): JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from administracion_producto_categoria_sub where id_producto_categoria_sub = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/producto_categoria_sub/listar",
     *     tags={"Categorías"},
     *     summary="Listar todas las subcategorías de productos",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de subcategorías", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_PRODUCTO_CATEGORIA_SUB_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/producto_categoria_sub/crear",
     *     tags={"Categorías"},
     *     summary="Crear nueva subcategoría de producto",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_producto_categoria","nombre","Activo"},
     *         @OA\Property(property="id_producto_categoria", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string", example="Smartphones"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Subcategoría creada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request): JsonResponse
    {
        // id_producto_categoria_sub no es auto-increment en la BD → lo generamos manualmente
        $nextId = (int) DB::table('administracion_producto_categoria_sub')->max('id_producto_categoria_sub') + 1;

        $result = ProductoCategoriaSub::create([
            'id_producto_categoria_sub' => $nextId,
            'id_producto_categoria'     => $request->id_producto_categoria,
            'nombre'                    => $request->nombre,
            'created_at'                => date("Y-m-d H:i:s"),
            'updated_at'                => date("Y-m-d H:i:s"),
            'Activo'                    => $request->Activo ?? 'S',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result'  =>  $result
        ]);
    }

    /**
     * @OA\Put(
     *     path="/producto_categoria_sub/actualizar",
     *     tags={"Categorías"},
     *     summary="Actualizar subcategoría de producto existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id","nombre","Activo"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Subcategoría actualizada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request): JsonResponse
    {
        Log::channel('stderr')->info("Aqui" . json_encode($request->all()));
        $id = $request->input('id');

        $sub = ProductoCategoriaSub::find($id);
        if (! $sub) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategoría no encontrada',
                'result'  => null,
            ], 404);
        }

        $sub->nombre = $request->nombre;
        $sub->Activo = $request->Activo;
        $sub->updated_at = date('Y-m-d H:i:s');
        $sub->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $sub,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/producto_categoria_sub/eliminar",
     *     tags={"Categorías"},
     *     summary="Eliminar subcategoría de producto",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Subcategoría eliminada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=422, description="No se puede eliminar (tiene productos asociados)")
     * )
     */
    public function eliminar(Request $request): JsonResponse
    {
        $id = $request->input('id');

        // Validar si la subcategoría tiene productos asociados
        $tieneProductos = DB::table('administracion_producto')
            ->where('id_producto_categoria_sub', $id)
            ->exists();

        if ($tieneProductos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la subcategoría porque tiene productos asociados',
                'result' => null
            ], 422);
        }

        // Si no tiene productos asociados, proceder con la eliminación
        $result = ProductoCategoriaSub::where('id_producto_categoria_sub', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría eliminada correctamente',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/producto_categoria_sub/listar_obtener",
     *     tags={"Categorías"},
     *     summary="Listar subcategorías filtradas por categoría",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_producto_categoria", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Lista de subcategorías filtrada", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_obtener(Request $request)
    {
        $id_producto_categoria = $request->id_producto_categoria;
        $result = DB::select('CALL USP_ADMINISTRACION_PRODUCTO_CATEGORIA_SUB_LISTAR_OBTENER(?)', [$id_producto_categoria]); //'CALL USP_ADMINISTRACION_PRODUCTO_CATEGORIA_SUB_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }
}
