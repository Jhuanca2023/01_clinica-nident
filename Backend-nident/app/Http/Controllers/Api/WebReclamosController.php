<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ContactoValento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebReclamosController extends Controller
{
    /**
     * Listar todos los reclamos con filtros opcionales.
     */
    /**
     * @OA\Get(
     *     path="/web_reclamos/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar reclamos registrados",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="estado", in="query", required=false, @OA\Schema(type="string", enum={"pendiente","en_proceso","resuelto","rechazado"})),
     *     @OA\Parameter(name="tipo_solicitud", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de reclamos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(Request $request): JsonResponse
    {
        $estado         = $request->estado ?? null;
        $tipo_solicitud = $request->tipo_solicitud ?? null;

        $query = DB::table('web_reclamos')
            ->select(
                'id_web_reclamos',
                'tipo_documento',
                'numero_documento',
                'razon_social',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                DB::raw("CONCAT(nombres, ' ', apellido_paterno, IFNULL(CONCAT(' ', apellido_materno), '')) AS nombre_completo"),
                'telefono',
                'email',
                'departamento',
                'provincia',
                'distrito',
                'direccion',
                'tipo_solicitud',
                'estado',
                'ip_cliente',
                'fecha_registro',
                'fecha_actualizacion',
                'usuario_atencion',
                'fecha_atencion',
                'comentario_atencion',
                'created_at',
                'updated_at'
            )
            ->orderBy('fecha_registro', 'desc');

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($tipo_solicitud) {
            $query->where('tipo_solicitud', $tipo_solicitud);
        }

        $result = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $result
        ]);
    }

    /**
     * Obtener un reclamo por ID (incluye archivos adjuntos).
     */
    /**
     * @OA\Get(
     *     path="/web_reclamos/obtener",
     *     tags={"Sitio Web"},
     *     summary="Obtener un reclamo por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Reclamo encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener(Request $request): JsonResponse
    {
        $id = $request->id;

        $result = DB::table('web_reclamos')
            ->where('id_web_reclamos', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Obtener registro',
            'result'  => $result ? [$result] : []
        ]);
    }

    /**
     * Actualizar estado y gestión del reclamo (solo campos de atención).
     */
    /**
     * @OA\Put(
     *     path="/web_reclamos/actualizar",
     *     tags={"Sitio Web"},
     *     summary="Actualizar estado y atención de un reclamo",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_web_reclamos","estado"},
     *         @OA\Property(property="id_web_reclamos", type="integer", example=1),
     *         @OA\Property(property="estado", type="string", enum={"pendiente","en_proceso","resuelto","rechazado"}),
     *         @OA\Property(property="comentario_atencion", type="string"),
     *         @OA\Property(property="usuario_atencion", type="string")
     *     )),
     *     @OA\Response(response=200, description="Reclamo actualizado y cliente notificado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_web_reclamos'    => 'required|integer',
            'estado'             => 'required|in:pendiente,en_proceso,resuelto,rechazado',
            'comentario_atencion'=> 'nullable|string|max:2000',
            'usuario_atencion'   => 'nullable|string|max:50',
        ]);

        $id = $request->id_web_reclamos;

        // 1. Obtener datos del cliente antes de actualizar
        $reclamo = DB::table('web_reclamos')->where('id_web_reclamos', $id)->first();
        if (!$reclamo) {
            return response()->json(['success' => false, 'message' => 'Reclamo no encontrado'], 404);
        }

        // 2. Ejecutar actualización en BD
        DB::table('web_reclamos')
            ->where('id_web_reclamos', $id)
            ->update([
                'estado'              => $request->estado,
                'comentario_atencion' => $request->comentario_atencion,
                'usuario_atencion'    => $request->usuario_atencion,
                'fecha_atencion'      => Carbon::now(),
                'updated_at'          => Carbon::now(),
            ]);

        // 3. ENVIAR NOTIFICACIÓN AL CLIENTE
        try {
            $datosCorreo = [
                'nombres'            => $reclamo->nombres . ' ' . $reclamo->apellido_paterno,
                'numero_reclamo'     => 'REC-' . $id,
                'tipo_solicitud'     => $reclamo->tipo_solicitud,
                'estado'             => $request->estado,
                'comentario_atencion'=> $request->comentario_atencion,
            ];

            Mail::send('email.actualizacion_estado_reclamo', $datosCorreo, function ($message) use ($reclamo, $id) {
                $message->from(ContactoValento::ventasEmail(), 'Valento');
                $message->to($reclamo->email)
                        ->subject('Novedades en tu reclamos (REC-' . $id . ') - Valento');
            });
            Log::info('EMAIL_ACTUALIZACION_EXITO: Correo de estado enviado a ' . $reclamo->email . ' para REC-' . $id);
        } catch (\Exception $e) {
            Log::error('Error al enviar email de actualización: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado y cliente notificado',
            'result'  => 1
        ]);
    }

    /**
     * Eliminar un reclamo por ID.
     */
    /**
     * @OA\Delete(
     *     path="/web_reclamos/eliminar",
     *     tags={"Sitio Web"},
     *     summary="Eliminar un reclamo por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Reclamo eliminado correctamente", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request): JsonResponse
    {
        try {
            $id = $request->id;

            if (!$id) {
                return response()->json([
                    'success' => false, 
                    'message' => 'ID no proporcionado'
                ], 400);
            }

            // Verificar si existe
            $reclamo = DB::table('web_reclamos')->where('id_web_reclamos', $id)->first();
            
            if (!$reclamo) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Reclamo no encontrado'
                ], 404);
            }

            // Eliminar registro
            DB::table('web_reclamos')->where('id_web_reclamos', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'result'  => 1
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar reclamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }
}
