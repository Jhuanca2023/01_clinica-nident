<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CategoriaPopularController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categoria_popular/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar todas las categorías populares",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de categorías populares", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(): JsonResponse
    {
        $result = DB::select('CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("LISTAR", NULL, NULL, NULL, NULL, NULL, NULL)');

        return response()->json([
            'success' => true,
            'message' => 'Lista de categorías populares',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/categoria_popular/obtener/{id}",
     *     tags={"Sitio Web"},
     *     summary="Obtener categoría popular por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Categoría encontrada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener($id): JsonResponse
    {
        $result = DB::select('CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("OBTENER", ?, NULL, NULL, NULL, NULL, NULL)', [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría obtenida',
            'result' => $result[0] ?? null
        ]);
    }

    /**
     * @OA\Post(
     *     path="/categoria_popular/crear",
     *     tags={"Sitio Web"},
     *     summary="Crear una nueva categoría popular (con imagen)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nombre_categoria","orden","Activo"},
     *                 @OA\Property(property="nombre_categoria", type="string", example="Electrónica"),
     *                 @OA\Property(property="imagen", type="string", format="binary"),
     *                 @OA\Property(property="orden", type="integer", example=1),
     *                 @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
     *                 @OA\Property(property="url_direccion", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Categoría creada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request): JsonResponse
    {
        try {
            $url_direccion = $request->input('url_direccion', null);
            // Validación mejorada con mensajes personalizados
            $validatedData = $request->validate([
                'nombre_categoria' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) {
                        if (strlen(trim($value)) < 3) {
                            $fail('El nombre de categoría debe tener al menos 3 caracteres.');
                        }
                    }
                ],
                
                'imagen' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,webp',
                    'max:2048',
                    'dimensions:min_width=100,min_height=100'
                ],
                'orden' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:999'
                ],

                'Activo' => [
                    'required',
                    'in:S,N'
                ]
            ], [
                'imagen.dimensions' => 'La imagen debe tener al menos 100x100 píxeles.',
                'orden.max' => 'El orden no puede ser mayor a 999.'
            ]);

            // Procesamiento de la imagen con nombre único y manejo seguro
            $imagenPath = null;
            if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
                $file       = $request->file("imagen");
                $ext        = $file->getClientOriginalExtension();
                $filename_  = time() . '.' . $ext;
                $imagenPath   = \App\Support\CmsImageStorage::store($file, 'categorias_populares');
            }

            // Ejecución del procedimiento almacenado con transacción
            $result = DB::transaction(function () use ($validatedData, $imagenPath, $url_direccion) {
                return DB::select(
                    'CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("INSERTAR", NULL, ?, ?, ?, ?,?)',
                    [
                        $validatedData['nombre_categoria'],
                        $imagenPath,
                        $validatedData['orden'],
                        $validatedData['Activo'],
                        $url_direccion,

                    ]
                );
            });

            // Respuesta estándar API
            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => $result[0], // Cambiado 'result' por 'data' (convención API REST)
                'image_url' => $imagenPath ? url($imagenPath) : null // URL completa opcional
            ], 201); // Código 201 para creación exitosa

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error al crear categoría: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null // Solo muestra error en desarrollo
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/categoria_popular/actualizar/{id}",
     *     tags={"Sitio Web"},
     *     summary="Actualizar categoría popular existente (soporta imagen)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nombre_categoria","orden","Activo"},
     *                 @OA\Property(property="nombre_categoria", type="string"),
     *                 @OA\Property(property="imagen", type="string", format="binary", description="Nueva imagen (opcional)"),
     *                 @OA\Property(property="orden", type="integer"),
     *                 @OA\Property(property="Activo", type="string", enum={"S","N"}),
     *                 @OA\Property(property="url_direccion", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Categoría actualizada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request, $id): JsonResponse
    {
        // 1. Validación básica de campos
        $validated = $request->validate([
            'nombre_categoria' => 'required|string|max:100',
            'imagen' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value === 'null' || $value === '') {
                        return; // Permite strings 'null' o vacíos
                    }

                    // Validación normal para archivos
                    $validator = Validator::make(
                        [$attribute => $value],
                        [$attribute => 'image|mimes:jpeg,png,jpg,webp|max:2048']
                    );

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                }
            ],
            'orden' => 'required|integer',
            'url_direccion' => 'nullable|url|max:2000',

            'Activo' => 'required|in:S,N'
        ]);

        // 2. Obtener categoría existente
        $categoriaActual = DB::select('CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("OBTENER", ?, NULL, NULL, NULL, NULL, NULL)', [$id])[0] ?? null;

        // 3. Manejo de la imagen según escenarios
        $imagenPath = $categoriaActual->url_imagen ?? null;

        if ($request->hasFile('imagen')) {
            if ($categoriaActual && $categoriaActual->url_imagen) {
                \App\Support\CmsImageStorage::deleteIfOwned($categoriaActual->url_imagen);
            }

            $file = $request->file('imagen');
            $imagenPath = \App\Support\CmsImageStorage::store($file, 'categorias_populares');
        }

        // 4. Actualizar registro
        $result = DB::select('CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("ACTUALIZAR", ?, ?, ?, ?, ?,?)', [
            $id,
            $validated['nombre_categoria'],
            $imagenPath,
            $validated['orden'],
            $validated['Activo'],
            $validated['url_direccion'],

        ]);


        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'result' => $result[0]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/categoria_popular/eliminar/{id}",
     *     tags={"Sitio Web"},
     *     summary="Eliminar categoría popular por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Categoría eliminada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar($id): JsonResponse
    {

        // Log::channel('stderr')->info("test" . json_encode($id));
        // Obtener la categoría para eliminar la imagen asociada
        $resultObtener = DB::select('CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("OBTENER", ?, NULL, NULL, NULL, NULL, NULL)', [$id]);

        if (!empty($resultObtener[0]->url_imagen ?? null)) {
            \App\Support\CmsImageStorage::deleteIfOwned($resultObtener[0]->url_imagen);
        }

        $result = DB::select('CALL USP_WEB_CATEGORIAS_POPULARES_GESTION("ELIMINAR", ?, NULL, NULL, NULL, NULL, NULL)', [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente',
            'result' => $result[0] ?? null
        ]);
    }
}
