<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\RolesResource;
use App\Models\Roles;
use App\Models\RolesMenu;
use Illuminate\Support\Facades\DB;
use App\Support\UbigeoQuery;

class UbigeoController extends Controller
{

    /**
     * @OA\Get(
     *     path="/ubigeo/listar_departamentos",
     *     tags={"Ubigeo"},
     *     summary="Listar departamentos del Perú",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de departamentos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_departamentos()
    {
        $result = DB::table('ubigeo_peru_departments')->select('id','name')->get();
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/ubigeo/obtener_provicias",
     *     tags={"Ubigeo"},
     *     summary="Listar provincias por departamento",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_departamento", in="query", required=true, @OA\Schema(type="string"), example="15"),
     *     @OA\Response(response=200, description="Lista de provincias", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function obtener_provicias(Request $request) : JsonResponse
    {
        $department_id = $request->id_departamento;
        $result = UbigeoQuery::provincias($department_id);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/ubigeo/obtener_distritos",
     *     tags={"Ubigeo"},
     *     summary="Listar distritos por provincia",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_departamento", in="query", required=true, @OA\Schema(type="string"), example="15"),
     *     @OA\Parameter(name="id_distrito", in="query", required=true, @OA\Schema(type="string"), description="ID de la provincia", example="1501"),
     *     @OA\Response(response=200, description="Lista de distritos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function obtener_distritos(Request $request) : JsonResponse
    {
        $department_id = $request->id_departamento;
        $province_id = $request->id_distrito;

        $result = UbigeoQuery::distritos($department_id, $province_id);

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
      
}