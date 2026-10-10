<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use App\Models\Promociones;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PromocionController extends Controller
{

    /**
     * @OA\Get(
     *     path="/promocion/obtener",
     *     tags={"Sitio Web"},
     *     summary="Obtener promoción por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_promociones", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Promoción encontrada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener(Request $request): JsonResponse
    {
        $id_promociones = $request->id_promociones;
        $result = DB::select('select * from oferta_promociones where id_promociones = ?', [$id_promociones]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/promocion/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar todas las promociones",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de promociones", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => Promociones::orderBy('id_promociones', 'DESC')->get()
        ]);
    }



    /**
     * @OA\Post(
     *     path="/promocion/crear",
     *     tags={"Sitio Web"},
     *     summary="Crear nueva promoción",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","descripcion","start_date","end_date","Activo"},
     *         @OA\Property(property="nombre", type="string", example="Descuento de Verano"),
     *         @OA\Property(property="descripcion", type="string"),
     *         @OA\Property(property="start_date", type="string", format="date-time"),
     *         @OA\Property(property="end_date", type="string", format="date-time"),
     *         @OA\Property(property="id_membresia", type="integer"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Promoción creada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'id_membresia' => 'nullable|exists:oferta_promociones_membresia,id_membresia',
        ]);

        $result = Promociones::create([
            'nombre'                => $request->nombre,
            'descripcion'           => $request->descripcion,
            'start_date'            => $request->start_date,
            'end_date'              => $request->end_date,
            'id_membresia'          => $request->id_membresia,

            'created_at'            => date("Y-m-d H:i:s"),
            'updated_at'            => date("Y-m-d H:i:s"),
            'Activo'                => $request->Activo, //Es un String de "N" o "S"
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' => $result
        ]);
    }

    /**
     * @OA\Put(
     *     path="/promocion/actualizar",
     *     tags={"Sitio Web"},
     *     summary="Actualizar promoción existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_promociones","nombre","descripcion","start_date","end_date","Activo"},
     *         @OA\Property(property="id_promociones", type="integer", example=1),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="descripcion", type="string"),
     *         @OA\Property(property="start_date", type="string", format="date-time"),
     *         @OA\Property(property="end_date", type="string", format="date-time"),
     *         @OA\Property(property="id_membresia", type="integer"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Promoción actualizada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request): JsonResponse
    {
        $id_promociones = $request->input('id_promociones');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'id_membresia' => 'nullable|exists:oferta_promociones_membresia,id_membresia',
        ]);

        $result = Promociones::where('id_promociones', $id_promociones)
            ->update([
                'nombre'                => $request->nombre,
                'descripcion'           => $request->descripcion,
                'start_date'            => Carbon::parse($request->start_date)->format('Y-m-d H:i:s'),
                'end_date'              => Carbon::parse($request->end_date)->format('Y-m-d H:i:s'),
                'id_membresia'          => $request->id_membresia,

                'updated_at'            => date("Y-m-d H:i:s"),
                'Activo'                => $request->Activo,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/promocion/eliminar",
     *     tags={"Sitio Web"},
     *     summary="Eliminar promoción por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_promociones"},
     *         @OA\Property(property="id_promociones", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Promoción eliminada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request): JsonResponse
    {
        $id_promociones = $request->input('id_promociones');
        $result = Promociones::where('id_promociones', $id_promociones)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/promocion/listar_membresias",
     *     tags={"Sitio Web"},
     *     summary="Listar tipos de membresías disponibles para promociones",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de membresías", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_membresias()
    {
         $result = DB::select('select * from oferta_promociones_membresia ORDER BY id_membresia DESC');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }
}
