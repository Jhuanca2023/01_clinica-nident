<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EmpleadoResource;
use App\Models\Empleados;
use Illuminate\Support\Facades\DB;

class EmpleadosController extends Controller
{

    /**
     * @OA\Get(
     *     path="/empleados/obtener",
     *     tags={"Empleados"},
     *     summary="Obtener empleado por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Empleado encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from administracion_empleado where id_empleado = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/empleados/listar",
     *     tags={"Empleados"},
     *     summary="Listar todos los empleados",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de empleados", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_EMPLEADO_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }


    public function crearValidar($dni,$email_acceso,$email)
    {
        $result = DB::select('CALL USP_ADMINISTRACION_EMPLEADO_CREAR_VALIDAR(?,?,?)',[
            $dni,
            $email_acceso,
            $email,
        ]);
  
        return $result[0]->MensajeValidacion != "" ? $result[0]->MensajeValidacion : "";
    }

    public function actualizarValidar($dni,$email,$id_empleado)
    {
        $result = DB::select('CALL USP_ADMINISTRACION_EMPLEADO_ACTUALIZAR_VALIDAR(?,?,?)',[
            $dni,
            $email,
            $id_empleado
        ]);
  
        return $result[0]->MensajeValidacion != "" ? $result[0]->MensajeValidacion : "";
    }

    /**
     * @OA\Post(
     *     path="/empleados/crear",
     *     tags={"Empleados"},
     *     summary="Crear nuevo empleado",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","apellido","dni","email","Activo"},
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="apellido", type="string"),
     *         @OA\Property(property="dni", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="telefono", type="string"),
     *         @OA\Property(property="direccion", type="string"),
     *         @OA\Property(property="licencia", type="string"),
     *         @OA\Property(property="email_acceso", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Empleado creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        if($request->email_acceso =="EXAMPLE@GMAL.COM"){
            $email_acceso = "";
        }else{
            $email_acceso = $request->email_acceso;
        }

        $constValidarMensaje = $this->crearValidar($request->dni,$email_acceso,$request->email);

        if($constValidarMensaje == "")
        {

            DB::beginTransaction();
            try {
                // Log::channel('stderr')->info($request->Activo);
                $result = DB::select('CALL USP_ADMINISTRACION_EMPLEADO_CREAR(?,?,?,?,?,?,?,?,?,?,?)',[
                    $request->nombre,
                    $request->apellido,
                    $request->telefono,
                    $request->email,
                    $request->direccion,
        
                    $request->dni,
                    $request->licencia,
                    $request->Activo,

                    $email_acceso,
                    Hash::make($request->password),
                    auth()->user()->id,
                ]);

                DB::commit();
            } catch (\Exception $e) {
                Log::channel('stderr')->info($e);
                DB::rollback();
                // something went wrong
            }

            $constArrays = [0 => true, 1 => $constValidarMensaje];
         }else{
            $constArrays = [0 => false, 1 => $constValidarMensaje];
         }
        
         return response()->json([
            'result' => [
                'success' => $constArrays[0],
                'message' => $constArrays[1],
            ]
        ]);

    }

    /**
     * @OA\Put(
     *     path="/empleados/actualizar",
     *     tags={"Empleados"},
     *     summary="Actualizar empleado existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="apellido", type="string"),
     *         @OA\Property(property="dni", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Empleado actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        $id = $request->input('id');//id_empleado
        $constValidarMensaje = $this->actualizarValidar($request->dni,$request->email,$id);

        if($constValidarMensaje == "")
        {

            DB::beginTransaction();
            try {
                
                Empleados::where('id_empleado',$id)
                ->update([
                        'nombre'          => $request->nombre,
                        'apellido'        => $request->apellido,
                        'telefono'        => $request->telefono,
                        'email'           => $request->email,
                        'direccion'       => $request->direccion,
                        'dni'             => $request->dni,
                        'licencia'        => $request->licencia,

                        'updated_at'      => date("Y-m-d H:i:s"),
                        'Activo'          => $request->Activo,
                    ]);

                DB::commit();
            } catch (\Exception $e) {
                Log::channel('stderr')->info($e);
                DB::rollback();
                // something went wrong
            }

            $constArrays = [0 => true, 1 => $constValidarMensaje];
         }else{
            $constArrays = [0 => false, 1 => $constValidarMensaje];
         }
        
         return response()->json([
            'result' => [
                'success' => $constArrays[0],
                'message' => $constArrays[1],
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/empleados/eliminar",
     *     tags={"Empleados"},
     *     summary="Eliminar empleado",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Empleado eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = Empleados::where('id_empleado',$id)->delete();
 
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Put(
     *     path="/empleados/asignar_usuario",
     *     tags={"Empleados"},
     *     summary="Asignar un usuario de acceso a un empleado",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_empleado","id_usuario"},
     *         @OA\Property(property="id_empleado", type="integer", example=1),
     *         @OA\Property(property="id_usuario", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Usuario asignado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function asignar_usuario(Request $request) : JsonResponse
    {
        $id_usuario = $request->input('id_usuario');
        $id_empleado = $request->input('id_empleado');

        DB::beginTransaction();
        try {
            
            $result = Empleados::where('id_empleado',$id_empleado)
            ->update([
                    'id_usuario'      => $request->id_usuario,
                    'updated_at'      => date("Y-m-d H:i:s"),
                ]);

            DB::commit();
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            DB::rollback();
            // something went wrong
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }
}