<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class BannerPopularController extends Controller
{
    /**
     * @OA\Get(
     *     path="/banner_popular/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar todos los banners populares",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de banners", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(): JsonResponse
    {
        $result = DB::select('CALL USP_WEB_BANNERS_POPULARES_GESTION("LISTAR", NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)');

        return response()->json([
            'success' => true,
            'message' => 'Lista de banners populares',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/banner_popular/obtener/{id}",
     *     tags={"Sitio Web"},
     *     summary="Obtener banner popular por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Banner encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener($id): JsonResponse
    {
        $result = DB::select('CALL USP_WEB_BANNERS_POPULARES_GESTION("OBTENER", ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)', [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Banner obtenido',
            'result' => $result[0] ?? null
        ]);
    }


    /**
     * @OA\Post(
     *     path="/banner_popular/crear",
     *     tags={"Sitio Web"},
     *     summary="Crear un nuevo banner popular (con imagen)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"titulo_principal","imagen","orden","Activo"},
     *                 @OA\Property(property="titulo_principal", type="string", example="Nuevos Arribos"),
     *                 @OA\Property(property="titulo_secundario", type="string"),
     *                 @OA\Property(property="url_direccion", type="string"),
     *                 @OA\Property(property="texto_descuento", type="string"),
     *                 @OA\Property(property="precio_desde", type="number"),
     *                 @OA\Property(property="imagen", type="string", format="binary"),
     *                 @OA\Property(property="orden", type="integer", example=1),
     *                 @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
     *                 @OA\Property(property="estilo_css", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Banner creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request): JsonResponse
    {
        
 
        try {
            // Validación mejorada con mensajes personalizados
            $validatedData = $request->validate([
                'titulo_principal' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) {
                        if (strlen(trim($value)) < 3) {
                            $fail('El título principal debe tener al menos 3 caracteres.');
                        }
                    }
                ],
                'titulo_secundario' => [
                    'nullable',
                    'string',
                    'max:100'
                ],
                 'url_direccion' => [
                    'nullable',
                    'url',
                    'max:2000'
                ],
                'texto_descuento' => [
                    'nullable',
                    'string',
                    'max:50'
                ],
                'precio_desde' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999.99'
                ],
                'imagen' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg',
                    'max:2048',
                    // 'dimensions:min_width=300,min_height=150'
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
                ], 
                'estilo_css' => [
                    'nullable',
                    'string'
                ],
            ], [
                // 'imagen.dimensions' => 'La imagen debe tener al menos 300px de ancho y 150px de alto.',
                'precio_desde.max' => 'El precio no puede ser mayor a 999,999.99',
                'orden.max' => 'El orden no puede ser mayor a 999.'
            ]);
            // Procesamiento de la imagen con nombre único y manejo seguro
            $imagenPath = null;

            $file       = $request->file("imagen");
            $ext        = $file->getClientOriginalExtension();
            $filename_  = time() . '.' . $ext;
            $imagenPath   = \App\Support\CmsImageStorage::store($file, 'banners_populares');

            // Ejecución en transacción para integridad de datos
            $result = DB::transaction(function () use ($validatedData, $imagenPath) {
                return DB::select(
                    'CALL USP_WEB_BANNERS_POPULARES_GESTION("INSERTAR", NULL, ?, ?, ?, ?, ?, ?, ?,?,?)',
                    [
                        $validatedData['titulo_principal'],
                        $validatedData['titulo_secundario'] ?? null,
                        $validatedData['texto_descuento'] ?? null,
                        $validatedData['precio_desde'] ?? null,
                        $imagenPath,
                        $validatedData['orden'],
                        $validatedData['Activo'],
                        $validatedData['estilo_css'],
                        $validatedData['url_direccion'] ?? null
                    ]
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Banner creado exitosamente',
                'data' => $result[0],
                'image_url' => url($imagenPath) // URL completa
            ], 201); // Código HTTP 201 para creación exitosa

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Limpiar: eliminar imagen si falló después de subirla
            if (isset($imagenPath)) {
                \App\Support\CmsImageStorage::deleteIfOwned($imagenPath);
            }

            Log::error('Error al crear banner: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/banner_popular/actualizar/{id}",
     *     tags={"Sitio Web"},
     *     summary="Actualizar banner popular existente (soporta imagen)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"titulo_principal","orden","Activo"},
     *                 @OA\Property(property="titulo_principal", type="string"),
     *                 @OA\Property(property="titulo_secundario", type="string"),
     *                 @OA\Property(property="url_direccion", type="string"),
     *                 @OA\Property(property="texto_descuento", type="string"),
     *                 @OA\Property(property="precio_desde", type="number"),
     *                 @OA\Property(property="imagen", type="string", format="binary", description="Nueva imagen (opcional)"),
     *                 @OA\Property(property="orden", type="integer"),
     *                 @OA\Property(property="Activo", type="string", enum={"S","N"}),
     *                 @OA\Property(property="estilo_css", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Banner actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request, $id): JsonResponse
    {
        // 1. Validación básica de campos
        $validated = $request->validate([
            'titulo_principal' => 'required|string|max:100',
            'titulo_secundario' => 'nullable|string|max:100',
            'texto_descuento' => 'nullable|string|max:50',
            'precio_desde' => 'nullable|numeric',
            'url_direccion' => 'nullable|url|max:2000', // Si usas TEXT/LONGTEXT

            'imagen' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value === 'null' || $value === '') {
                        return; // Permite strings 'null' o vacíos
                    }

                    // Validación normal para archivos
                    $validator = Validator::make(
                        [$attribute => $value],
                        [$attribute => 'image|mimes:jpeg,png,jpg|max:2048']
                    );

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                }
            ],
            'orden' => 'required|integer',
            'Activo' => 'required|in:S,N',
            'estilo_css' => 'required|string|max:100',

        ]);

        // 2. Obtener categoría existente
        $categoriaActual = DB::select('CALL USP_WEB_BANNERS_POPULARES_GESTION("OBTENER", ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)', [$id])[0] ?? null;

        // 3. Manejo de la imagen según escenarios
        $imagenPath = $categoriaActual->url_imagen ?? null;

        if ($request->hasFile('imagen')) {
            if ($categoriaActual && $categoriaActual->url_imagen) {
                \App\Support\CmsImageStorage::deleteIfOwned($categoriaActual->url_imagen);
            }

            $file = $request->file('imagen');
            $imagenPath = \App\Support\CmsImageStorage::store($file, 'banners_populares');
        }

        // 4. Actualizar registro
        $result = DB::select('CALL USP_WEB_BANNERS_POPULARES_GESTION("ACTUALIZAR", ?, ?, ?, ?, ?, ?, ?, ?,?,?)', [
            $id,
            $validated['titulo_principal'],
            $validated['titulo_secundario'],
            $validated['texto_descuento'],

            $validated['precio_desde'],
            $imagenPath,
            $validated['orden'],
            $validated['Activo'],
            $validated['estilo_css'],
            $validated['url_direccion'] ?? null
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Banner actualizada exitosamente',
            'result' => $result[0]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/banner_popular/eliminar/{id}",
     *     tags={"Sitio Web"},
     *     summary="Eliminar banner popular por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Banner eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar($id): JsonResponse
    {
        // Obtener el banner para eliminar la imagen asociada
        $banner = DB::select('CALL USP_WEB_BANNERS_POPULARES_GESTION("OBTENER", ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)', [$id]);

        if (isset($banner[0])) {
            \App\Support\CmsImageStorage::deleteIfOwned($banner[0]->url_imagen ?? null);
        }

        $result = DB::select('CALL USP_WEB_BANNERS_POPULARES_GESTION("ELIMINAR", ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)', [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Banner eliminado exitosamente',
            'result' => $result[0] ?? null
        ]);
    }
}
