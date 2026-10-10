<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ClienteResource;
use App\Models\Producto;
use App\Models\ProductoFoto;
use App\Models\ProductoFichaTecnica;
use App\Models\WebHomeColeccion;
use App\Support\ProductoEcommerceCatalog;
use App\Support\ProductoImagePath;
use App\Exports\ProductosExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Get(
 *     path="/producto/obtener",
 *     tags={"Productos"},
 *     summary="Obtener producto por ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=5),
 *     @OA\Response(response=200, description="Producto encontrado",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/producto/listar",
 *     tags={"Productos"},
 *     summary="Listar todos los productos activos",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Lista de productos",
 *         @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/producto/listar_filtro",
 *     tags={"Productos"},
 *     summary="Listar productos filtrados por tipo",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_producto_tipo", in="query", @OA\Schema(type="integer"), example=1),
 *     @OA\Response(response=200, description="Lista filtrada",
 *         @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/producto/listar_por_fechas",
 *     tags={"Productos"},
 *     summary="Listar productos por rango de fechas de creación",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="fecha_inicio", in="query", @OA\Schema(type="string", format="date"), example="2024-01-01"),
 *     @OA\Parameter(name="fecha_fin", in="query", @OA\Schema(type="string", format="date"), example="2024-12-31"),
 *     @OA\Response(response=200, description="Productos encontrados",
 *         @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/producto/exportar",
 *     tags={"Productos"},
 *     summary="Exportar productos a Excel",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="fecha_inicio", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="fecha_fin", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="Archivo Excel en base64",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="result", type="object",
 *                 @OA\Property(property="fileName", type="string", example="productos_2024-01-01.xlsx"),
 *                 @OA\Property(property="fileBase64", type="string", description="Excel codificado en base64")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=500, description="Error al exportar")
 * )
 *
 * @OA\Post(
 *     path="/producto/crear",
 *     tags={"Productos"},
 *     summary="Crear producto (soporta multipart con imagen)",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\MediaType(mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"nombre","precio","stock","id_producto_categoria","Activo"},
 *                 @OA\Property(property="nombre", type="string", example="Maleta de Viaje"),
 *                 @OA\Property(property="descripcion", type="string"),
 *                 @OA\Property(property="precio", type="number", example=99.90),
 *                 @OA\Property(property="precio_old", type="number", example=129.90),
 *                 @OA\Property(property="stock", type="integer", example=50),
 *                 @OA\Property(property="id_producto_categoria", type="integer", example=1),
 *                 @OA\Property(property="id_producto_categoria_sub", type="integer"),
 *                 @OA\Property(property="id_producto_tipo", type="integer"),
 *                 @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
 *                 @OA\Property(property="peso_kilogramo", type="number"),
 *                 @OA\Property(property="precio_mayorista", type="number"),
 *                 @OA\Property(property="precio_mayorista_old", type="number"),
 *                 @OA\Property(property="precio_yape", type="number"),
 *                 @OA\Property(property="numero_estrellas", type="integer"),
 *                 @OA\Property(property="ratings_enabled", type="boolean", example=false),
 *                 @OA\Property(property="admin_rating", type="number"),
 *                 @OA\Property(property="meta_titulo_producto", type="string"),
 *                 @OA\Property(property="meta_descripcion_producto", type="string"),
 *                 @OA\Property(property="pro_imagen", type="string", format="binary", description="Imagen principal (max 15MB)")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Producto creado",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(response=422, description="Imagen inválida")
 * )
 *
 * @OA\Post(
 *     path="/producto/actualizar",
 *     tags={"Productos"},
 *     summary="Actualizar producto principal",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\MediaType(mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"id_producto"},
 *                 @OA\Property(property="id_producto", type="integer", example=5),
 *                 @OA\Property(property="nombre", type="string", example="Maleta de Viaje Premium"),
 *                 @OA\Property(property="precio", type="number", example=89.90),
 *                 @OA\Property(property="stock", type="integer", example=30),
 *                 @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
 *                 @OA\Property(property="pro_imagen", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Producto actualizado")
 * )
 *
 * @OA\Delete(
 *     path="/producto/eliminar",
 *     tags={"Productos"},
 *     summary="Eliminar producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id"},
 *             @OA\Property(property="id", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Producto eliminado")
 * )
 *
 * @OA\Get(
 *     path="/producto/listar_imagen",
 *     tags={"Productos"},
 *     summary="Listar sub-imágenes de un producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_producto", in="query", required=true, @OA\Schema(type="integer"), example=5),
 *     @OA\Response(response=200, description="Lista de imágenes")
 * )
 *
 * @OA\Post(
 *     path="/producto/crear_imagen",
 *     tags={"Productos"},
 *     summary="Agregar sub-imagen (sub-producto) a un producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\MediaType(mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"id_producto","Activo"},
 *                 @OA\Property(property="id_producto", type="integer", example=5),
 *                 @OA\Property(property="titulo", type="string"),
 *                 @OA\Property(property="precio", type="number"),
 *                 @OA\Property(property="descripcion", type="string"),
 *                 @OA\Property(property="stock", type="integer"),
 *                 @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
 *                 @OA\Property(property="image", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Sub-imagen creada")
 * )
 *
 * @OA\Delete(
 *     path="/producto/eliminar_imagen",
 *     tags={"Productos"},
 *     summary="Eliminar sub-imagen de producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_producto_imagen"},
 *             @OA\Property(property="id_producto_imagen", type="integer", example=10)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Imagen eliminada")
 * )
 *
 * @OA\Post(
 *     path="/producto/actualizar_ficha_tecnica",
 *     tags={"Productos"},
 *     summary="Crear o actualizar ficha técnica del producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_producto"},
 *             @OA\Property(property="id_producto", type="integer", example=5),
 *             @OA\Property(property="titulo1", type="string", example="Descripción"),
 *             @OA\Property(property="titulo2", type="string", example="Especificaciones"),
 *             @OA\Property(property="editorValue1", type="string"),
 *             @OA\Property(property="editorValue2", type="object"),
 *             @OA\Property(property="Activo", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Ficha técnica guardada"),
 *     @OA\Response(response=500, description="Error interno")
 * )
 *
 * @OA\Get(
 *     path="/producto/obtener_ficha_tecnica",
 *     tags={"Productos"},
 *     summary="Obtener ficha técnica del producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_producto", in="query", required=true, @OA\Schema(type="integer"), example=5),
 *     @OA\Response(response=200, description="Ficha técnica")
 * )
 *
 * @OA\Get(
 *     path="/producto/listar_fotos",
 *     tags={"Productos"},
 *     summary="Listar galería de fotos del producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id_producto", in="query", required=true, @OA\Schema(type="integer"), example=5),
 *     @OA\Response(response=200, description="Galería de fotos")
 * )
 *
 * @OA\Post(
 *     path="/producto/crear_fotos",
 *     tags={"Productos"},
 *     summary="Agregar foto a la galería del producto",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\MediaType(mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"id_producto"},
 *                 @OA\Property(property="id_producto", type="integer", example=5),
 *                 @OA\Property(property="image", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Foto agregada")
 * )
 *
 * @OA\Delete(
 *     path="/producto/eliminar_fotos",
 *     tags={"Productos"},
 *     summary="Eliminar foto de galería",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             required={"id_producto_foto"},
 *             @OA\Property(property="id_producto_foto", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Foto eliminada")
 * )
 */
class ProductoController extends Controller
{
    private const BADGE_ETIQUETAS = ['NUEVO', 'POPULAR', 'LIMITADO'];

    /**
     * FormData desde JS puede enviar el string "undefined"; MySQL rechaza eso en columnas DECIMAL.
     */
    private function nullableDecimal($value): ?float
    {
        if ($value === null || $value === '' || $value === 'null' || $value === 'undefined') {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || $value === 'null' || $value === 'undefined') {
                return null;
            }
        }
        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function nullableInt($value): ?int
    {
        $f = $this->nullableDecimal($value);

        return $f === null ? null : (int) round($f);
    }

    /**
     * Solo límites técnicos: tipo imagen y tamaño máximo. Las medidas son recomendación en el front (modal).
     *
     * @return string|null null si es válida, mensaje de error en español si no
     */
    private function isEmptyCode(?string $val): bool
    {
        return $val === null || $val === '' || $val === 'null' || $val === 'undefined';
    }

    /** Categoría/subcategoría: vacío → null (no forzar una categoría por defecto). */
    private function nullableCategoriaId($value): ?int
    {
        if ($value === null || $value === '' || $value === 'null' || $value === 'undefined' || $value === '0' || $value === 0) {
            return null;
        }

        return (int) $value;
    }

    private function isEtiquetaBadge(?string $val): bool
    {
        return in_array(strtoupper(trim((string) $val)), self::BADGE_ETIQUETAS, true);
    }

    /** Separa etiqueta visual (NUEVO/POPULAR/LIMITADO) del código de barra real. */
    private function splitEtiquetaYBarra(?string $codigoBarra, ?string $etiquetaBadge): array
    {
        $badge   = $etiquetaBadge;
        $barcode = $codigoBarra;

        if ($this->isEmptyCode($badge) && ! $this->isEmptyCode($codigoBarra) && $this->isEtiquetaBadge($codigoBarra)) {
            $badge   = strtoupper(trim((string) $codigoBarra));
            $barcode = null;
        }

        if (! $this->isEmptyCode($badge) && ! $this->isEtiquetaBadge($badge)) {
            $badge = null;
        }

        return [
            'etiqueta_badge' => $this->isEmptyCode($badge) ? null : strtoupper(trim((string) $badge)),
            'codigo_barra'   => $this->isEmptyCode($barcode) ? null : trim((string) $barcode),
        ];
    }

    private function applyEtiquetaYBarraFields(Request $request, array &$datos): void
    {
        $split = $this->splitEtiquetaYBarra($request->input('codigo_barra'), $request->input('etiqueta_badge'));
        $datos['codigo_barra'] = $split['codigo_barra'];
        if (Schema::hasColumn('administracion_producto', 'etiqueta_badge')) {
            $datos['etiqueta_badge'] = $split['etiqueta_badge'];
        }
    }

    private function validateProImagenBasico(?UploadedFile $file): ?string
    {
        if (!$file || !$file->isValid()) {
            return 'Archivo de imagen no válido.';
        }
        $maxBytes = 15 * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return 'La imagen no debe superar 15 MB.';
        }
        $mime = strtolower((string) $file->getMimeType());
        if (! str_starts_with($mime, 'image/')) {
            return 'Debe ser un archivo de imagen (JPG, PNG, WebP, GIF, etc.).';
        }

        return null;
    }

    public function verificarCodigo(Request $request): JsonResponse
    {
        $campo     = $request->input('campo');
        $valor     = $request->input('valor');
        $excluirId = $request->input('id_producto');

        $camposPermitidos = ['codigo_producto_new', 'codigo_barra'];
        if (!in_array($campo, $camposPermitidos) || $this->isEmptyCode($valor)) {
            return response()->json(['duplicado' => false]);
        }

        $query = Producto::where($campo, $valor);
        if ($excluirId) {
            $query->where('id_producto', '!=', (int) $excluirId);
        }

        return response()->json(['duplicado' => $query->exists()]);
    }

    public function obtener(Request $request): JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from administracion_producto where id_producto = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    public function listar()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_PRODUCTO_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    public function listar_filtro(Request $request)
    {
        $query = Producto::query()
            ->from('administracion_producto')
            ->leftJoin('administracion_producto_categoria as cat', 'administracion_producto.id_producto_categoria', '=', 'cat.id_producto_categoria')
            ->leftJoin('administracion_producto_categoria_sub as sub', 'administracion_producto.id_producto_categoria_sub', '=', 'sub.id_producto_categoria_sub')
            ->select(
                'administracion_producto.*',
                'cat.nombre as nombre_categoria',
                'sub.nombre as nombre_subcategoria'
            );

        $tipo = $request->input('id_producto_tipo');
        if ($tipo !== null && $tipo !== '' && $tipo !== '0' && $tipo !== 0 && $tipo !== 'null') {
            $query->where('administracion_producto.id_producto_tipo', $tipo);
        }

        $nombre = trim((string) $request->input('nombre', $request->input('q', '')));
        if ($nombre !== '') {
            $query->where(function ($q) use ($nombre) {
                $q->where('administracion_producto.nombre', 'like', "%{$nombre}%")
                    ->orWhere('administracion_producto.descripcion', 'like', "%{$nombre}%");
            });
        }

        $codigo = trim((string) $request->input('codigo_producto', ''));
        if ($codigo !== '') {
            $query->where(function ($q) use ($codigo) {
                $q->where('administracion_producto.codigo_producto', 'like', "%{$codigo}%")
                    ->orWhere('administracion_producto.codigo_producto_new', 'like', "%{$codigo}%")
                    ->orWhere('administracion_producto.codigo_barra', 'like', "%{$codigo}%");
            });
        }

        $categoria = $request->input('id_producto_categoria');
        if ($categoria === 'none' || $categoria === 'sin') {
            $query->where(function ($q) {
                $q->whereNull('administracion_producto.id_producto_categoria')
                    ->orWhere('administracion_producto.id_producto_categoria', 0);
            });
        } elseif ($categoria !== null && $categoria !== '' && $categoria !== '0') {
            $query->where('administracion_producto.id_producto_categoria', $categoria);
        }

        $subcategoria = $request->input('id_producto_categoria_sub');
        if ($subcategoria !== null && $subcategoria !== '' && $subcategoria !== '0') {
            $query->where('administracion_producto.id_producto_categoria_sub', $subcategoria);
        }

        // Filtrar por bloque / submódulo de ofertas (modal vincular productos)
        $idPromoItem = (int) $request->input('id_promo_item', 0);
        $idPromoBloque = (int) $request->input('id_promo_bloque', 0);
        if ($idPromoItem > 0 || $idPromoBloque > 0) {
            $idsPromo = [];
            if ($idPromoItem > 0 && Schema::hasTable('web_promo_item_producto')) {
                $idsPromo = array_merge(
                    $idsPromo,
                    DB::table('web_promo_item_producto')
                        ->where('id_promo_item', $idPromoItem)
                        ->pluck('id_producto')
                        ->map(fn ($id) => (int) $id)
                        ->all()
                );
                $legacy = DB::table('web_promo_item')
                    ->where('id_promo_item', $idPromoItem)
                    ->whereNotNull('id_producto')
                    ->where('id_producto', '>', 0)
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $idsPromo = array_merge($idsPromo, $legacy);
            } elseif ($idPromoBloque > 0) {
                if (Schema::hasTable('web_promo_bloque_producto')) {
                    $idsPromo = array_merge(
                        $idsPromo,
                        DB::table('web_promo_bloque_producto')
                            ->where('id_promo_bloque', $idPromoBloque)
                            ->pluck('id_producto')
                            ->map(fn ($id) => (int) $id)
                            ->all()
                    );
                }
                $itemIds = DB::table('web_promo_item')
                    ->where('id_promo_bloque', $idPromoBloque)
                    ->where('Activo', 'S')
                    ->pluck('id_promo_item')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                if (! empty($itemIds) && Schema::hasTable('web_promo_item_producto')) {
                    $idsPromo = array_merge(
                        $idsPromo,
                        DB::table('web_promo_item_producto')
                            ->whereIn('id_promo_item', $itemIds)
                            ->pluck('id_producto')
                            ->map(fn ($id) => (int) $id)
                            ->all()
                    );
                }
                $legacyBloque = DB::table('web_promo_item')
                    ->where('id_promo_bloque', $idPromoBloque)
                    ->where('Activo', 'S')
                    ->whereNotNull('id_producto')
                    ->where('id_producto', '>', 0)
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $idsPromo = array_merge($idsPromo, $legacyBloque);
            }
            $idsPromo = array_values(array_unique(array_filter($idsPromo)));
            $query->whereIn('administracion_producto.id_producto', $idsPromo ?: [-1]);
        }

        $activo = $request->input('Activo');
        if ($activo === 'S' || $activo === 'N') {
            $query->where('administracion_producto.Activo', $activo);
        }

        $badge = trim((string) ($request->input('etiqueta_badge') ?? $request->input('codigo_barra', '')));
        if ($badge !== '') {
            if (Schema::hasColumn('administracion_producto', 'etiqueta_badge') && $this->isEtiquetaBadge($badge)) {
                $query->where('administracion_producto.etiqueta_badge', strtoupper($badge));
            } else {
                $query->where('administracion_producto.codigo_barra', $badge);
            }
        }

        if ($request->filled('precio_min')) {
            $query->where('administracion_producto.precio', '>=', (float) $request->input('precio_min'));
        }
        if ($request->filled('precio_max')) {
            $query->where('administracion_producto.precio', '<=', (float) $request->input('precio_max'));
        }
        if ($request->filled('stock_min')) {
            $query->where('administracion_producto.stock', '>=', (int) $request->input('stock_min'));
        }
        if ($request->filled('stock_max')) {
            $query->where('administracion_producto.stock', '<=', (int) $request->input('stock_max'));
        }

        $enHomeFilter = $request->input('en_home');
        if ($enHomeFilter === 'S' || $enHomeFilter === '1' || $enHomeFilter === 1 || $enHomeFilter === true) {
            if (Schema::hasTable('web_home_coleccion')) {
                $idsEnHome = WebHomeColeccion::where('Activo', 'S')->pluck('id_producto');
                $query->whereIn('administracion_producto.id_producto', $idsEnHome->isEmpty() ? [-1] : $idsEnHome);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($enHomeFilter === 'N' || $enHomeFilter === '0' || $enHomeFilter === 0 || $enHomeFilter === false) {
            if (Schema::hasTable('web_home_coleccion')) {
                $idsEnHome = WebHomeColeccion::where('Activo', 'S')->pluck('id_producto');
                if ($idsEnHome->isNotEmpty()) {
                    $query->whereNotIn('administracion_producto.id_producto', $idsEnHome);
                }
            }
        }

        $orden = $request->input('orden', 'reciente');
        match ($orden) {
            'nombre_asc'  => $query->orderBy('administracion_producto.nombre'),
            'nombre_desc' => $query->orderByDesc('administracion_producto.nombre'),
            'precio_asc'  => $query->orderBy('administracion_producto.precio'),
            'precio_desc' => $query->orderByDesc('administracion_producto.precio'),
            'stock_asc'   => $query->orderBy('administracion_producto.stock'),
            'stock_desc'  => $query->orderByDesc('administracion_producto.stock'),
            default       => $query->orderByDesc('administracion_producto.id_producto'),
        };

        if ($request->has('page')) {
            $perPage = min(50, max(1, (int) $request->input('per_page', 12)));
            $paginated = $query->paginate($perPage);
            $items = $this->enrichProductsWithEnHome($paginated->items());

            return response()->json([
                'success' => true,
                'message' => 'Listar registros',
                'result'  => [
                    'data'      => $items,
                    'total'     => $paginated->total(),
                    'page'      => $paginated->currentPage(),
                    'per_page'  => $paginated->perPage(),
                    'last_page' => $paginated->lastPage(),
                    'from'      => $paginated->firstItem(),
                    'to'        => $paginated->lastItem(),
                ],
            ]);
        }

        $result = $this->enrichProductsWithEnHome($query->get());

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $result,
        ]);
    }

    /**
     * Listar productos filtrados por rango de fechas (created_at).
     */
    public function listar_por_fechas(Request $request): JsonResponse
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $query = Producto::query()
            ->with(['categoria', 'subcategoria'])
            ->orderBy('created_at', 'desc');

        if ($fechaInicio) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        $result = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    /**
     * Exportar productos a Excel por rango de fechas.
     */
    public function exportar(Request $request): JsonResponse
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        try {
            $fileName = 'reportes/productos_' . date('Y-m-d_His') . '.xlsx';
            Excel::store(new ProductosExport($fechaInicio, $fechaFin), $fileName);

            $file = Storage::disk('local')->get($fileName);
            if ($file) {
                $fileLink = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,' . base64_encode($file);
            } else {
                return response()->json(['success' => false, 'message' => 'Error al generar el archivo'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Exportación exitosa',
                'result' => [
                    'fileName' => basename($fileName),
                    'fileBase64' => $fileLink,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        $filename = null;
        $message = null;

        $nombre = trim((string) $request->input('nombre', ''));
        $stock = (int) $request->input('stock', 0);
        $precioRaw = $request->input('precio');
        $precio = is_numeric($precioRaw) ? (float) $precioRaw : null;
        $tieneImagen = $request->hasFile('pro_imagen');
        $stockVacio = ! $request->filled('stock') || trim((string) $request->input('stock')) === '';

        if (! $tieneImagen || $nombre === '' || $stockVacio || $stock < 1 || $precio === null || $precio < 0) {
            $faltantes = [];
            if (! $tieneImagen) {
                $faltantes[] = 'fotografía principal';
            }
            if ($nombre === '') {
                $faltantes[] = 'nombre';
            }
            if ($precio === null || $precio < 0) {
                $faltantes[] = 'precio';
            }
            if ($stockVacio || $stock < 1) {
                $faltantes[] = 'stock (mínimo 1, no vacío)';
            }
            $msg = 'Para crear un producto es obligatorio: ' . implode(', ', $faltantes) . '.';

            return response()->json([
                'success' => false,
                'message' => $msg,
                'result'  => ['message' => $msg],
            ], 422);
        }

        if ($request->hasFile('pro_imagen')) {
            $errImg = $this->validateProImagenBasico($request->file('pro_imagen'));
            if ($errImg) {
                return response()->json([
                    'success' => false,
                    'message' => $errImg,
                    'result' => [
                        'message' => $errImg,
                    ],
                ], 422);
            }
        }

        //============= UNICIDAD: codigo_producto_new y codigo_barra ============
        $codigoNew = $request->codigo_producto_new;
        if (!$this->isEmptyCode($codigoNew)) {
            if (Producto::where('codigo_producto_new', $codigoNew)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "El código de producto «{$codigoNew}» ya está registrado en otro producto.",
                    'result'  => ['message' => "El código de producto «{$codigoNew}» ya está registrado en otro producto."],
                ]);
            }
        }
        $split       = $this->splitEtiquetaYBarra($request->codigo_barra, $request->input('etiqueta_badge'));
        $codigoBarra = $split['codigo_barra'];
        if (! $this->isEmptyCode($codigoBarra)) {
            if (Producto::where('codigo_barra', $codigoBarra)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "El código de barra «{$codigoBarra}» ya está registrado en otro producto.",
                    'result'  => ['message' => "El código de barra «{$codigoBarra}» ya está registrado en otro producto."],
                ]);
            }
        }
        //============= CODIGO DE PRODUCTO ===============
        do {
            $codigo_producto = 'P-' . Str::upper(Str::random(6));
        } while (DB::table('administracion_producto')->where('codigo_producto', $codigo_producto)->exists());

        DB::beginTransaction();

        try {
            if ($request->hasFile('pro_imagen')) {
                $file       = $request->file("pro_imagen");
                $ext        = $file->getClientOriginalExtension();
                $filename_  = time() . '.' . $ext;
                $filename   = \App\Support\CmsImageStorage::store($file, 'producto_principal');
            }

            $dataToCreate = [
                'codigo_producto'               => $codigo_producto,
                'nombre'                        => $request->nombre,
                'descripcion'                   => ($request->descripcion !== null && $request->descripcion !== '' && $request->descripcion !== 'null') ? $request->descripcion : null,
                'precio'                        => $request->precio,
                'stock'                         => $request->stock,
                'id_producto_categoria'         => $this->nullableCategoriaId($request->id_producto_categoria),
                'id_producto_categoria_sub'     => $this->nullableCategoriaId($request->id_producto_categoria_sub),
                'id_producto_tipo'              => ($request->id_producto_tipo === null || $request->id_producto_tipo === '' || $request->id_producto_tipo === 'undefined' || $request->id_producto_tipo === 'null') ? 2 : $request->id_producto_tipo,
                'url_imagen'                    => $filename,
                'created_at'                    => date("Y-m-d H:i:s"),
                'updated_at'                    => date("Y-m-d H:i:s"),
                'Activo'                        => $request->Activo,

                'precio_old'                    => $this->nullableDecimal($request->precio_old),
                'peso_kilogramo'                => $this->nullableDecimal($request->peso_kilogramo),
                'corte_tiempo_promocion'        => $request->corte_tiempo_promocion,
                'corte_tiempo_sabado'           => $request->corte_tiempo_sabado,
                'numero_estrellas'              => $this->nullableInt($request->input('numero_estrellas')),
                'precio_yape'                   => $this->nullableDecimal($request->precio_yape),

                'paquete_medidas'               => $request->paquete_medidas !== null && $request->paquete_medidas !== '' && $request->paquete_medidas !== 'null' && $request->paquete_medidas !== 'undefined' ? $request->paquete_medidas : null,
                'paquete_dimencion'             => $request->paquete_dimencion !== null && $request->paquete_dimencion !== '' && $request->paquete_dimencion !== 'null' && $request->paquete_dimencion !== 'undefined' ? $request->paquete_dimencion : null,
                'meta_titulo_producto'          => $request->meta_titulo_producto !== 'null' && $request->meta_titulo_producto !== 'undefined' ? $request->meta_titulo_producto : null,
                'meta_descripcion_producto'     => $request->meta_descripcion_producto !== 'null' && $request->meta_descripcion_producto !== 'undefined' ? $request->meta_descripcion_producto : null,
                'codigo_producto_new'           => $request->codigo_producto_new !== 'null' && $request->codigo_producto_new !== 'undefined' ? $request->codigo_producto_new : null,
                'codigo_barra'                  => $request->codigo_barra !== 'null' && $request->codigo_barra !== 'null' && $request->codigo_barra !== 'undefined' ? $request->codigo_barra : null,
                'ratings_enabled'               => $request->ratings_enabled === 'true' || $request->ratings_enabled === true || $request->ratings_enabled === 1 || $request->ratings_enabled === '1' ? 1 : 0,
                'admin_rating'                  => $this->nullableDecimal($request->admin_rating),
                'precio_mayorista'              => $this->nullableDecimal($request->precio_mayorista),
                'precio_mayorista_old'          => $this->nullableDecimal($request->precio_mayorista_old),
            ];

            if (Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad')) {
                $dataToCreate['oferta_maxima_cantidad'] = $this->nullableInt($request->input('oferta_maxima_cantidad'));
            }
            if (Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad_por_precio')) {
                $dataToCreate['oferta_maxima_cantidad_por_precio'] = $this->nullableDecimal($request->input('oferta_maxima_cantidad_por_precio'));
            }
            if (Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
                $dataToCreate['descuento_porcentaje'] = $this->nullableDecimal($request->input('descuento_porcentaje'));
            }
            if (Schema::hasColumn('administracion_producto', 'color')) {
                $dataToCreate['color'] = $request->input('color');
            }
            if (Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
                $dataToCreate['tallas_disponibles'] = $request->input('tallas_disponibles', 'S,M,L,XL,XXL');
            }
            if (Schema::hasColumn('administracion_producto', 'colores_disponibles')) {
                $rawColores = $request->input('colores_disponibles');
                if ($rawColores === null || $rawColores === '') {
                    $dataToCreate['colores_disponibles'] = json_encode([], JSON_UNESCAPED_UNICODE);
                } else {
                    $dataToCreate['colores_disponibles'] = $rawColores;
                }
            }
            $this->applyEcommerceCatalogFields($request, $dataToCreate);

            $this->applyEtiquetaYBarraFields($request, $dataToCreate);

            Producto::create($dataToCreate);

            DB::commit();
            $message = 'Registro insertado';
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            $message = $e->getMessage();
            DB::rollback();
        }

        return response()->json([
            'success' => $message != null ? true : false,
            'message' => 'Procesos exitosos.',
            'result' => [
                'message' => $message,
            ]
        ], 200);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $filename = null;
        $message = null;

        if ($request->hasFile('pro_imagen')) {
            $errImg = $this->validateProImagenBasico($request->file('pro_imagen'));
            if ($errImg) {
                return response()->json([
                    'success' => false,
                    'message' => $errImg,
                    'result' => [
                        'message' => $errImg,
                    ],
                ], 422);
            }
        }

        if ($request->hasFile('pro_imagen')) {
            $result = Producto::where('id_producto', $request->id_producto)->get();

            if (isset($result) && !empty($result[0]->url_imagen) && $result[0]->url_imagen != null) {
                $this->eliminarArchivoProductoDesdeDisco($result[0]->url_imagen);
            }

            $file = $request->file("pro_imagen");
            $ext = $file->getClientOriginalExtension();
            $filename_ = time() . '.' . $ext;
            $filename = \App\Support\CmsImageStorage::store($file, 'producto_principal');
        }

        //============= UNICIDAD: codigo_producto_new y codigo_barra ============
        $idProducto = $request->input('id_producto');
        $codigoNew  = $request->codigo_producto_new;
        if (!$this->isEmptyCode($codigoNew)) {
            if (Producto::where('codigo_producto_new', $codigoNew)->where('id_producto', '!=', $idProducto)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "El código de producto «{$codigoNew}» ya está en uso por otro producto.",
                    'result'  => ['message' => "El código de producto «{$codigoNew}» ya está en uso por otro producto."],
                ]);
            }
        }
        $split       = $this->splitEtiquetaYBarra($request->codigo_barra, $request->input('etiqueta_badge'));
        $codigoBarra = $split['codigo_barra'];
        if (! $this->isEmptyCode($codigoBarra)) {
            if (Producto::where('codigo_barra', $codigoBarra)->where('id_producto', '!=', $idProducto)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "El código de barra «{$codigoBarra}» ya está en uso por otro producto.",
                    'result'  => ['message' => "El código de barra «{$codigoBarra}» ya está en uso por otro producto."],
                ]);
            }
        }

        // % del módulo Productos incompatible con oferta ya asignada en Bloques
        if (Schema::hasColumn('administracion_producto', 'descuento_porcentaje')
            && $request->has('descuento_porcentaje')) {
            $pctIn = $this->nullableDecimal($request->input('descuento_porcentaje'));
            if ($pctIn !== null && (float) $pctIn > 0
                && $this->productoTieneAsignacionBloque((int) $idProducto)) {
                $msg = 'Este producto no está disponible para una oferta por porcentaje porque ya tiene una oferta asignada en el módulo Bloques. Libéralo primero (quita la asignación) y luego podrás colocar el %.';

                return response()->json([
                    'success' => false,
                    'code' => 'ASIGNADO_EN_BLOQUE',
                    'message' => $msg,
                    'result' => ['message' => $msg],
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $datos = [];

            if ($request->hasFile('pro_imagen')) {
                $datos['nombre']                            = $request->nombre;
                $datos['descripcion']                       = ($request->descripcion !== null && $request->descripcion !== '' && $request->descripcion !== 'null') ? $request->descripcion : null;
                $datos['stock']                             = $request->stock;
                $datos['id_producto_categoria']             = $this->nullableCategoriaId($request->id_producto_categoria);
                $datos['id_producto_categoria_sub']         = $this->nullableCategoriaId($request->id_producto_categoria_sub);
                $datos['id_producto_tipo']                  = ($request->id_producto_tipo === null || $request->id_producto_tipo === '' || $request->id_producto_tipo === 'undefined' || $request->id_producto_tipo === 'null') ? null : $request->id_producto_tipo;
                $datos['url_imagen']                        = $filename;
                $datos['updated_at']                        = date("Y-m-d H:i:s");
                $datos['Activo']                            = $request->Activo;
                $datos['numero_estrellas']                  = $request->numero_estrellas!==null?$request->numero_estrellas:null;
                $datos['peso_kilogramo']                    = $request->peso_kilogramo;
                $datos['corte_tiempo_promocion']            = $request->corte_tiempo_promocion;
                $datos['corte_tiempo_sabado']               = $request->corte_tiempo_sabado;
                
                $datos['paquete_medidas']                   = $request->paquete_medidas!== 'null' ? $request->paquete_medidas : null;
                $datos['paquete_dimencion']                 = $request->paquete_dimencion!== 'null' ? $request->paquete_dimencion : null;
                $datos['meta_titulo_producto']                 = $request->meta_titulo_producto!== 'null' ? $request->meta_titulo_producto : null;
                $datos['meta_descripcion_producto']                 = $request->meta_descripcion_producto!== 'null' ? $request->meta_descripcion_producto : null;
                $datos['codigo_producto_new']               = $request->codigo_producto_new!== 'null' ? $request->codigo_producto_new : null;
                $datos['codigo_barra']                      = $request->codigo_barra!== 'null' ? $request->codigo_barra : null;
                $datos['ratings_enabled']                   = $request->ratings_enabled === 'true' || $request->ratings_enabled === true || $request->ratings_enabled === 1 || $request->ratings_enabled === '1' ? 1 : 0;
                $datos['admin_rating']                      = $request->admin_rating !== 'null' ? $request->admin_rating : null;
                //=========== PRECIO AL PUBLICO =============
                $datos['precio_old']                        = $request->precio_old;
                $datos['precio']                            = $request->precio;

                //=========== PRECIO AL MAYORISTA =============
                $datos['precio_mayorista']                  = $this->nullableDecimal($request->precio_mayorista);
                $datos['precio_mayorista_old']              = $this->nullableDecimal($request->precio_mayorista_old);

                $datos['precio_yape']                       = $this->nullableDecimal($request->precio_yape);
                $datos['oferta_maxima_cantidad']            = $this->nullableInt($request->input('oferta_maxima_cantidad'));
                $datos['oferta_maxima_cantidad_por_precio'] = $this->nullableDecimal($request->input('oferta_maxima_cantidad_por_precio'));
                if (Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
                    $datos['descuento_porcentaje'] = $this->nullableDecimal($request->input('descuento_porcentaje'));
                }
            } else {
                $datos['nombre']                            = $request->nombre;
                $datos['descripcion']                       = ($request->descripcion !== null && $request->descripcion !== '' && $request->descripcion !== 'null') ? $request->descripcion : null;
                $datos['stock']                             = $request->stock;
                $datos['id_producto_categoria']             = $this->nullableCategoriaId($request->id_producto_categoria);
                $datos['id_producto_categoria_sub']         = $this->nullableCategoriaId($request->id_producto_categoria_sub);
                $datos['id_producto_tipo']                  = ($request->id_producto_tipo === null || $request->id_producto_tipo === '' || $request->id_producto_tipo === 'undefined' || $request->id_producto_tipo === 'null') ? null : $request->id_producto_tipo;
                $datos['updated_at']                        = date("Y-m-d H:i:s");
                $datos['Activo']                            = $request->Activo;
                $datos['numero_estrellas']                  = $request->numero_estrellas;
                $datos['peso_kilogramo']                    = $request->peso_kilogramo;
                $datos['corte_tiempo_promocion']            = $request->corte_tiempo_promocion;
                $datos['corte_tiempo_sabado']               = $request->corte_tiempo_sabado;
                
                $datos['paquete_medidas']                   = $request->paquete_medidas!== 'null' ? $request->paquete_medidas : null;
                $datos['paquete_dimencion']                 = $request->paquete_dimencion!== 'null' ? $request->paquete_dimencion : null;
                $datos['meta_titulo_producto']              = $request->meta_titulo_producto!== 'null' ? $request->meta_titulo_producto : null;
                $datos['meta_descripcion_producto']         = $request->meta_descripcion_producto!== 'null' ? $request->meta_descripcion_producto : null;
                $datos['codigo_producto_new']               = $request->codigo_producto_new!== 'null' ? $request->codigo_producto_new : null;
                $datos['codigo_barra']                      = $request->codigo_barra!== 'null' ? $request->codigo_barra : null;
                $datos['ratings_enabled']                   = $request->ratings_enabled === 'true' || $request->ratings_enabled === true || $request->ratings_enabled === 1 || $request->ratings_enabled === '1' ? 1 : 0;
                $datos['admin_rating']                      = $request->admin_rating !== 'null' ? $request->admin_rating : null;
                
                //=========== PRECIO AL PUBLICO =============
                $datos['precio']                            = $request->precio;
                $datos['precio_old']                        = $request->precio_old;
                //=========== PRECIO AL MAYORISTA =============
                $datos['precio_mayorista']                  = $this->nullableDecimal($request->precio_mayorista);
                $datos['precio_mayorista_old']              = $this->nullableDecimal($request->precio_mayorista_old);

                $datos['precio_yape']                       = $this->nullableDecimal($request->precio_yape);
                
                if (Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad')) {
                    $datos['oferta_maxima_cantidad'] = $this->nullableInt($request->input('oferta_maxima_cantidad'));
                }
                if (Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad_por_precio')) {
                    $datos['oferta_maxima_cantidad_por_precio'] = $this->nullableDecimal($request->input('oferta_maxima_cantidad_por_precio'));
                }
                if (Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
                    $datos['descuento_porcentaje'] = $this->nullableDecimal($request->input('descuento_porcentaje'));
                }
                }

            $this->applyEcommerceCatalogFields($request, $datos);
            $this->applyEtiquetaYBarraFields($request, $datos);

            $id_producto = $request->input('id_producto');
            $producto = Producto::where('id_producto', $id_producto)->first();
            if (! $producto) {
                throw new \RuntimeException('Producto no encontrado.');
            }
            // fill + save dispara el boot que regenera el slug SEO desde el nombre
            $producto->fill($datos);
            $producto->save();

            DB::commit();
            $message = 'Se ha actualizado correctamente!';
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            $message = $e->getMessage();
            DB::rollback();
        }

        return response()->json([
            'success' => $message != null ? true : false,
            'message' => 'Payroll are successfully.',
            'result' => [
                'message' => $message,
            ]
        ], 200);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $id = $request->input('id') ?? $request->input('id_producto');
        if ($id === null || $id === '' || $id === 'null') {
            return response()->json([
                'success' => false,
                'message' => 'Identificador de producto requerido.',
            ], 422);
        }

        $producto = Producto::query()->where('id_producto', $id)->first();
        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        $nGaleriaFotos = (int) DB::table('administracion_producto_fotos')->where('id_producto', $id)->count();

        if ($nGaleriaFotos > 0) {
            return response()->json([
                'success' => false,
                'code'    => 'TIENE_SUBIMAGENES',
                'message' => 'No se puede eliminar el producto mientras tenga imágenes en la galería. Elimínelas primero desde la edición del producto y vuelva a intentar.',
                'result'  => [
                    'galeria_fotos' => $nGaleriaFotos,
                ],
            ], 409);
        }

        $urlsArchivo = [];
        if (!empty($producto->url_imagen)) {
            $urlsArchivo[] = (string) $producto->url_imagen;
        }

        $fotosUrls = DB::table('administracion_producto_fotos')
            ->where('id_producto', $id)
            ->pluck('url_imagen');
        foreach ($fotosUrls as $u) {
            if ($u !== null && trim((string) $u) !== '') {
                $urlsArchivo[] = (string) $u;
            }
        }

        if (Schema::hasTable(ProductoEcommerceCatalog::TABLA_FOTOS)) {
            $subUrls = DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                ->where('id_producto', $id)
                ->whereNotNull('codigo_producto')
                ->where('codigo_producto', 'like', 'S-%')
                ->pluck('url_imagen');
            foreach ($subUrls as $u) {
                if ($u !== null && trim((string) $u) !== '') {
                    $urlsArchivo[] = (string) $u;
                }
            }
        }

        $urlsArchivo = array_values(array_unique($urlsArchivo));

        try {
            DB::transaction(function () use ($id) {
                DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)->where('id_producto', $id)->delete();

                Producto::where('id_producto', $id)->delete();
            });
        } catch (\Throwable $e) {
            Log::channel('stderr')->error('Producto eliminar BD: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el producto.',
            ], 500);
        }

        foreach ($urlsArchivo as $ruta) {
            $this->eliminarArchivoProductoDesdeDisco($ruta);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => 1,
        ]);
    }

    public function listar_imagen(Request $request): JsonResponse
    {
        $idProducto = (int) $request->input('id_producto');
        $filas = DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
            ->where('id_producto', $idProducto)
            ->where('Activo', 'S')
            ->whereNotNull('codigo_producto')
            ->where('codigo_producto', 'like', 'S-%')
            ->orderBy('orden')
            ->get()
            ->map(function ($f) {
                $row = (array) $f;
                $row['id_producto_imagen'] = $f->id_producto_foto;

                return (object) $row;
            });

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $filas,
        ]);
    }

    public function actualizar_imagen_orden(Request $request): JsonResponse
    {
        $message = null;
        DB::beginTransaction();
        try {
            $items = $request->input('items');

            foreach ($items as $item) {
                $idFoto = $item['id_producto_foto'] ?? $item['id_producto_imagen'] ?? null;
                if ($idFoto === null) {
                    continue;
                }
                DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                    ->where('id_producto_foto', $idFoto)
                    ->update(['orden' => $item['orden']]);
            }

            DB::commit();
            $message = 'Procesos exitosos.';
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            DB::rollback();
        }

        return response()->json([
            'success' => $message != null ? true : false,
            'message' => 'Mesaje de procesos exitosos.',
            'result' => [
                'message' => $message,
            ]
        ], 200);
    }

    public function eliminar_imagen(Request $request): JsonResponse
    {
        $id = $request->input('id_producto_foto', $request->input('id_producto_imagen', $request->query('id_producto_foto', $request->query('id_producto_imagen'))));
        if ($id === null || $id === '' || $id === 'null') {
            return response()->json([
                'success' => false,
                'message' => 'Identificador de imagen requerido.',
            ], 422);
        }

        $fila = ProductoFoto::where('id_producto_foto', $id)->first();
        if (!$fila) {
            return response()->json([
                'success' => false,
                'message' => 'Imagen no encontrada.',
            ], 404);
        }

        $urlRelativa = $fila->url_imagen ?? null;

        try {
            DB::transaction(function () use ($id) {
                ProductoFoto::where('id_producto_foto', $id)->delete();
            });
        } catch (\Throwable $e) {
            Log::channel('stderr')->error('eliminar_imagen BD: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la imagen.',
            ], 500);
        }

        $this->eliminarArchivoProductoDesdeDisco($urlRelativa !== null ? (string) $urlRelativa : null);

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => 1,
        ]);
    }

    public function actualizar_ficha_tecnica(Request $request): JsonResponse
    {
        $id_producto = (int) $request->input('id_producto');
        if ($id_producto <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'id_producto requerido',
                'result'  => 'Error',
            ], 422);
        }

        $fichas = $request->input('fichas');
        if (is_array($fichas)) {
            return $this->guardarFichasTecnicasPorOrden($id_producto, $fichas);
        }

        DB::beginTransaction();

        try {
            $titulo1 = $request->input('titulo1');
            $titulo2 = $request->input('titulo2');
            $editorValue1 = $request->input('editorValue1');
            $editorValue2 = $request->input('editorValue2');
            $Activo = $request->input('Activo', true);
            $ficha_principal_orden = (int) $request->input('ficha_principal_orden', 1);

            $dataResult = ProductoFichaTecnica::where('id_producto', $id_producto)->get();

            if (!empty($dataResult[0]->id_producto)) {
                if (Schema::hasColumn('administracion_producto_ficha_tecnica', 'es_principal')) {
                    DB::table('administracion_producto_ficha_tecnica')
                        ->where('id_producto', $id_producto)
                        ->update(['es_principal' => 'N']);
                    DB::table('administracion_producto_ficha_tecnica')
                        ->where('id_producto', $id_producto)
                        ->where('orden', $ficha_principal_orden)
                        ->update(['es_principal' => 'S']);
                }

                DB::table('administracion_producto_ficha_tecnica')
                    ->where('id_producto', $id_producto)
                    ->where('orden', 1)
                    ->update([
                        'titulo' => $titulo1,
                        'descripcion' => $editorValue1,
                        'Activo' => $Activo,
                        'updated_at' => now()
                    ]);

                if ($editorValue2 && is_array($editorValue2)) {
                    $caracteristicas_array = isset($editorValue2['caracteristicas']) ?
                        json_encode($editorValue2['caracteristicas']) : null;

                    $nuevo_titulo = isset($editorValue2['titulo']) ? $editorValue2['titulo'] : $titulo2;
                    $nueva_descripcion = isset($editorValue2['descripcion']) ? $editorValue2['descripcion'] : '';

                    DB::table('administracion_producto_ficha_tecnica')
                        ->where('id_producto', $id_producto)
                        ->where('orden', 2)
                        ->update([
                            'titulo' => $titulo2,
                            'nuevo_caracteristicas' => $caracteristicas_array,
                            'nuevo_titulo' => $nuevo_titulo,
                            'nuevo_descripcion' => $nueva_descripcion,
                            'Activo' => $Activo,
                            'updated_at' => now()
                        ]);
                }

                $result = "Actualizado correctamente!";
            } else {
                $createAttrs1 = [
                    'id_producto'      => $id_producto,
                    'titulo'           => $titulo1,
                    'descripcion'      => $editorValue1,
                    'orden'            => 1,
                    'Activo'           => $Activo,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                if (Schema::hasColumn('administracion_producto_ficha_tecnica', 'es_principal')) {
                    $createAttrs1['es_principal'] = $ficha_principal_orden === 1 ? 'S' : 'N';
                }
                ProductoFichaTecnica::create($createAttrs1);

                $caracteristicas_array = null;
                $nuevo_titulo = $titulo2;
                $nueva_descripcion = '';

                if ($editorValue2 && is_array($editorValue2)) {
                    $caracteristicas_array = isset($editorValue2['caracteristicas']) ?
                        json_encode($editorValue2['caracteristicas']) : null;

                    $nuevo_titulo = isset($editorValue2['titulo']) ? $editorValue2['titulo'] : $titulo2;
                    $nueva_descripcion = isset($editorValue2['descripcion']) ? $editorValue2['descripcion'] : '';
                }

                $createAttrs2 = [
                    'id_producto'      => $id_producto,
                    'titulo'           => $titulo2,
                    'nuevo_caracteristicas' => $caracteristicas_array,
                    'nuevo_titulo'     => $nuevo_titulo,
                    'nuevo_descripcion' => $nueva_descripcion,
                    'orden'            => 2,
                    'Activo'           => $Activo,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                if (Schema::hasColumn('administracion_producto_ficha_tecnica', 'es_principal')) {
                    $createAttrs2['es_principal'] = $ficha_principal_orden === 2 ? 'S' : 'N';
                }
                ProductoFichaTecnica::create($createAttrs2);

                $result = "Se registró correctamente!";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Operación completada correctamente',
                'result'  => $result
            ]);
        } catch (\Exception $e) {
            Log::channel('stderr')->error('Error en actualizar_ficha_tecnica: ' . $e->getMessage());
            Log::channel('stderr')->error($e->getTraceAsString());

            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                'result'  => 'Error'
            ], 500);
        }
    }

    private function guardarFichasTecnicasPorOrden(int $idProducto, array $fichas): JsonResponse
    {
        DB::beginTransaction();

        try {
            $hasTipo = Schema::hasColumn('administracion_producto_ficha_tecnica', 'tipo');
            $keptIds = [];
            $orden = 0;
            $nextId = $this->nextFichaTecnicaId();

            foreach ($fichas as $ficha) {
                if (! is_array($ficha)) {
                    continue;
                }

                $orden++;
                $tipoRaw = strtolower(trim((string) ($ficha['tipo'] ?? '')));
                if ($tipoRaw !== 'html' && $tipoRaw !== 'tabla') {
                    // Compatibilidad con payload antiguo (orden 1–2 = tabla, 3 = html)
                    $legacyOrden = (int) ($ficha['orden'] ?? 0);
                    if ($legacyOrden === 3 || (array_key_exists('descripcion', $ficha) && ! array_key_exists('caracteristicas', $ficha))) {
                        $tipoRaw = 'html';
                    } else {
                        $tipoRaw = 'tabla';
                    }
                }

                $tituloDefault = $tipoRaw === 'html' ? 'Información' : 'Descripción';
                $titulo = trim((string) ($ficha['titulo'] ?? ''));
                if ($titulo === '') {
                    $titulo = $tituloDefault;
                }

                $attrs = [
                    'titulo'     => $titulo,
                    'orden'      => $orden,
                    'Activo'     => ($ficha['Activo'] ?? 'S') === 'N' ? 'N' : 'S',
                    'updated_at' => now(),
                ];

                if ($hasTipo) {
                    $attrs['tipo'] = $tipoRaw;
                }

                if ($tipoRaw === 'tabla') {
                    $caracteristicas = $ficha['caracteristicas'] ?? [];
                    if (! is_array($caracteristicas)) {
                        $caracteristicas = [];
                    }
                    $caracteristicas = array_values(array_filter($caracteristicas, function ($row) {
                        return is_array($row)
                            && trim((string) ($row['nombre'] ?? '')) !== '';
                    }));
                    $attrs['nuevo_caracteristicas'] = json_encode($caracteristicas, JSON_UNESCAPED_UNICODE);
                    $attrs['nuevo_titulo']          = null;
                    $attrs['nuevo_descripcion']     = null;
                    $attrs['descripcion']           = null;
                } else {
                    $attrs['descripcion']           = $ficha['descripcion'] ?? '';
                    $attrs['nuevo_titulo']          = $ficha['enlace_ver_mas'] ?? null;
                    $attrs['nuevo_descripcion']     = null;
                    $attrs['nuevo_caracteristicas'] = null;
                }

                if (Schema::hasColumn('administracion_producto_ficha_tecnica', 'es_principal')) {
                    $attrs['es_principal'] = $orden === 1 ? 'S' : 'N';
                }

                $idFicha = (int) ($ficha['id_ficha_tecnica'] ?? 0);
                $existente = null;
                if ($idFicha > 0) {
                    $existente = ProductoFichaTecnica::where('id_producto', $idProducto)
                        ->where('id_ficha_tecnica', $idFicha)
                        ->first();
                }

                if ($existente) {
                    $existente->update($attrs);
                    $keptIds[] = (int) $existente->id_ficha_tecnica;
                } else {
                    $newId = $nextId++;
                    ProductoFichaTecnica::create(array_merge($attrs, [
                        'id_ficha_tecnica' => $newId,
                        'id_producto'      => $idProducto,
                        'created_at'       => now(),
                    ]));
                    $keptIds[] = $newId;
                }
            }

            $deleteQ = ProductoFichaTecnica::where('id_producto', $idProducto);
            if (count($keptIds) > 0) {
                $deleteQ->whereNotIn('id_ficha_tecnica', $keptIds);
            }
            $deleteQ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fichas técnicas guardadas correctamente',
                'result'  => 'Fichas técnicas guardadas correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error guardarFichasTecnicasPorOrden: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar fichas técnicas: ' . $e->getMessage(),
                'result'  => 'Error',
            ], 500);
        }
    }

    public function obtener_ficha_tecnica(Request $request): JsonResponse
    {
        $id_producto = (int) $request->id_producto;
        $result = ProductoFichaTecnica::where('id_producto', $id_producto)
            ->orderBy('orden')
            ->get()
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    public function obtener_imagen(Request $request): JsonResponse
    {
        $id = $request->input('id_producto_foto', $request->input('id', $request->input('id_producto_imagen')));
        $result = DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
            ->where('id_producto_foto', $id)
            ->get()
            ->map(function ($f) {
                $row = (array) $f;
                $row['id_producto_imagen'] = $f->id_producto_foto;

                return (object) $row;
            });

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result'  => $result,
        ]);
    }

    //=========== GALERIA DE FOTOS (administracion_producto_fotos) ==================
    public function listar_fotos(Request $request): JsonResponse
    {
        $result = DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
            ->where('id_producto', $request->id_producto)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $result,
        ]);
    }

    public function eliminar_fotos(Request $request): JsonResponse
    {
        $id = $request->input('id_producto_foto', $request->query('id_producto_foto'));
        if ($id === null || $id === '' || $id === 'null') {
            return response()->json([
                'success' => false,
                'message' => 'Identificador de foto (id_producto_foto) requerido.',
            ], 422);
        }

        $fila = DB::table('administracion_producto_fotos')
            ->where('id_producto_foto', $id)
            ->first();

        if (!$fila) {
            return response()->json([
                'success' => false,
                'message' => 'Foto de galería no encontrada.',
            ], 404);
        }

        $urlRelativa = $fila->url_imagen ?? null;

        try {
            DB::transaction(function () use ($id) {
                DB::table('administracion_producto_fotos')
                    ->where('id_producto_foto', $id)
                    ->delete();
            });
        } catch (\Throwable $e) {
            Log::channel('stderr')->error('eliminar_fotos BD: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el registro de la galería.',
            ], 500);
        }

        $this->eliminarArchivoProductoDesdeDisco($urlRelativa !== null ? (string) $urlRelativa : null);

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => [],
        ]);
    }

    public function crear_fotos(Request $request): JsonResponse
    {
        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Debe enviar una imagen.',
            ], 422);
        }

        $id_producto = $request->input('id_producto');
        if (empty($id_producto)) {
            return response()->json([
                'success' => false,
                'message' => 'id_producto requerido.',
            ], 422);
        }

        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $filename_ = time() . '.' . $ext;
        $filename = \App\Support\CmsImageStorage::store($file, 'producto_principal_fotos');

        DB::beginTransaction();

        try {
            $orden = (int) DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                ->where('id_producto', $id_producto)
                ->count() + 1;

            DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)->insert([
                'id_producto'  => $id_producto,
                'url_imagen'   => $filename,
                'orden'        => $orden,
                'color'        => $request->input('color'),
                'titulo'       => $request->input('titulo'),
                'Activo'       => 'S',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro insertado',
                'result'  => ['message' => 'Registro insertado'],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('stderr')->error('crear_fotos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'result'  => ['message' => $e->getMessage()],
            ], 500);
        }
    }

    public function actualizar_foto_orden(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $items = $request->input('items', []);

            foreach ($items as $item) {
                DB::table('administracion_producto_fotos')
                    ->where('id_producto_foto', $item['id_producto_foto'])
                    ->update(['orden' => $item['orden']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado',
                'result'  => ['message' => 'Orden actualizado'],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('stderr')->error('actualizar_foto_orden: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //================  VARIANTES VENDIBLES (administracion_producto_fotos + codigo S-) ==================
    public function crear_imagen(Request $request): JsonResponse
    {
        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Debe enviar una imagen.',
            ], 422);
        }

        $id_producto = $request->input('id_producto');
        if (empty($id_producto)) {
            return response()->json([
                'success' => false,
                'message' => 'id_producto requerido.',
            ], 422);
        }

        $padre = Producto::find($id_producto);

        do {
            $codigo_producto = 'S-' . Str::upper(Str::random(6));
        } while (DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)->where('codigo_producto', $codigo_producto)->exists());

        $errImg = $this->validateProImagenBasico($request->file('image'));
        if ($errImg) {
            return response()->json([
                'success' => false,
                'message' => $errImg,
                'result'  => ['message' => $errImg],
            ], 422);
        }

        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $filename_ = time() . '.' . $ext;
        $filename = \App\Support\CmsImageStorage::store($file, 'producto_principal_fotos');

        DB::beginTransaction();

        try {
            $orden = (int) DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                ->where('id_producto', $id_producto)
                ->count() + 1;

            DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)->insert([
                'id_producto'     => $id_producto,
                'url_imagen'      => $filename,
                'orden'           => $orden,
                'Activo'          => $request->input('Activo', 'S'),
                'titulo'          => $request->input('titulo') ?: ($padre?->nombre ?? 'Variante'),
                'precio'          => $request->input('precio') !== null && $request->input('precio') !== '' ? $request->input('precio') : ($padre?->precio ?? 0),
                'descripcion'     => $request->input('descripcion') ?? ($padre?->descripcion ?? ''),
                'codigo_producto' => $codigo_producto,
                'stock'           => $request->input('stock') !== null && $request->input('stock') !== '' ? $request->input('stock') : ($padre?->stock ?? 0),
                'precio_old'      => $request->input('precio_old') ?? ($padre?->precio_old ?? null),
                'numero_estrellas'=> $request->input('numero_estrellas'),
                'color'           => $request->input('color'),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro insertado',
                'result'  => ['message' => 'Registro insertado'],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('stderr')->error('crear_imagen: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'result'  => ['message' => $e->getMessage()],
            ], 500);
        }
    }

    public function actualizar_subproducto(Request $request): JsonResponse
    {
        $filename = null;
        $message = null;

        if ($request->hasFile('pro_imagen')) {
            $errImg = $this->validateProImagenBasico($request->file('pro_imagen'));
            if ($errImg) {
                return response()->json([
                    'success' => false,
                    'message' => $errImg,
                    'result' => [
                        'message' => $errImg,
                    ],
                ], 422);
            }
        }
        
        if ($request->hasFile('pro_imagen')) {
            $idFoto = $request->input('id_producto_foto', $request->input('id_producto_imagen'));
            $result = DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                ->where('id_producto_foto', $idFoto)
                ->get();

            if (isset($result) && !empty($result[0]->url_imagen) && $result[0]->url_imagen != null) {
                $this->eliminarArchivoProductoDesdeDisco($result[0]->url_imagen);
            }

            $file = $request->file("pro_imagen");
            $ext = $file->getClientOriginalExtension();
            $filename_ = time() . '.' . $ext;
            $filename = \App\Support\CmsImageStorage::store($file, 'producto_principal_fotos');
        }

        DB::beginTransaction();

        try {
            $datos = [];

            if ($request->hasFile('pro_imagen')) {
                $datos['titulo']                = $request->titulo;
                $datos['precio']                = $request->precio;
                $datos['stock']                 = $request->stock;
                $datos['descripcion']           = $request->descripcion;
                $datos['url_imagen']            = $filename;
                $datos['updated_at']            = date("Y-m-d H:i:s");
                $datos['Activo']                = $request->Activo;
                $datos['numero_estrellas']      = $request->numero_estrellas;
                $datos['precio_old']            = $request->precio_old;
                $datos['color']                 = $request->color;
            } else {
                $datos['titulo']                = $request->titulo;
                $datos['precio']                = $request->precio;
                $datos['stock']                 = $request->stock;
                $datos['descripcion']           = $request->descripcion;
                $datos['updated_at']            = date("Y-m-d H:i:s");
                $datos['Activo']                = $request->Activo;
                $datos['numero_estrellas']      = $request->numero_estrellas;
                $datos['precio_old']            = $request->precio_old;
                $datos['color']                 = $request->color;
            }

            $idFoto = $request->input('id_producto_foto', $request->input('id_producto_imagen'));
            DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                ->where('id_producto_foto', $idFoto)
                ->update($datos);

            DB::commit();
            $message = 'Se ha actualizado correctamente!';
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            $message = $e->getMessage();
            DB::rollback();
        }

        return response()->json([
            'success' => $message != null ? true : false,
            'message' => 'Payroll are successfully.',
            'result' => [
                'message' => $message,
            ]
        ], 200);
    }

    public function actualizar_imagen_principal(Request $request): JsonResponse
    {
        $idProducto = $request->input('id_producto');
        if (!$idProducto) {
            return response()->json(['success' => false, 'result' => ['message' => 'id_producto requerido']], 422);
        }
        if (!$request->hasFile('pro_imagen')) {
            return response()->json(['success' => false, 'result' => ['message' => 'Imagen requerida']], 422);
        }
        $errImg = $this->validateProImagenBasico($request->file('pro_imagen'));
        if ($errImg) {
            return response()->json(['success' => false, 'result' => ['message' => $errImg]], 422);
        }

        $producto = Producto::find($idProducto);
        if ($producto && !empty($producto->url_imagen)) {
            $this->eliminarArchivoProductoDesdeDisco($producto->url_imagen);
        }

        $file     = $request->file('pro_imagen');
        $ext      = $file->getClientOriginalExtension();
        $filename = \App\Support\CmsImageStorage::store($file, 'producto_principal');

        Producto::where('id_producto', $idProducto)->update(['url_imagen' => $filename]);

        return response()->json([
            'success' => true,
            'result'  => ['message' => 'Imagen actualizada correctamente', 'url_imagen' => $filename],
        ], 200);
    }

    public function crear_imagen_HtmlEditor(Request $request)
    {
        if ($request->hasFile('image')) {
            $file       = $request->file("image");
            $ext        = $file->getClientOriginalExtension();
            $filename_  = time() . '.' . $ext;
            $filename   = \App\Support\CmsImageStorage::store($file, 'ckeditor');

            return response()->json([
                'filename' => $filename_,
                'url' => $filename,
                'message' => 'Imagen subida con 茅xito',
            ], 200);
        }

        return response()->json([
            'message' => 'No se encontr贸 el archivo de imagen.',
        ], 400);
    }

    public function obtener_dimension_producto()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_PRODUCTO_OBTENER_DIMENSION()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    private function nextFichaTecnicaId(): int
    {
        return (int) DB::table('administracion_producto_ficha_tecnica')->max('id_ficha_tecnica') + 1;
    }

    /**
     * color, tallas_disponibles, tallas_variantes y colores_disponibles.
     * Si hay variantes por talla, sincroniza precio (mínimo) y stock (suma).
     */
    private function applyEcommerceCatalogFields(Request $request, array &$datos): void
    {
        if (Schema::hasColumn('administracion_producto', 'color') && $request->has('color')) {
            $datos['color'] = $request->input('color') ?: null;
        }
        if (Schema::hasColumn('administracion_producto', 'tallas_disponibles') && $request->has('tallas_disponibles')) {
            // Permitir vacío: producto sin tallas (precio/stock únicos)
            $datos['tallas_disponibles'] = (string) ($request->input('tallas_disponibles') ?? '');
        }
        if (Schema::hasColumn('administracion_producto', 'colores_disponibles') && $request->has('colores_disponibles')) {
            $raw = $request->input('colores_disponibles');
            if (is_array($raw)) {
                $datos['colores_disponibles'] = json_encode($raw, JSON_UNESCAPED_UNICODE);
            } elseif (is_string($raw)) {
                // Permitir "[]" (sin colores)
                $datos['colores_disponibles'] = $raw !== '' ? $raw : json_encode([]);
            } else {
                $datos['colores_disponibles'] = json_encode([]);
            }
        }

        if (! Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            return;
        }

        if (! $request->has('tallas_variantes') && ! $request->has('tallas_disponibles')) {
            return;
        }

        $rawVar = $request->input('tallas_variantes');
        if (is_string($rawVar) && $rawVar !== '') {
            $decoded = json_decode($rawVar, true);
            $rawVar = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($rawVar)) {
            $rawVar = [];
        }

        // Si solo vienen nombres CSV (no vacío), armar variantes con precio/stock del request
        if (empty($rawVar) && $request->filled('tallas_disponibles')) {
            $names = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('tallas_disponibles')))));
            $precioBase = (float) ($request->input('precio') ?? ($datos['precio'] ?? 0));
            $stockBase = max(0, (int) ($request->input('stock') ?? ($datos['stock'] ?? 0)));
            foreach ($names as $name) {
                $rawVar[] = ['talla' => $name, 'precio' => $precioBase, 'stock' => $stockBase];
            }
        }

        $variantes = ProductoEcommerceCatalog::normalizarTallasVariantes($rawVar, (object) [
            'precio' => $request->input('precio') ?? ($datos['precio'] ?? 0),
        ]);

        // Sin tallas: producto independiente con precio/stock base
        if (empty($variantes)) {
            $datos['tallas_variantes'] = json_encode([]);
            $datos['tallas_disponibles'] = '';
            return;
        }

        $datos['tallas_variantes'] = json_encode($variantes, JSON_UNESCAPED_UNICODE);
        $datos['tallas_disponibles'] = implode(',', array_map(static fn ($v) => $v['talla'], $variantes));

        $precios = array_map(static fn ($v) => (float) $v['precio'], $variantes);
        $stocks = array_map(static fn ($v) => (int) $v['stock'], $variantes);
        $datos['precio'] = round(min($precios), 2);
        $datos['stock'] = array_sum($stocks);
    }

    /**
     * Marca cada producto con en_home según web_home_coleccion (colección principal del home).
     *
     * @param  iterable<int, object>  $items
     * @return array<int, object>
     */
    private function enrichProductsWithEnHome(iterable $items): array
    {
        $enHomeIds = [];
        if (Schema::hasTable('web_home_coleccion')) {
            $enHomeIds = WebHomeColeccion::where('Activo', 'S')
                ->pluck('id_producto')
                ->flip()
                ->all();
        }

        $result = [];
        $productIds = [];
        foreach ($items as $item) {
            $id = $item->id_producto ?? null;
            $item->en_home = $id !== null && isset($enHomeIds[$id]);
            $item->promo_asignaciones = [];
            if ($id !== null) {
                $productIds[] = (int) $id;
            }
            $result[] = $item;
        }

        $productIds = array_values(array_unique(array_filter($productIds)));
        if (empty($productIds)
            || ! Schema::hasTable('web_promo_bloque')
            || ! Schema::hasTable('web_promo_item')) {
            return $result;
        }

        $asignaciones = [];

        // Asignaciones preferidas: producto → submódulo → bloque.
        if (Schema::hasTable('web_promo_item_producto')) {
            $rows = DB::table('web_promo_item_producto as pip')
                ->join('web_promo_item as pi', 'pip.id_promo_item', '=', 'pi.id_promo_item')
                ->join('web_promo_bloque as pb', 'pi.id_promo_bloque', '=', 'pb.id_promo_bloque')
                ->whereIn('pip.id_producto', $productIds)
                ->select(array_values(array_filter([
                    'pip.id_producto',
                    'pb.id_promo_bloque',
                    'pb.titulo_seccion',
                    'pb.subtitulo_seccion',
                    'pi.id_promo_item',
                    'pi.nombre as nombre_item',
                    'pi.categoria as categoria_item',
                    Schema::hasColumn('web_promo_item', 'tipo_oferta') ? 'pi.tipo_oferta' : null,
                    Schema::hasColumn('web_promo_item', 'descuento_porcentaje') ? 'pi.descuento_porcentaje' : null,
                    Schema::hasColumn('web_promo_item', 'multiplicador') ? 'pi.multiplicador' : null,
                    Schema::hasColumn('web_promo_item', 'precio') ? 'pi.precio as precio_oferta' : null,
                ])))
                ->orderBy('pb.orden')
                ->orderBy('pi.orden')
                ->get();

            foreach ($rows as $row) {
                $idProducto = (int) $row->id_producto;
                $key = (int) $row->id_promo_bloque . ':' . (int) $row->id_promo_item;
                $asignaciones[$idProducto][$key] = $this->mapPromoAsignacionRow($row);
            }
        }

        // Compatibilidad con asignaciones antiguas guardadas directamente en web_promo_item.id_producto.
        $legacyRows = DB::table('web_promo_item as pi')
            ->join('web_promo_bloque as pb', 'pi.id_promo_bloque', '=', 'pb.id_promo_bloque')
            ->whereIn('pi.id_producto', $productIds)
            ->select(array_values(array_filter([
                'pi.id_producto',
                'pb.id_promo_bloque',
                'pb.titulo_seccion',
                'pb.subtitulo_seccion',
                'pi.id_promo_item',
                'pi.nombre as nombre_item',
                'pi.categoria as categoria_item',
                Schema::hasColumn('web_promo_item', 'tipo_oferta') ? 'pi.tipo_oferta' : null,
                Schema::hasColumn('web_promo_item', 'descuento_porcentaje') ? 'pi.descuento_porcentaje' : null,
                Schema::hasColumn('web_promo_item', 'multiplicador') ? 'pi.multiplicador' : null,
                Schema::hasColumn('web_promo_item', 'precio') ? 'pi.precio as precio_oferta' : null,
            ])))
            ->orderBy('pb.orden')
            ->orderBy('pi.orden')
            ->get();

        foreach ($legacyRows as $row) {
            $idProducto = (int) $row->id_producto;
            $key = (int) $row->id_promo_bloque . ':' . (int) $row->id_promo_item;
            $asignaciones[$idProducto][$key] = $this->mapPromoAsignacionRow($row);
        }

        // Si el producto está vinculado únicamente al bloque, mostrar el bloque sin submódulo.
        if (Schema::hasTable('web_promo_bloque_producto')) {
            $blockRows = DB::table('web_promo_bloque_producto as pbp')
                ->join('web_promo_bloque as pb', 'pbp.id_promo_bloque', '=', 'pb.id_promo_bloque')
                ->whereIn('pbp.id_producto', $productIds)
                ->select([
                    'pbp.id_producto',
                    'pb.id_promo_bloque',
                    'pb.titulo_seccion',
                    'pb.subtitulo_seccion',
                ])
                ->orderBy('pb.orden')
                ->get();

            foreach ($blockRows as $row) {
                $idProducto = (int) $row->id_producto;
                $idBloque = (int) $row->id_promo_bloque;
                $hasItemInBlock = collect($asignaciones[$idProducto] ?? [])
                    ->contains(fn ($a) => (int) $a['id_promo_bloque'] === $idBloque);
                if (! $hasItemInBlock) {
                    $asignaciones[$idProducto][$idBloque . ':0'] = [
                        'id_promo_bloque' => $idBloque,
                        'bloque' => trim((string) ($row->subtitulo_seccion ?: $row->titulo_seccion ?: 'Bloque')),
                        'id_promo_item' => null,
                        'submodulo' => null,
                    ];
                }
            }
        }

        foreach ($result as $item) {
            $id = (int) ($item->id_producto ?? 0);
            $item->promo_asignaciones = array_values($asignaciones[$id] ?? []);
        }

        return $result;
    }

    /** @param  object  $row */
    private function mapPromoAsignacionRow($row): array
    {
        $tipo = strtolower(trim((string) ($row->tipo_oferta ?? 'pack')));
        $pct = isset($row->descuento_porcentaje) ? (float) $row->descuento_porcentaje : 0.0;
        $ofertaLabel = null;
        if ($tipo === 'porcentaje' && $pct > 0) {
            $ofertaLabel = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.') . '% DSCTO';
        } elseif ($tipo === 'pack') {
            $mult = trim((string) ($row->multiplicador ?? ''));
            $precioOferta = isset($row->precio_oferta) ? (float) $row->precio_oferta : 0.0;
            if ($mult !== '' && $precioOferta > 0) {
                $ofertaLabel = $mult . ' · S/ ' . number_format($precioOferta, 2);
            } elseif ($mult !== '') {
                $ofertaLabel = $mult;
            }
        }

        return [
            'id_promo_bloque' => (int) $row->id_promo_bloque,
            'bloque' => trim((string) ($row->subtitulo_seccion ?: $row->titulo_seccion ?: 'Bloque')),
            'id_promo_item' => isset($row->id_promo_item) ? (int) $row->id_promo_item : null,
            'submodulo' => trim((string) ($row->nombre_item ?: $row->categoria_item ?: 'Submódulo')),
            'tipo_oferta' => $tipo,
            'descuento_porcentaje' => $pct > 0 ? $pct : null,
            'oferta_label' => $ofertaLabel,
        ];
    }

    /** True si el producto está vinculado a un submódulo/bloque de ofertas. */
    private function productoTieneAsignacionBloque(int $idProducto): bool
    {
        if ($idProducto <= 0) {
            return false;
        }
        if (Schema::hasTable('web_promo_item_producto')) {
            if (DB::table('web_promo_item_producto')->where('id_producto', $idProducto)->exists()) {
                return true;
            }
        }
        if (Schema::hasTable('web_promo_item') && Schema::hasColumn('web_promo_item', 'id_producto')) {
            if (DB::table('web_promo_item')->where('id_producto', $idProducto)->exists()) {
                return true;
            }
        }
        if (Schema::hasTable('web_promo_bloque_producto')) {
            if (DB::table('web_promo_bloque_producto')->where('id_producto', $idProducto)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Borra un archivo de imagen relativo si existe (disco public_imagenes o ruta legacy). Ignora rutas vacías o URL absolutas.
     */
    private function eliminarArchivoProductoDesdeDisco(?string $relativePath): void
    {
        if ($relativePath === null) {
            return;
        }
        $relativePath = trim(str_replace('\\', '/', (string) $relativePath));
        if ($relativePath === '') {
            return;
        }
        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return;
        }
        $relativePath = ltrim($relativePath, '/');

        try {
            \App\Support\CmsImageStorage::deleteIfOwned($relativePath);
        } catch (\Throwable $e) {
            Log::channel('stderr')->info('eliminarArchivoProductoDesdeDisco storage: ' . $e->getMessage());
        }

        try {
            if (str_contains($relativePath, 'storage_/media/')) {
                return;
            }
            $legacy = Helper::getPublicHtmlPath($relativePath);
            if ($legacy !== '' && @is_file($legacy)) {
                @unlink($legacy);
            }
        } catch (\Throwable $e) {
            Log::channel('stderr')->info('eliminarArchivoProductoDesdeDisco legacy: ' . $e->getMessage());
        }
    }
}