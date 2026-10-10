<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\WebPromoBloque;
use App\Models\WebPromoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WebPromoBloqueController extends Controller
{
    /**
     * Guarda la imagen con nombre único (timestamp) y borra la anterior.
     */
    private function storeImage(Request $request, string $inputName, string $folder, string $prefix, int $id, ?string $oldPath): string
    {
        if ($oldPath) {
            \App\Support\CmsImageStorage::deleteIfOwned($oldPath);
        }

        $file = $request->file($inputName);

        return \App\Support\CmsImageStorage::store($file, $folder);
    }

    /** Normaliza productos_ids desde JSON, array o CSV. */
    private function parseProductosIds($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                $raw = array_filter(array_map('trim', explode(',', $raw)));
            }
        }
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $raw))));
    }

    /** Sincroniza productos vinculados al bloque. */
    private function syncProductosVinculados(WebPromoBloque $bloque, array $ids): void
    {
        if (! Schema::hasTable('web_promo_bloque_producto')) {
            return;
        }

        $bloque->productosVinculados()->detach();

        $orden = 1;
        foreach ($ids as $idProducto) {
            if ($idProducto <= 0) {
                continue;
            }
            $bloque->productosVinculados()->attach($idProducto, ['orden' => $orden++]);
        }
    }

    /** Mapea un producto Eloquent a array liviano para el admin. */
    private function productoResumen($p): array
    {
        return [
            'id_producto'     => (int) $p->id_producto,
            'nombre'          => $p->nombre,
            'codigo_producto' => $p->codigo_producto,
            'precio'          => $p->precio,
            'stock'           => $p->stock,
            'url_imagen'      => $p->url_imagen,
        ];
    }

    /** Sincroniza productos de un submódulo y reconstruye el pivote del bloque. */
    private function syncProductosItem(WebPromoItem $item, array $ids): void
    {
        if (! Schema::hasTable('web_promo_item_producto')) {
            return;
        }

        $idItem = (int) $item->id_promo_item;
        $idBloque = (int) $item->id_promo_bloque;
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $prevIds = DB::table('web_promo_item_producto')
            ->where('id_promo_item', $idItem)
            ->pluck('id_producto')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::table('web_promo_item_producto')->where('id_promo_item', $idItem)->delete();

        $orden = 1;
        foreach ($ids as $idProducto) {
            if ($idProducto <= 0) {
                continue;
            }
            DB::table('web_promo_item_producto')->insert([
                'id_promo_item' => $idItem,
                'id_producto'   => $idProducto,
                'orden'         => $orden++,
            ]);
            $this->ensureProductoEnBloque($idBloque, $idProducto);
        }

        if (Schema::hasColumn('web_promo_item', 'id_producto')) {
            $item->id_producto = $ids[0] ?? null;
            $item->save();
        }

        // Si el producto de portada ya no está vinculado, limpiarlo
        if (Schema::hasColumn('web_promo_item', 'id_producto_portada')) {
            $portada = (int) ($item->id_producto_portada ?? 0);
            if ($portada > 0 && ! in_array($portada, $ids, true)) {
                $item->id_producto_portada = null;
                if (Schema::hasColumn('web_promo_item', 'mostrar_precio_portada')) {
                    $item->mostrar_precio_portada = 'N';
                }
                $item->save();
            }
        }

        foreach (array_diff($prevIds, $ids) as $removedId) {
            $this->removeProductoFromBloqueIfOrphan($idBloque, (int) $removedId);
        }

        // Unión de todos los productos de submódulos → pivote del bloque
        $unionIds = DB::table('web_promo_item_producto as pip')
            ->join('web_promo_item as i', 'pip.id_promo_item', '=', 'i.id_promo_item')
            ->where('i.id_promo_bloque', $idBloque)
            ->orderBy('pip.orden')
            ->pluck('pip.id_producto')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $bloque = WebPromoBloque::find($idBloque);
        if ($bloque) {
            $this->syncProductosVinculados($bloque, $unionIds);
        }
    }

    /** Payload del bloque con items y productos vinculados. */
    private function bloquePayload(WebPromoBloque $bloque): array
    {
        $with = ['items'];
        if (Schema::hasTable('web_promo_bloque_producto')) {
            $with['productosVinculados'] = function ($q) {
                $q->select(
                    'administracion_producto.id_producto',
                    'administracion_producto.nombre',
                    'administracion_producto.codigo_producto',
                    'administracion_producto.precio',
                    'administracion_producto.stock',
                    'administracion_producto.url_imagen'
                );
            };
        }
        if (Schema::hasTable('web_promo_item_producto')) {
            $with['items.productosVinculados'] = function ($q) {
                $q->select(
                    'administracion_producto.id_producto',
                    'administracion_producto.nombre',
                    'administracion_producto.codigo_producto',
                    'administracion_producto.precio',
                    'administracion_producto.stock',
                    'administracion_producto.url_imagen'
                );
            };
        }

        $bloque->load($with);

        $data = $bloque->toArray();
        $data['productos_ids'] = $bloque->productosVinculados
            ? $bloque->productosVinculados->pluck('id_producto')->map(fn ($id) => (int) $id)->values()->all()
            : [];
        $data['productos_vinculados'] = $bloque->productosVinculados
            ? $bloque->productosVinculados->map(fn ($p) => $this->productoResumen($p))->values()->all()
            : [];

        // Conteos / listados por submódulo (ítem)
        if (! empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$itemArr) {
                $itemModel = $bloque->items->firstWhere('id_promo_item', $itemArr['id_promo_item'] ?? null);
                $idsItem = [];
                $vinculados = [];
                if ($itemModel && Schema::hasTable('web_promo_item_producto')) {
                    $idsItem = $itemModel->productosVinculados
                        ->pluck('id_producto')
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->values()
                        ->all();
                    $vinculados = $itemModel->productosVinculados
                        ->map(fn ($p) => $this->productoResumen($p))
                        ->values()
                        ->all();
                }
                if (empty($idsItem) && ! empty($itemArr['id_producto'])) {
                    $idsItem = [(int) $itemArr['id_producto']];
                }
                $itemArr['productos_ids'] = $idsItem;
                $itemArr['productos_count'] = count($idsItem);
                $itemArr['productos_vinculados'] = $vinculados;
            }
            unset($itemArr);
        }

        return $data;
    }

    /** Asegura que el producto también esté en el pivote del bloque (para /tienda/ofertas). */
    private function ensureProductoEnBloque(int $idBloque, int $idProducto): void
    {
        if (! Schema::hasTable('web_promo_bloque_producto') || $idProducto <= 0) {
            return;
        }
        $bloque = WebPromoBloque::find($idBloque);
        if (! $bloque) {
            return;
        }
        $exists = $bloque->productosVinculados()->where('administracion_producto.id_producto', $idProducto)->exists();
        if (! $exists) {
            $orden = ((int) $bloque->productosVinculados()->max('web_promo_bloque_producto.orden')) + 1;
            $bloque->productosVinculados()->attach($idProducto, ['orden' => max(1, $orden)]);
        }
    }

    private function removeProductoFromBloqueIfOrphan(int $idBloque, int $idProducto): void
    {
        if (! Schema::hasTable('web_promo_bloque_producto') || ! Schema::hasTable('web_promo_item_producto')) {
            return;
        }
        $itemIds = WebPromoItem::where('id_promo_bloque', $idBloque)->pluck('id_promo_item');
        $stillLinked = DB::table('web_promo_item_producto')
            ->whereIn('id_promo_item', $itemIds)
            ->where('id_producto', $idProducto)
            ->exists();
        if (! $stillLinked) {
            WebPromoBloque::find($idBloque)?->productosVinculados()->detach($idProducto);
        }
    }

    // ─── BLOQUES ─────────────────────────────────────────────────────────────

    public function listar()
    {
        $with = ['items'];
        if (Schema::hasTable('web_promo_bloque_producto')) {
            $with[] = 'productosVinculados';
        }
        if (Schema::hasTable('web_promo_item_producto')) {
            $with[] = 'items.productosVinculados';
        }

        $bloques = WebPromoBloque::with($with)->orderBy('orden')->get()
            ->map(fn ($b) => $this->bloquePayload($b));

        return response()->json(['success' => true, 'result' => $bloques]);
    }

    /**
     * Árbol bloque → submódulos para el modal de asignación desde Productos.
     */
    public function listarArbol()
    {
        $with = ['items' => fn ($q) => $q->where('Activo', 'S')->orderBy('orden')];
        if (Schema::hasTable('web_promo_item_producto')) {
            $with[] = 'items.productosVinculados';
        }

        $bloques = WebPromoBloque::with($with)
            ->where('Activo', 'S')
            ->orderBy('orden')
            ->get()
            ->map(function (WebPromoBloque $b) {
                $items = ($b->items ?? collect())->map(function (WebPromoItem $it) {
                    $count = 0;
                    if (Schema::hasTable('web_promo_item_producto')) {
                        $count = $it->productosVinculados?->count() ?? 0;
                    }
                    if ($count === 0 && ! empty($it->id_producto)) {
                        $count = 1;
                    }

                    return [
                        'id_promo_item'   => (int) $it->id_promo_item,
                        'id_promo_bloque' => (int) $it->id_promo_bloque,
                        'nombre'          => $it->nombre ?: 'Sin nombre',
                        'categoria'       => $it->categoria,
                        'precio'          => $it->precio,
                        'productos_count' => $count,
                        'Activo'          => $it->Activo,
                    ];
                })->values()->all();

                return [
                    'id_promo_bloque'   => (int) $b->id_promo_bloque,
                    'titulo_seccion'    => $b->titulo_seccion,
                    'subtitulo_seccion' => $b->subtitulo_seccion,
                    'slug'              => $b->slug,
                    'items'             => $items,
                    'items_count'       => count($items),
                ];
            })
            ->values();

        return response()->json(['success' => true, 'result' => $bloques]);
    }

    /** Asignación actual de un producto a submódulo(s). */
    public function asignacionProducto(Request $request)
    {
        $request->validate(['id_producto' => 'required|integer']);
        $idProducto = (int) $request->id_producto;

        if (! Schema::hasTable('web_promo_item_producto')) {
            return response()->json(['success' => true, 'result' => null]);
        }

        $row = DB::table('web_promo_item_producto as pip')
            ->join('web_promo_item as i', 'pip.id_promo_item', '=', 'i.id_promo_item')
            ->join('web_promo_bloque as b', 'i.id_promo_bloque', '=', 'b.id_promo_bloque')
            ->where('pip.id_producto', $idProducto)
            ->orderByDesc('pip.id_promo_item_producto')
            ->select([
                'pip.id_promo_item',
                'pip.id_producto',
                'i.nombre as item_nombre',
                'i.categoria as item_categoria',
                'i.id_promo_bloque',
                'i.tipo_oferta',
                'i.descuento_porcentaje',
                'i.multiplicador',
                'i.precio as precio_oferta',
                'b.subtitulo_seccion',
                'b.titulo_seccion',
                'b.slug',
            ])
            ->first();

        $ofertaLabel = null;
        if ($row) {
            $tipo = strtolower(trim((string) ($row->tipo_oferta ?? 'pack')));
            $pct = (float) ($row->descuento_porcentaje ?? 0);
            if ($tipo === 'porcentaje' && $pct > 0) {
                $ofertaLabel = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.') . '% DSCTO';
            } elseif ($tipo === 'pack') {
                $mult = trim((string) ($row->multiplicador ?? ''));
                $precioOferta = (float) ($row->precio_oferta ?? 0);
                if ($mult !== '' && $precioOferta > 0) {
                    $ofertaLabel = $mult . ' · S/ ' . number_format($precioOferta, 2);
                } elseif ($mult !== '') {
                    $ofertaLabel = $mult;
                }
            }
        }

        return response()->json([
            'success' => true,
            'result'  => $row ? [
                'id_promo_item'     => (int) $row->id_promo_item,
                'id_promo_bloque'   => (int) $row->id_promo_bloque,
                'id_producto'       => (int) $row->id_producto,
                'item_nombre'       => $row->item_nombre,
                'item_categoria'    => $row->item_categoria,
                'bloque_titulo'     => $row->subtitulo_seccion ?: $row->titulo_seccion,
                'bloque_slug'       => $row->slug,
                'tipo_oferta'       => $row->tipo_oferta ?? 'pack',
                'descuento_porcentaje' => isset($row->descuento_porcentaje) ? (float) $row->descuento_porcentaje : null,
                'oferta_label'      => $ofertaLabel,
            ] : null,
        ]);
    }

    /**
     * Asigna un producto a un submódulo (tarjeta). Reemplaza asignación previa del producto.
     * También lo agrega al pivote del bloque para la tienda de ofertas.
     */
    public function asignarProductoItem(Request $request)
    {
        $request->validate([
            'id_producto'   => 'required|integer|exists:administracion_producto,id_producto',
            'id_promo_item' => 'required|integer|exists:web_promo_item,id_promo_item',
        ]);

        if (! Schema::hasTable('web_promo_item_producto')) {
            return response()->json(['success' => false, 'message' => 'Tabla de asignación no disponible'], 500);
        }

        $idProducto = (int) $request->id_producto;
        $idItem = (int) $request->id_promo_item;
        $item = WebPromoItem::findOrFail($idItem);

        if (Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
            $pct = (float) (Producto::where('id_producto', $idProducto)->value('descuento_porcentaje') ?? 0);
            if ($pct > 0) {
                return response()->json([
                    'success' => false,
                    'code'    => 'TIENE_DESCUENTO_PORCENTAJE',
                    'message' => 'Este producto tiene descuento por porcentaje en el módulo Productos. Libéralo (quita el %) antes de integrarlo a un bloque.',
                    'result'  => [
                        'message' => 'Este producto tiene descuento por porcentaje en el módulo Productos. Libéralo (quita el %) antes de integrarlo a un bloque.',
                        'descuento_porcentaje' => $pct,
                    ],
                ], 422);
            }
        }

        // Quitar de otros submódulos (1 producto → 1 submódulo principal)
        $prevItems = DB::table('web_promo_item_producto')
            ->where('id_producto', $idProducto)
            ->where('id_promo_item', '!=', $idItem)
            ->pluck('id_promo_item');

        DB::table('web_promo_item_producto')->where('id_producto', $idProducto)->delete();

        foreach ($prevItems as $prevItemId) {
            $prevItem = WebPromoItem::find($prevItemId);
            if ($prevItem) {
                $this->removeProductoFromBloqueIfOrphan((int) $prevItem->id_promo_bloque, $idProducto);
            }
        }

        $orden = ((int) DB::table('web_promo_item_producto')->where('id_promo_item', $idItem)->max('orden')) + 1;
        DB::table('web_promo_item_producto')->insert([
            'id_promo_item' => $idItem,
            'id_producto'   => $idProducto,
            'orden'         => max(1, $orden),
        ]);

        // Compatibilidad legacy
        if (Schema::hasColumn('web_promo_item', 'id_producto')) {
            $item->id_producto = $idProducto;
            $item->save();
        }

        $this->ensureProductoEnBloque((int) $item->id_promo_bloque, $idProducto);

        $tipo = strtolower(trim((string) ($item->tipo_oferta ?? 'pack')));
        $pct = (float) ($item->descuento_porcentaje ?? 0);
        $ofertaLabel = null;
        if ($tipo === 'porcentaje' && $pct > 0) {
            $ofertaLabel = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.') . '% DSCTO';
        } elseif ($tipo === 'pack') {
            $mult = trim((string) ($item->multiplicador ?? ''));
            $precioOferta = (float) ($item->precio ?? 0);
            if ($mult !== '' && $precioOferta > 0) {
                $ofertaLabel = $mult . ' · S/ ' . number_format($precioOferta, 2);
            } elseif ($mult !== '') {
                $ofertaLabel = $mult;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Producto asignado al submódulo',
            'result'  => [
                'id_promo_item'   => $idItem,
                'id_promo_bloque' => (int) $item->id_promo_bloque,
                'id_producto'     => $idProducto,
                'item_nombre'     => $item->nombre,
                'bloque_titulo'   => optional($item->bloque)->subtitulo_seccion
                    ?: optional($item->bloque)->titulo_seccion,
                'tipo_oferta'     => $tipo,
                'descuento_porcentaje' => $pct > 0 ? $pct : null,
                'oferta_label'    => $ofertaLabel,
            ],
        ]);
    }

    public function quitarProductoItem(Request $request)
    {
        $request->validate([
            'id_producto'   => 'required|integer',
            'id_promo_item' => 'nullable|integer',
        ]);

        if (! Schema::hasTable('web_promo_item_producto')) {
            return response()->json(['success' => true]);
        }

        $idProducto = (int) $request->id_producto;
        $query = DB::table('web_promo_item_producto')->where('id_producto', $idProducto);
        if ($request->filled('id_promo_item')) {
            $query->where('id_promo_item', (int) $request->id_promo_item);
        }
        $itemIds = (clone $query)->pluck('id_promo_item');
        $query->delete();

        foreach ($itemIds as $itemId) {
            $item = WebPromoItem::find($itemId);
            if ($item) {
                if (Schema::hasColumn('web_promo_item', 'id_producto') && (int) $item->id_producto === $idProducto) {
                    $item->id_producto = null;
                    $item->save();
                }
                $this->removeProductoFromBloqueIfOrphan((int) $item->id_promo_bloque, $idProducto);
            }
        }

        return response()->json(['success' => true, 'message' => 'Asignación eliminada']);
    }

    public function crear(Request $request)
    {
        $request->validate([
            'titulo_seccion'    => 'nullable|string|max:120',
            'subtitulo_seccion' => 'nullable|string|max:180',
            'hero_etiqueta'     => 'nullable|string|max:80',
            'hero_titulo'       => 'nullable|string',
            'hero_subtitulo'    => 'nullable|string|max:200',
            'Activo'            => 'nullable|in:S,N',
        ]);

        $bloque = WebPromoBloque::create([
            'titulo_seccion'    => $request->titulo_seccion    ?? 'OFERTAS DESTACADAS',
            'subtitulo_seccion' => $request->subtitulo_seccion ?? 'OFERTAS & PROMOCIONES',
            'hero_etiqueta'     => $request->hero_etiqueta     ?? 'NUEVA COLECCIÓN',
            'hero_titulo'       => $request->hero_titulo       ?? '',
            'hero_subtitulo'    => $request->hero_subtitulo    ?? '',
            'orden'             => (WebPromoBloque::max('orden') ?? 0) + 1,
            'Activo'            => $request->Activo ?? 'S',
        ]);

        if ($request->hasFile('imagen_hero')) {
            $bloque->update([
                'imagen_hero' => $this->storeImage($request, 'imagen_hero', 'promo_bloque', 'bloque', $bloque->id_promo_bloque, null),
            ]);
        }

        if ($request->has('productos_ids')) {
            $this->syncProductosVinculados($bloque, $this->parseProductosIds($request->input('productos_ids')));
        }

        return response()->json(['success' => true, 'result' => $this->bloquePayload($bloque->fresh())]);
    }

    public function actualizar(Request $request)
    {
        $request->validate(['id_promo_bloque' => 'required|integer|exists:web_promo_bloque,id_promo_bloque']);

        $bloque = WebPromoBloque::findOrFail($request->id_promo_bloque);
        $bloque->fill($request->only([
            'titulo_seccion', 'subtitulo_seccion',
            'hero_etiqueta', 'hero_titulo', 'hero_subtitulo',
            'orden', 'Activo',
        ]));

        if ($request->hasFile('imagen_hero')) {
            $bloque->imagen_hero = $this->storeImage($request, 'imagen_hero', 'promo_bloque', 'bloque', $bloque->id_promo_bloque, $bloque->getOriginal('imagen_hero'));
        }

        $bloque->save();

        if ($request->has('productos_ids')) {
            $this->syncProductosVinculados($bloque, $this->parseProductosIds($request->input('productos_ids')));
        }

        return response()->json(['success' => true, 'result' => $this->bloquePayload($bloque->fresh())]);
    }

    /** Vincular múltiples productos del catálogo a un bloque (desde modal admin). */
    public function vincularProductos(Request $request)
    {
        $request->validate([
            'id_promo_bloque' => 'required|integer|exists:web_promo_bloque,id_promo_bloque',
            'productos_ids'   => 'nullable',
            'id_promo_item'   => 'nullable|integer|exists:web_promo_item,id_promo_item',
        ]);

        $bloque = WebPromoBloque::findOrFail($request->id_promo_bloque);
        $ids = $this->parseProductosIds($request->input('productos_ids'));

        if (! empty($ids) && Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
            $conPct = Producto::whereIn('id_producto', $ids)
                ->whereNotNull('descuento_porcentaje')
                ->where('descuento_porcentaje', '>', 0)
                ->get(['id_producto', 'nombre', 'descuento_porcentaje']);
            if ($conPct->isNotEmpty()) {
                $lista = $conPct->map(function ($p) {
                    $pct = rtrim(rtrim(number_format((float) $p->descuento_porcentaje, 2, '.', ''), '0'), '.');

                    return "#{$p->id_producto} {$p->nombre} ({$pct}%)";
                })->implode(', ');

                return response()->json([
                    'success' => false,
                    'code'    => 'TIENE_DESCUENTO_PORCENTAJE',
                    'message' => "No se pueden vincular productos con descuento % del módulo Productos. Libéralos primero: {$lista}",
                    'result'  => [
                        'message' => "No se pueden vincular productos con descuento % del módulo Productos. Libéralos primero: {$lista}",
                        'productos' => $conPct,
                    ],
                ], 422);
            }
        }

        // Si se indica submódulo, sincroniza ahí (y reconstruye el pivote del bloque).
        if ($request->filled('id_promo_item')) {
            $item = WebPromoItem::where('id_promo_item', (int) $request->id_promo_item)
                ->where('id_promo_bloque', $bloque->id_promo_bloque)
                ->firstOrFail();
            $this->syncProductosItem($item, $ids);

            $dirty = false;
            if ($request->has('id_producto_portada') && Schema::hasColumn('web_promo_item', 'id_producto_portada')) {
                $portadaId = (int) ($request->id_producto_portada ?: 0);
                $item->id_producto_portada = ($portadaId > 0 && in_array($portadaId, $ids, true))
                    ? $portadaId
                    : null;
                $dirty = true;
            }
            if ($request->has('mostrar_precio_portada') && Schema::hasColumn('web_promo_item', 'mostrar_precio_portada')) {
                $item->mostrar_precio_portada = strtoupper((string) $request->mostrar_precio_portada) === 'S' ? 'S' : 'N';
                // No auto-asignar Portada: la imagen del producto solo se usa si el usuario marca Portada.
                $dirty = true;
            }
            if ($dirty) {
                $item->save();
            }
        } else {
            $this->syncProductosVinculados($bloque, $ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Productos vinculados',
            'result'  => $this->bloquePayload($bloque->fresh()),
        ]);
    }

    public function eliminar(Request $request)
    {
        $request->validate(['id_promo_bloque' => 'required|integer|exists:web_promo_bloque,id_promo_bloque']);

        $id = (int) $request->id_promo_bloque;
        if (Schema::hasTable('web_promo_bloque_producto')) {
            WebPromoBloque::find($id)?->productosVinculados()->detach();
        }
        WebPromoItem::where('id_promo_bloque', $id)->delete();
        WebPromoBloque::destroy($id);

        return response()->json(['success' => true]);
    }

    // ─── ITEMS ───────────────────────────────────────────────────────────────

    public function crearItem(Request $request)
    {
        $request->validate([
            'id_promo_bloque' => 'required|integer|exists:web_promo_bloque,id_promo_bloque',
            'nombre'          => 'nullable|string|max:120',
        ]);

        $payload = [
            'id_promo_bloque' => $request->id_promo_bloque,
            'id_producto'     => $request->id_producto ?: null,
            'categoria'       => $request->categoria    ?? 'POLOS',
            'nombre'          => $request->nombre        ?? '',
            'multiplicador'   => $request->multiplicador ?? 'HASTA',
            'precio'          => $request->precio        ?? 0,
            'descripcion'     => $request->descripcion   ?? 'Válido en nuestra tienda online. Hasta agotar stock.',
            'orden'           => (WebPromoItem::where('id_promo_bloque', $request->id_promo_bloque)->max('orden') ?? 0) + 1,
            'Activo'          => $request->Activo ?? 'S',
        ];
        if (Schema::hasColumn('web_promo_item', 'tipo_oferta')) {
            $payload['tipo_oferta'] = in_array($request->tipo_oferta, ['pack', 'porcentaje'], true)
                ? $request->tipo_oferta
                : 'pack';
        }
        if (Schema::hasColumn('web_promo_item', 'descuento_porcentaje')) {
            $payload['descuento_porcentaje'] = $request->descuento_porcentaje !== null && $request->descuento_porcentaje !== ''
                ? (float) $request->descuento_porcentaje
                : null;
        }
        if (Schema::hasColumn('web_promo_item', 'mostrar_precio_portada')) {
            $payload['mostrar_precio_portada'] = strtoupper((string) ($request->mostrar_precio_portada ?? 'N')) === 'S' ? 'S' : 'N';
        }
        if (Schema::hasColumn('web_promo_item', 'id_producto_portada')) {
            $portadaId = (int) ($request->id_producto_portada ?: 0);
            $payload['id_producto_portada'] = $portadaId > 0 ? $portadaId : null;
        }
        if (($payload['tipo_oferta'] ?? 'pack') === 'porcentaje') {
            $payload['precio'] = 0;
        }
        $item = WebPromoItem::create($payload);

        if ($request->hasFile('imagen')) {
            $item->update([
                'imagen' => $this->storeImage($request, 'imagen', 'promo_item', 'item', $item->id_promo_item, null),
            ]);
        }

        return response()->json(['success' => true, 'result' => $item->fresh()]);
    }

    public function actualizarItem(Request $request)
    {
        $request->validate(['id_promo_item' => 'required|integer|exists:web_promo_item,id_promo_item']);

        $item = WebPromoItem::findOrFail($request->id_promo_item);
        $item->fill($request->only([
            'id_promo_bloque', 'id_producto', 'categoria', 'nombre',
            'tipo_oferta', 'multiplicador', 'precio', 'descuento_porcentaje',
            'mostrar_precio_portada', 'id_producto_portada',
            'descripcion', 'orden', 'Activo',
        ]));
        if ($request->has('tipo_oferta') && ! in_array($request->tipo_oferta, ['pack', 'porcentaje'], true)) {
            $item->tipo_oferta = 'pack';
        }
        if ($request->has('descuento_porcentaje') && ($request->descuento_porcentaje === '' || $request->descuento_porcentaje === null)) {
            $item->descuento_porcentaje = null;
        }
        if ($request->has('mostrar_precio_portada')) {
            $item->mostrar_precio_portada = strtoupper((string) $request->mostrar_precio_portada) === 'S' ? 'S' : 'N';
        }
        if ($request->has('id_producto_portada')) {
            $portadaId = (int) ($request->id_producto_portada ?: 0);
            $item->id_producto_portada = $portadaId > 0 ? $portadaId : null;
        }
        if (($item->tipo_oferta ?? 'pack') === 'porcentaje') {
            $item->precio = 0;
        }

        if ($request->hasFile('imagen')) {
            $item->imagen = $this->storeImage($request, 'imagen', 'promo_item', 'item', $item->id_promo_item, $item->getOriginal('imagen'));
        }

        $item->save();

        return response()->json(['success' => true, 'result' => $item->fresh()]);
    }

    public function eliminarItem(Request $request)
    {
        $request->validate(['id_promo_item' => 'required|integer|exists:web_promo_item,id_promo_item']);
        $idItem = (int) $request->id_promo_item;
        $item = WebPromoItem::find($idItem);
        if ($item && Schema::hasTable('web_promo_item_producto')) {
            $productIds = DB::table('web_promo_item_producto')->where('id_promo_item', $idItem)->pluck('id_producto');
            DB::table('web_promo_item_producto')->where('id_promo_item', $idItem)->delete();
            foreach ($productIds as $idProducto) {
                $this->removeProductoFromBloqueIfOrphan((int) $item->id_promo_bloque, (int) $idProducto);
            }
        }
        WebPromoItem::destroy($idItem);

        return response()->json(['success' => true]);
    }
}
