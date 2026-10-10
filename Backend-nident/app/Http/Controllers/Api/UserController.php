<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CmsImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Seguridad\AdminPrincipalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    /**
     * @OA\Post(
     *     path="/usuario/obtener",
     *     tags={"Usuarios"},
     *     summary="Obtener usuario por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        // $result = User::where('id',$id)->get();
        $result = DB::select('select id,name as username,email,created_at,Activo,avatar,google_id from users where id = ?', [$id]);
       if($result[0]->avatar){
           $file = Storage::get($result[0]->avatar);
           $fileBase64 = base64_encode($file);
       }else{
            $fileBase64=null;
       }
      
       // Log::channel('stderr')->info($contents);

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => array_merge($result,['fileBase64' => $fileBase64]) 
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/usuario/listar",
     *     tags={"Usuarios"},
     *     summary="Listar todos los usuarios",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de usuarios",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse")
     *     )
     * )
     */
    public function listar()
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => User::orderBy('id', 'DESC')->get()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/usuario/crear",
     *     tags={"Usuarios"},
     *     summary="Crear nuevo usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","Activo"},
     *             @OA\Property(property="name", type="string", example="María García"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@valento.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario creado",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
		$result = User::create([
            'name' => $request->name,
            'email' => $request->email,

            'password' => Hash::make($request->password),
            'id_owner' => auth()->user()->id,
            'Activo' => $request->Activo,
            
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  new UserResource($result)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/usuario/actualizar",
     *     tags={"Usuarios"},
     *     summary="Actualizar usuario (soporta multipart con avatar)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"id"},
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="María García"),
     *                 @OA\Property(property="email", type="string", format="email", example="maria@valento.com"),
     *                 @OA\Property(property="password", type="string", format="password"),
     *                 @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Foto de perfil (opcional)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario actualizado",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        
        // $request->validate([
        //     'image' => 'required|image|mimes:jpg,png,jpeg|max:3048'
        // ]);
 
        // Log::channel('stderr')->info($request->password);
        

        $filePath = "";
        if($request->hasFile('image')){
            if (!empty($request->avatar)) {
                CmsImageStorage::deleteIfOwned($request->avatar);
            }

            $filePath = CmsImageStorage::store($request->file('image'), 'avatar');
        }

        $id = $request->input('id');
        $userRow = User::find($id);
        $principal = app(AdminPrincipalService::class);

        if ($userRow && $principal->esPrincipal($userRow)) {
            if ($request->email && strcasecmp($request->email, $userRow->email) !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puede cambiar el correo del administrador principal del sistema.',
                ], 422);
            }
            if ($request->Activo === 'N') {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe transferir el cargo de administrador principal antes de desactivar esta cuenta.',
                    'requires_transfer' => true,
                ], 422);
            }
        }

        $cuentaGoogle = $userRow && !empty($userRow->google_id);

        if ($cuentaGoogle) {
            $updateData = ['Activo' => $request->Activo];
            if ($request->password != '' && $request->password != 'undefined') {
                $updateData['password'] = Hash::make($request->password);
            }
            if ($filePath != '') {
                if (!empty($userRow->avatar)) {
                    CmsImageStorage::deleteIfOwned($userRow->avatar);
                }
                $updateData['avatar'] = $filePath;
            }
            $result = User::where('id', $id)->update($updateData);
        } elseif ($request->password != '' && $request->password != 'undefined') {
            if ($filePath != '') {
                $result = User::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'avatar' => $filePath,
                        'Activo' => $request->Activo,
                    ]);
            } else {
                $result = User::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'Activo' => $request->Activo,
                    ]);
            }
        } else {
            if ($filePath != '') {
                $result = User::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'avatar' => $filePath,
                        'Activo' => $request->Activo,
                    ]);
            } else {
                $result = User::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'Activo' => $request->Activo,
                    ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result' => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/usuario/eliminar",
     *     tags={"Usuarios"},
     *     summary="Eliminar usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario eliminado",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = (int) $request->input('id');
        $principal = app(AdminPrincipalService::class);
        $check = $principal->puedeEliminar($id);

        if (!$check['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $check['message'],
                'requires_transfer' => $check['requires_transfer'] ?? false,
                'candidatos_disponibles' => $check['candidatos_disponibles'] ?? false,
            ], 422);
        }

        $result = User::where('id', $id)->delete();

        try {
            $principal->asegurarExistePrincipal();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    public function adminPrincipal(): JsonResponse
    {
        $p = app(AdminPrincipalService::class)->obtenerPrincipal();

        return response()->json([
            'success' => true,
            'result' => $p ? [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
            ] : null,
        ]);
    }

    public function transferirAdminPrincipal(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_nuevo' => 'required|integer|exists:users,id',
        ]);

        try {
            $nuevo = app(AdminPrincipalService::class)->transferirPrincipal(
                (int) $request->id_usuario_nuevo,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Administrador principal actualizado.',
                'result' => [
                    'id' => $nuevo->id,
                    'name' => $nuevo->name,
                    'email' => $nuevo->email,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/usuario/listar_repartidores",
     *     tags={"Usuarios"},
     *     summary="Listar solo usuarios con rol REPARTIDOR",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de repartidores")
     * )
     */
    public function listar_repartidores(): JsonResponse
    {
        $result = DB::select("
            SELECT DISTINCT u.id, u.name, u.email
            FROM users u
            INNER JOIN seguridad_perfil_users spu ON spu.id_usuario = u.id
            INNER JOIN seguridad_roles_perfil srp ON srp.id_perfil = spu.id_perfil
            INNER JOIN seguridad_roles sr ON sr.id_roles = srp.id_roles
            WHERE sr.nombre = 'REPARTIDOR'
            ORDER BY u.name ASC
        ");

        return response()->json([
            'success' => true,
            'message' => 'Listar repartidores',
            'result'  => $result,
        ]);
    }
}