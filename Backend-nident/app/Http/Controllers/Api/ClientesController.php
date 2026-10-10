<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ClienteResource;
use App\Models\Clientes;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{

    /**
     * @OA\Get(
     *     path="/clientes/obtener",
     *     tags={"Clientes"},
     *     summary="Obtener cliente por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Cliente encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from administracion_cliente where id_cliente = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/clientes/listar",
     *     tags={"Clientes"},
     *     summary="Listar todos los clientes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de clientes", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_CLIENTE_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/clientes/crear",
     *     tags={"Clientes"},
     *     summary="Crear nuevo cliente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","apellido","email","Activo"},
     *         @OA\Property(property="nombre", type="string", example="Carlos"),
     *         @OA\Property(property="apellido", type="string", example="López"),
     *         @OA\Property(property="email", type="string", format="email", example="carlos@mail.com"),
     *         @OA\Property(property="telefono", type="string"),
     *         @OA\Property(property="direccion", type="string"),
     *         @OA\Property(property="ruc", type="string"),
     *         @OA\Property(property="razon_social", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Cliente creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
		$result = Clientes::create([
            'nombre'          => $request->nombre,
            'apellido'        => $request->apellido,
            'telefono'        => $request->telefono,
            'email'           => $request->email,
            'direccion'       => $request->direccion,
            'ruc'             => $request->ruc,
            'razon_social'    => $request->razon_social,

            'created_at'      => date("Y-m-d H:i:s"),
            'updated_at'      => date("Y-m-d H:i:s"),
            'Activo'          => $request->Activo,
            
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  new ClienteResource($result)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/clientes/actualizar",
     *     tags={"Clientes"},
     *     summary="Actualizar cliente existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id","nombre","Activo"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="apellido", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Cliente actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        
        $result = Clientes::where('id_cliente',$id)
                ->update([
                        'nombre'          => $request->nombre,
                        'apellido'        => $request->apellido,
                        'telefono'        => $request->telefono,
                        'email'           => $request->email,
                        'direccion'       => $request->direccion,
                        'ruc'             => $request->ruc,
                        'razon_social'    => $request->razon_social,
            
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
     *     path="/clientes/eliminar",
     *     tags={"Clientes"},
     *     summary="Eliminar cliente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Cliente eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Clientes::where('id_cliente',$id)->delete();
 
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }
}