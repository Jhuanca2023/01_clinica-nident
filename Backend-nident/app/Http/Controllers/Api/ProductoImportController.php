<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\ProductoImport;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoCategoriaSub;
use App\Support\ProductoImagePath;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductoImportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/producto/importar",
     *     tags={"Productos"},
     *     summary="Importar productos desde un archivo Excel (xlsx, xls, csv)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *         required={"archivo_excel"},
     *         @OA\Property(property="archivo_excel", type="string", format="binary", description="Archivo Excel con cabeceras: codigo_producto, codigo_barra, nombre_producto, nombre_categoria, nombre_sub_categoria, descripcion, ubicacion, precio_venta, stock, precio_costo, stock_minimo, peso_kilogramos, imagen_principal, imagen_segundaria, precio_mayorista, precio_anterior")
     *     ))),
     *     @OA\Response(response=200, description="Productos importados correctamente"),
     *     @OA\Response(response=422, description="Error de validación o archivo vacío")
     * )
     */
    public function importar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $archivo = $request->file('archivo_excel');
            $data = Excel::toArray(new ProductoImport(), $archivo);

            if (empty($data) || empty($data[0])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no contiene filas de datos',
                    'result' => []
                ], 422);
            }

            $rows = $data[0];
            $imported = [];
            $errors = [];
            $noImportados = []; // Código duplicado u otro motivo
            $codigosEnBatch = []; // Para no duplicar dentro del mismo Excel

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // 1-based + header

                $nombreCategoria    = $this->getRowValue($row, ['categoria', 'nombre_categoria', 'nombre categoria']);
                $nombreSubCategoria = $this->getRowValue($row, ['sub_categoria', 'nombre_sub_categoria', 'nombre sub categoria']);
                $codigoProducto     = $this->getRowValue($row, ['codigo_producto', 'codigo producto']);
                $codigoBarra        = $this->getRowValue($row, ['codigo_barra', 'codigo barra']);
                $nombre             = $this->getRowValue($row, ['nombre_producto', 'nombre']);
                $descripcion        = $this->getRowValue($row, ['descripcion']);
                $ubicacion          = $this->getRowValue($row, ['ubicación', 'ubicacion']);
                $precioCosto        = $this->parseDecimal($this->getRowValue($row, ['precio_costo', 'precio-costo']));
                $precioNormal       = $this->parseDecimal($this->getRowValue($row, ['precio_anterior', 'precio_normal', 'precio-normal']));
                $precioVenta        = $this->parseDecimal($this->getRowValue($row, ['precio_venta', 'precio_descuento', 'precio-descuento', 'precio descuento']));
                $precioMayorista    = $this->parseDecimal($this->getRowValue($row, ['precio_mayorista', 'precio-mayorista']));
                $stock              = $this->getRowValue($row, ['stock']);
                $stockMinimo        = $this->parseDecimal($this->getRowValue($row, ['stock_minimo', 'stock minimo']));
                $pesoKilogramos     = $this->parseDecimal($this->getRowValue($row, ['peso_kilogramos', 'peso_kilogramo', 'peso kilogramos']));
                $imagenPrincipal    = $this->getRowValue($row, ['imagen_principal', 'imagen principal']);
                $imagenSegundaria   = $this->getRowValue($row, [
                    'imagen_segundaria', 'imagen segundaria', 'imagen_secundaria', 'imagen secundaria',
                    'imagen_secundarias', 'imagenes_secundarias',
                ]);

                if (empty(trim((string) $nombre)) && empty(trim((string) $codigoProducto))) {
                    continue;
                }

                // Código que viene del Excel (se guarda en codigo_producto_new)
                $codigoProductoFromExcel = $codigoProducto;
                if (is_numeric($codigoProducto) && (float) $codigoProducto > 1e10) {
                    $codigoProductoFromExcel = (string) (int) (float) $codigoProducto;
                }
                $codigoProductoFromExcel = trim((string) $codigoProductoFromExcel) !== '' ? $codigoProductoFromExcel : null;

                // Validar que codigo_producto_new (código del Excel) no esté duplicado en BD o en el mismo archivo
                if ($codigoProductoFromExcel !== null) {
                    $yaExisteEnBd = Producto::where('codigo_producto_new', $codigoProductoFromExcel)->exists();
                    $yaEnBatch = isset($codigosEnBatch[$codigoProductoFromExcel]);
                    if ($yaExisteEnBd || $yaEnBatch) {
                        $noImportados[] = [
                            'fila'                 => $rowNumber,
                            'codigo_producto'      => $codigoProductoFromExcel,
                            'codigo_barra'         => $codigoBarra,
                            'nombre'               => $nombre ?: '-',
                            'ubicacion'            => $ubicacion,
                            'descripcion'          => $descripcion,
                            'precio_costo'         => $precioCosto,
                            'precio_normal'        => $precioNormal,
                            'precio_venta'         => $precioVenta,
                            'precio_mayorista'     => $precioMayorista,
                            'stock'                => $stock,
                            'stock_minimo'         => $stockMinimo,
                            'nombre_categoria'     => $nombreCategoria,
                            'nombre_sub_categoria' => $nombreSubCategoria,
                            'motivo'               => 'Código duplicado',
                        ];
                        continue;
                    }
                    $codigosEnBatch[$codigoProductoFromExcel] = true;
                }

                //============= CODIGO DE PRODUCTO (aleatorio único, como en ProductoController::crear) ===============
                do {
                    $codigo_producto = 'P-' . Str::upper(Str::random(6));
                } while (DB::table('administracion_producto')->where('codigo_producto', $codigo_producto)->exists());

                $id_producto_categoria = 1;
                if (!empty(trim((string) $nombreCategoria))) {
                    $cat = ProductoCategoria::firstOrCreate(
                        ['nombre' => trim($nombreCategoria)],
                        ['Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]
                    );
                    $id_producto_categoria = $cat->id_producto_categoria;
                }

                $id_producto_categoria_sub = null;
                if (!empty(trim((string) $nombreSubCategoria))) {
                    $sub = ProductoCategoriaSub::firstOrCreate(
                        [
                            'nombre' => trim($nombreSubCategoria),
                            'id_producto_categoria' => $id_producto_categoria
                        ],
                        ['Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]
                    );
                    $id_producto_categoria_sub = $sub->id_producto_categoria_sub;
                }

                try {
                    $producto = Producto::create([
                        'codigo_producto'           => $codigo_producto,
                        'codigo_producto_new'       => $codigoProductoFromExcel,
                        'codigo_barra'              => $codigoBarra !== null && trim((string)$codigoBarra) !== '' ? trim((string)$codigoBarra) : null,
                        'nombre'                    => $nombre ?: '-',
                        'descripcion'               => $descripcion ?: null,
                        'ubicacion'                 => $ubicacion !== null && trim((string)$ubicacion) !== '' ? trim((string)$ubicacion) : null,
                        'precio'                    => $precioVenta !== null ? $precioVenta : 0,
                        'precio_old'                => $precioNormal !== null ? (string) $precioNormal : null,
                        'precio_costo'              => $precioCosto !== null ? $precioCosto : null,
                        'precio_mayorista'          => $precioMayorista !== null ? $precioMayorista : null,
                        'stock'                     => $stock !== null && $stock !== '' ? (string) $stock : '0',
                        'stock_minimo'              => $stockMinimo !== null ? $stockMinimo : null,
                        'peso_kilogramo'            => $pesoKilogramos !== null ? $pesoKilogramos : null,
                        'url_imagen'                => $imagenPrincipal !== null && trim((string)$imagenPrincipal) !== ''
                            ? ProductoImagePath::normalize(trim((string)$imagenPrincipal))
                            : null,
                        'id_producto_categoria'     => $id_producto_categoria,
                        'id_producto_categoria_sub' => $id_producto_categoria_sub,
                        'id_producto_tipo'          => 2,
                        'tallas_disponibles'        => 'S,M,L,XL,XXL',
                        'colores_disponibles'       => json_encode(\App\Support\ProductoEcommerceCatalog::paletaColoresPorDefecto(), JSON_UNESCAPED_UNICODE),
                        'created_at'                => now(),
                        'updated_at'                => now(),
                        'Activo'                    => 'S',
                    ]);

                    $imported[] = $producto->toArray();

                    // ── Imágenes secundarias → administracion_producto_fotos ──
                    if ($imagenSegundaria !== null && trim((string) $imagenSegundaria) !== '') {
                        $rutasSecundarias = $this->partirRutasImagenesSecundariasDesdeExcel((string) $imagenSegundaria);
                        foreach ($rutasSecundarias as $orden => $ruta) {
                            DB::table('administracion_producto_fotos')->insert([
                                'id_producto' => $producto->id_producto,
                                'url_imagen'  => ProductoImagePath::normalize($ruta),
                                'orden'       => $orden + 1,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                    $noImportados[] = [
                        'fila'                 => $rowNumber,
                        'codigo_producto'      => $codigoProductoFromExcel ?? ($codigo_producto ?? null),
                        'codigo_barra'         => $codigoBarra,
                        'nombre'               => $nombre ?: '-',
                        'ubicacion'            => $ubicacion,
                        'descripcion'          => $descripcion,
                        'precio_costo'         => $precioCosto,
                        'precio_normal'        => $precioNormal,
                        'precio_venta'         => $precioVenta,
                        'precio_mayorista'     => $precioMayorista,
                        'stock'                => $stock,
                        'stock_minimo'         => $stockMinimo,
                        'nombre_categoria'     => $nombreCategoria,
                        'nombre_sub_categoria' => $nombreSubCategoria,
                        'motivo'               => $e->getMessage(),
                    ];
                    Log::channel('stderr')->error("Importar producto fila {$rowNumber}: " . $e->getMessage());
                }
            }

            DB::commit();

            $totalImportados = count($imported);
            $totalFallidos = count($noImportados);

            return response()->json([
                'success' => true,
                'message' => $totalImportados . ' producto(s) importado(s).' . ($totalFallidos ? ' No importados: ' . $totalFallidos . ' (código duplicado u otro error).' : ''),
                'result' => $imported,
                'no_importados' => $noImportados,
                'total_importados' => $totalImportados,
                'total_fallidos' => $totalFallidos,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel()) {
                DB::rollBack();
            }
            Log::channel('stderr')->error('Producto importar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo',
                'error' => $e->getMessage(),
                'result' => []
            ], 500);
        }
    }

    // =========================================================================
    //  IMPORTACIÓN MASIVA DE IMÁGENES DESDE ZIP
    // =========================================================================

    /**
     * Recibe un .zip con la estructura:
     *   principales/  → {codigo}.ext → storage_/producto_principal/{codigo}.ext
     *   secundarias/  → {codigo}_N.ext → storage_/producto_principal_fotos/{codigo}_N.ext
     */
    public function importarImagenes(Request $request): JsonResponse
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $validator = Validator::make($request->all(), [
            'archivo_zip' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Se requiere un archivo ZIP.'], 422);
        }

        $archivo = $request->file('archivo_zip');
        $ext     = strtolower($archivo->getClientOriginalExtension());
        if ($ext !== 'zip') {
            return response()->json(['success' => false, 'message' => 'Solo se aceptan archivos .zip.'], 422);
        }

        $tempPath = storage_path('app/temp_img_' . uniqid());

        try {
            // 1. Extraer ZIP
            $zip = new \ZipArchive();
            if ($zip->open($archivo->getRealPath()) !== true) {
                return response()->json(['success' => false, 'message' => 'No se pudo abrir el archivo ZIP.'], 422);
            }
            $zip->extractTo($tempPath);
            $zip->close();

            $extensiones       = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $principalesDirs   = $this->findAllImageSubdirs($tempPath, 'principales');
            $secundariasDirs   = array_merge(
                $this->findAllImageSubdirs($tempPath, 'secundarias'),
                $this->findAllImageSubdirs($tempPath, 'secundaria')
            );

            $resPrincipales = ['importadas' => 0, 'no_encontradas' => [], 'errores' => []];
            $resSecundarias = [
                'importadas'       => 0,
                'no_encontradas'   => [],
                'errores'          => [],
                'sin_registro_bd'  => [],
            ];

            // 2. Procesar imágenes principales
            if (!empty($principalesDirs)) {
                foreach ($principalesDirs as $principalesDir) {
                    foreach ($this->getImageFiles($principalesDir, $extensiones) as $file) {
                        $imgExt  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $codigo  = pathinfo($file, PATHINFO_FILENAME);
                        $codigo  = is_string($codigo) ? trim($codigo) : $codigo;

                        $producto = $this->findProductoPorCodigoZip($codigo);
                        if (!$producto) {
                            $resPrincipales['no_encontradas'][] = $codigo;
                            continue;
                        }

                        $this->eliminarArchivoImagenDisco($producto->url_imagen);
                        $contents = (string) file_get_contents($file);
                        if ($contents !== '') {
                            $producto->url_imagen = \App\Support\CmsImageStorage::put('producto_principal', $contents, $imgExt);
                            $producto->save();
                            $resPrincipales['importadas']++;
                        } else {
                            $resPrincipales['errores'][] = $codigo;
                        }
                    }
                }
            }

            // 3. Secundarias: solo actualiza archivos y BD si ya existe la fila (Excel/admin). No inserta registros nuevos.
            if (!empty($secundariasDirs)) {
                $productMap = $this->buildProductMapParaImagenesZip();

                foreach ($secundariasDirs as $secundariasDir) {
                    foreach ($this->getImageFiles($secundariasDir, $extensiones) as $file) {
                        $stem = $this->normalizeZipImageStem((string) pathinfo($file, PATHINFO_FILENAME));
                        $idProducto = $this->resolverIdProductoParaFotoSecundaria($stem, $productMap);
                        if ($idProducto === null) {
                            $resSecundarias['no_encontradas'][] = $stem !== '' ? $stem : basename($file);
                            continue;
                        }

                        $destRel = ProductoImagePath::normalize(
                            ProductoImagePath::DIR_FOTOS . '/' . $this->sanitizarNombreArchivoFotoDesdeZip(basename($file))
                        );

                        $fila = $this->buscarFilaFotoSecundariaExistente((int) $idProducto, $destRel);
                        if (!$fila) {
                            $resSecundarias['sin_registro_bd'][] = basename($file) . ' (id_producto ' . $idProducto . ')';
                            continue;
                        }

                        $this->eliminarArchivoImagenDisco($fila->url_imagen ?? null);
                        $imgExt = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?: 'jpg');
                        $contents = (string) file_get_contents($file);
                        if ($contents === '') {
                            $resSecundarias['errores'][] = basename($file);
                            continue;
                        }
                        $destRel = \App\Support\CmsImageStorage::put('producto_principal_fotos', $contents, $imgExt);

                        DB::table('administracion_producto_fotos')
                            ->where('id_producto_foto', $fila->id_producto_foto)
                            ->update([
                                'url_imagen'  => $destRel,
                                'updated_at'  => now(),
                            ]);
                        $resSecundarias['importadas']++;
                    }
                }
            }

            $this->deleteDirectory($tempPath);

            $totalImportadas    = $resPrincipales['importadas'] + $resSecundarias['importadas'];
            $totalNoEncontradas = count($resPrincipales['no_encontradas']) + count($resSecundarias['no_encontradas']);
            $nSinFilaBd         = count($resSecundarias['sin_registro_bd']);

            $msg = "{$totalImportadas} imagen(es) actualizadas en disco/BD.";
            if ($totalNoEncontradas > 0) {
                $msg .= " {$totalNoEncontradas} sin producto coincidente en BD.";
            }
            if ($nSinFilaBd > 0) {
                $msg .= " {$nSinFilaBd} secundaria(s) del ZIP sin fila previa en BD (no se crean registros; importe rutas desde Excel o cree la galería antes).";
            }

            return response()->json([
                'success'        => true,
                'message'        => $msg,
                'principales'    => $resPrincipales,
                'secundarias'    => $resSecundarias,
            ]);

        } catch (\Exception $e) {
            $this->deleteDirectory($tempPath);
            Log::channel('stderr')->error('ImportarImagenes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el ZIP: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── Helpers internos ─────────────────────────────────────────────────────

    /**
     * Coincide con el código mostrado en importación (codigo_producto_new / Excel)
     * o con el código interno único (codigo_producto, ej. P-XXXXXX).
     */
    private function findProductoPorCodigoZip(string $codigo): ?Producto
    {
        if ($codigo === '') {
            return null;
        }

        return Producto::where('codigo_producto_new', $codigo)->first()
            ?? Producto::where('codigo_producto', $codigo)->first();
    }

    /**
     * Mapa código (nombre de archivo) → id_producto para secundarias.
     * Incluye SKU (codigo_producto_new) y código interno (codigo_producto).
     */
    private function buildProductMapParaImagenesZip(): array
    {
        $porCodigoInterno = Producto::query()
            ->whereNotNull('codigo_producto')
            ->where('codigo_producto', '!=', '')
            ->pluck('id_producto', 'codigo_producto')
            ->all();

        $porCodigoUsuario = Producto::query()
            ->whereNotNull('codigo_producto_new')
            ->where('codigo_producto_new', '!=', '')
            ->pluck('id_producto', 'codigo_producto_new')
            ->all();

        // Si hubiera la misma clave en ambos (muy improbable), gana codigo_producto_new
        $merged = array_merge($porCodigoInterno, $porCodigoUsuario);
        $normalized = [];
        foreach ($merged as $code => $id) {
            $normalized[trim((string) $code)] = (int) $id;
        }

        return $normalized;
    }

    /**
     * Elimina un archivo de imagen relativo al proyecto (disco public_imagenes o ruta legacy public_html).
     */
    private function eliminarArchivoImagenDisco(?string $relativePath): void
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
        \App\Support\CmsImageStorage::deleteIfOwned($relativePath);

        $legacy = Helper::getPublicHtmlPath($relativePath);
        if ($legacy !== '' && @is_file($legacy) && !str_contains($relativePath, 'storage_/media/')) {
            @unlink($legacy);
        }
    }

    /**
     * Fila existente en administracion_producto_fotos: misma ruta normalizada o mismo nombre de archivo.
     */
    private function buscarFilaFotoSecundariaExistente(int $idProducto, string $destRel): ?object
    {
        $destNorm = ProductoImagePath::normalize($destRel) ?? $destRel;

        $fila = DB::table('administracion_producto_fotos')
            ->where('id_producto', $idProducto)
            ->where('url_imagen', $destNorm)
            ->first();
        if ($fila) {
            return $fila;
        }

        $fila = DB::table('administracion_producto_fotos')
            ->where('id_producto', $idProducto)
            ->where('url_imagen', $destRel)
            ->first();
        if ($fila) {
            return $fila;
        }

        $base = basename(str_replace('\\', '/', $destNorm));
        if ($base === '' || $base === '.') {
            return null;
        }

        $candidatos = DB::table('administracion_producto_fotos')
            ->where('id_producto', $idProducto)
            ->get(['id_producto_foto', 'url_imagen']);

        foreach ($candidatos as $row) {
            $dbBase = basename(str_replace('\\', '/', (string) ($row->url_imagen ?? '')));
            if ($dbBase !== '' && strcasecmp($dbBase, $base) === 0) {
                return DB::table('administracion_producto_fotos')
                    ->where('id_producto_foto', $row->id_producto_foto)
                    ->first();
            }
        }

        return null;
    }

    /**
     * Todas las carpetas con el nombre dado (p. ej. principales / secundarias), ignorando __MACOSX.
     */
    private function findAllImageSubdirs(string $base, string $folderName): array
    {
        $found = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        $needle = strtolower($folderName);
        foreach ($it as $item) {
            if (!$item->isDir()) {
                continue;
            }
            if (strtolower($item->getFilename()) !== $needle) {
                continue;
            }
            $path = str_replace('\\', '/', $item->getPathname());
            if (preg_match('/(^|\\/)__MACOSX(\\/|$)/i', $path)) {
                continue;
            }
            $found[] = $item->getPathname();
        }

        return $found;
    }

    private function normalizeZipImageStem(string $stem): string
    {
        $stem = trim($stem);
        if (str_starts_with($stem, "\xEF\xBB\xBF")) {
            $stem = substr($stem, 3);
        }

        return trim($stem);
    }

    /**
     * CODIGO_1 / CODIGO-2 → id_producto; mismo criterio de códigos que la imagen principal.
     */
    private function resolverIdProductoParaFotoSecundaria(string $stem, array $productMap): ?int
    {
        $stem = $this->normalizeZipImageStem($stem);
        if ($stem === '') {
            return null;
        }

        $base = $this->extraerBaseNombreFotoSiTerminaEnIndice($stem);
        if ($base !== null) {
            $id = $this->buscarIdEnMapaCodigoProducto($productMap, $base);
            if ($id !== null) {
                return $id;
            }
            $p = $this->findProductoPorCodigoZip($base);
            if ($p) {
                return (int) $p->id_producto;
            }
        }

        $codigoExtract = $this->extractCodigo($stem, array_keys($productMap));
        if ($codigoExtract !== null) {
            $id = $this->buscarIdEnMapaCodigoProducto($productMap, $codigoExtract);
            if ($id !== null) {
                return $id;
            }
        }

        return null;
    }

    private function extraerBaseNombreFotoSiTerminaEnIndice(string $stem): ?string
    {
        foreach (['_', '-'] as $sep) {
            $pos = strrpos($stem, $sep);
            if ($pos === false) {
                continue;
            }
            $suffix = substr($stem, $pos + 1);
            if ($suffix !== '' && ctype_digit($suffix)) {
                $base = substr($stem, 0, $pos);

                return $base !== '' ? $base : null;
            }
        }

        return null;
    }

    private function buscarIdEnMapaCodigoProducto(array $productMap, string $base): ?int
    {
        $base = trim($base);
        if ($base === '') {
            return null;
        }
        foreach ($productMap as $codigo => $id) {
            if ((string) $codigo === $base) {
                return (int) $id;
            }
        }
        if (ctype_digit($base)) {
            $norm = (string) (int) $base;
            foreach ($productMap as $codigo => $id) {
                if ((string) $codigo === $norm) {
                    return (int) $id;
                }
            }
        }

        return null;
    }

    /**
     * Varias rutas en una celda Excel: coma, punto y coma, saltos de línea, coma ancha UTF-8 (U+FF0C).
     */
    private function partirRutasImagenesSecundariasDesdeExcel(string $raw): array
    {
        $s = trim($raw);
        if ($s === '') {
            return [];
        }
        $s = str_replace(
            ["\xEF\xBC\x8C", "\xEF\xBC\x9B"],
            [',', ';'],
            $s
        );
        $s = preg_replace('/\R+/u', ',', $s);
        $parts = preg_split('/\s*[,;]\s*/u', $s, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($parts as $p) {
            $t = trim($p);
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return array_values($out);
    }

    /**
     * Corrige rutas legacy y tipografías (p. ej. producto_principal/… → storage_/producto_principal/…).
     */
    private function normalizarRutaRelativaImagen(string $ruta): string
    {
        return ProductoImagePath::normalize($ruta) ?? '';
    }

    /**
     * Nombre de archivo seguro a partir del código de producto (coincide con plantilla Excel / ZIP).
     */
    private function sanitizarNombreArchivoCodigoProducto(string $codigo): string
    {
        $codigo = trim($codigo);
        $codigo = preg_replace('/[^A-Za-z0-9._-]/', '_', $codigo);

        return $codigo !== '' ? $codigo : 'sin_codigo';
    }

    /**
     * Nombre final bajo storage_/producto_principal_fotos/ respetando el nombre del ZIP (p. ej. 120_1.jpg).
     */
    private function sanitizarNombreArchivoFotoDesdeZip(string $basename): string
    {
        $basename = basename(str_replace('\\', '/', $basename));
        $stem = pathinfo($basename, PATHINFO_FILENAME);
        $ext  = strtolower((string) pathinfo($basename, PATHINFO_EXTENSION));
        $ext  = preg_replace('/[^a-z0-9]/', '', $ext);
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if ($ext === '' || !in_array($ext, $allowed, true)) {
            $ext = 'jpg';
        }

        return $this->sanitizarNombreArchivoCodigoProducto((string) $stem) . '.' . $ext;
    }

    private function getImageFiles(string $dir, array $extensions): array
    {
        $files = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($it as $item) {
            if (!$item->isFile()) {
                continue;
            }
            $baseName = $item->getBasename();
            // Archivos de metadatos de macOS dentro del ZIP (._imagen.jpg)
            if (str_starts_with($baseName, '._')) {
                continue;
            }
            if (in_array(strtolower($item->getExtension()), $extensions)) {
                $files[] = $item->getPathname();
            }
        }
        return $files;
    }

    private function extractCodigo(string $filename, array $codigos): ?string
    {
        foreach ($codigos as $codigo) {
            if ((string) $codigo === (string) $filename) {
                return (string) $codigo;
            }
        }
        if (preg_match('/^(.+)_\d+$/', $filename, $m)) {
            foreach ($codigos as $codigo) {
                if ((string) $codigo === (string) $m[1]) {
                    return (string) $codigo;
                }
            }
        }
        $best = null;
        $bestLen = 0;
        foreach ($codigos as $codigo) {
            $c = (string) $codigo;
            if ($c !== '' && str_starts_with($filename, $c) && strlen($c) > $bestLen) {
                $best    = $c;
                $bestLen = strlen($c);
            }
        }

        return $best;
    }

    private function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) return;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($path);
    }

    // ── Helpers de datos (Excel) ─────────────────────────────────────────────

    private function getRowValue(array $row, array $keys)
    {
        foreach ($keys as $key) {
            $keySlug = str_replace(' ', '_', strtolower($key));
            $keyDash = str_replace(' ', '-', strtolower($key));
            if (isset($row[$key])) {
                return $row[$key];
            }
            if (isset($row[$keySlug])) {
                return $row[$keySlug];
            }
            if (isset($row[$keyDash])) {
                return $row[$keyDash];
            }
        }
        return null;
    }

    private function parseDecimal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Normalizar: trim y quitar espacios no estándar (ej. número como texto desde Excel)
        $value = trim((string) $value);
        $value = str_replace(["\xA0", "\xC2\xA0"], '', $value); // non-breaking space
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $value = preg_replace('/[^0-9.,\-]/', '', $value);
        $value = str_replace(',', '.', $value);
        return $value === '' ? null : (float) $value;
    }
}
