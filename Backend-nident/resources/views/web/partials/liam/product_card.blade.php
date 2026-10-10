@php
    $nombreProducto = $producto->titulo ?? $producto->nombre ?? '';
    $slug           = $producto->slug ?? $producto->id_producto;
    $img            = \App\Support\LiamPageData::img($producto->url_imagen ?? null, 'temp02/img/inicio/productos/1.jpg');
    $precio         = (float)($producto->precio ?? 0);
    $precioOld      = (float)($producto->precio_old ?? 0);
    $idProducto     = $producto->id_producto ?? 0;
    $packOffer = \App\Support\PromoPackOffer::resolveForProducto(
        (int) $idProducto,
        $producto->oferta_maxima_cantidad ?? null,
        $producto->oferta_maxima_cantidad_por_precio ?? null
    );
    $percentOffer = \App\Support\PromoPercentOffer::resolveForProducto(
        (int) $idProducto,
        $producto->descuento_porcentaje ?? null,
        isset($producto->id_promo_item_contexto) ? (int) $producto->id_promo_item_contexto : (isset($promoItemId) ? (int) $promoItemId : null),
        isset($producto->id_promo_bloque_contexto) ? (int) $producto->id_promo_bloque_contexto : (isset($promoBloqueId) ? (int) $promoBloqueId : null)
    );
    // Descuento del popup (Comprar): solo productos libres sin otra oferta
    if (! $percentOffer && ! $packOffer) {
        $popupPct = \App\Support\PopupPromoDiscount::percentForLibreProducto($producto);
        if ($popupPct !== null) {
            $percentOffer = \App\Support\PromoPercentOffer::normalize($popupPct, null, 'popup_promo');
        }
    }
    // Aplicar % en listado si no hay pack xN activo
    $pctBadge = 0;
    if ($percentOffer && ! $packOffer) {
        $listaPct = $precioOld > 0 ? $precioOld : $precio;
        $applied = \App\Support\PromoPercentOffer::applyToPrice($listaPct, (float) $percentOffer['porcentaje']);
        if ($applied['has_percent']) {
            $precioOld = $applied['precio_lista'];
            $precio = $applied['precio_final'];
            $pctBadge = (int) round($applied['porcentaje']);
        }
    }
    $descuento = ($precioOld > $precio && $precioOld > 0) ? round((1 - $precio / $precioOld) * 100) : 0;
    if ($pctBadge > 0) {
        $descuento = $pctBadge;
    }
    $categoria      = $producto->nombre_categoria ?? $producto->categoria ?? '';
    $esFavorito     = !empty($producto->es_favorito);
    $urlDetalle = url('/web_shopDetail/' . $slug);
    $promoItemQs = $packOffer['id_promo_item'] ?? ($percentOffer['id_promo_item'] ?? null);
    if ($promoItemQs) {
        $urlDetalle .= '?promo_item=' . (int) $promoItemQs;
    }
    // Solo muestra badge si el admin eligió NUEVO / POPULAR / LIMITADO (sin fallback inventado)
    $badgeRaw = trim((string) ($producto->etiqueta_badge ?? ''));
    if ($badgeRaw === '' && ! empty($producto->codigo_barra)) {
        $legacyBarra = strtoupper(trim((string) $producto->codigo_barra));
        if (in_array($legacyBarra, ['NUEVO', 'POPULAR', 'LIMITADO'], true)) {
            $badgeRaw = $legacyBarra;
        }
    }
    // Datos para "Agregar" directo al carrito (como la plantilla: talla y color por defecto)
    $codigoCarrito = trim((string) ($producto->codigo_producto ?? ''));
    $tallasRaw = $producto->tallas_variantes ?? null;
    $coloresRaw = $producto->colores_disponibles ?? null;
    if ($codigoCarrito === '' || ($tallasRaw === null && ! isset($producto->tallas_disponibles))) {
        $datosCarrito = \Illuminate\Support\Facades\DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->first(['codigo_producto', 'tallas_variantes', 'tallas_disponibles', 'colores_disponibles']);
        if ($datosCarrito) {
            $codigoCarrito = $codigoCarrito !== '' ? $codigoCarrito : trim((string) $datosCarrito->codigo_producto);
            $tallasRaw = $datosCarrito->tallas_variantes ?: $datosCarrito->tallas_disponibles;
            $coloresRaw = $datosCarrito->colores_disponibles;
        }
    } elseif ($tallasRaw === null) {
        $tallasRaw = $producto->tallas_disponibles;
    }
    // Tallas con stock; la plantilla usa la segunda talla de la lista (o la primera)
    $tallasConStock = [];
    $variantes = is_string($tallasRaw) ? json_decode($tallasRaw, true) : null;
    if (is_array($variantes)) {
        foreach ($variantes as $v) {
            if (! empty($v['talla']) && (int) ($v['stock'] ?? 0) > 0) {
                $tallasConStock[] = (string) $v['talla'];
            }
        }
    } elseif (is_string($tallasRaw) && trim($tallasRaw) !== '') {
        $tallasConStock = array_values(array_filter(array_map('trim', explode(',', $tallasRaw))));
    }
    $tallaCarrito = $tallasConStock[1] ?? ($tallasConStock[0] ?? '');
    $colores = is_string($coloresRaw) ? json_decode($coloresRaw, true) : null;
    $colorCarrito = is_array($colores) && ! empty($colores[0]['nombre']) ? (string) $colores[0]['nombre'] : '';
    $badgeLabel = strtoupper($badgeRaw);
    $badgeColorsMap = ['NUEVO' => '#111', 'POPULAR' => '#f4a261', 'LIMITADO' => '#6a0572'];
    $showBadge = isset($badgeColorsMap[$badgeLabel]);
    $badgeColor = $showBadge ? $badgeColorsMap[$badgeLabel] : null;
@endphp
<div class="cat-card">
    <div class="cat-card__img-wrap">
        <a href="{{ $urlDetalle }}">
            <img src="{{ $img }}" alt="{{ $nombreProducto }}" loading="lazy">
        </a>
        @if($showBadge)
        <span class="cat-card__badge-top" style="background:{{ $badgeColor }};">{{ $badgeLabel }}</span>
        @endif
        @if($descuento > 0)
        <span class="cat-card__badge">{{ $descuento }}% DSCTO</span>
        @endif
        <button type="button" class="cat-card__wish {{ $esFavorito ? 'active' : '' }}"
                id="idActualizarDivFavorito{{ $idProducto }}"
                data-id="{{ $idProducto }}"
                @if($esFavorito && Auth::check()) data-user-id="{{ Auth::id() }}" @endif
                data-tooltip="{{ $esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos' }}"
                aria-label="{{ $esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos' }}">
            <i class="fa {{ $esFavorito ? 'fa-heart' : 'fa-heart-o' }}"></i>
        </button>
    </div>
    <p class="cat-card__category">{{ strtoupper($categoria) }}</p>
    <p class="cat-card__name"><a href="{{ $urlDetalle }}" style="color:inherit;text-decoration:none;">{{ $nombreProducto }}</a></p>
    <div class="cat-card__footer">
        <div>
            @if($precioOld > $precio)
            <span class="cat-card__price-old">S/ {{ number_format($precioOld, 2) }}</span>
            @endif
            <span class="cat-card__price">S/ {{ number_format($precio, 2) }}</span>
        </div>
        @if($codigoCarrito !== '')
        <button type="button" class="cat-card__add lv-cat-add-btn"
                data-codigo="{{ $codigoCarrito }}"
                data-talla="{{ $tallaCarrito }}"
                data-color="{{ $colorCarrito }}">
            <i class="fa fa-shopping-bag"></i> Agregar
        </button>
        @else
        <a href="{{ $urlDetalle }}" class="cat-card__add" data-tooltip="Ver características">
            <i class="fa fa-shopping-bag"></i> Agregar
        </a>
        @endif
    </div>
</div>
