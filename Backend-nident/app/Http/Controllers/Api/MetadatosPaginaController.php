<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetadatosPagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetadatosPaginaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/metadatospagina/obtener",
     *     tags={"Sitio Web"},
     *     summary="Obtener metadatos de página por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Metadatos encontrados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:metadatos_paginas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = MetadatosPagina::find($request->id);

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'data' => $result ? [$result] : [],
        ]);
    }

    /**
     * @OA\Parameter(name="solo", in="query", required=false, description="activos | inactivos | todos", @OA\Schema(type="string", enum={"activos","inactivos","todos"}))
     *
     * @OA\Get(
     *     path="/metadatospagina/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar metadatos (activos, inactivos o todos)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de metadatos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(Request $request)
    {
        $solo = $request->query('solo', 'activos');
        if (! in_array($solo, ['activos', 'inactivos', 'todos'], true)) {
            $solo = 'activos';
        }

        $q = MetadatosPagina::query()->orderBy('created_at', 'asc');

        if ($solo === 'activos') {
            $q->where('activo', 'S');
        } elseif ($solo === 'inactivos') {
            $q->where('activo', 'N');
        }

        return response()->json([
            'success' => true,
            'data' => $q->get(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/metadatospagina/crear",
     *     tags={"Sitio Web"},
     *     summary="Crear metadatos para una nueva clave de página",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_pagina' => 'required|string|max:191|regex:/^[a-z0-9_]+$/i|unique:metadatos_paginas,nombre_pagina',
            'titulo_pagina' => 'required|string|max:500',
            'descripcion_pagina' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $row = MetadatosPagina::create([
            'nombre_pagina' => strtolower($request->nombre_pagina),
            'titulo_pagina' => $request->titulo_pagina,
            'descripcion_pagina' => $request->descripcion_pagina,
            'activo' => 'S',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metadatos creados correctamente',
            'data' => $row,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/metadatospagina/actualizar",
     *     tags={"Sitio Web"},
     *     summary="Actualizar metadatos SEO de una página",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Metadatos actualizados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:metadatos_paginas,id',
            'titulo_pagina' => 'required|string',
            'descripcion_pagina' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $metadatosPagina = MetadatosPagina::find($request->id);
            $metadatosPagina->titulo_pagina = $request->titulo_pagina;
            $metadatosPagina->descripcion_pagina = $request->descripcion_pagina;
            $metadatosPagina->save();

            return response()->json([
                'success' => true,
                'message' => 'Metadatos de la página actualizados correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los metadatos de la página',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * “Eliminar” lógico: deja de usarse en la web (activo = N).
     *
     * @OA\Put(
     *     path="/metadatospagina/desactivar",
     *     tags={"Sitio Web"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Desactivado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function desactivar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:metadatos_paginas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $meta = MetadatosPagina::find($request->id);
        $meta->activo = 'N';
        $meta->save();

        return response()->json([
            'success' => true,
            'message' => 'Página desactivada (ya no se usa en el sitio web). Puedes restaurarla desde la pestaña Inactivas.',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/metadatospagina/restaurar",
     *     tags={"Sitio Web"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Restaurado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function restaurar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:metadatos_paginas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $meta = MetadatosPagina::find($request->id);
        $meta->activo = 'S';
        $meta->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro restaurado y activo en el sitio web.',
        ]);
    }
}
