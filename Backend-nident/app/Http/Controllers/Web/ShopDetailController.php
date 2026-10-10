<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Gloudemans\Shoppingcart\Facades\Cart;
use Session;
use Illuminate\Support\Facades\Log;
use App\Models\MetadatosPagina;
use App\Support\LiamPageData;
use App\Support\MysqlTimezone;
use App\Support\ProductoEcommerceCatalog;
use App\Support\PromoPackOffer;
use App\Support\PromoPercentOffer;
use App\Support\PedidoProductoRepositorio;
use App\Support\ClienteSesion;
use App\Services\EnvioService;

class ShopDetailController extends Controller
{
    public function __construct(private EnvioService $envioService)
    {
    }

    public function web_shopDetail($slug)
    {
        $id_usuario = Auth::user()->id ?? null;

        // Buscar el producto por slug SEO
        $productoModel = \App\Models\Producto::where('slug', $slug)->first();

        // Compatibilidad: URLs antiguas con ID numérico
        if (!$productoModel && is_numeric($slug)) {
            $productoModel = \App\Models\Producto::find($slug);
        }

        if (!$productoModel) {
            return redirect()->route('home')->with('error', 'El producto no está disponible');
        }

        // Redirigir a la URL canónica con slug del nombre (SEO)
        $canonical = trim((string) ($productoModel->slug ?? ''));
        if ($canonical === '' || ctype_digit($canonical)) {
            $canonical = \App\Models\Producto::uniqueSlugFromNombre(
                (string) $productoModel->nombre,
                (int) $productoModel->id_producto
            );
            $productoModel->slug = $canonical;
            $productoModel->saveQuietly();
        }
        if ($slug !== $canonical) {
            return redirect()->to(url('/web_shopDetail/' . $canonical), 301);
        }

        $id = $productoModel->id_producto;
        session(['id_producto_temp' => $productoModel->slug]);

        $obtenerDetalleProducto = DB::select('CALL USP_WEB_PRODUCTO_OBTENER(?, ?)', [$id, $id_usuario]); //id_producto
        // Verificamos si el producto retornó datos (está activo)
        if (empty($obtenerDetalleProducto) || empty($obtenerDetalleProducto[0]->id_producto)) {
            return redirect()->route('home')->with('error', 'El producto no está disponible');
        }

        // Compatibilidad: SP nuevo expone "nombre", vistas legacy usan "titulo"
        $obtenerDetalleProducto[0]->titulo = $obtenerDetalleProducto[0]->titulo
            ?? $obtenerDetalleProducto[0]->nombre
            ?? $productoModel->nombre
            ?? '';

        // CARGAR DATOS DE CALIFICACIÓN (No están en el SP)
        if ($productoModel) {
            $obtenerDetalleProducto[0]->ratings_enabled = $productoModel->ratings_enabled;
            $obtenerDetalleProducto[0]->admin_rating = $productoModel->admin_rating;
            $obtenerDetalleProducto[0]->rating_summary = $productoModel->rating_summary;
            $obtenerDetalleProducto[0]->precio_mayorista = $productoModel->precio_mayorista;
            $obtenerDetalleProducto[0]->precio_mayorista_old = $productoModel->precio_mayorista_old;
            
            
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad = isset($productoModel->oferta_maxima_cantidad) ? $productoModel->oferta_maxima_cantidad : null;
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad_por_precio = isset($productoModel->oferta_maxima_cantidad_por_precio) ? $productoModel->oferta_maxima_cantidad_por_precio : null;
            $obtenerDetalleProducto[0]->codigo_barra = $productoModel->codigo_barra;
        } else {
            $obtenerDetalleProducto[0]->ratings_enabled = false;
            $obtenerDetalleProducto[0]->admin_rating = 0;
            $obtenerDetalleProducto[0]->rating_summary = (object) ['promedio' => 0, 'total' => 0, 'estrellas' => (object) []];
            $obtenerDetalleProducto[0]->precio_mayorista = null;
            $obtenerDetalleProducto[0]->precio_mayorista_old = null;
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad = null;
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad_por_precio = null;
        }

        // Pack xN desde cartel de ofertas (sesión / web_promo_item) o campos del producto
        $idPromoItemReq = (int) request()->input('promo_item', 0);
        $idBloqueReq = (int) request()->input('promo', 0);
        $packOffer = PromoPackOffer::resolveForProducto(
            (int) $id,
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad,
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad_por_precio,
            $idPromoItemReq > 0 ? $idPromoItemReq : null,
            $idBloqueReq > 0 ? $idBloqueReq : null
        );
        if ($packOffer) {
            PromoPackOffer::remember((int) $id, $packOffer);
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad = $packOffer['pack_qty'];
            $obtenerDetalleProducto[0]->oferta_maxima_cantidad_por_precio = $packOffer['pack_price'];
            $obtenerDetalleProducto[0]->promo_pack_source = $packOffer['source'];
        }

        // Descuento % (producto o cartel tipo porcentaje)
        $pctProducto = null;
        if ($productoModel && Schema::hasColumn('administracion_producto', 'descuento_porcentaje')) {
            $pctProducto = $productoModel->descuento_porcentaje ?? null;
        }
        $percentOffer = PromoPercentOffer::resolveForProducto(
            (int) $id,
            $pctProducto,
            $idPromoItemReq > 0 ? $idPromoItemReq : null,
            $idBloqueReq > 0 ? $idBloqueReq : null
        );
        if (! $percentOffer && ! $packOffer && $productoModel) {
            $popupPct = \App\Support\PopupPromoDiscount::percentForLibreProducto($productoModel);
            if ($popupPct !== null) {
                $percentOffer = PromoPercentOffer::normalize($popupPct, null, 'popup_promo');
            }
        }
        if ($percentOffer) {
            PromoPercentOffer::remember((int) $id, $percentOffer);
            $obtenerDetalleProducto[0]->descuento_porcentaje = $percentOffer['porcentaje'];
        } else {
            $obtenerDetalleProducto[0]->descuento_porcentaje = $pctProducto;
        }

        // APLICAR OFERTA DEL DÍA SI EXISTE
        $ofertaDelDia = \App\Models\OfertaDelDia::where('id_producto', $id)
            ->where('Activo', 'S')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereRaw('cantidad_disponible > cantidad_vendida')
            ->first();

        if ($ofertaDelDia) {
            $obtenerDetalleProducto[0]->precio_old = $ofertaDelDia->precio_original;
            $obtenerDetalleProducto[0]->precio = $ofertaDelDia->precio_oferta;
        } else {
            $segundaOferta = \App\Models\OfertaDelDia::where('segundo_id_producto', $id)
                ->where('Activo', 'S')
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->whereRaw('cantidad_disponible > cantidad_vendida')
                ->first();

            if ($segundaOferta) {
                $obtenerDetalleProducto[0]->precio_old = $obtenerDetalleProducto[0]->precio;
                $obtenerDetalleProducto[0]->precio = $segundaOferta->segundo_precio_oferta;
            } elseif ($percentOffer && ! $packOffer) {
                // % sobre precio de lista (si no hay oferta del día ni pack xN)
                $listaPct = (float) ($obtenerDetalleProducto[0]->precio_old > 0
                    ? $obtenerDetalleProducto[0]->precio_old
                    : $obtenerDetalleProducto[0]->precio);
                $appliedPct = PromoPercentOffer::applyToPrice($listaPct, (float) $percentOffer['porcentaje']);
                if ($appliedPct['has_percent']) {
                    $obtenerDetalleProducto[0]->precio_old = $appliedPct['precio_lista'];
                    $obtenerDetalleProducto[0]->precio = $appliedPct['precio_final'];
                }
            }
        }

        $listaImagen = ProductoEcommerceCatalog::listarVariantesVenta((int) $id);
        if ($ofertaDelDia) {
            foreach ($listaImagen as $img) {
                $img->img_precio = $ofertaDelDia->precio_oferta;
            }
        } elseif (isset($segundaOferta) && $segundaOferta) {
            foreach ($listaImagen as $img) {
                $img->img_precio = $segundaOferta->segundo_precio_oferta;
            }
        }

        $fichasTecnicasList = DB::table('administracion_producto_ficha_tecnica')
            ->where('id_producto', (int) $id)
            ->where('Activo', 'S')
            ->orderBy('orden')
            ->get();

        // Compat: vistas antiguas indexaban por orden (1, 2, 3)
        $fichasTecnicas = $fichasTecnicasList->keyBy('orden');
        $obtenerFichaTecnica = $fichasTecnicasList->values()->all();
        $listProductosQuePuedenIteresarte = DB::table('administracion_producto')
            ->where('Activo', 'S')
            ->where('id_producto', '!=', $id)
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(function ($p) {
                $p->titulo = $p->nombre;
                return $p;
            })
            ->all();

        // MARCAR FAVORITOS DEL USUARIO ACTUAL
        if (Auth::check()) {
            $idProductos = collect($listProductosQuePuedenIteresarte)->pluck('id_producto')->toArray();
            $favoritosIds = DB::table('web_favoritos')
                ->where('id_usuario', Auth::id())
                ->whereIn('id_producto', $idProductos)
                ->pluck('id_producto')
                ->toArray();
            foreach ($listProductosQuePuedenIteresarte as $producto) {
                $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
            }
        } else {
            foreach ($listProductosQuePuedenIteresarte as $producto) {
                $producto->es_favorito = false;
            }
        }

        $listaFotos = ProductoEcommerceCatalog::listarGaleriaFotos((int) $id);

        $coloresDisponibles = ProductoEcommerceCatalog::resolverColoresUi($productoModel, $listaFotos);
        $tallasDisponibles = ProductoEcommerceCatalog::resolverTallas($productoModel);
        $tallasVariantes = ProductoEcommerceCatalog::resolverTallasVariantes($productoModel);

        $listaDepartamentos = $this->envioService->listarDepartamentosEnvio();

        // Filtrar solo Lima Metropolitana
        $listaDepartamentos = $listaDepartamentos->filter(function ($item) {
            return $item->address_departamento === 'Lima Metropolitana';
        })->values();

        $product_meta = DB::table('administracion_producto')
            ->select('meta_titulo_producto', 'meta_descripcion_producto')
            ->where('id_producto', $id)
            ->first();

        return view('web.pages.web_shopDetail')
            ->with(array_merge(LiamPageData::layout('tienda'), compact(
                'obtenerFichaTecnica',
                'fichasTecnicas',
                'fichasTecnicasList',
                'listProductosQuePuedenIteresarte',
                'obtenerDetalleProducto',
                'listaImagen',
                'listaFotos',
                'coloresDisponibles',
                'tallasDisponibles',
                'tallasVariantes',
                'listaDepartamentos',
                'product_meta'
            )));
    }

    private function calcularIgv($total)
    {
        $subtotal = $total / (config('peru.igv_rate') + 1);
        $igv_rate = config('peru.igv_rate') * $subtotal;

        return [
            'subtotal' => round($subtotal, 2),
            'igv' => round($igv_rate, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * NUEVO MÉTODO: Obtener el total actual del carrito
     */
    public function getCartItems()
    {
        try {
            $items = Cart::content()->map(function ($item) {
                $slug = $item->options->slug ?? $item->options->id_producto ?? null;
                $url  = $slug ? url('/web_shopDetail/' . $slug) : null;

                return [
                    'rowId'       => $item->rowId,
                    'nombre'      => $item->name,
                    'cantidad'    => $item->qty,
                    'precio'      => number_format((float) $item->price, 2),
                    'subtotal'    => number_format($item->qty * (float) $item->price, 2),
                    'imagen'      => $item->options->img ?? $item->options->imagen ?? '',
                    'slug'        => $slug,
                    'id_producto' => $item->options->id_producto ?? null,
                    'url'         => $url,
                ];
            })->values();

            $totalCalc = $items->sum(fn($i) => (float)$i['subtotal']);

            return response()->json([
                'success'     => true,
                'items'       => $items,
                'total'       => number_format($totalCalc, 2),
                'items_count' => Cart::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'items' => [], 'total' => '0.00', 'items_count' => 0]);
        }
    }

    public function getTotalCarrito()
    {
        try {
            // Usar el helper de Cart de Laravel
            $total = floatval(str_replace(',', '', Cart::total()));
            $itemsCount = Cart::count();

            return response()->json([
                'success' => true,
                'total' => $total,
                'items_count' => $itemsCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'total' => 0,
                'items_count' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function web_shopDetail_agregar(Request $request)
    {

        $cantidad = $request->cantidad;
        $codigo_producto = $request->codigo_producto;
        $talla = strtoupper(trim((string) $request->input('talla', '')));
        // Snapshot informativo del color elegido: no interviene en precio ni en stock
        $color = mb_substr(trim((string) $request->input('color', $request->input('color_nombre', ''))), 0, 60);
        $is_estado = substr($codigo_producto, 0, 1);

        $query = DB::table('administracion_producto AS P')
            ->leftJoin(ProductoEcommerceCatalog::TABLA_FOTOS . ' AS FOT', function ($join) {
                $join->on('P.id_producto', '=', 'FOT.id_producto')
                    ->where('FOT.Activo', 'S');
            });

        $hasVariantes = \Illuminate\Support\Facades\Schema::hasColumn('administracion_producto', 'tallas_variantes');

        if ($is_estado == 'P') {
            $cols = ['P.nombre', 'P.precio', 'P.precio_mayorista', 'P.url_imagen', 'P.codigo_producto', 'P.id_producto', 'P.descripcion', 'P.peso_kilogramo', 'P.stock', 'P.slug'];
            if ($hasVariantes) {
                $cols[] = 'P.tallas_variantes';
                $cols[] = 'P.tallas_disponibles';
            }
            $query
                ->select($cols)
                ->where('P.codigo_producto', $codigo_producto);
        } else {
            $select = [
                DB::raw('COALESCE(FOT.titulo, P.nombre) AS nombre'),
                DB::raw('COALESCE(FOT.precio, P.precio) AS precio'),
                'FOT.url_imagen',
                'FOT.codigo_producto',
                'P.id_producto',
                DB::raw('COALESCE(FOT.descripcion, P.descripcion) AS descripcion'),
                DB::raw('COALESCE(FOT.stock, P.stock) AS stock'),
                'P.slug',
            ];
            if ($hasVariantes) {
                $select[] = 'P.tallas_variantes';
                $select[] = 'P.tallas_disponibles';
            }
            $query
                ->select($select)
                ->where('FOT.codigo_producto', $codigo_producto);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Producto no encontrado. Código: ' . $codigo_producto,
            ], 404);
        }

        $cantidad = (int) $cantidad;
        if ($cantidad < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Indique una cantidad válida (mínimo 1).',
            ]);
        }

        $productoLinea = $products[0];
        $idProdLinea = (int) $productoLinea->id_producto;
        $variante = ($talla !== '' && $hasVariantes)
            ? ProductoEcommerceCatalog::buscarVariantePorTalla($productoLinea, $talla)
            : null;

        if ($talla !== '' && $hasVariantes && ! $variante) {
            return response()->json([
                'status' => 'error',
                'message' => "La talla \"{$talla}\" no está disponible para este producto.",
            ]);
        }

        if ($variante) {
            $productoLinea->precio = (float) $variante['precio'];
            $productoLinea->stock = (int) $variante['stock'];
        }

        $stockDisponible = max(0, (int) ($productoLinea->stock ?? 0));
        $yaEnCarrito = $talla !== ''
            ? \App\Helpers\Helper::cantidadEnCarritoPorProductoYTalla($idProdLinea, $talla)
            : \App\Helpers\Helper::cantidadEnCarritoPorIdProducto($idProdLinea);
        if ($yaEnCarrito + $cantidad > $stockDisponible) {
            $msg = $stockDisponible <= 0
                ? ($talla !== '' ? "Sin stock para la talla {$talla}." : 'Producto sin stock disponible. No se puede añadir al carrito.')
                : ($talla !== ''
                    ? "Stock insuficiente en talla {$talla}. Solo hay {$stockDisponible} unidad(es); ya tiene {$yaEnCarrito} en el carrito."
                    : "Stock insuficiente. Solo hay {$stockDisponible} unidad(es) disponible(s); ya tiene {$yaEnCarrito} en el carrito.");

            return response()->json([
                'status' => 'error',
                'message' => $msg,
            ]);
        }

        // El color entra en el id de línea para que dos colores no compartan fila;
        // el nombre no lo repite porque carrito y checkout ya lo pintan desde options.
        $cartRowId = $productoLinea->codigo_producto
            . ($color !== '' ? '|C:' . $color : '')
            . ($talla !== '' ? '|T:' . $talla : '');
        $nombreLinea = $productoLinea->nombre . ($talla !== '' ? ' · Talla ' . $talla : '');

        // Cantidad total tras el add (incluye lo ya existente en la misma línea)
        $qtyPrevia = 0;
        foreach (Cart::content() as $ci) {
            if ((string) $ci->id === (string) $cartRowId) {
                $qtyPrevia = (int) $ci->qty;
                break;
            }
        }
        $qtyTotal = $qtyPrevia + $cantidad;

        try {
            if ($variante) {
                $precioLista = (float) $variante['precio'];
                $lineaPrecio = $this->aplicarOfertaPackSobreBase($idProdLinea, $precioLista, $qtyTotal);
            } else {
                $lineaPrecio = $this->resolverPrecioLineaCarrito($codigo_producto, $qtyTotal);
            }
            $precioFinal = (float) $lineaPrecio['unit_price'];
            $precioLista = (float) $lineaPrecio['precio_lista'];
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }

        $subtotal = (float) $lineaPrecio['line_total'];
        $resultados = $this->calcularIgv($subtotal);

        session(['id_producto_temp' => $productoLinea->slug]);

        Cart::add(
            $cartRowId,
            $nombreLinea,
            $cantidad,
            $precioFinal,
            [
                'img' => $productoLinea->url_imagen,
                'codigo_producto' => $codigo_producto,
                'id_producto' => $productoLinea->id_producto,
                'slug' => $productoLinea->slug,
                'descripcion' => $productoLinea->descripcion,
                'descripcion_t_igv' => $resultados["igv"],
                'peso_kilogramo' => $productoLinea->peso_kilogramo ?? null,
                'talla' => $talla !== '' ? $talla : null,
                'color' => $color !== '' ? $color : null,
                // No guardar descuento_pack: cambia con la cantidad y regenera rowId del carrito
                'precio_lista' => $precioLista,
                'pack_qty' => $lineaPrecio['pack_qty'] ?? 0,
                'pack_price' => $lineaPrecio['pack_price'] ?? 0,
            ]
        );

        // Reaplica precio pack sobre la cantidad consolidada (solo price: rowId estable)
        foreach (Cart::content() as $ci) {
            if ((string) $ci->id === (string) $cartRowId) {
                Cart::update($ci->rowId, [
                    'price' => $precioFinal,
                ]);
                break;
            }
        }

        // Total del carrito completo (suma de todas las líneas) — el modal debe mostrar esto, no solo la línea añadida
        $totalCarrito = floatval(str_replace(',', '', Cart::total()));
        $resultadosCarrito = $this->calcularIgv($totalCarrito);

        return response()->json([
            'status' => 'success',
            'message' => 'Se agrego en el carrito de compras',
            'result' => [
                'descripcion_cantidad' => $cantidad,
                'descripcion_precio' => $precioFinal,
                'descripcion_nombre' => $nombreLinea,
                'descripcion_imagen' => $productoLinea->url_imagen,
                'descripcion_total' => $cantidad * $precioFinal,

                'detalle_cantidad' => Cart::count(),
                'detalle_total' => Cart::total(),

                'descripcion_t_subtotal' => $resultadosCarrito['subtotal'],
                'descripcion_t_igv' => $resultadosCarrito['igv'],
                'descripcion_t_total' => $resultadosCarrito['total'],

                'total_carrito' => $totalCarrito,
            ],
        ]);
    }

    public function web_shopDetail_lista()
    {
        if (Cart::count() === 0) {
            return redirect('/tienda');
        }

        // Reaplica packs de promociones (carteles xN) sobre líneas ya existentes
        $this->recalcularPacksEnCarrito();

        $cartItems = Cart::content();
        $metaData = MetadatosPagina::metaVigente('web_shopDetail_lista');
        return view('web.pages.web_shopDetail_lista')
            ->with(array_merge(LiamPageData::layout('tienda'), compact('cartItems', 'metaData')));
    }

    /**
     * Recalcula precio pack sin regenerar rowId en cada +/-.
     * (Cambiar options regenera md5(rowId) y rompe los botones del carrito.)
     */
    private function recalcularPacksEnCarrito(): void
    {
        $items = Cart::content()->values()->all();

        foreach ($items as $ci) {
            $rowId = $ci->rowId;
            $idProd = (int) ($ci->options->id_producto ?? 0);
            $codigo = (string) ($ci->options->codigo_producto ?? '');
            $qty = (int) $ci->qty;
            if ($idProd <= 0 || $qty < 1) {
                continue;
            }

            try {
                $talla = (string) ($ci->options->talla ?? '');
                $precioListaOpt = $ci->options->precio_lista ?? null;
                if ($talla !== '' && $precioListaOpt !== null && $precioListaOpt !== '') {
                    $linea = $this->aplicarOfertaPackSobreBase($idProd, (float) $precioListaOpt, $qty);
                } elseif ($codigo !== '') {
                    $linea = $this->resolverPrecioLineaCarrito($codigo, $qty);
                } else {
                    $precioLista = (float) ($precioListaOpt ?? $ci->price);
                    $linea = $this->aplicarOfertaPackSobreBase($idProd, $precioLista, $qty);
                }
            } catch (\Throwable $e) {
                continue;
            }

            $opts = $ci->options->toArray();
            unset($opts['descuento_pack']); // inestable: cambia con qty y regenera rowId
            $opts['precio_lista'] = $linea['precio_lista'];
            $opts['pack_qty'] = (int) ($linea['pack_qty'] ?? 0);
            $opts['pack_price'] = (float) ($linea['pack_price'] ?? 0);

            $hadDescuentoKey = array_key_exists('descuento_pack', $ci->options->toArray());
            $metaChanged = $hadDescuentoKey
                || (float) ($ci->options->precio_lista ?? 0) !== (float) $opts['precio_lista']
                || (int) ($ci->options->pack_qty ?? 0) !== (int) $opts['pack_qty']
                || abs((float) ($ci->options->pack_price ?? 0) - (float) $opts['pack_price']) > 0.0001;

            try {
                if ($metaChanged) {
                    // Antes de pintar HTML: limpia meta y acepta un cambio único de rowId
                    Cart::update($rowId, [
                        'price' => (float) $linea['unit_price'],
                        'options' => $opts,
                    ]);
                } elseif (abs((float) $ci->price - (float) $linea['unit_price']) > 0.0001) {
                    Cart::update($rowId, [
                        'price' => (float) $linea['unit_price'],
                    ]);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    public function web_shopDetail_eliminar(Request $request)
    {
        $validatedData = $request->validate([
            'id_producto' => 'required|string',
        ]);

        try {

            // quitar cualquier prefijo tipo delete_
            $rowId = str_replace('delete_', '', $validatedData['id_producto']);

            // Verificar si existe
            $cartItem = Cart::get($rowId);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'El item no se encuentra en el carrito.'
                ], 404);
            }

            // Eliminar
            Cart::remove($rowId);

            // Total limpio
            $totalCarrito = floatval(str_replace(',', '', Cart::total()));

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente.',
                'total' => Cart::total(),
                'count' => Cart::count(),
                'total_carrito' => $totalCarrito
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }


    public function web_shopDetail_actualizar(Request $request)
    {
        $rowId = $request->id_producto;
        $cantidad = (int) $request->cantidad;

        if ($cantidad < 1) {
            return response()->json([
                'success' => false,
                'message' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        try {
            $cartItem = Cart::get($rowId);
        } catch (\Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La fila del carrito cambió. Recarga la página e intenta de nuevo.',
                'reload' => true,
            ], 409);
        }

        if (! $cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'El producto no está en el carrito.',
                'reload' => true,
            ]);
        }

        $idProd = (int) ($cartItem->options['id_producto'] ?? $cartItem->options->id_producto ?? 0);
        $stockDisponible = \App\Helpers\Helper::obtenerStockProducto($idProd);
        $otrasLineas = \App\Helpers\Helper::cantidadEnCarritoPorIdProductoExcluyendoFila($rowId, $idProd);

        if ($otrasLineas + $cantidad > $stockDisponible) {
            $msg = $stockDisponible <= 0
                ? 'Producto sin stock disponible.'
                : "Stock insuficiente. Máximo {$stockDisponible} unidad(es) disponible(s) (otras líneas del mismo producto cuentan).";

            return response()->json([
                'success' => false,
                'message' => $msg,
            ]);
        }

        $codigoProducto = $cartItem->options['codigo_producto'] ?? $cartItem->options->codigo_producto ?? null;
        if (! $codigoProducto) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar el producto en el carrito.',
            ], 400);
        }

        try {
            $tallaCart = (string) ($cartItem->options['talla'] ?? $cartItem->options->talla ?? '');
            $precioListaOpt = $cartItem->options['precio_lista'] ?? $cartItem->options->precio_lista ?? null;

            if ($tallaCart !== '' && $precioListaOpt !== null && $precioListaOpt !== '') {
                $lineaPrecio = $this->aplicarOfertaPackSobreBase($idProd, (float) $precioListaOpt, $cantidad);
            } else {
                $lineaPrecio = $this->resolverPrecioLineaCarrito($codigoProducto, $cantidad);
            }
            $precioUnitario = (float) $lineaPrecio['unit_price'];
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }

        // Solo qty + price: tocar options regenera rowId y rompe los botones +/- del carrito
        try {
            $updated = Cart::update($rowId, [
                'qty' => $cantidad,
                'price' => $precioUnitario,
            ]);
        } catch (\Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La fila del carrito cambió. Recarga la página e intenta de nuevo.',
                'reload' => true,
            ], 409);
        }

        $newRowId = is_object($updated) ? (string) $updated->rowId : (string) $rowId;
        $totalCarrito = floatval(str_replace(',', '', Cart::total()));
        $lineTotal = (float) $lineaPrecio['line_total'];

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada',
            'total_carrito' => $totalCarrito,
            'precio_unitario' => (float) $precioUnitario,
            'line_total' => $lineTotal,
            'descuento_pack' => (float) ($lineaPrecio['discount'] ?? 0),
            'precio_lista' => (float) $lineaPrecio['precio_lista'],
            'pack_qty' => (int) ($lineaPrecio['pack_qty'] ?? 0),
            'pack_price' => (float) ($lineaPrecio['pack_price'] ?? 0),
            'row_id' => $newRowId,
        ]);
    }

    /**
     * Precio de línea para carrito: oferta del día / mayorista + pack xN.
     *
     * @return array{unit_price: float, line_total: float, discount: float, precio_lista: float, pack_qty: int, pack_price: float}
     */
    private function resolverPrecioLineaCarrito(string $codigo_producto, int $cantidad): array
    {
        $precioLista = $this->resolverPrecioBaseSinPack($codigo_producto);
        $idProd = (int) (DB::table('administracion_producto')
            ->where('codigo_producto', $codigo_producto)
            ->value('id_producto') ?? 0);

        // Variaciones de foto (código no empieza con P) ya resueltas en precio base
        if ($idProd <= 0) {
            $idProd = (int) (DB::table(ProductoEcommerceCatalog::TABLA_FOTOS)
                ->where('codigo_producto', $codigo_producto)
                ->value('id_producto') ?? 0);
        }

        return $this->aplicarOfertaPackSobreBase($idProd, $precioLista, $cantidad);
    }

    /**
     * @return array{unit_price: float, line_total: float, discount: float, precio_lista: float, pack_qty: int, pack_price: float}
     */
    private function aplicarOfertaPackSobreBase(int $idProducto, float $precioLista, int $cantidad): array
    {
        $umbral = null;
        $precioPack = null;
        $prodFull = null;
        if ($idProducto > 0) {
            $prodFull = DB::table('administracion_producto')->where('id_producto', $idProducto)->first();
            $packOffer = PromoPackOffer::resolveForProducto(
                $idProducto,
                $prodFull->oferta_maxima_cantidad ?? null,
                $prodFull->oferta_maxima_cantidad_por_precio ?? null
            );
            if ($packOffer) {
                $umbral = $packOffer['pack_qty'];
                $precioPack = $packOffer['pack_price'];
            }
        }

        // Pack xN tiene prioridad; si no hay pack, aplicar descuento %
        if ($umbral && $precioPack) {
            $calc = \App\Helpers\Helper::calcularOfertaPackLinea($umbral, $precioPack, $precioLista, $cantidad);

            return [
                'unit_price' => (float) $calc['unit_price'],
                'line_total' => (float) $calc['line_total'],
                'discount' => (float) $calc['discount'],
                'precio_lista' => $precioLista,
                'pack_qty' => (int) $calc['pack_qty'],
                'pack_price' => (float) $calc['pack_price'],
            ];
        }

        $percentOffer = $idProducto > 0
            ? PromoPercentOffer::resolveForProducto(
                $idProducto,
                $prodFull->descuento_porcentaje ?? null
            )
            : null;

        if (! $percentOffer && $idProducto > 0 && $prodFull) {
            $popupPct = \App\Support\PopupPromoDiscount::percentForLibreProducto($prodFull);
            if ($popupPct !== null) {
                $percentOffer = PromoPercentOffer::normalize($popupPct, null, 'popup_promo');
            }
        }

        if ($percentOffer) {
            $applied = PromoPercentOffer::applyToPrice($precioLista, (float) $percentOffer['porcentaje']);
            $unit = (float) $applied['precio_final'];
            $lineTotal = round($unit * max(0, $cantidad), 2);
            $full = round($precioLista * max(0, $cantidad), 2);

            return [
                'unit_price' => $unit,
                'line_total' => $lineTotal,
                'discount' => max(0.0, round($full - $lineTotal, 2)),
                'precio_lista' => $precioLista,
                'pack_qty' => 0,
                'pack_price' => 0.0,
            ];
        }

        $calc = \App\Helpers\Helper::calcularOfertaPackLinea(null, null, $precioLista, $cantidad);

        return [
            'unit_price' => (float) $calc['unit_price'],
            'line_total' => (float) $calc['line_total'],
            'discount' => (float) $calc['discount'],
            'precio_lista' => $precioLista,
            'pack_qty' => (int) $calc['pack_qty'],
            'pack_price' => (float) $calc['pack_price'],
        ];
    }

    /**
     * Precio unitario lista (sin pack): oferta del día / mayorista.
     */
    private function resolverPrecioBaseSinPack(string $codigo_producto): float
    {
        return $this->resolverPrecioUnitarioCarrito($codigo_producto, 1, false);
    }

    /**
     * Precio unitario para carrito: oferta del día, mayorista y (opcional) oferta pack xN.
     */
    private function resolverPrecioUnitarioCarrito(string $codigo_producto, int $cantidad, bool $aplicarPack = true): float
    {
        $is_estado = substr($codigo_producto, 0, 1);

        $query = DB::table('administracion_producto AS P')
            ->leftJoin(ProductoEcommerceCatalog::TABLA_FOTOS . ' AS FOT', function ($join) {
                $join->on('P.id_producto', '=', 'FOT.id_producto')
                    ->where('FOT.Activo', 'S');
            });

        if ($is_estado == 'P') {
            $query
                ->select('P.nombre', 'P.precio', 'P.precio_mayorista', 'P.url_imagen', 'P.codigo_producto', 'P.id_producto', 'P.descripcion', 'P.peso_kilogramo', 'P.stock')
                ->where('P.codigo_producto', $codigo_producto);
        } else {
            $query
                ->select(
                    DB::raw('COALESCE(FOT.titulo, P.nombre) AS nombre'),
                    DB::raw('COALESCE(FOT.precio, P.precio) AS precio'),
                    'FOT.url_imagen',
                    'FOT.codigo_producto',
                    'P.id_producto',
                    DB::raw('COALESCE(FOT.descripcion, P.descripcion) AS descripcion'),
                    DB::raw('COALESCE(FOT.stock, P.stock) AS stock'),
                    'P.precio_mayorista'
                )
                ->where('FOT.codigo_producto', $codigo_producto);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            throw new \RuntimeException('Producto no encontrado.');
        }

        $idProdCart = (int) $products[0]->id_producto;

        $ofertaCart = \App\Models\OfertaDelDia::where('id_producto', $idProdCart)
            ->where('Activo', 'S')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereRaw('cantidad_disponible > cantidad_vendida')
            ->first();

        if ($ofertaCart) {
            $products[0]->precio = $ofertaCart->precio_oferta;
        } else {
            $segundaOfertaCart = \App\Models\OfertaDelDia::where('segundo_id_producto', $idProdCart)
                ->where('Activo', 'S')
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->whereRaw('cantidad_disponible > cantidad_vendida')
                ->first();

            if ($segundaOfertaCart) {
                $products[0]->precio = $segundaOfertaCart->segundo_precio_oferta;
            }
        }

        $isMayorista = Auth::check() ? \App\Helpers\Helper::validateIsMayorista(Auth::id()) : false;

        $precioFinal = (float) $products[0]->precio;
        if ($isMayorista && ! empty($products[0]->precio_mayorista) && $products[0]->precio_mayorista > 0) {
            if ((float) $products[0]->precio_mayorista < (float) $products[0]->precio) {
                $precioFinal = (float) $products[0]->precio_mayorista;
            }
        }

        if ($aplicarPack) {
            $prodFull = DB::table('administracion_producto')->where('id_producto', $idProdCart)->first();
            $packOffer = PromoPackOffer::resolveForProducto(
                $idProdCart,
                $prodFull->oferta_maxima_cantidad ?? null,
                $prodFull->oferta_maxima_cantidad_por_precio ?? null
            );
            if ($packOffer) {
                $precioFinal = \App\Helpers\Helper::aplicarPrecioOfertaPorCantidad(
                    $packOffer['pack_qty'],
                    $packOffer['pack_price'],
                    $precioFinal,
                    $cantidad
                );
            }
        }

        return $precioFinal;
    }


    public function getProductosSugeridos($id)
    {
        try {
            $producto = DB::table('administracion_producto')
                ->where('id_producto', $id)
                ->where('Activo', 'S')
                ->first();

            if (!$producto) {
                return response()->json(['sugeridos' => []]);
            }

            $selectColumns = ['id_producto', 'nombre', 'precio', 'precio_old', 'precio_mayorista', 'precio_mayorista_old', 'url_imagen', 'codigo_producto', 'numero_estrellas', 'slug'];

            // Buscar productos de la misma subcategoria primero
            $sugeridos = collect();

            if ($producto->id_producto_categoria_sub) {
                $sugeridos = DB::table('administracion_producto')
                    ->select($selectColumns)
                    ->where('id_producto_categoria_sub', $producto->id_producto_categoria_sub)
                    ->where('id_producto', '!=', $id)
                    ->where('Activo', 'S')
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            }

            // Si no hay suficientes, complementar con la misma categoria
            if ($sugeridos->count() < 10 && $producto->id_producto_categoria) {
                $idsExcluir = $sugeridos->pluck('id_producto')->push($id)->toArray();
                $faltantes = DB::table('administracion_producto')
                    ->select($selectColumns)
                    ->where('id_producto_categoria', $producto->id_producto_categoria)
                    ->whereNotIn('id_producto', $idsExcluir)
                    ->where('Activo', 'S')
                    ->inRandomOrder()
                    ->limit(10 - $sugeridos->count())
                    ->get();

                $sugeridos = $sugeridos->merge($faltantes);
            }

            // Fallback: si no se encontraron por categoría, mostrar productos aleatorios
            if ($sugeridos->isEmpty()) {
                $sugeridos = DB::table('administracion_producto')
                    ->select($selectColumns)
                    ->where('id_producto', '!=', $id)
                    ->where('Activo', 'S')
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            }

            $sugeridosFinal = \App\Models\Producto::enhanceList($sugeridos->values()->all());

            // MARCAR FAVORITOS DEL USUARIO ACTUAL
            if (Auth::check()) {
                $idProductos = collect($sugeridosFinal)->pluck('id_producto')->toArray();
                $favoritosIds = DB::table('web_favoritos')
                    ->where('id_usuario', Auth::id())
                    ->whereIn('id_producto', $idProductos)
                    ->pluck('id_producto')
                    ->toArray();
                foreach ($sugeridosFinal as $producto) {
                    $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
                }
            } else {
                foreach ($sugeridosFinal as $producto) {
                    $producto->es_favorito = false;
                }
            }

            return response()->json(['sugeridos' => $sugeridosFinal]);

        } catch (\Exception $e) {
            return response()->json(['sugeridos' => [], 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /web_whatsapp_pedido_producto
     * Pedido WhatsApp desde la página de producto individual (sin carrito previo).
     */
    public function whatsappPedidoProducto(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|integer',
            'nombre'      => 'required|string|max:255',
            'precio'      => 'required|numeric',
            'cantidad'    => 'required|integer|min:1',
            'talla'       => 'nullable|string|max:20',
        ]);

        try {
            do {
                $codigoPedido = 'PED-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
            } while (DB::table('ope_pedidos')->where('codigo_pedido', $codigoPedido)->exists());

            $count        = DB::table('ope_pedidos')->count() + 1;
            $codigoBoleta = 'B001-' . str_pad($count, 8, '0', STR_PAD_LEFT);
            // Mis Compras filtra por administracion_cliente.id_cliente (NO users.id)
            $idCliente    = ClienteSesion::idCliente();
            $contacto     = ClienteSesion::datosContacto();

            MysqlTimezone::applySession();

            $result = DB::select('CALL USP_WEB_OPE_PEDIDOS_CREAR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $contacto['nombre'],
                $contacto['apellido'],
                $contacto['email'],
                'Por confirmar',
                $contacto['telefono'] !== '' ? $contacto['telefono'] : '',
                '', null, null, null, '', '',
                $codigoPedido, $codigoBoleta, $idCliente,
                1, 1, 1, 0, 0,
                'Pedido por WhatsApp (producto individual)',
                'whatsapp',
            ]);

            $idPedido = $result[0]->id_pedido ?? null;
            if (!$idPedido) throw new \RuntimeException('No se pudo crear el pedido.');

            $talla = strtoupper(trim((string) $request->input('talla', '')));
            $nombre = trim((string) $request->input('nombre'));
            if ($talla !== '' && ! preg_match('/\bTalla\s+/iu', $nombre)) {
                $nombre .= ' · Talla ' . $talla;
            }

            $idProducto = (int) $request->input('id_producto');
            $codigoProducto = trim((string) $request->input('codigo_producto', ''));
            if ($codigoProducto === '' || ctype_digit($codigoProducto)) {
                $codigoProducto = (string) (DB::table('administracion_producto')
                    ->where('id_producto', $idProducto)
                    ->value('codigo_producto') ?: $idProducto);
            }

            $imagenRaw = trim((string) $request->input('imagen', ''));
            // Guardar ruta relativa si viene URL absoluta del zoom
            $imagenRel = $imagenRaw;
            if (preg_match('#^https?://#i', $imagenRaw)) {
                $path = parse_url($imagenRaw, PHP_URL_PATH) ?: '';
                $imagenRel = ltrim((string) $path, '/');
            }
            if ($imagenRel === '') {
                $imagenRel = (string) (DB::table('administracion_producto')
                    ->where('id_producto', $idProducto)
                    ->value('url_imagen') ?: '');
            }

            DB::select('CALL USP_WEB_OPE_PEDIDOS_PRODUCTO_CREAR(?,?,?,?,?,?,?,?)', [
                $idPedido,
                $codigoProducto,
                $request->input('cantidad'),
                $idProducto,
                $imagenRel,
                $nombre,
                $request->input('precio'),
                $request->input('descripcion', ''),
            ]);

            PedidoProductoRepositorio::snapshotTrasCrearLinea(
                (int) $idPedido,
                $idProducto,
                [
                    'talla' => $talla,
                    'nombre_linea' => $nombre,
                    'precio' => $request->input('precio'),
                    'cantidad' => $request->input('cantidad'),
                    'url_imagen' => $imagenRel,
                    'descripcion' => $request->input('descripcion', ''),
                ]
            );

            DB::table('ope_pedidos')->where('id_pedido', $idPedido)->update([
                'metodo_pago'     => 'WhatsApp',
                'radio_tipo_pago' => 'whatsapp',
                'updated_at'      => now(),
            ]);

            $totalPedido = round((float) $request->input('precio') * (int) $request->input('cantidad'), 2);
            $this->guardarConfirmacionWhatsapp($codigoPedido, $totalPedido);

            // Vaciar carrito también (puede tener items de antes)
            Cart::destroy();

            return response()->json([
                'success'       => true,
                'codigo_pedido' => $codigoPedido,
                'id_pedido'     => $idPedido,
                'total'         => $totalPedido,
                'redirect'      => url('/thank-you'),
                'message'       => 'Tu pedido ha sido enviado por WhatsApp. Esperamos tu pronta respuesta.',
            ]);

        } catch (\Exception $e) {
            Log::error('whatsappPedidoProducto error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /web_whatsapp_pedido
     * Registra el pedido con origen 'whatsapp', vacía el carrito y devuelve el código.
     */
    public function whatsappPedido(Request $request)
    {
        if (Cart::count() === 0) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.'], 422);
        }

        try {
            // Generar código de pedido único (mismo esquema que CheckoutController)
            do {
                $codigoPedido = 'PED-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
            } while (DB::table('ope_pedidos')->where('codigo_pedido', $codigoPedido)->exists());

            $count = DB::table('ope_pedidos')->count() + 1;
            $codigoBoleta = 'B001-' . str_pad($count, 8, '0', STR_PAD_LEFT);

            // Mis Compras filtra por administracion_cliente.id_cliente (NO users.id)
            $idCliente = ClienteSesion::idCliente();
            $contacto  = ClienteSesion::datosContacto();
            $telefono  = trim((string) $request->input('telefono', ''));
            if ($telefono === '') {
                $telefono = $contacto['telefono'];
            }

            // Crear pedido usando el mismo SP, con datos mínimos (se completarán por WhatsApp)
            MysqlTimezone::applySession();

            $result = DB::select('CALL USP_WEB_OPE_PEDIDOS_CREAR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $contacto['nombre'],
                $contacto['apellido'],
                $contacto['email'],
                'Por confirmar',     // dirección
                $telefono,
                '',                  // dni
                null,                // ruc
                null, null,          // pais, dept
                '',                  // provincia
                '',                  // distrito
                $codigoPedido,
                $codigoBoleta,
                $idCliente,
                1, 1, 1,             // check_terminos, check_direccion, check_privacidad
                0,                   // radio_metodo_pago (0 = otro)
                0,                   // precio envío (se coordina por WA)
                'Pedido por WhatsApp',
                'whatsapp',          // radio_tipo_pago → marca el origen
            ]);

            $idPedido = $result[0]->id_pedido ?? null;

            if (!$idPedido) {
                throw new \RuntimeException('No se pudo crear el pedido.');
            }

            // Registrar cada producto del carrito (sin descontar stock, aún no está pagado)
            foreach (Cart::content() as $item) {
                $tallaItem = strtoupper(trim((string) ($item->options['talla'] ?? $item->options->talla ?? '')));
                $nombreItem = (string) ($item->name ?? '');
                if ($tallaItem !== '' && ! preg_match('/\bTalla\s+/iu', $nombreItem)) {
                    $nombreItem = trim($nombreItem) . ' · Talla ' . $tallaItem;
                }

                $idProducto = (int) ($item->options['id_producto'] ?? $item->id);
                $codigoItem = trim((string) ($item->options['codigo_producto'] ?? $item->options->codigo_producto ?? $item->id));
                if ($codigoItem === '' || ctype_digit($codigoItem)) {
                    $codigoItem = (string) (DB::table('administracion_producto')
                        ->where('id_producto', $idProducto)
                        ->value('codigo_producto') ?: $idProducto);
                }
                $imgItem = trim((string) ($item->options['img'] ?? ''));

                DB::select('CALL USP_WEB_OPE_PEDIDOS_PRODUCTO_CREAR(?,?,?,?,?,?,?,?)', [
                    $idPedido,
                    $codigoItem,
                    $item->qty,
                    $idProducto,
                    $imgItem,
                    $nombreItem,
                    $item->price,
                    $item->options['descripcion'] ?? '',
                ]);

                PedidoProductoRepositorio::snapshotTrasCrearLinea((int) $idPedido, $idProducto, [
                    'talla' => $tallaItem,
                    'color' => trim((string) ($item->options['color'] ?? $item->options->color ?? '')),
                    'nombre_linea' => $nombreItem,
                    'precio' => $item->price,
                    'cantidad' => $item->qty,
                    'url_imagen' => $imgItem,
                    'descripcion' => $item->options['descripcion'] ?? '',
                ]);
            }

            // Marcar el pedido como WhatsApp en la tabla directamente
            DB::table('ope_pedidos')
                ->where('id_pedido', $idPedido)
                ->update([
                    'metodo_pago'     => 'WhatsApp',
                    'radio_tipo_pago' => 'whatsapp',
                    'updated_at'      => now(),
                ]);

            $totalPedido = 0.0;
            foreach (Cart::content() as $item) {
                $totalPedido += (float) $item->price * (int) $item->qty;
            }
            $totalPedido = round($totalPedido, 2);
            $this->guardarConfirmacionWhatsapp($codigoPedido, $totalPedido);

            // Vaciar el carrito
            Cart::destroy();

            return response()->json([
                'success'       => true,
                'codigo_pedido' => $codigoPedido,
                'id_pedido'     => $idPedido,
                'total'         => $totalPedido,
                'redirect'      => url('/thank-you'),
                'message'       => 'Tu pedido ha sido enviado por WhatsApp. Esperamos tu pronta respuesta.',
            ]);

        } catch (\Exception $e) {
            Log::error('whatsappPedido error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Sesión para /thank-you tras pedido WhatsApp (misma ventana de 15 min que checkout).
     */
    private function guardarConfirmacionWhatsapp(string $codigoPedido, float $total): void
    {
        session([
            'order_confirmation' => [
                'codigo_pedido' => $codigoPedido,
                'total' => $total,
                'expires_at' => now()->addMinutes(15)->toDateTimeString(),
                'origen' => 'whatsapp',
                'delivery_razon' => null,
                'delivery_mensaje' => 'Coordinaremos la entrega por WhatsApp.',
                'monto_pagado' => null,
            ],
        ]);
    }
}