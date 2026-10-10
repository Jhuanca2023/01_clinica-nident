<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\VendedorResource;
use App\Models\Vendedor;
use Illuminate\Support\Facades\DB;

class VendedorController extends Controller
{

    /**
     * @OA\Get(
     *     path="/vendedor/obtener",
     *     tags={"Vendedor"},
     *     summary="Obtener vendedor por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Vendedor encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from administracion_vendedor where id_vendedor = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    /**
     * @OA\Get(
     *     path="/vendedor/listar",
     *     tags={"Vendedor"},
     *     summary="Listar todos los vendedores",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de vendedores", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => Vendedor::orderBy('id_vendedor', 'DESC')->get()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/vendedor/crear",
     *     tags={"Vendedor"},
     *     summary="Crear nuevo vendedor",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","Activo"},
     *         @OA\Property(property="nombre", type="string", example="Juan Vendedor"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Vendedor creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
		$result = Vendedor::create([
                    'nombre'          => $request->nombre,
                    'created_at'      => date("Y-m-d H:i:s"),
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'Activo'          => $request->Activo,
            
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  new VendedorResource($result)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/vendedor/actualizar",
     *     tags={"Vendedor"},
     *     summary="Actualizar vendedor existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id","nombre","Activo"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Vendedor actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Vendedor::where('id_vendedor',$id)
                ->update([
                    'nombre'          => $request->nombre,
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'Activo'          => $request->Activo,
                    ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/vendedor/eliminar",
     *     tags={"Vendedor"},
     *     summary="Eliminar vendedor",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Vendedor eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Vendedor::where('id_vendedor',$id)->delete();
 
        return response()->json([
            'success' => true,
           'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }
}