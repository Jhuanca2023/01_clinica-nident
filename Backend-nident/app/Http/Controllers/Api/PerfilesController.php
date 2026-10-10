<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PerfilResource;
use App\Models\Perfiles;
use Illuminate\Support\Facades\DB;

class PerfilesController extends Controller
{

    /**
     * @OA\Get(
     *     path="/perfiles/obtener",
     *     tags={"Perfiles"},
     *     summary="Obtener perfil por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Perfil encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from seguridad_perfil where id_perfil = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/perfiles/listar",
     *     tags={"Perfiles"},
     *     summary="Listar todos los perfiles",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de perfiles", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = DB::select('CALL USP_SEGURIDAD_PERFIL_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' =>  $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/perfiles/crear",
     *     tags={"Perfiles"},
     *     summary="Crear nuevo perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","Activo"},
     *         @OA\Property(property="nombre", type="string", example="Administrador"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Perfil creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
		$result = Perfiles::create([
                    'nombre' => $request->nombre,

                    'created_at'      => date("Y-m-d H:i:s"),
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'Activo'          => $request->Activo,
            
                    ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  new PerfilResource($result)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/perfiles/actualizar",
     *     tags={"Perfiles"},
     *     summary="Actualizar perfil existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id","nombre","Activo"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Perfil actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Perfiles::where('id_perfil',$id)
                ->update([
                        'nombre' => $request->nombre,

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
     *     path="/perfiles/eliminar",
     *     tags={"Perfiles"},
     *     summary="Eliminar perfil",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Perfil eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Perfiles::where('id_perfil',$id)->delete();
 
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/perfiles/obtener_asignar",
     *     tags={"Perfiles"},
     *     summary="Asignar perfiles a un usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_usuario","id_perfil"},
     *         @OA\Property(property="id_usuario", type="integer", example=1),
     *         @OA\Property(property="id_perfil", type="string", description="IDs de perfiles separados por coma", example="1,2,3")
     *     )),
     *     @OA\Response(response=200, description="Perfiles asignados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener_asignar(Request $request) : JsonResponse
    {
        $id_usuario = (int) $request->id_usuario;
        $cadena_id_perfil = (string) $request->id_perfil;
        $ids = array_values(array_filter(array_map(
            'intval',
            preg_split('/[|,]/', $cadena_id_perfil)
        )));

        foreach ($ids as $id_perfil) {
            $exists = DB::table('seguridad_perfil_users')
                ->where('id_usuario', $id_usuario)
                ->where('id_perfil', $id_perfil)
                ->exists();

            if (! $exists) {
                DB::table('seguridad_perfil_users')->insert([
                    'id_usuario' => $id_usuario,
                    'id_perfil'    => $id_perfil,
                    'created_at'   => now(),
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
     *     path="/perfiles/obtener_lista",
     *     tags={"Perfiles"},
     *     summary="Listar perfiles asignados a un usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_usuario", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Lista de perfiles", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function obtener_lista(Request $request) : JsonResponse
    {
        $id_usuario = $request->id_usuario;
        $result = DB::select('CALL USP_SEGURIDAD_PERFIL_OBTENER_LISTA(?)', [$id_usuario]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/perfiles/obtener_check",
     *     tags={"Perfiles"},
     *     summary="Obtener lista de perfiles con estado de asignación (check) para un usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_usuario", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Lista de perfiles con check")
     * )
     */
    public function obtener_check(Request $request) : JsonResponse
    {
        $id_usuario = $request->id_usuario;
        $result = DB::select('CALL USP_SEGURIDAD_PERFIL_OBTENER_CHECK(?)', [$id_usuario]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/perfiles/obtener_eliminar",
     *     tags={"Perfiles"},
     *     summary="Quitar un perfil asignado a un usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", description="ID de la relación perfil_user", example=1)
     *     )),
     *     @OA\Response(response=200, description="Relación eliminada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener_eliminar(Request $request) : JsonResponse
    {
        $id_perfil_users = $request->input('id');
        $result = DB::select('CALL USP_SEGURIDAD_PERFIL_OBTENER_ELIMINAR(?)', [$id_perfil_users]);
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/perfiles/eliminar_multiple",
     *     tags={"Perfiles"},
     *     summary="Eliminar múltiples perfiles por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_perfil"},
     *         @OA\Property(property="id_perfil", type="string", description="IDs de perfiles separados por coma", example="1,2")
     *     )),
     *     @OA\Response(response=200, description="Perfiles eliminados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar_multiple(Request $request) : JsonResponse
    {
        $id_perfil = (string) $request->input('id_perfil', '');
        $id_usuario = $request->input('id_usuario');

        // Soporta listas separadas por "|" o ",".
        $ids = array_values(array_filter(array_map(
            'intval',
            preg_split('/[|,]/', $id_perfil)
        )));

        // Camino robusto: si llega id_usuario, elimina relaciones perfil-usuario
        // del usuario actual sin depender del SP.
        if (! empty($id_usuario) && ! empty($ids)) {
            DB::table('seguridad_perfil_users')
                ->where('id_usuario', (int) $id_usuario)
                ->where(function ($query) use ($ids) {
                    $query->whereIn('id_perfil', $ids)
                        ->orWhereIn('id_perfil_users', $ids);
                })
                ->delete();
            $result = [];
        } else {
            // Compatibilidad con flujo legacy.
            $result = DB::select('CALL USP_SEGURIDAD_PERFIL_ELIMINAR_MULTIPLE(?)', [$id_perfil]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }
}