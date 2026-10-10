<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Session;
use Illuminate\Support\Collection;
use App\Models\MetadatosPagina;
use App\Models\Producto;
use App\Models\WebPromoBloque;
use App\Helpers\CategoryHelper;
use App\Support\CategoriaTiendaUrl;
use App\Support\LiamPageData;
use App\Support\ProductoEcommerceCatalog;
use App\Support\PromoTiendaUrl;
use App\Support\PromoPackOffer;
use App\Support\PromoPercentOffer;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductoCategoria;
use App\Models\ProductoCategoriaSub;
use Illuminate\Support\Str;

class GaleriaController extends Controller
{
    public function web_galeria_listar(Request $request, $category = null, $subcategory = null)
    {
        $filtros = $request->input('filtros', []);
        $filtros = is_array($filtros) ? $filtros : explode(',', (string) $filtros);
        $filtros = array_values(array_filter(array_map('trim', $filtros), static fn ($f) => $f !== '' && $f !== '0'));
        $page = $request->input('page', 1);
        $view = $request->input('view', 'grid3');
        $orden = $request->input('orden', 'relevancia');
        $busqueda = $request->input('busqueda', $request->input('q', ''));
        $promoId = (int) $request->input('promo', 0);
        $focusId = (int) $request->input('focus', 0);
        $promoSeo = $request->boolean('promo_seo');
        $attrsCatalogo = $this->parseFiltrosAtributoCatalogo($request);

        // Productos libres = no vinculados a bloques/submódulos de ofertas
        $filtroLibres = in_array('libres', $filtros, true);
        if ($filtroLibres) {
            $filtros = array_values(array_filter($filtros, static fn ($f) => $f !== 'libres'));
        }

        // Redirigir URLs antiguas ?promo=6&focus=17 → /tienda/ofertas/slug-producto
        if ($promoId > 0 && ! $request->ajax() && ! $promoSeo) {
            $bloqueRedirect = WebPromoBloque::find($promoId);
            if ($bloqueRedirect) {
                return redirect(PromoTiendaUrl::tiendaOfertas($bloqueRedirect, null, $focusId ?: null), 301);
            }
        }

        // Resolver categoría/subcategoría por slug SEO (o ID legado → redirect 301)
        $idCategoriaUrl = null;
        $idSubcategoriaUrl = null;
        if ($category && strtolower((string) $category) !== 'ofertas') {
            $resolvedCat = $this->resolverCategoriaParam($category);
            if ($resolvedCat) {
                $canonicalCat = CategoriaTiendaUrl::categorySlug($resolvedCat);
                $resolvedSub = null;
                $canonicalSub = null;
                if ($subcategory) {
                    $resolvedSub = $this->resolverSubcategoriaParam($resolvedCat->id_producto_categoria, $subcategory);
                    if ($resolvedSub) {
                        $canonicalSub = CategoriaTiendaUrl::subcategorySlug($resolvedSub);
                    }
                }

                if (! $request->ajax()) {
                    $needsRedirect = (string) $category !== (string) $canonicalCat
                        || ($subcategory && $resolvedSub && (string) $subcategory !== (string) $canonicalSub);
                    if ($needsRedirect) {
                        $target = CategoriaTiendaUrl::tienda($resolvedCat, $resolvedSub);
                        $qs = $request->getQueryString();
                        if ($qs) {
                            $target .= (str_contains($target, '?') ? '&' : '?') . $qs;
                        }

                        return redirect($target, 301);
                    }
                }

                $idCategoriaUrl = (int) $resolvedCat->id_producto_categoria;
                $idSubcategoriaUrl = $resolvedSub ? (int) $resolvedSub->id_producto_categoria_sub : null;
                $this->procesarFiltrosURL($idCategoriaUrl, $idSubcategoriaUrl, $filtros);
            }
        }

        // Separar filtros de categoría (N-M) de filtros de bloques dinámicos (promo-*)
        [$filtrosCat, $filtrosPromo] = $this->separarFiltrosPromo($filtros);

        // Landing SEO /tienda/ofertas/{bloque}/{producto}: preseleccionar el submódulo
        // Solo en la carga inicial (no AJAX), para que «Limpiar filtros» no lo vuelva a forzar.
        if ($promoId > 0 && empty($filtrosPromo) && empty($filtrosCat) && ! $request->ajax()) {
            $filtroLanding = $this->resolverFiltroPromoDesdeLanding($promoId, $focusId);
            if ($filtroLanding) {
                $filtrosPromo[] = $filtroLanding;
                $filtros[] = $filtroLanding;
            }
            // Guardar pack xN del cartel en sesión para detalle/carrito
            $this->recordarOfertasPackDesdeFiltrosPromo($filtrosPromo, $focusId);
        } elseif ($promoId > 0 && ! $request->ajax() && (! empty($filtrosPromo) || $focusId > 0)) {
            $this->recordarOfertasPackDesdeFiltrosPromo($filtrosPromo, $focusId, $promoId);
        }

        $idsFromPromoFiltros = $this->resolverIdsDesdeFiltrosPromo($filtrosPromo);
        $idsFromCatFiltros = $this->resolverIdsDesdeFiltrosCategoria($filtrosCat, $busqueda);

        // Unión: categorías independientes + bloques/submódulos = un solo universo
        $idsUnionSidebar = array_values(array_unique(array_filter(array_merge(
            $idsFromPromoFiltros,
            $idsFromCatFiltros
        ))));

        // Filtro por bloque de ofertas (productos vinculados en admin)
        $idsPromo = [];
        $tituloPromo = null;
        if ($promoId > 0) {
            $bloque = \App\Models\WebPromoBloque::with([
                'productosVinculados',
                'items' => function ($q) {
                    $q->where('Activo', 'S');
                },
            ])->find($promoId);

            if ($bloque) {
                $idsPromo = $bloque->productosVinculados
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                // Productos asignados a submódulos (ítems) del bloque
                if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                    $itemIds = $bloque->items->pluck('id_promo_item')->filter()->all();
                    if (! empty($itemIds)) {
                        $idsFromItems = DB::table('web_promo_item_producto')
                            ->whereIn('id_promo_item', $itemIds)
                            ->pluck('id_producto')
                            ->map(fn ($id) => (int) $id)
                            ->filter()
                            ->all();
                        $idsPromo = array_values(array_unique(array_merge($idsPromo, $idsFromItems)));
                    }
                }

                // Compatibilidad: ítems con id_producto si aún no hay pivote
                if (empty($idsPromo)) {
                    $idsPromo = $bloque->items
                        ->pluck('id_producto')
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                $tituloPromo = trim((string) ($bloque->subtitulo_seccion ?: $bloque->titulo_seccion ?: 'Ofertas'));
            }
        }

        // Sidebar siempre completo: categorías independientes + bloques/submódulos con productos
        $estructura = $this->generarEstructuraFiltros($filtrosCat, $busqueda);
        $estructuraPromo = $this->generarEstructuraPromoFiltros($filtrosPromo);

        // Productos: unión de todo lo marcado en el sidebar (sin cruzar/AND entre tipos)
        if ($filtroLibres) {
            $idsExcluir = $this->resolverIdsConDescuentoOOferta();
            $idsLibresQ = DB::table('administracion_producto as P')->where('P.Activo', 'S');

            if (! empty($idsExcluir)) {
                $idsLibresQ->whereNotIn('P.id_producto', $idsExcluir);
            }

            // Refuerzo SQL: sin % de descuento en el producto
            if (\Illuminate\Support\Facades\Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
                $idsLibresQ->where(function ($w) {
                    $w->whereNull('P.descuento_porcentaje')
                        ->orWhere('P.descuento_porcentaje', '<=', 0);
                });
            }

            // Sin precio tachado (oferta por precio_old)
            if (\Illuminate\Support\Facades\Schema::hasColumn('administracion_producto', 'precio_old')) {
                $idsLibresQ->where(function ($w) {
                    $w->whereNull('P.precio_old')
                        ->orWhere('P.precio_old', '<=', 0)
                        ->orWhereColumn('P.precio_old', '<=', 'P.precio');
                });
            }

            $idsLibres = $idsLibresQ
                ->pluck('P.id_producto')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all();

            // Refuerzo: excluir cualquier producto que aún resuelva % o pack desde catálogo de ofertas
            $idsLibres = array_values(array_filter($idsLibres, static function (int $id) {
                if (PromoPercentOffer::resolveFromCatalog($id)) {
                    return false;
                }

                return empty(PromoPackOffer::resolveFromCatalog($id));
            }));

            $queryExtra = ['view' => $view, 'filtros' => 'libres'];
            if ($orden !== 'relevancia') {
                $queryExtra['orden'] = $orden;
            }
            $productos = $this->obtenerProductosPorIds($idsLibres, $page, $orden, 0, $queryExtra, []);
            $filtros[] = 'libres';
        } elseif (! empty($idsUnionSidebar)) {
            $queryExtra = ['view' => $view, 'filtros' => implode(',', $filtros)];
            if ($orden !== 'relevancia') {
                $queryExtra['orden'] = $orden;
            }
            $productos = $this->obtenerProductosPorIds(
                $idsUnionSidebar,
                $page,
                $orden,
                $focusId,
                $queryExtra,
                [] // no re-aplicar categorías: ya van en la unión
            );
        } elseif (! empty($filtrosCat) || ! empty($filtrosPromo)) {
            // Filtros marcados pero sin IDs (p.ej. submódulo vacío) → lista vacía
            $queryExtra = ['view' => $view, 'filtros' => implode(',', $filtros)];
            $productos = $this->obtenerProductosPorIds([], $page, $orden, 0, $queryExtra, []);
        } elseif (! empty($idsPromo)) {
            $queryExtra = ['view' => $view];
            if (! $promoSeo) {
                $queryExtra['promo'] = $promoId;
                if ($focusId > 0) {
                    $queryExtra['focus'] = $focusId;
                }
            } elseif ($orden !== 'relevancia') {
                $queryExtra['orden'] = $orden;
            }
            if (! empty($filtros)) {
                $queryExtra['filtros'] = implode(',', $filtros);
            }
            if ($this->tieneFiltrosAtributoCatalogo($attrsCatalogo)) {
                $productos = $this->obtenerProductosPorIds(
                    $this->filtrarIdsPorAtributosCatalogo($idsPromo, $attrsCatalogo),
                    $page,
                    $orden,
                    $focusId,
                    $queryExtra,
                    []
                );
            } else {
                $productos = $this->obtenerProductosPorIds($idsPromo, $page, $orden, $focusId, $queryExtra, []);
            }
        } elseif ($this->tieneFiltrosAtributoCatalogo($attrsCatalogo) || (string) $busqueda === '') {
            // Sin búsqueda de texto: Query Builder (soporta A-Z / Z-A y el orden de alta del catálogo).
            // Con búsqueda se mantiene USP_WEB_PRODUCTO_LISTAR_GALERIA por su ranking de relevancia.
            $productos = $this->obtenerProductosFiltradosConAtributos(
                $filtrosCat,
                $page,
                $orden,
                (string) $busqueda,
                $attrsCatalogo
            );
        } else {
            $productos = $this->obtenerProductosFiltrados($filtrosCat, $page, $orden, $busqueda);
        }

        $metaData = MetadatosPagina::metaVigente('web_gallery');
        $productosView = 'web.pages.liam.partials.shop_products';

        if ($request->ajax() || $request->boolean('ajax') || $request->wantsJson()) {
            // Solo query params escalares (nunca modelos Eloquent: rompen el paginador)
            $ajaxQuery = array_filter([
                'filtros' => ! empty($filtros) ? implode(',', $filtros) : null,
                'view' => $view !== 'grid3' ? $view : null,
                'orden' => $orden !== 'relevancia' ? $orden : null,
                'q' => $busqueda !== '' && $busqueda !== null ? $busqueda : null,
                'promo' => ($promoId > 0 && ! $promoSeo) ? $promoId : null,
                'focus' => ($focusId > 0 && ! $promoSeo) ? $focusId : null,
                'talla' => ! empty($attrsCatalogo['tallas']) ? implode(',', $attrsCatalogo['tallas']) : null,
                'color' => ! empty($attrsCatalogo['colores']) ? implode(',', $attrsCatalogo['colores']) : null,
                'precio_min' => $attrsCatalogo['precio_min'],
                'precio_max' => $attrsCatalogo['precio_max'],
            ], static fn ($v) => $v !== null && $v !== '');

            return response()->json([
                'productosDeGaleria' => view($productosView, [
                    'productosDeGaleria' => $productos,
                    'promoBloqueId' => $promoId > 0 ? $promoId : null,
                ])->render(),
                'pagination' => $productos->appends($ajaxQuery)->links()->toHtml(),
                'from' => $productos->firstItem(),
                'to' => $productos->lastItem(),
                'total' => $productos->total(),
            ]);
        }

        // Rango de precios reales
        $precioRange = DB::table('administracion_producto')->where('Activo', 'S')
            ->selectRaw('MIN(precio) as min_p, MAX(precio) as max_p')->first();
        $precioMin = (int) floor((float) ($precioRange->min_p ?? 0));
        $precioMax = (int) ceil((float) ($precioRange->max_p ?? 500));
        if ($precioMax < $precioMin) {
            $precioMax = $precioMin;
        }

        $precioSelMin = $attrsCatalogo['precio_min'] !== null
            ? max($precioMin, (int) $attrsCatalogo['precio_min'])
            : $precioMin;
        $precioSelMax = $attrsCatalogo['precio_max'] !== null
            ? min($precioMax, (int) $attrsCatalogo['precio_max'])
            : $precioMax;

        $facetas = $this->facetasCatalogoTallaColor();

        // Título dinámico por categoría activa / bloque de ofertas
        $tituloCategoria = 'Catálogo';
        if ($filtroLibres) {
            $tituloCategoria = 'SIN DESCUENTO NI OFERTAS';
        } elseif ($tituloPromo) {
            $tituloCategoria = mb_strtoupper($tituloPromo);
        } elseif (!empty($filtros) && count($estructura) === 1) {
            $tituloCategoria = strtoupper($estructura->first()['categoria']['nombre'] ?? 'Catálogo');
        } elseif ($busqueda) {
            $tituloCategoria = 'Resultados: "' . $busqueda . '"';
        }

        return view('web.pages.liam.shop', array_merge(LiamPageData::layout('tienda'), [
            'estructuraGaleriaCategoria' => $estructura,
            'estructuraPromoGaleria' => $estructuraPromo,
            'productosDeGaleria' => $productos,
            'filtrosSeleccionados' => $filtros,
            'currentView' => $view,
            'ordenSeleccionado' => $orden,
            'busqueda' => $busqueda,
            'metaData' => $metaData,
            'precioMin' => $precioMin,
            'precioMax' => $precioMax,
            'precioSelMin' => $precioSelMin,
            'precioSelMax' => $precioSelMax,
            'filtrosTalla' => $facetas['tallas'],
            'filtrosColor' => $facetas['colores'],
            'tallasSeleccionadas' => $attrsCatalogo['tallas'],
            'coloresSeleccionados' => $attrsCatalogo['colores'],
            'tituloCategoria' => $tituloCategoria,
            'promoBloqueId' => $promoId > 0 ? $promoId : null,
            'promoFocusId' => $focusId > 0 ? $focusId : null,
        ]));
    }

    /**
     * Resuelve categoría por slug SEO o ID numérico legado.
     */
    private function resolverCategoriaParam($param): ?ProductoCategoria
    {
        $param = trim((string) $param);
        if ($param === '' || strtolower($param) === 'ofertas') {
            return null;
        }

        if (CategoriaTiendaUrl::hasSlugColumn()) {
            $bySlug = ProductoCategoria::where('slug', $param)->first();
            if ($bySlug) {
                return $bySlug;
            }
        }

        if (ctype_digit($param)) {
            return ProductoCategoria::find((int) $param);
        }

        // Fallback: slug generado desde el nombre (antes de migrar columna)
        $needle = Str::slug($param);
        return ProductoCategoria::get()->first(function (ProductoCategoria $c) use ($needle, $param) {
            return Str::slug((string) $c->nombre) === $needle
                || (string) $c->id_producto_categoria === $param;
        });
    }

    private function resolverSubcategoriaParam(int $idCategoria, $param): ?ProductoCategoriaSub
    {
        $param = trim((string) $param);
        if ($param === '') {
            return null;
        }

        $q = ProductoCategoriaSub::where('id_producto_categoria', $idCategoria);

        if (\Illuminate\Support\Facades\Schema::hasColumn('administracion_producto_categoria_sub', 'slug')) {
            $bySlug = (clone $q)->where('slug', $param)->first();
            if ($bySlug) {
                return $bySlug;
            }
        }

        if (ctype_digit($param)) {
            return (clone $q)->where('id_producto_categoria_sub', (int) $param)->first();
        }

        $needle = Str::slug($param);
        return $q->get()->first(fn (ProductoCategoriaSub $s) => Str::slug((string) $s->nombre) === $needle);
    }

    /**
     * Procesa los filtros que vienen por URL.
     * Si solo se pasa categoría (sin subcategoría), incluye toda la rama: productos con y sin subcategoría.
     */
    private function procesarFiltrosURL($category, $subcategory, &$filtros)
    {
        if (!$category) {
            return;
        }

        if ($subcategory) {
            $filtros[] = "{$category}-{$subcategory}";
        } else {
            // Categoría sola: mostrar todos los productos de la rama (con y sin subcategoría)
            $filtros[] = "{$category}-null";
            $subcategorias = CategoryHelper::getSubcategories($category);
            foreach ($subcategorias as $sub) {
                $filtros[] = "{$category}-{$sub->id_producto_categoria_sub}";
            }
        }

        $filtros = array_unique($filtros);
    }

    //======================= START: FILTROS CON CATEGORIA Y SUBCATEGORIA =======================

    private function generarEstructuraFiltros(array $filtrosActivos = [],string $busqueda = '')
    {
        //  Log::channel('stderr')->info("test" . json_encode($filtrosActivos));
        // dd($filtrosActivos);exit;

        // Tiene los siguientes casos:
        // [] . No selecciono ninguno
        // ["4-null"] . Cuando hay productos con categoria y no con Subcategoria
        // ["3-13","4-null"] .Cuando hay un producto con categoria y subcategoria y otro con categoria y no subcategoria
        // ["3-13","3-14","4-null"] .Hay 2 que tienen un producto con categoria y subcategoria y otro con categoria y no subcategoria

        //Se suspende temporariamente el filtro de búsqueda - Esto es para columnas de filtros
       // $categorias = DB::select('(?)', [null]);
        
        // Procesar filtros activos para extraer categorías y subcategorías
    $filtrosParseados = $this->parsearFiltrosActivos($filtrosActivos);
    
    // Obtener categorías con filtrado aplicado
    $categorias = $this->obtenerCategoriasFiltradas($filtrosParseados, $busqueda);
    
    // Log::channel('stderr')->info("test" . json_encode($categorias));
    
        // Procesar filtros activos
        $filtrosActivosMap = [];
        foreach ($filtrosActivos as $filtro) {
            $filtro = (string) $filtro;
            if (str_starts_with($filtro, 'promo-') || strpos($filtro, '-') === false) {
                continue;
            }
            [$categoriaId, $subcategoriaId] = explode('-', $filtro, 2);
            $filtrosActivosMap[$categoriaId][$subcategoriaId] = true;
        }
        
        return collect($categorias)
            ->groupBy('id_producto_categoria')
            ->map(function ($itemsCategoria, $categoriaId) use ($filtrosActivosMap) {
                $categoria = $itemsCategoria->first();
                
                $subcategorias = $itemsCategoria
                    ->filter(function ($item) {
                        return $item->id_producto_categoria_sub !== null;
                    })
                    ->map(function ($item) use ($categoriaId, $filtrosActivosMap) {
                        return [
                            'id' => $item->id_producto_categoria_sub,
                            'nombre' => $item->nombre_categoria_sub,
                            'total_productos' => $item->total_productos,
                            'activo' => isset($filtrosActivosMap[$categoriaId][$item->id_producto_categoria_sub])
                        ];
                    })
                    // Ocultar subcategorías sin productos (salvo la que esté marcada)
                    ->filter(fn ($sub) => (int) $sub['total_productos'] > 0 || ! empty($sub['activo']))
                    ->values();
                
                $totalCategoria = $itemsCategoria->sum('total_productos');
              
                return [
                    'categoria' => [
                        'id' => $categoriaId,
                        'nombre' => $categoria->nombre_categoria,
                        'total_productos' => $totalCategoria,
                        'activo' => isset($filtrosActivosMap[$categoriaId])
                    ],
                    'subcategorias' => $subcategorias
                ];
            })
            // Ocultar categorías sin productos (salvo la que esté marcada)
            ->filter(function ($grupo) {
                return (int) ($grupo['categoria']['total_productos'] ?? 0) > 0
                    || ! empty($grupo['categoria']['activo'])
                    || collect($grupo['subcategorias'] ?? [])->contains(fn ($s) => ! empty($s['activo']));
            })
            ->values();
    }

    /**
 * Parsear los filtros activos en un formato estructurado
 */
private function parsearFiltrosActivos(array $filtrosActivos): array
{
    $categoriasSeleccionadas = [];
    $subcategoriasSeleccionadas = [];
    $categoriasSinSubcategoria = [];
    
    foreach ($filtrosActivos as $filtro) {
        $filtro = (string) $filtro;
        // Ignorar filtros de bloques dinámicos (promo-bloqueId o promo-bloqueId-itemId)
        if (str_starts_with($filtro, 'promo-')) {
            continue;
        }
        if (strpos($filtro, '-') !== false) {
            [$categoria, $subcategoria] = explode('-', $filtro, 2);
            
            if ($subcategoria === 'null' || $subcategoria === null) {
                // Categoría sin subcategoría
                $categoriasSinSubcategoria[] = (int)$categoria;
            } else {
                // Categoría con subcategoría
                $categoriasSeleccionadas[] = (int)$categoria;
                $subcategoriasSeleccionadas[] = (int)$subcategoria;
            }
        }
    }
    
    // Eliminar duplicados
    $categoriasSeleccionadas = array_unique($categoriasSeleccionadas);
    $subcategoriasSeleccionadas = array_unique($subcategoriasSeleccionadas);
    $categoriasSinSubcategoria = array_unique($categoriasSinSubcategoria);
    
    $tiene = !empty($categoriasSeleccionadas)
        || !empty($subcategoriasSeleccionadas)
        || !empty($categoriasSinSubcategoria);

    return [
        'categorias_con_sub' => $categoriasSeleccionadas,
        'subcategorias' => $subcategoriasSeleccionadas,
        'categorias_sin_sub' => $categoriasSinSubcategoria,
        'tiene_filtros' => $tiene,
    ];
}

/**
 * Obtener categorías aplicando los filtros
 */
private function obtenerCategoriasFiltradas(array $filtrosParseados, string $busqueda)
{
    // Construir parámetros para el stored procedure
    $parametrosFiltros = [
        'categorias_con_sub' => implode(',', $filtrosParseados['categorias_con_sub']),
        'subcategorias' => implode(',', $filtrosParseados['subcategorias']),
        'categorias_sin_sub' => implode(',', $filtrosParseados['categorias_sin_sub']),
        'tiene_filtros' => $filtrosParseados['tiene_filtros'] ? 1 : 0
    ];
    
    // Llamar al stored procedure con filtros
    $categorias = DB::select('CALL USP_WEB_PRODUCTO_LISTAR_GALERIA_CATEGORIA_FILTRADO(?, ?, ?, ?, ?)', [
        $busqueda,
        $parametrosFiltros['categorias_con_sub'],
        $parametrosFiltros['subcategorias'],
        $parametrosFiltros['categorias_sin_sub'],
        $parametrosFiltros['tiene_filtros']
    ]);
    
    return $categorias;
}
//======================= END: FILTROS CON CATEGORIA Y SUBCATEGORIA =======================
    private function obtenerProductosFiltrados($filtros, $page, $orden = 'relevancia', string $busqueda = '')
    {

       
        $idUsuario = Auth::id() ?? 0;
        $pageSize = 12;
        
        // Procesar los filtros
        $categoriasIds = [];
        $subcategoriasIds = [];
        $soloCategorias = [];
        
        foreach ($filtros as $filtro) {
            $filtro = (string) $filtro;
            if (str_starts_with($filtro, 'promo-') || strpos($filtro, '-') === false) {
                continue;
            }
            list($categoriaId, $subcategoriaId) = explode('-', $filtro, 2);
            
            if ($subcategoriaId === 'null') {
                $soloCategorias[] = $categoriaId;
            } else {
                $categoriasIds[] = $categoriaId;
                $subcategoriasIds[] = $subcategoriaId;
            }
        }
        
        // Convertir a cadenas para el procedimiento almacenado
        $categoriasStr = implode(',', array_unique($categoriasIds));
        $subcategoriasStr = implode(',', array_unique($subcategoriasIds));
        $soloCategoriasStr = implode(',', array_unique($soloCategorias));

        // Búsqueda de texto sin filtros de categoría: ORM con ranking (ProductoLiveSearch)
        $sinFiltroCategoria = $categoriasStr === '' && $subcategoriasStr === '' && $soloCategoriasStr === '';
        if ($busqueda !== '' && $sinFiltroCategoria && ($orden === 'relevancia' || $orden === '' || $orden === null)) {
            return $this->obtenerProductosPorLiveSearch($busqueda, $page, $pageSize, [
                'filtros' => $filtros,
                'orden' => $orden,
                'busqueda' => $busqueda,
                'q' => $busqueda,
            ]);
        }
        
        // Llamar al procedimiento almacenado con todos los parámetros
        $productosRaw = DB::select('CALL USP_WEB_PRODUCTO_LISTAR_GALERIA(?,?,?,?,?,?,?,?)', [
            $page, 
            $pageSize, 
            $idUsuario,
            $categoriasStr,
            $subcategoriasStr,
            $soloCategoriasStr,
            $orden,
            $busqueda
        ]);

        // ⭐ ENRIQUECER CON DATOS DE CALIFICACIÓN
        $productosEnriquecidos = \App\Models\Producto::enhanceList($productosRaw);

        // ⭐ MARCAR FAVORITOS DEL USUARIO ACTUAL
        if (Auth::check()) {
            $idProductos = collect($productosEnriquecidos)->pluck('id_producto')->toArray();
            $favoritosIds = DB::table('web_favoritos')
                ->where('id_usuario', Auth::id())
                ->whereIn('id_producto', $idProductos)
                ->pluck('id_producto')
                ->toArray();
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
            }
        } else {
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = false;
            }
        }
      
        // Contar el total alineado con USP_WEB_PRODUCTO_LISTAR_GALERIA (Activo=S + join categoría + mismos filtros)
        $total = $this->contarProductosFiltrados($categoriasStr, $subcategoriasStr, $soloCategoriasStr, $busqueda);
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect($productosEnriquecidos),
            $total,
            $pageSize,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => [
                    'filtros' => $filtros,
                    'orden' => $orden,
                    'busqueda' => $busqueda
                ]
            ]
        );
    }

    /**
     * Listado /tienda?q=… con ranking ORM (misma lógica que el autocomplete pls-).
     */
    private function obtenerProductosPorLiveSearch(string $busqueda, int $page, int $pageSize, array $queryExtra = [])
    {
        $page = max(1, (int) $page);
        $pageSize = max(1, (int) $pageSize);
        $fetch = min(200, max($page * $pageSize, $pageSize * 3));

        $ranked = \App\Support\ProductoLiveSearch::rankedQuery($busqueda, $fetch);
        $total = $ranked->count();
        $slice = $ranked->slice(($page - 1) * $pageSize, $pageSize)->values();

        $productosEnriquecidos = \App\Models\Producto::enhanceList($slice->all());

        if (Auth::check()) {
            $idProductos = collect($productosEnriquecidos)->pluck('id_producto')->toArray();
            $favoritosIds = DB::table('web_favoritos')
                ->where('id_usuario', Auth::id())
                ->whereIn('id_producto', $idProductos)
                ->pluck('id_producto')
                ->toArray();
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
            }
        } else {
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = false;
            }
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect($productosEnriquecidos),
            $total,
            $pageSize,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => $queryExtra,
            ]
        );
    }

    /**
     * Productos de un bloque de ofertas (admin → Producto del catálogo).
     * El producto en focus (tarjeta clickeada) aparece primero.
     * Los filtros de categoría/subcategoría se aplican sobre el set del bloque.
     */
    private function obtenerProductosPorIds(array $ids, $page, string $orden, int $focusId = 0, array $queryExtra = [], array $filtros = [])
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $pageSize = 12;
        $page = max(1, (int) $page);

        if (empty($ids)) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                $pageSize,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $queryExtra]
            );
        }

        $query = DB::table('administracion_producto as P')
            ->leftJoin('administracion_producto_categoria as PC', 'P.id_producto_categoria', '=', 'PC.id_producto_categoria')
            ->leftJoin('administracion_producto_categoria_sub as PCS', 'P.id_producto_categoria_sub', '=', 'PCS.id_producto_categoria_sub')
            ->where('P.Activo', 'S')
            ->whereIn('P.id_producto', $ids)
            ->select([
                'P.id_producto',
                'P.codigo_producto',
                'P.nombre',
                'P.nombre as titulo',
                'P.descripcion',
                'P.precio',
                'P.precio_old',
                'P.stock',
                'P.url_imagen',
                'P.slug',
                'P.numero_estrellas',
                'P.etiqueta_badge',
                'P.codigo_barra',
                'P.id_producto_categoria',
                'P.id_producto_categoria_sub',
                'PC.nombre as nombre_categoria',
                'PCS.nombre as nombre_subcategoria',
            ]);

        $this->aplicarFiltrosCategoriaQuery($query, $filtros);

        // Producto de la tarjeta clickeada primero; luego el orden del bloque / precio
        $orderIds = $ids;
        if ($focusId > 0 && in_array($focusId, $ids, true)) {
            $orderIds = array_values(array_unique(array_merge([$focusId], $ids)));
        }

        if ($orden === 'precio_asc') {
            $query->orderBy('P.precio', 'asc');
        } elseif ($orden === 'precio_desc') {
            $query->orderBy('P.precio', 'desc');
        } elseif ($orden === 'nuevo') {
            $query->orderByDesc('P.id_producto');
        } else {
            $fieldOrder = 'FIELD(P.id_producto,' . implode(',', $orderIds) . ')';
            $query->orderByRaw($fieldOrder);
        }

        $total = (clone $query)->count();
        $rows = $query->forPage($page, $pageSize)->get()->all();

        $productosEnriquecidos = \App\Models\Producto::enhanceList($rows);

        if (Auth::check()) {
            $idProductos = collect($productosEnriquecidos)->pluck('id_producto')->toArray();
            $favoritosIds = DB::table('web_favoritos')
                ->where('id_usuario', Auth::id())
                ->whereIn('id_producto', $idProductos)
                ->pluck('id_producto')
                ->toArray();
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
            }
        } else {
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = false;
            }
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect($productosEnriquecidos),
            $total,
            $pageSize,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => array_merge([
                    'orden' => $orden,
                ], $queryExtra),
            ]
        );
    }

    /**
     * Aplica filtros cat/sub (valores "catId-subId" o "catId-null") con lógica OR entre opciones.
     */
    private function aplicarFiltrosCategoriaQuery($query, array $filtros): void
    {
        $parsed = $this->parsearFiltrosActivos($filtros);
        if (empty($parsed['tiene_filtros'])) {
            return;
        }

        $subs = array_values(array_filter(array_map('intval', $parsed['subcategorias'])));
        $solo = array_values(array_filter(array_map('intval', $parsed['categorias_sin_sub'])));
        $catsConSub = array_values(array_filter(array_map('intval', $parsed['categorias_con_sub'])));

        $query->where(function ($q) use ($subs, $solo, $catsConSub) {
            $applied = false;

            if (! empty($subs)) {
                $applied = true;
                $q->orWhereIn('P.id_producto_categoria_sub', $subs);
            }

            if (! empty($solo)) {
                $applied = true;
                $q->orWhere(function ($q2) use ($solo) {
                    $q2->whereIn('P.id_producto_categoria', $solo)
                        ->whereNull('P.id_producto_categoria_sub');
                });
            }

            // Fallback: solo categorías padre sin pares sub válidos
            if (! $applied && ! empty($catsConSub)) {
                $q->orWhereIn('P.id_producto_categoria', $catsConSub);
            }
        });
    }

    /**
     * Sidebar de filtros acotado a los productos del bloque de ofertas.
     */
    private function generarEstructuraFiltrosDesdeIds(array $ids, array $filtrosActivos = [])
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return collect();
        }

        $productRows = DB::table('administracion_producto as P')
            ->join('administracion_producto_categoria as PC', 'P.id_producto_categoria', '=', 'PC.id_producto_categoria')
            ->leftJoin('administracion_producto_categoria_sub as PCS', 'P.id_producto_categoria_sub', '=', 'PCS.id_producto_categoria_sub')
            ->where('P.Activo', 'S')
            ->whereIn('P.id_producto', $ids)
            ->select([
                'P.id_producto',
                'P.id_producto_categoria',
                'PC.nombre as nombre_categoria',
                'P.id_producto_categoria_sub',
                'PCS.nombre as nombre_categoria_sub',
            ])
            ->get();

        // Agrupar en PHP para evitar ONLY_FULL_GROUP_BY
        $rows = $productRows
            ->groupBy(function ($row) {
                return ($row->id_producto_categoria ?? '0') . '-' . ($row->id_producto_categoria_sub ?? 'null');
            })
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    'id_producto_categoria' => $first->id_producto_categoria,
                    'nombre_categoria' => $first->nombre_categoria,
                    'id_producto_categoria_sub' => $first->id_producto_categoria_sub,
                    'nombre_categoria_sub' => $first->nombre_categoria_sub,
                    'total_productos' => $group->count(),
                ];
            })
            ->values();

        $filtrosActivosMap = [];
        foreach ($filtrosActivos as $filtro) {
            if (strpos((string) $filtro, '-') === false) {
                continue;
            }
            [$categoriaId, $subcategoriaId] = explode('-', $filtro, 2);
            $filtrosActivosMap[$categoriaId][$subcategoriaId] = true;
        }

        return $rows
            ->groupBy('id_producto_categoria')
            ->map(function ($itemsCategoria, $categoriaId) use ($filtrosActivosMap) {
                $categoria = $itemsCategoria->first();

                $subcategorias = $itemsCategoria
                    ->filter(fn ($item) => $item->id_producto_categoria_sub !== null)
                    ->map(function ($item) use ($categoriaId, $filtrosActivosMap) {
                        return [
                            'id' => $item->id_producto_categoria_sub,
                            'nombre' => $item->nombre_categoria_sub,
                            'total_productos' => (int) $item->total_productos,
                            'activo' => isset($filtrosActivosMap[$categoriaId][$item->id_producto_categoria_sub]),
                        ];
                    })
                    // Ocultar subcategorías sin productos (salvo la que esté marcada)
                    ->filter(fn ($sub) => (int) $sub['total_productos'] > 0 || ! empty($sub['activo']))
                    ->values();

                return [
                    'categoria' => [
                        'id' => $categoriaId,
                        'nombre' => $categoria->nombre_categoria,
                        'total_productos' => (int) $itemsCategoria->sum('total_productos'),
                        'activo' => isset($filtrosActivosMap[$categoriaId]),
                    ],
                    'subcategorias' => $subcategorias,
                ];
            })
            // Ocultar categorías sin productos (salvo la que esté marcada)
            ->filter(function ($grupo) {
                return (int) ($grupo['categoria']['total_productos'] ?? 0) > 0
                    || ! empty($grupo['categoria']['activo'])
                    || collect($grupo['subcategorias'] ?? [])->contains(fn ($s) => ! empty($s['activo']));
            })
            ->sortBy(fn ($g) => mb_strtolower($g['categoria']['nombre'] ?? ''))
            ->values();
    }

    /**
     * Cuenta productos con la misma lógica que USP_WEB_PRODUCTO_LISTAR_GALERIA:
     * INNER JOIN categoría, P.Activo = 'S', ramas de filtro por cadenas como el SP.
     */
    private function contarProductosFiltrados(string $categoriasStr, string $subcategoriasStr, string $soloCategoriasStr, string $busqueda): int
    {
        $query = DB::table('administracion_producto as P')
            ->leftJoin('administracion_producto_categoria as PC', 'P.id_producto_categoria', '=', 'PC.id_producto_categoria')
            ->where('P.Activo', 'S');

        if ($categoriasStr !== '') {
            $cats = array_values(array_filter(array_map('intval', explode(',', $categoriasStr))));
            $query->whereIn('P.id_producto_categoria', $cats);
            if ($subcategoriasStr !== '') {
                $subs = array_values(array_filter(array_map('intval', explode(',', $subcategoriasStr))));
                $query->whereIn('P.id_producto_categoria_sub', $subs);
            } else {
                $query->whereNull('P.id_producto_categoria_sub');
            }
        } elseif ($soloCategoriasStr !== '') {
            $solo = array_values(array_filter(array_map('intval', explode(',', $soloCategoriasStr))));
            $query->whereIn('P.id_producto_categoria', $solo)
                ->whereNull('P.id_producto_categoria_sub');
        }

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('P.nombre', 'LIKE', "%{$busqueda}%")
                    ->orWhere('P.descripcion', 'LIKE', "%{$busqueda}%")
                    ->orWhere('P.codigo_producto', 'LIKE', "%{$busqueda}%");
            });
        }

        return (int) $query->count();
    }

    /**
     * Persiste en sesión el pack (xN / precio) de los carteles activos
     * para que detalle y carrito apliquen 2x70, 3x129, etc.
     *
     * @param  array<int,string>  $filtrosPromo
     */
    private function recordarOfertasPackDesdeFiltrosPromo(array $filtrosPromo, int $focusId = 0, int $promoIdFallback = 0): void
    {
        $itemIds = [];
        $bloqueIds = [];

        foreach ($filtrosPromo as $f) {
            if (! preg_match('/^promo-(\d+)(?:-(\d+))?$/', (string) $f, $m)) {
                continue;
            }
            $bloqueIds[] = (int) $m[1];
            if (! empty($m[2])) {
                $itemIds[] = (int) $m[2];
            }
        }

        if ($focusId > 0) {
            $bloque = $promoIdFallback > 0 ? $promoIdFallback : ($bloqueIds[0] ?? null);
            $itemHint = $itemIds[0] ?? null;
            PromoPackOffer::rememberFromLanding($focusId, $bloque, $itemHint);
            PromoPercentOffer::rememberFromLanding($focusId, $bloque, $itemHint);
        }

        // "Todo el bloque" (promo-X sin ítem): recordar pack/% de cada submódulo a sus productos
        if (empty($itemIds) && ! empty($bloqueIds) && \Illuminate\Support\Facades\Schema::hasTable('web_promo_item')) {
            $itemsBloque = DB::table('web_promo_item')
                ->whereIn('id_promo_bloque', array_unique($bloqueIds))
                ->where('Activo', 'S')
                ->get();
            foreach ($itemsBloque as $item) {
                $packOffer = PromoPackOffer::fromPromoItem($item);
                $percentOffer = PromoPercentOffer::fromPromoItem($item);
                if (! $packOffer && ! $percentOffer) {
                    continue;
                }
                $productIds = [];
                if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                    $productIds = DB::table('web_promo_item_producto')
                        ->where('id_promo_item', $item->id_promo_item)
                        ->pluck('id_producto')
                        ->map(fn ($id) => (int) $id)
                        ->all();
                }
                if (empty($productIds) && ! empty($item->id_producto)) {
                    $productIds = [(int) $item->id_producto];
                }
                foreach ($productIds as $pid) {
                    if ($pid <= 0) {
                        continue;
                    }
                    if ($packOffer) {
                        PromoPackOffer::remember($pid, $packOffer);
                    }
                    if ($percentOffer) {
                        PromoPercentOffer::remember($pid, $percentOffer);
                    }
                }
            }
        }

        if (empty($itemIds) || ! \Illuminate\Support\Facades\Schema::hasTable('web_promo_item')) {
            return;
        }

        $items = DB::table('web_promo_item')
            ->whereIn('id_promo_item', array_unique($itemIds))
            ->where('Activo', 'S')
            ->get();

        foreach ($items as $item) {
            $packOffer = PromoPackOffer::fromPromoItem($item);
            $percentOffer = PromoPercentOffer::fromPromoItem($item);
            if (! $packOffer && ! $percentOffer) {
                continue;
            }
            $productIds = [];
            if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                $productIds = DB::table('web_promo_item_producto')
                    ->where('id_promo_item', $item->id_promo_item)
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
            if (empty($productIds) && ! empty($item->id_producto)) {
                $productIds = [(int) $item->id_producto];
            }
            foreach ($productIds as $pid) {
                if ($pid <= 0) {
                    continue;
                }
                if ($packOffer) {
                    PromoPackOffer::remember($pid, $packOffer);
                }
                if ($percentOffer) {
                    PromoPercentOffer::remember($pid, $percentOffer);
                }
            }
        }
    }

    /**
     * Convierte el landing SEO de un bloque/producto focus en un filtro promo-*
     * para marcar el submódulo en el sidebar y filtrar sus productos.
     */
    private function resolverFiltroPromoDesdeLanding(int $promoId, int $focusId): ?string
    {
        if ($promoId <= 0) {
            return null;
        }

        if ($focusId > 0) {
            if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                $itemId = (int) DB::table('web_promo_item_producto as pip')
                    ->join('web_promo_item as pi', 'pip.id_promo_item', '=', 'pi.id_promo_item')
                    ->where('pip.id_producto', $focusId)
                    ->where('pi.id_promo_bloque', $promoId)
                    ->where('pi.Activo', 'S')
                    ->orderBy('pi.orden')
                    ->value('pip.id_promo_item');
                if ($itemId > 0) {
                    return 'promo-' . $promoId . '-' . $itemId;
                }
            }

            $itemIdLegacy = (int) DB::table('web_promo_item')
                ->where('id_promo_bloque', $promoId)
                ->where('id_producto', $focusId)
                ->where('Activo', 'S')
                ->orderBy('orden')
                ->value('id_promo_item');
            if ($itemIdLegacy > 0) {
                return 'promo-' . $promoId . '-' . $itemIdLegacy;
            }
        }

        return 'promo-' . $promoId;
    }

    /**
     * IDs de productos vinculados a bloques u ofertas (carteles).
     */
    private function resolverIdsEnOfertas(): array
    {
        $ids = [];

        if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
            $ids = array_merge(
                $ids,
                DB::table('web_promo_item_producto')->pluck('id_producto')->all()
            );
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_bloque_producto')) {
            $ids = array_merge(
                $ids,
                DB::table('web_promo_bloque_producto')->pluck('id_producto')->all()
            );
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item')) {
            $ids = array_merge(
                $ids,
                DB::table('web_promo_item')
                    ->whereNotNull('id_producto')
                    ->where('id_producto', '>', 0)
                    ->pluck('id_producto')
                    ->all()
            );
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /**
     * Productos que NO deben aparecer en el filtro "libres" del popup:
     * - vinculados a bloques / submódulos de ofertas
     * - con descuento % en el módulo Productos
     * - con precio tachado (precio_old > precio)
     * - en oferta del día
     */
    private function resolverIdsConDescuentoOOferta(): array
    {
        $ids = $this->resolverIdsEnOfertas();

        if (\Illuminate\Support\Facades\Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
            $ids = array_merge(
                $ids,
                DB::table('administracion_producto')
                    ->where('Activo', 'S')
                    ->whereNotNull('descuento_porcentaje')
                    ->where('descuento_porcentaje', '>', 0)
                    ->pluck('id_producto')
                    ->all()
            );
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('administracion_producto', 'precio_old')) {
            $ids = array_merge(
                $ids,
                DB::table('administracion_producto')
                    ->where('Activo', 'S')
                    ->whereNotNull('precio_old')
                    ->where('precio_old', '>', 0)
                    ->whereColumn('precio_old', '>', 'precio')
                    ->pluck('id_producto')
                    ->all()
            );
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('oferta_del_dia')) {
            $ofertaQ = DB::table('oferta_del_dia');
            if (\Illuminate\Support\Facades\Schema::hasColumn('oferta_del_dia', 'Activo')) {
                $ofertaQ->where('Activo', 'S');
            }
            $ids = array_merge($ids, $ofertaQ->pluck('id_producto')->all());

            if (\Illuminate\Support\Facades\Schema::hasColumn('oferta_del_dia', 'segundo_id_producto')) {
                $oferta2 = DB::table('oferta_del_dia')->whereNotNull('segundo_id_producto')->where('segundo_id_producto', '>', 0);
                if (\Illuminate\Support\Facades\Schema::hasColumn('oferta_del_dia', 'Activo')) {
                    $oferta2->where('Activo', 'S');
                }
                $ids = array_merge($ids, $oferta2->pluck('segundo_id_producto')->all());
            }
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /**
     * Separa filtros de categoría (ej. "3-13", "4-null") de filtros de bloques dinámicos
     * (ej. "promo-6", "promo-6-12").
     *
     * @return array{0: array<int,string>, 1: array<int,string>}
     */
    private function separarFiltrosPromo(array $filtros): array
    {
        $cat = [];
        $promo = [];
        foreach ($filtros as $f) {
            $f = trim((string) $f);
            if ($f === '' || $f === 'libres') {
                continue;
            }
            if (str_starts_with($f, 'promo-')) {
                $promo[] = $f;
            } else {
                $cat[] = $f;
            }
        }

        return [$cat, $promo];
    }

    /**
     * Resuelve IDs de producto a partir de filtros promo-*.
     * - promo-{bloqueId} → todos los productos del bloque (pivote + ítems)
     * - promo-{bloqueId}-{itemId} → productos del submódulo
     */
    private function resolverIdsDesdeFiltrosPromo(array $filtrosPromo): array
    {
        if (empty($filtrosPromo)) {
            return [];
        }

        $ids = [];
        $bloqueIds = [];
        $itemIds = [];

        foreach ($filtrosPromo as $f) {
            $parts = explode('-', $f);
            // ["promo", bloqueId] o ["promo", bloqueId, itemId]
            if (count($parts) < 2 || $parts[0] !== 'promo') {
                continue;
            }
            $bloqueId = (int) ($parts[1] ?? 0);
            $itemId = isset($parts[2]) ? (int) $parts[2] : 0;
            if ($itemId > 0) {
                $itemIds[] = $itemId;
            } elseif ($bloqueId > 0) {
                $bloqueIds[] = $bloqueId;
            }
        }

        $bloqueIds = array_values(array_unique(array_filter($bloqueIds)));
        $itemIds = array_values(array_unique(array_filter($itemIds)));

        if (! empty($itemIds) && \Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
            $fromItems = DB::table('web_promo_item_producto')
                ->whereIn('id_promo_item', $itemIds)
                ->pluck('id_producto')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();
            $ids = array_merge($ids, $fromItems);

            // Legacy: id_producto directo en el ítem
            $legacy = DB::table('web_promo_item')
                ->whereIn('id_promo_item', $itemIds)
                ->whereNotNull('id_producto')
                ->where('id_producto', '>', 0)
                ->pluck('id_producto')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();
            $ids = array_merge($ids, $legacy);
        }

        if (! empty($bloqueIds)) {
            if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_bloque_producto')) {
                $fromBloque = DB::table('web_promo_bloque_producto')
                    ->whereIn('id_promo_bloque', $bloqueIds)
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->all();
                $ids = array_merge($ids, $fromBloque);
            }

            $itemIdsOfBloques = DB::table('web_promo_item')
                ->whereIn('id_promo_bloque', $bloqueIds)
                ->where('Activo', 'S')
                ->pluck('id_promo_item')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();

            if (! empty($itemIdsOfBloques) && \Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                $fromItemsBloque = DB::table('web_promo_item_producto')
                    ->whereIn('id_promo_item', $itemIdsOfBloques)
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->all();
                $ids = array_merge($ids, $fromItemsBloque);
            }

            $legacyBloque = DB::table('web_promo_item')
                ->whereIn('id_promo_bloque', $bloqueIds)
                ->where('Activo', 'S')
                ->whereNotNull('id_producto')
                ->where('id_producto', '>', 0)
                ->pluck('id_producto')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();
            $ids = array_merge($ids, $legacyBloque);
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Resuelve IDs de producto a partir de filtros de categoría/subcategoría (OR entre opciones).
     */
    private function resolverIdsDesdeFiltrosCategoria(array $filtrosCat, string $busqueda = ''): array
    {
        if (empty($filtrosCat)) {
            return [];
        }

        $parsed = $this->parsearFiltrosActivos($filtrosCat);
        if (empty($parsed['tiene_filtros'])) {
            return [];
        }

        $query = DB::table('administracion_producto as P')
            ->where('P.Activo', 'S')
            ->select('P.id_producto');

        $this->aplicarFiltrosCategoriaQuery($query, $filtrosCat);

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('P.nombre', 'LIKE', "%{$busqueda}%")
                    ->orWhere('P.descripcion', 'LIKE', "%{$busqueda}%")
                    ->orWhere('P.codigo_producto', 'LIKE', "%{$busqueda}%");
            });
        }

        return $query->pluck('id_producto')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Jerarquía para el sidebar: cada bloque activo = padre, sus ítems = hijos.
     * Values: promo-{bloqueId} / promo-{bloqueId}-{itemId}
     */
    private function generarEstructuraPromoFiltros(array $filtrosPromo): Collection
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('web_promo_bloque')) {
            return collect();
        }

        $bloques = WebPromoBloque::with([
            'items' => function ($q) {
                $q->where('Activo', 'S')->orderBy('orden');
            },
            'productosVinculados',
        ])
            ->where('Activo', 'S')
            ->orderBy('orden')
            ->orderBy('id_promo_bloque')
            ->get();

        if ($bloques->isEmpty()) {
            return collect();
        }

        $itemIdsAll = $bloques->flatMap(fn ($b) => $b->items->pluck('id_promo_item'))->filter()->unique()->values()->all();
        $countByItem = [];
        if (! empty($itemIdsAll) && \Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
            $countByItem = DB::table('web_promo_item_producto')
                ->select('id_promo_item', DB::raw('COUNT(*) as total'))
                ->whereIn('id_promo_item', $itemIdsAll)
                ->groupBy('id_promo_item')
                ->pluck('total', 'id_promo_item')
                ->map(fn ($n) => (int) $n)
                ->all();
        }

        $filtrosSet = array_fill_keys($filtrosPromo, true);

        return $bloques->map(function (WebPromoBloque $bloque) use ($countByItem, $filtrosSet) {
            $bloqueId = (int) $bloque->id_promo_bloque;
            $bloqueVal = 'promo-' . $bloqueId;
            $subs = [];
            $idsBloque = $bloque->productosVinculados
                ->pluck('id_producto')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();

            foreach ($bloque->items as $item) {
                $itemId = (int) $item->id_promo_item;
                $filterVal = $bloqueVal . '-' . $itemId;
                $totalItem = (int) ($countByItem[$itemId] ?? 0);
                if ($totalItem === 0 && (int) ($item->id_producto ?? 0) > 0) {
                    $totalItem = 1;
                    $idsBloque[] = (int) $item->id_producto;
                }
                $activoItem = isset($filtrosSet[$filterVal]);
                // Ocultar submódulos sin productos (salvo el que esté marcado)
                if ($totalItem === 0 && ! $activoItem) {
                    continue;
                }

                $subs[] = [
                    'id' => $itemId,
                    'nombre' => $item->nombre ?: ($item->categoria ?: 'Submódulo'),
                    'total_productos' => $totalItem,
                    'activo' => $activoItem,
                    'filter_value' => $filterVal,
                ];
            }

            // Contar productos del bloque (unión pivote bloque + ítems)
            $idsFromItems = [];
            if (! empty($subs) && \Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                $itemIds = array_column($subs, 'id');
                $idsFromItems = DB::table('web_promo_item_producto')
                    ->whereIn('id_promo_item', $itemIds)
                    ->pluck('id_producto')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
            $totalBloque = count(array_unique(array_filter(array_merge($idsBloque, $idsFromItems))));

            $algunoActivo = isset($filtrosSet[$bloqueVal]);
            if (! $algunoActivo) {
                foreach ($subs as $s) {
                    if (! empty($s['activo'])) {
                        $algunoActivo = true;
                        break;
                    }
                }
            }

            return [
                'tipo' => 'promo',
                'categoria' => [
                    'id' => $bloqueId,
                    'nombre' => trim((string) ($bloque->subtitulo_seccion ?: $bloque->titulo_seccion ?: 'Ofertas')),
                    'total_productos' => $totalBloque,
                    'activo' => $algunoActivo,
                    'filter_value' => $bloqueVal,
                ],
                'subcategorias' => $subs,
            ];
        })
        // Ocultar bloques sin productos (salvo el que esté marcado)
        ->filter(function ($grupo) {
            return (int) ($grupo['categoria']['total_productos'] ?? 0) > 0
                || ! empty($grupo['categoria']['activo']);
        })
        ->values();
    }

    /**
     * Tienda filtrada por bloque de ofertas — URL SEO: /tienda/ofertas/{bloqueSlug}/{productoSlug?}
     */
    public function web_galeria_promo(Request $request, string $promoSlug, ?string $productoSlug = null)
    {
        $bloque = WebPromoBloque::where('slug', $promoSlug)->first();
        if (! $bloque) {
            abort(404);
        }

        $focusId = 0;
        if ($productoSlug) {
            $prod = Producto::where(function ($q) use ($productoSlug) {
                $q->where('slug', $productoSlug);
                if (is_numeric($productoSlug)) {
                    $q->orWhere('id_producto', (int) $productoSlug);
                }
            })->first();
            $focusId = (int) ($prod->id_producto ?? 0);
        }

        $request->merge([
            'promo'     => $bloque->id_promo_bloque,
            'focus'     => $focusId ?: null,
            'promo_seo' => true,
        ]);

        return $this->web_galeria_listar($request);
    }

    /**
     * Filtros de catálogo (talla / color / precio) — opción A: columnas JSON existentes.
     *
     * @return array{tallas: array<int,string>, colores: array<int,string>, precio_min: ?int, precio_max: ?int}
     */
    private function parseFiltrosAtributoCatalogo(Request $request): array
    {
        $parseList = static function ($raw): array {
            if (is_array($raw)) {
                $items = $raw;
            } else {
                $items = preg_split('/\s*,\s*/', (string) $raw) ?: [];
            }

            return array_values(array_unique(array_filter(array_map(
                static fn ($v) => trim((string) $v),
                $items
            ), static fn ($v) => $v !== '')));
        };

        $precioMin = $request->input('precio_min');
        $precioMax = $request->input('precio_max');

        return [
            'tallas' => $parseList($request->input('talla', [])),
            'colores' => $parseList($request->input('color', [])),
            'precio_min' => ($precioMin !== null && $precioMin !== '') ? (int) $precioMin : null,
            'precio_max' => ($precioMax !== null && $precioMax !== '') ? (int) $precioMax : null,
        ];
    }

    private function tieneFiltrosAtributoCatalogo(array $attrs): bool
    {
        return ! empty($attrs['tallas'])
            || ! empty($attrs['colores'])
            || $attrs['precio_min'] !== null
            || $attrs['precio_max'] !== null;
    }

    /**
     * Facetas para el sidebar (conteo aproximado por producto activo).
     *
     * @return array{tallas: array<int, array{nombre: string, count: int}>, colores: array<int, array{nombre: string, hex: string, count: int}>}
     */
    private function facetasCatalogoTallaColor(): array
    {
        $cols = ['id_producto', 'precio'];
        if (Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
            $cols[] = 'tallas_disponibles';
        }
        if (Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            $cols[] = 'tallas_variantes';
        }
        if (Schema::hasColumn('administracion_producto', 'colores_disponibles')) {
            $cols[] = 'colores_disponibles';
        }
        if (Schema::hasColumn('administracion_producto', 'color')) {
            $cols[] = 'color';
        }

        $rows = DB::table('administracion_producto')
            ->where('Activo', 'S')
            ->select($cols)
            ->get();

        $tallas = [];
        $colores = [];

        foreach ($rows as $row) {
            foreach (ProductoEcommerceCatalog::resolverTallasVariantes($row) as $v) {
                $t = trim((string) ($v['talla'] ?? ''));
                if ($t === '') {
                    continue;
                }
                $key = mb_strtoupper($t);
                if (! isset($tallas[$key])) {
                    $tallas[$key] = ['nombre' => $t, 'count' => 0];
                }
                $tallas[$key]['count']++;
            }

            $paleta = [];
            if (! empty($row->colores_disponibles)) {
                $raw = $row->colores_disponibles;
                $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
                if (is_array($decoded)) {
                    $paleta = $decoded;
                }
            }
            if (empty($paleta) && ! empty($row->color)) {
                $paleta = [['nombre' => (string) $row->color, 'hex' => '']];
            }
            foreach ($paleta as $c) {
                if (is_string($c)) {
                    $nombre = trim($c);
                    $hex = '';
                } else {
                    $nombre = trim((string) ($c['nombre'] ?? $c['name'] ?? ''));
                    $hex = trim((string) ($c['hex'] ?? $c['color'] ?? ''));
                }
                if ($nombre === '') {
                    continue;
                }
                $key = mb_strtolower($nombre);
                if (! isset($colores[$key])) {
                    $colores[$key] = ['nombre' => $nombre, 'hex' => $hex, 'count' => 0];
                }
                $colores[$key]['count']++;
                if ($hex !== '' && ($colores[$key]['hex'] ?? '') === '') {
                    $colores[$key]['hex'] = $hex;
                }
            }
        }

        $ordenTalla = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6, '2XL' => 6, '3XL' => 7];
        uasort($tallas, static function ($a, $b) use ($ordenTalla) {
            $ka = $ordenTalla[mb_strtoupper($a['nombre'])] ?? 50;
            $kb = $ordenTalla[mb_strtoupper($b['nombre'])] ?? 50;
            if ($ka === $kb) {
                return strcasecmp($a['nombre'], $b['nombre']);
            }

            return $ka <=> $kb;
        });
        uasort($colores, static fn ($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        return [
            'tallas' => array_values($tallas),
            'colores' => array_values($colores),
        ];
    }

    private function aplicarFiltrosAtributoCatalogo($query, array $attrs): void
    {
        if (! empty($attrs['tallas'])) {
            $query->where(function ($q) use ($attrs) {
                foreach ($attrs['tallas'] as $talla) {
                    $t = trim((string) $talla);
                    if ($t === '') {
                        continue;
                    }
                    $q->orWhere(function ($qq) use ($t) {
                        if (Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
                            $qq->orWhere('P.tallas_disponibles', 'like', '%' . $t . '%');
                        }
                        if (Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
                            $qq->orWhere('P.tallas_variantes', 'like', '%"talla":"' . $t . '"%')
                                ->orWhere('P.tallas_variantes', 'like', '%"talla": "' . $t . '"%');
                        }
                    });
                }
            });
        }

        if (! empty($attrs['colores'])) {
            $query->where(function ($q) use ($attrs) {
                foreach ($attrs['colores'] as $color) {
                    $c = trim((string) $color);
                    if ($c === '') {
                        continue;
                    }
                    $q->orWhere(function ($qq) use ($c) {
                        if (Schema::hasColumn('administracion_producto', 'colores_disponibles')) {
                            $qq->orWhere('P.colores_disponibles', 'like', '%' . $c . '%');
                        }
                        if (Schema::hasColumn('administracion_producto', 'color')) {
                            $qq->orWhere('P.color', 'like', '%' . $c . '%');
                        }
                    });
                }
            });
        }

        if ($attrs['precio_min'] !== null) {
            $query->where('P.precio', '>=', (float) $attrs['precio_min']);
        }
        if ($attrs['precio_max'] !== null) {
            $query->where('P.precio', '<=', (float) $attrs['precio_max']);
        }
    }

    /**
     * @param  array<int,int|string>  $ids
     * @return array<int,int>
     */
    private function filtrarIdsPorAtributosCatalogo(array $ids, array $attrs): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === [] || ! $this->tieneFiltrosAtributoCatalogo($attrs)) {
            return $ids;
        }

        $query = DB::table('administracion_producto as P')
            ->where('P.Activo', 'S')
            ->whereIn('P.id_producto', $ids);
        $this->aplicarFiltrosAtributoCatalogo($query, $attrs);

        return $query->pluck('P.id_producto')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Listado con atributos sin SP (paginación correcta).
     */
    private function obtenerProductosFiltradosConAtributos(
        array $filtros,
        $page,
        string $orden,
        string $busqueda,
        array $attrs
    ) {
        $pageSize = 12;
        $page = max(1, (int) $page);

        $query = DB::table('administracion_producto as P')
            ->leftJoin('administracion_producto_categoria as PC', 'P.id_producto_categoria', '=', 'PC.id_producto_categoria')
            ->leftJoin('administracion_producto_categoria_sub as PCS', 'P.id_producto_categoria_sub', '=', 'PCS.id_producto_categoria_sub')
            ->where('P.Activo', 'S')
            ->select([
                'P.id_producto',
                'P.codigo_producto',
                'P.nombre',
                'P.nombre as titulo',
                'P.descripcion',
                'P.precio',
                'P.precio_old',
                'P.stock',
                'P.url_imagen',
                'P.slug',
                'P.numero_estrellas',
                'P.etiqueta_badge',
                'P.codigo_barra',
                'P.id_producto_categoria',
                'P.id_producto_categoria_sub',
                'PC.nombre as nombre_categoria',
                'PCS.nombre as nombre_subcategoria',
            ]);

        $this->aplicarFiltrosCategoriaQuery($query, $filtros);
        $this->aplicarFiltrosAtributoCatalogo($query, $attrs);

        if ($busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $query->where(function ($q) use ($like) {
                $q->where('P.nombre', 'like', $like)
                    ->orWhere('P.descripcion', 'like', $like)
                    ->orWhere('P.codigo_producto', 'like', $like);
            });
        }

        if ($orden === 'precio_asc') {
            $query->orderBy('P.precio', 'asc');
        } elseif ($orden === 'precio_desc') {
            $query->orderBy('P.precio', 'desc');
        } elseif ($orden === 'az') {
            $query->orderBy('P.nombre', 'asc');
        } elseif ($orden === 'za') {
            $query->orderBy('P.nombre', 'desc');
        } elseif ($orden === 'nuevo') {
            $query->orderByDesc('P.id_producto');
        } else {
            // Relevancia: orden de alta del catálogo (como la plantilla)
            $query->orderBy('P.id_producto');
        }

        $total = (clone $query)->count();
        $rows = $query->forPage($page, $pageSize)->get()->all();
        $productosEnriquecidos = Producto::enhanceList($rows);

        if (Auth::check()) {
            $idProductos = collect($productosEnriquecidos)->pluck('id_producto')->toArray();
            $favoritosIds = DB::table('web_favoritos')
                ->where('id_usuario', Auth::id())
                ->whereIn('id_producto', $idProductos)
                ->pluck('id_producto')
                ->toArray();
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
            }
        } else {
            foreach ($productosEnriquecidos as $producto) {
                $producto->es_favorito = false;
            }
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect($productosEnriquecidos),
            $total,
            $pageSize,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => array_filter([
                    'filtros' => ! empty($filtros) ? implode(',', $filtros) : null,
                    'orden' => $orden !== 'relevancia' ? $orden : null,
                    'busqueda' => $busqueda !== '' ? $busqueda : null,
                    'talla' => ! empty($attrs['tallas']) ? implode(',', $attrs['tallas']) : null,
                    'color' => ! empty($attrs['colores']) ? implode(',', $attrs['colores']) : null,
                    'precio_min' => $attrs['precio_min'],
                    'precio_max' => $attrs['precio_max'],
                ], static fn ($v) => $v !== null && $v !== ''),
            ]
        );
    }
}