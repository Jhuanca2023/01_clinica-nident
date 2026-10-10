<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Copia inmutable del producto al crear un pedido (tablas repositorio_*).
 * Las lecturas de pedido deben preferir este repositorio sobre el catálogo vivo.
 */
class PedidoProductoRepositorio
{
    public const DIR_BASE = 'storage_/repositorio';

    /**
     * Tras insertar ope_pedidos_producto: clona catálogo + imágenes + ficha.
     *
     * @param  array{talla?:string,color?:string,nombre_linea?:string,precio?:float|int|string,cantidad?:int,url_imagen?:string,descripcion?:string}  $overrides
     */
    public static function snapshotLinea(int $idPedido, int $idPedidoProducto, int $idProducto, array $overrides = []): ?int
    {
        if ($idPedido <= 0 || $idPedidoProducto <= 0 || $idProducto <= 0) {
            return null;
        }
        if (! Schema::hasTable('repositorio_producto')) {
            Log::warning('[PedidoProductoRepositorio] Tabla repositorio_producto no existe. Ejecute migraciones.');

            return null;
        }

        // Evitar duplicados si se reintenta
        $existente = DB::table('repositorio_producto')
            ->where('id_pedido_producto', $idPedidoProducto)
            ->value('id_repositorio_producto');
        if ($existente) {
            return (int) $existente;
        }

        $producto = DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->first();
        if (! $producto) {
            Log::warning('[PedidoProductoRepositorio] Producto origen no encontrado', [
                'id_producto' => $idProducto,
                'id_pedido' => $idPedido,
            ]);

            return null;
        }

        $nombreCat = null;
        $nombreSub = null;
        $idCat = $producto->id_producto_categoria ?? null;
        $idSub = $producto->id_producto_categoria_sub ?? null;

        if ($idCat && Schema::hasTable('administracion_producto_categoria')) {
            $nombreCat = DB::table('administracion_producto_categoria')
                ->where('id_producto_categoria', $idCat)
                ->value('nombre');
        }
        if ($idSub && Schema::hasTable('administracion_producto_categoria_sub')) {
            $nombreSub = DB::table('administracion_producto_categoria_sub')
                ->where('id_producto_categoria_sub', $idSub)
                ->value('nombre');
        }

        $talla = strtoupper(trim((string) ($overrides['talla'] ?? '')));
        $colorPedido = trim((string) ($overrides['color'] ?? ''));
        $nombreLinea = trim((string) ($overrides['nombre_linea'] ?? ($producto->nombre ?? '')));
        if ($talla === '' && preg_match('/\s*[·•]\s*Talla\s+(.+)$/iu', $nombreLinea, $m)) {
            $talla = trim((string) $m[1]);
        }

        $urlImagenOrigen = trim((string) (
            $overrides['url_imagen']
            ?? $producto->url_imagen
            ?? ''
        ));
        $urlImagenRepo = self::copiarArchivoARepositorio(
            $urlImagenOrigen,
            $idPedido,
            $idPedidoProducto,
            'principal'
        ) ?: ProductoImagePath::normalize($urlImagenOrigen);

        $descripcion = $overrides['descripcion'] ?? ($producto->descripcion ?? null);

        $row = [
            'id_pedido' => $idPedido,
            'id_pedido_producto' => $idPedidoProducto,
            'id_producto_origen' => $idProducto,
            'codigo_producto' => $producto->codigo_producto ?? null,
            'codigo_producto_new' => $producto->codigo_producto_new ?? null,
            'codigo_barra' => $producto->codigo_barra ?? null,
            'etiqueta_badge' => $producto->etiqueta_badge ?? null,
            'nombre' => $producto->nombre ?? null,
            'slug' => $producto->slug ?? null,
            'descripcion' => $descripcion,
            'ubicacion' => $producto->ubicacion ?? null,
            'precio' => $producto->precio ?? null,
            'precio_old' => $producto->precio_old ?? null,
            'precio_costo' => $producto->precio_costo ?? null,
            'precio_mayorista' => $producto->precio_mayorista ?? null,
            'precio_mayorista_old' => $producto->precio_mayorista_old ?? null,
            'precio_yape' => $producto->precio_yape ?? null,
            'stock' => $producto->stock ?? null,
            'stock_minimo' => $producto->stock_minimo ?? null,
            'id_producto_categoria' => $idCat,
            'id_producto_categoria_sub' => $idSub,
            'nombre_categoria' => $nombreCat,
            'nombre_subcategoria' => $nombreSub,
            'id_producto_tipo' => $producto->id_producto_tipo ?? null,
            'url_imagen' => $urlImagenRepo,
            'color' => $producto->color ?? null,
            'tallas_disponibles' => $producto->tallas_disponibles ?? null,
            'tallas_variantes' => self::asJsonString($producto->tallas_variantes ?? null),
            'colores_disponibles' => self::asJsonString($producto->colores_disponibles ?? null),
            'peso_kilogramo' => $producto->peso_kilogramo ?? null,
            'paquete_medidas' => $producto->paquete_medidas ?? null,
            'paquete_dimencion' => $producto->paquete_dimencion ?? null,
            'meta_titulo_producto' => $producto->meta_titulo_producto ?? null,
            'meta_descripcion_producto' => $producto->meta_descripcion_producto ?? null,
            'numero_estrellas' => $producto->numero_estrellas ?? null,
            'Activo' => $producto->Activo ?? null,
            'talla_pedida' => $talla !== '' ? $talla : null,
            'color_pedido' => $colorPedido !== '' ? $colorPedido : null,
            'cantidad_pedida' => isset($overrides['cantidad']) ? (int) $overrides['cantidad'] : null,
            'precio_unitario_pedido' => isset($overrides['precio']) ? (float) $overrides['precio'] : null,
            'pro_nombre_linea' => $nombreLinea !== '' ? $nombreLinea : null,
            'snapshot_json' => json_encode([
                'origen' => 'administracion_producto',
                'copiado_en' => now()->toDateTimeString(),
                'producto' => (array) $producto,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $idRepo = (int) DB::table('repositorio_producto')->insertGetId($row);

        if (Schema::hasColumn('ope_pedidos_producto', 'id_repositorio_producto')) {
            DB::table('ope_pedidos_producto')
                ->where('id_pedido_producto', $idPedidoProducto)
                ->update(['id_repositorio_producto' => $idRepo]);
        }

        self::snapshotFotos($idRepo, $idProducto, $idPedido, $idPedidoProducto);
        self::snapshotFichas($idRepo, $idProducto, $idPedido);

        return $idRepo;
    }

    /**
     * Localiza la línea recién creada y genera el snapshot.
     */
    public static function snapshotTrasCrearLinea(
        int $idPedido,
        int $idProducto,
        array $overrides = []
    ): ?int {
        $q = DB::table('ope_pedidos_producto')
            ->where('id_pedido', $idPedido)
            ->where('id_producto', $idProducto)
            ->orderByDesc('id_pedido_producto');

        if (! empty($overrides['nombre_linea'])) {
            $q->where('pro_nombre', $overrides['nombre_linea']);
        }

        $linea = $q->first();
        if (! $linea) {
            $linea = DB::table('ope_pedidos_producto')
                ->where('id_pedido', $idPedido)
                ->orderByDesc('id_pedido_producto')
                ->first();
        }
        if (! $linea) {
            return null;
        }

        return self::snapshotLinea(
            $idPedido,
            (int) $linea->id_pedido_producto,
            $idProducto > 0 ? $idProducto : (int) $linea->id_producto,
            array_merge([
                'nombre_linea' => $linea->pro_nombre ?? null,
                'precio' => $linea->pro_precio ?? null,
                'cantidad' => $linea->pro_cantidad ?? null,
                'url_imagen' => $linea->pro_url_imagen ?? null,
                'descripcion' => $linea->pro_descripcion ?? null,
            ], $overrides)
        );
    }

    /**
     * Snapshot de todas las líneas de un pedido que aún no tienen repositorio.
     */
    public static function snapshotPedidoCompleto(int $idPedido): int
    {
        if (! Schema::hasTable('repositorio_producto')) {
            return 0;
        }
        $lineas = DB::table('ope_pedidos_producto')
            ->where('id_pedido', $idPedido)
            ->orderBy('id_pedido_producto')
            ->get();
        $n = 0;
        foreach ($lineas as $linea) {
            $idProd = (int) ($linea->id_producto ?? 0);
            if ($idProd <= 0) {
                continue;
            }
            $id = self::snapshotLinea(
                $idPedido,
                (int) $linea->id_pedido_producto,
                $idProd,
                [
                    'nombre_linea' => $linea->pro_nombre ?? null,
                    'precio' => $linea->pro_precio ?? null,
                    'cantidad' => $linea->pro_cantidad ?? null,
                    'url_imagen' => $linea->pro_url_imagen ?? null,
                    'descripcion' => $linea->pro_descripcion ?? null,
                ]
            );
            if ($id) {
                $n++;
            }
        }

        return $n;
    }

    public static function obtenerPorPedido(int $idPedido)
    {
        if (! Schema::hasTable('repositorio_producto')) {
            return collect();
        }

        return DB::table('repositorio_producto')
            ->where('id_pedido', $idPedido)
            ->orderBy('id_pedido_producto')
            ->get();
    }

    public static function obtenerPorLinea(int $idPedidoProducto): ?object
    {
        if (! Schema::hasTable('repositorio_producto')) {
            return null;
        }

        return DB::table('repositorio_producto')
            ->where('id_pedido_producto', $idPedidoProducto)
            ->first();
    }

    public static function fotos(int $idRepositorioProducto)
    {
        if (! Schema::hasTable('repositorio_producto_fotos')) {
            return collect();
        }

        return DB::table('repositorio_producto_fotos')
            ->where('id_repositorio_producto', $idRepositorioProducto)
            ->orderBy('orden')
            ->get();
    }

    public static function fichas(int $idRepositorioProducto)
    {
        if (! Schema::hasTable('repositorio_producto_ficha_tecnica')) {
            return collect();
        }

        return DB::table('repositorio_producto_ficha_tecnica')
            ->where('id_repositorio_producto', $idRepositorioProducto)
            ->orderBy('orden')
            ->get();
    }

    public static function eliminarPorPedido(int $idPedido): void
    {
        if (! Schema::hasTable('repositorio_producto')) {
            return;
        }
        $ids = DB::table('repositorio_producto')
            ->where('id_pedido', $idPedido)
            ->pluck('id_repositorio_producto');
        if ($ids->isEmpty()) {
            return;
        }
        if (Schema::hasTable('repositorio_producto_fotos')) {
            DB::table('repositorio_producto_fotos')->whereIn('id_repositorio_producto', $ids)->delete();
        }
        if (Schema::hasTable('repositorio_producto_ficha_tecnica')) {
            DB::table('repositorio_producto_ficha_tecnica')->whereIn('id_repositorio_producto', $ids)->delete();
        }
        DB::table('repositorio_producto')->where('id_pedido', $idPedido)->delete();

        $dir = public_path(str_replace('/', DIRECTORY_SEPARATOR, self::DIR_BASE . '/' . $idPedido));
        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }
    }

    private static function snapshotFotos(
        int $idRepo,
        int $idProducto,
        int $idPedido,
        int $idPedidoProducto
    ): void {
        if (! Schema::hasTable('repositorio_producto_fotos')
            || ! Schema::hasTable('administracion_producto_fotos')) {
            return;
        }

        $fotos = DB::table('administracion_producto_fotos')
            ->where('id_producto', $idProducto)
            ->orderBy('orden')
            ->get();

        $now = now();
        foreach ($fotos as $i => $foto) {
            $src = trim((string) ($foto->url_imagen ?? ''));
            $dest = self::copiarArchivoARepositorio(
                $src,
                $idPedido,
                $idPedidoProducto,
                'foto_' . ($foto->id_producto_foto ?? $i)
            ) ?: ProductoImagePath::normalize($src);

            DB::table('repositorio_producto_fotos')->insert([
                'id_repositorio_producto' => $idRepo,
                'id_producto_foto_origen' => $foto->id_producto_foto ?? null,
                'url_imagen' => $dest,
                'orden' => $foto->orden ?? $i,
                'titulo' => $foto->titulo ?? null,
                'descripcion' => $foto->descripcion ?? null,
                'precio' => $foto->precio ?? null,
                'precio_old' => $foto->precio_old ?? null,
                'stock' => $foto->stock ?? null,
                'codigo_producto' => $foto->codigo_producto ?? null,
                'numero_estrellas' => $foto->numero_estrellas ?? null,
                'color' => $foto->color ?? null,
                'Activo' => $foto->Activo ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private static function snapshotFichas(int $idRepo, int $idProducto, int $idPedido): void
    {
        if (! Schema::hasTable('administracion_producto_ficha_tecnica')) {
            return;
        }

        $fichas = DB::table('administracion_producto_ficha_tecnica')
            ->where('id_producto', $idProducto)
            ->orderBy('orden')
            ->get();

        $now = now();
        foreach ($fichas as $ficha) {
            if (Schema::hasTable('repositorio_producto_ficha_tecnica')) {
                DB::table('repositorio_producto_ficha_tecnica')->insert([
                    'id_repositorio_producto' => $idRepo,
                    'id_ficha_origen' => $ficha->id_ficha_tecnica ?? null,
                    'orden' => $ficha->orden ?? 0,
                    'tipo' => $ficha->tipo ?? 'tabla',
                    'es_principal' => (bool) ($ficha->es_principal ?? false),
                    'titulo' => $ficha->titulo ?? ($ficha->nuevo_titulo ?? null),
                    'descripcion' => $ficha->descripcion ?? ($ficha->nuevo_descripcion ?? null),
                    'nuevo_caracteristicas' => self::asJsonString($ficha->nuevo_caracteristicas ?? null),
                    'nuevo_titulo' => $ficha->nuevo_titulo ?? null,
                    'nuevo_descripcion' => $ficha->nuevo_descripcion ?? null,
                    'Activo' => $ficha->Activo ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Compatibilidad con SP / vistas legacy
            if (Schema::hasTable('ope_pedidos_ficha_tecnica')) {
                DB::table('ope_pedidos_ficha_tecnica')->insert([
                    'id_pedido' => $idPedido,
                    'id_producto' => $idProducto,
                    'orden' => $ficha->orden ?? 0,
                    'titulo' => $ficha->titulo ?? ($ficha->nuevo_titulo ?? ''),
                    'descripcion' => $ficha->descripcion ?? ($ficha->nuevo_descripcion ?? ''),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private static function copiarArchivoARepositorio(
        string $source,
        int $idPedido,
        int $idPedidoProducto,
        string $tag
    ): ?string {
        $relative = ProductoImagePath::normalize($source);
        if ($relative === null || $relative === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $relative)) {
            $path = parse_url($relative, PHP_URL_PATH);
            $relative = ProductoImagePath::normalize(ltrim((string) $path, '/'));
            if ($relative === null || preg_match('#^https?://#i', $relative)) {
                return $source;
            }
        }

        $diskRoot = rtrim((string) (config('filesystems.disks.public_imagenes.root') ?: public_path()), '/\\');
        $srcPath = $diskRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        if (! is_file($srcPath)) {
            $alt = public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative));
            if (is_file($alt)) {
                $srcPath = $alt;
            } else {
                return $relative;
            }
        }

        $ext = pathinfo($srcPath, PATHINFO_EXTENSION) ?: 'jpg';
        $safeTag = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tag) ?: 'img';
        $destRel = self::DIR_BASE . '/' . $idPedido . '/' . $idPedidoProducto . '/' . $safeTag . '.' . strtolower($ext);
        $destAbs = public_path(str_replace('/', DIRECTORY_SEPARATOR, $destRel));
        $destDir = dirname($destAbs);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        if (@copy($srcPath, $destAbs)) {
            return $destRel;
        }

        return $relative;
    }

    private static function asJsonString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $str = (string) $value;
        // Ya es JSON válido
        if ((str_starts_with($str, '{') || str_starts_with($str, '[')) && json_decode($str) !== null) {
            return $str;
        }

        return $str;
    }
}
