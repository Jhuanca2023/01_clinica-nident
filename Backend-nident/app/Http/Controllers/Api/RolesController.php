<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RolesResource;
use App\Models\Roles;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{

    /**
     * @OA\Get(
     *     path="/roles/obtener",
     *     tags={"Roles"},
     *     summary="Obtener rol por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Rol encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from seguridad_roles where id_roles = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/roles/listar",
     *     tags={"Roles"},
     *     summary="Listar todos los roles",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de roles", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => Roles::orderBy('id_roles', 'DESC')->get()
        ]);
    }



    /**
     * @OA\Post(
     *     path="/roles/crear",
     *     tags={"Roles"},
     *     summary="Crear nuevo rol",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","Activo"},
     *         @OA\Property(property="nombre", type="string", example="Vendedor"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Rol creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
		$result = Roles::create([
                'nombre'          => $request->nombre,
                'created_at'      => date("Y-m-d H:i:s"),
                'updated_at'      => date("Y-m-d H:i:s"),
                'Activo'          => $request->Activo,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  new RolesResource($result)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/roles/actualizar",
     *     tags={"Roles"},
     *     summary="Actualizar rol existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id","nombre","Activo"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Rol actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        
        $result = Roles::where('id_roles',$id)
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
     *     path="/roles/eliminar",
     *     tags={"Roles"},
     *     summary="Eliminar rol",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Rol eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Roles::where('id_roles',$id)->delete();
 
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    //======= Seleccion multiple =========
    
    /**
     * @OA\Post(
     *     path="/roles/obtener_asignar",
     *     tags={"Roles"},
     *     summary="Asignar roles a un perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_perfil","id_roles"},
     *         @OA\Property(property="id_perfil", type="integer", example=1),
     *         @OA\Property(property="id_roles", type="string", description="IDs de roles separados por coma", example="1,2,3")
     *     )),
     *     @OA\Response(response=200, description="Roles asignados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener_asignar(Request $request) : JsonResponse
    {
        $id_perfil = (int) $request->id_perfil;
        $cadena_id_roles = (string) $request->id_roles;
        $ids = array_values(array_filter(array_map(
            'intval',
            preg_split('/[|,]/', $cadena_id_roles)
        )));

        foreach ($ids as $id_roles) {
            $exists = DB::table('seguridad_roles_perfil')
                ->where('id_perfil', $id_perfil)
                ->where('id_roles', $id_roles)
                ->exists();

            if (! $exists) {
                DB::table('seguridad_roles_perfil')->insert([
                    'id_perfil'  => $id_perfil,
                    'id_roles'   => $id_roles,
                    'created_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Registros asignados!',
            'result' => count($ids),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/roles/obtener_lista",
     *     tags={"Roles"},
     *     summary="Listar roles asignados a un perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_perfil", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Lista de roles", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function obtener_lista(Request $request) : JsonResponse
    {
        $id_perfil = $request->id_perfil;
        $result = DB::select('CALL USP_SEGURIDAD_ROLES_OBTENER_LISTA(?)', [$id_perfil]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/roles/obtener_check",
     *     tags={"Roles"},
     *     summary="Obtener lista de roles con estado de asignación (check) para un perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_perfil", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Lista de roles con check")
     * )
     */
    public function obtener_check(Request $request) : JsonResponse
    {
        $id_perfil = $request->id_perfil;
        $result = DB::select('CALL USP_SEGURIDAD_ROLES_OBTENER_CHECK(?)', [$id_perfil]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/roles/obtener_eliminar",
     *     tags={"Roles"},
     *     summary="Quitar un rol asignado a un perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", description="ID de la relación roles_perfil", example=1)
     *     )),
     *     @OA\Response(response=200, description="Relación eliminada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener_eliminar(Request $request) : JsonResponse
    {
        $id_roles_perfil = $request->input('id');
        $result = DB::select('CALL USP_SEGURIDAD_ROLES_OBTENER_ELIMINAR(?)', [$id_roles_perfil]);
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/roles/eliminar_multiple",
     *     tags={"Roles"},
     *     summary="Eliminar múltiples relaciones de roles_perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_roles_perfil"},
     *         @OA\Property(property="id_roles_perfil", type="string", description="IDs de relaciones separados por coma", example="1,2")
     *     )),
     *     @OA\Response(response=200, description="Relaciones eliminadas", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar_multiple(Request $request) : JsonResponse
    {
        $id_roles_perfil = (string) $request->input('id_roles_perfil', '');
        $id_perfil = $request->input('id_perfil');
        $ids = array_values(array_filter(array_map(
            'intval',
            preg_split('/[|,]/', $id_roles_perfil)
        )));

        if (! empty($id_perfil) && ! empty($ids)) {
            DB::table('seguridad_roles_perfil')
                ->where('id_perfil', (int) $id_perfil)
                ->where(function ($query) use ($ids) {
                    $query->whereIn('id_roles', $ids)
                        ->orWhereIn('id_roles_perfil', $ids);
                })
                ->delete();
            $result = [];
        } else {
            $result = DB::select('CALL USP_SEGURIDAD_ROLES_ELIMINAR_MULTIPLE(?)', [$id_roles_perfil]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

}