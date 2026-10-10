@extends('web.base_shop')
<!-- Contenido en el Head de la pagina -->
@section('head_page')
<!-- extras -->
@vite(['resources/sass/web_shopDetail.scss'])
<link rel="stylesheet" href="{{ URL::asset('temp/assets/css/plugins/nouislider/nouislider.css') }}">
<link rel="stylesheet" href="{{ URL::asset('temp/assets/css/shop.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('temp02/css/shop-detail.css') }}?v={{ filemtime(public_path('temp02/css/shop-detail.css')) }}">
<link rel="stylesheet" href="{{ asset('temp02/css/cart-modal.css') }}">
{{-- fonts.css al final, como en la plantilla (títulos en Cinzel, ej. "Quizá también te guste...") --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ filemtime(public_path('temp02/css/fonts.css')) }}">
@endsection
<!-- COOKIES AND POLICE : HEADER-->
@section('title', $product_meta->meta_titulo_producto ?? 'Valento')
@section('meta_description', $product_meta->meta_descripcion_producto ?? '')
@section('content')
@include('web.partials.liam.header')
<style>
.sd-action-row .sd-back-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    flex:0 0 auto; white-space:nowrap;
    font-family:'Montserrat',sans-serif; font-size:12px; font-weight:600; letter-spacing:.3px; line-height:1;
    color:#c0392b; background:#fff; border:1.5px solid #c0392b; border-radius:22px;
    padding:10px 16px; cursor:pointer; height:100%;
    transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
    box-shadow:0 2px 8px rgba(192,57,43,.10);
}
.sd-action-row .sd-back-btn:hover { background:#c0392b; color:#fff; box-shadow:0 6px 14px rgba(192,57,43,.26); transform:translateY(-1px); }
.sd-action-row .sd-back-btn i { font-size:13px; transition:transform .2s ease; }
.sd-action-row .sd-back-btn:hover i { transform:translateX(-3px); }

/* Compra Rápida + WhatsApp: mismo diseño delgado tipo píldora que "Volver atrás" */
.sd-action-row .sd-quick-buy-btn,
.sd-action-row .sd-wa-order-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    flex:0 0 auto; white-space:nowrap; width:auto; min-width:0;
    font-family:'Montserrat',sans-serif; font-size:12px; font-weight:600; letter-spacing:.3px; line-height:1;
    text-transform:none; text-decoration:none;
    background:#fff; border-radius:22px;
    padding:10px 16px; margin:0; cursor:pointer; height:auto; min-height:0;
    transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}
/* Compra Rápida — teal */
.sd-action-row .sd-quick-buy-btn { color:#12a89b; border:1.5px solid #12a89b; }
.sd-action-row .sd-quick-buy-btn:hover {
    background:#12a89b; color:#fff; box-shadow:0 6px 14px rgba(18,168,155,.28); transform:translateY(-1px);
}
.sd-action-row .sd-quick-buy-btn i { font-size:13px; }
/* Comprar por WhatsApp — verde */
.sd-action-row .sd-wa-order-btn { color:#1a9e4b; border:1.5px solid #25d366; }
.sd-action-row .sd-wa-order-btn:hover {
    background:#25d366; color:#fff; box-shadow:0 6px 14px rgba(37,211,102,.28); transform:translateY(-1px);
}
.sd-action-row .sd-wa-order-btn svg,
.sd-action-row .sd-wa-order-btn svg path { fill: currentColor !important; }
.sd-action-row .sd-wa-order-btn svg { width:15px; height:15px; }
</style>
<script>
function lvVolverAtras() {
    try {
        var origin = window.location.origin;
        var ref = document.referrer || '';
        var path = (ref.indexOf(origin) === 0) ? ref.slice(origin.length) : '';
        var fromTienda = /^\/tienda(\/|\?|#|$)/.test(path);
        if (fromTienda && window.history.length > 1) {
            window.history.back();
            return;
        }
        var ret = null;
        try { ret = sessionStorage.getItem('lvShopReturnUrl'); } catch (e) {}
        window.location.href = ret || @json(url('/tienda'));
    } catch (e) {
        window.location.href = @json(url('/tienda'));
    }
}
</script>
 
@php
$isMayorista = Auth::check() ? \App\Helpers\Helper::validateIsMayorista(Auth::id()) : false;
@endphp
<div class="page-wrapper">
<main class="main">
<div class="container" style="padding-top:10px;">

    @php
    $prod = $obtenerDetalleProducto[0];
    $isMayoristaDet = $isMayorista;
    $tienePrecioMayorista = !empty($prod->precio_mayorista) && $prod->precio_mayorista > 0;
    $precioMayorista      = $tienePrecioMayorista ? $prod->precio_mayorista : $prod->precio;
    $descuentoPublico     = (!empty($prod->precio_old) && $prod->precio_old > $prod->precio)
        ? round((1 - $prod->precio / $prod->precio_old) * 100) : 0;
    $descuentoMayorista   = ($tienePrecioMayorista && !empty($prod->precio_mayorista_old) && $prod->precio_mayorista_old > $precioMayorista)
        ? round((1 - $precioMayorista / $prod->precio_mayorista_old) * 100) : 0;
    $precioUiBase = ($isMayorista && $tienePrecioMayorista) ? (float) $precioMayorista : (float) str_replace(',', '', $prod->precio);
    $qtyInicialLinea = 1;
    $puLineaInicial = $precioUiBase;
    $omC = $prod->oferta_maxima_cantidad ?? null;
    $omP = $prod->oferta_maxima_cantidad_por_precio ?? null;
    if ($omC !== null && $omC !== '' && $omP !== null && $omP !== '') {
        $um = (int) round((float) $omC);
        $pv = (float) $omP;
        if ($um >= 1 && $pv > 0 && $qtyInicialLinea >= $um) { $puLineaInicial = $pv; }
    }
    $subtotalLineaInicial = $puLineaInicial * $qtyInicialLinea;
    $descuentoSoles = (!empty($prod->precio_old) && $prod->precio_old > $prod->precio)
        ? round($prod->precio_old - $prod->precio, 2) : 0;

    $galeriaThumbs = [];
    $galeriaUrlsVistas = [];
    $agregarThumbGaleria = function (string $url, string $alt) use (&$galeriaThumbs, &$galeriaUrlsVistas) {
        $url = trim($url);
        if ($url === '') {
            return;
        }
        $clave = strtolower(str_replace('\\', '/', $url));
        if (isset($galeriaUrlsVistas[$clave])) {
            return;
        }
        $galeriaUrlsVistas[$clave] = true;
        $galeriaThumbs[] = ['url' => $url, 'alt' => $alt];
    };
    $agregarThumbGaleria((string) ($prod->url_imagen ?? ''), 'Principal');
    foreach ($listaFotos ?? [] as $foto) {
        $agregarThumbGaleria((string) ($foto->url_imagen ?? ''), 'Galería');
    }
    @endphp

    <!-- Breadcrumb -->
    <div class="sd-breadcrumb">
        <a href="{{ url('/') }}">INICIO</a>
        <span class="sep">></span>
        <a href="{{ url('/tienda') }}">TIENDA</a>
        <span class="sep">></span>
        <span>{{ strtoupper(Str::limit($prod->titulo, 50)) }}</span>
    </div>

    <!-- Main layout -->
    <div class="sd-main">

        <div class="sd-gallery">
            <div class="sd-thumbs" id="product-zoom-gallery">
                <div class="modern-thumbs-wrapper">
                @foreach($galeriaThumbs as $key => $thumb)
                <a class="modern-thumb-link @if($key === 0) active-thumb @endif"
                   href="javascript:void(0)"
                   data-src="{{ URL::asset($thumb['url']) }}"
                   data-index="{{ $key }}">
                    <img class="sd-thumb @if($key === 0) active @endif"
                         src="{{ URL::asset($thumb['url']) }}"
                         alt="{{ $thumb['alt'] }} {{ $key + 1 }}">
                </a>
                @endforeach
                </div>
            </div>

            <div class="sd-main-img-wrap">
                <img class="sd-main-img clickable-main-img" id="product-zoom"
                     src="{{ URL::asset($prod->url_imagen) }}"
                     alt="{{ $prod->titulo }}">
                <a href="javascript:void(0)" id="btn-product-gallery" class="sd-zoom-btn" title="Ver ampliado" aria-label="Ver imagen ampliada">
                    <i class="fas fa-expand-alt"></i>
                </a>
                <span class="sd-zoom-lens" aria-hidden="true"></span>
                <span class="sd-zoom-hint" aria-hidden="true">Clic para ampliar</span>
            </div>
        </div>

        <div class="sd-info">

            <!-- Name + Code -->
            <div class="sd-info__header">
                <h1 class="sd-info__name" id="txtTitulo">{{ $prod->titulo }}</h1>
                <p class="sd-info__code">Código: <strong id="txtCodigo">{{ $prod->codigo_producto }}</strong></p>
            </div>

            <!-- Delivery badge -->
            @php $deliveryInfo = \App\Helpers\Helper::corteTiempoPromocion($prod->id_producto); @endphp
            @if($prod->corte_tiempo_promocion != null)
            <div class="delivery-badge" id="idTiempoEntrega" style="margin-bottom:10px;">
                @if($deliveryInfo['dia_entrega'] === 'hoy')
                <p class="clsLlegaHoy" style="font-size:12px;"><i class="fas fa-bolt"></i> {{ $deliveryInfo['mensaje'] }}</p>
                @elseif($deliveryInfo['dia_entrega'] === 'lunes')
                <p class="clsLlegaLunes" style="font-size:12px;"><i class="fas fa-calendar"></i> {{ $deliveryInfo['mensaje'] }}</p>
                @else
                <p class="clsLlegaManana" style="font-size:12px;"><i class="fas fa-clock"></i> {{ $deliveryInfo['mensaje'] }}</p>
                @endif
            </div>
            @endif

            @if($prod->precio_yape != null)
            <div class="yape-price-row" style="margin-bottom:8px;">
                <span class="yape-price-value">S/ {{ number_format($prod->precio_yape, 2) }}</span>
                <img src="{{ URL::asset('/imagenes/logo-yape.jpg') }}" alt="Yape" class="yape-logo" />
            </div>
            @endif

            @if($isMayorista && $tienePrecioMayorista)
                @if($descuentoMayorista > 0)
                <div class="sd-info__price-wrap">
                    <span class="sd-price-label">De:</span>
                    <span class="sd-price-old">S/ {{ number_format($prod->precio_mayorista_old, 2) }}</span>
                </div>
                @endif
                <div class="sd-info__price-wrap">
                    <span class="sd-price-label">A:</span>
                    <span class="sd-price-new" id="txtPrecio">S/ {{ number_format($precioMayorista, 2) }}</span>
                    <span class="pc-mayorista-tag" style="font-size:11px;margin-left:6px;">May.</span>
                </div>
                @if($descuentoMayorista > 0)
                @php $descSolesMay = round($prod->precio_mayorista_old - $precioMayorista, 2); @endphp
                <p class="sd-discount-text">Descuento de S/ {{ number_format($descSolesMay, 2) }}</p>
                @endif
            @else
                @if($descuentoPublico > 0)
                <div class="sd-info__price-wrap">
                    <span class="sd-price-label">De:</span>
                    <span class="sd-price-old">S/ {{ number_format($prod->precio_old, 2) }}</span>
                </div>
                @endif
                <div class="sd-info__price-wrap">
                    <span class="sd-price-label">A:</span>
                    <span class="sd-price-new" id="txtPrecio">S/ {{ number_format(floatval(str_replace(',', '', $prod->precio)), 2) }}</span>
                </div>
                @if($descuentoPublico > 0)
                <p class="sd-discount-text">Descuento de S/ {{ number_format($descuentoSoles, 2) }}</p>
                @endif
            @endif

            @if($omC !== null && $omC !== '' && $omP !== null && $omP !== '')
            <span class="sd-promo-badge">{{ $omC }} X S/ {{ number_format((float)$omP, 2) }}</span>
            @endif

            <!-- Hidden price inputs for JS -->
            <input type="hidden" id="precio_unitario_base" value="{{ number_format($precioUiBase, 2, '.', '') }}" />
            <input type="hidden" id="oferta_maxima_cantidad" value="{{ $prod->oferta_maxima_cantidad ?? '' }}" />
            <input type="hidden" id="oferta_maxima_cantidad_por_precio" value="{{ $omP !== null && $omP !== '' ? number_format((float) $omP, 2, '.', '') : '' }}" />

            <hr class="sd-divider">

            <!-- Color swatches -->
            <div class="sd-colors-row">
                <div class="sd-colors-col">
                    @if(!empty($coloresDisponibles))
                    <span class="sd-label">Colores Disponibles</span>
                    <div class="sd-colors" id="sd-colors-wrap">
                        @foreach($coloresDisponibles as $idx => $color)
                        @php
                            $imgColorUrl = !empty($color['url_imagen']) ? URL::asset($color['url_imagen']) : null;
                            $isActiveColor = $idx === 0;
                        @endphp
                        <div class="sd-color-dot{{ $isActiveColor ? ' active' : '' }}"
                             style="background:{{ $color['hex'] }};"
                             data-name="{{ $color['nombre'] }}"
                             title="{{ $color['nombre'] }}"
                             @if($imgColorUrl) data-img="{{ $imgColorUrl }}" @endif
                             @if($idx === 0) data-trigger="compact-main-trigger" @endif
                             onclick="sdSelectColor(this,'{{ $idx === 0 ? 'main' : 'color-' . $idx }}')">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            <hr class="sd-divider">

            @php
                $variantesUi = $tallasVariantes ?? [];
                if (empty($variantesUi) && !empty($tallasDisponibles)) {
                    foreach ($tallasDisponibles as $tNombre) {
                        $variantesUi[] = [
                            'talla' => $tNombre,
                            'precio' => (float) ($precioUiBase ?? $prod->precio ?? 0),
                            'stock' => (int) ($prod->stock ?? 0),
                        ];
                    }
                }
                // Como la plantilla: serie completa S · M · L · XL · XXL; las que el producto
                // no tiene se muestran tachadas (stock 0). Solo para tallas de letra.
                $ordenLetras = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '2XL', '3XL'];
                $tallasProducto = array_map(
                    fn ($v) => strtoupper(trim((string) (is_array($v) ? ($v['talla'] ?? '') : $v))),
                    $variantesUi
                );
                if (count($tallasProducto) > 0 && count(array_diff($tallasProducto, $ordenLetras)) === 0) {
                    $porTalla = [];
                    foreach ($variantesUi as $i => $v) {
                        $porTalla[$tallasProducto[$i]] = $v;
                    }
                    $serie = array_values(array_filter(
                        $ordenLetras,
                        fn ($t) => in_array($t, ['S', 'M', 'L', 'XL', 'XXL'], true) || isset($porTalla[$t])
                    ));
                    $variantesUi = array_map(fn ($t) => $porTalla[$t] ?? [
                        'talla'  => $t,
                        'precio' => (float) ($precioUiBase ?? $prod->precio ?? 0),
                        'stock'  => 0,
                    ], $serie);
                }
                $tieneTallas = count($variantesUi) > 0;
            @endphp

            @if($tieneTallas)
            <!-- Size buttons (sin selección por defecto) -->
            <span class="sd-label" style="font-weight:700;color:#333;font-style:normal;">TALLAS</span>
            <div class="sd-size-btns" id="sd-size-btns">
                @foreach($variantesUi as $ti => $variante)
                @php
                    $tallaNombre = is_array($variante) ? ($variante['talla'] ?? '') : (string) $variante;
                    $tallaPrecio = is_array($variante) ? (float) ($variante['precio'] ?? $precioUiBase) : (float) $precioUiBase;
                    $tallaStock  = is_array($variante) ? (int) ($variante['stock'] ?? 0) : 0;
                    $sinStock = $tallaStock <= 0;
                @endphp
                <button type="button"
                    class="sd-size-btn{{ $sinStock ? ' sd-size-btn--disabled' : '' }}"
                    data-talla="{{ $tallaNombre }}"
                    data-precio="{{ number_format($tallaPrecio, 2, '.', '') }}"
                    data-stock="{{ $tallaStock }}"
                    {{ $sinStock ? 'disabled' : '' }}
                    onclick="sdSelectSize(this)">{{ $tallaNombre }}</button>
                @endforeach
            </div>
            <input type="hidden" id="sd_talla_selected" value="" />
            {{-- Guía de tallas / stock: ocultos como en la plantilla (el JS sigue usando sd-size-stock-val) --}}
            <p style="display:none;font-family:'Montserrat',sans-serif;font-size:12px;margin:8px 0 0;">
                <a href="{{ url('/guia-de-tallas') }}" style="color:#8C6B2E;text-decoration:underline;">Guía de tallas</a>
            </p>
            <p id="sd-size-stock-hint" style="display:none;font-family:'Montserrat',sans-serif;font-size:11px;color:#666;margin:6px 0 0;">
                Stock disponible: <strong id="sd-size-stock-val">—</strong>
                <span id="sd-size-pick-hint" style="color:#999;margin-left:6px;">Seleccione una talla</span>
            </p>
            @else
            <p id="sd-size-stock-hint" style="display:none;font-family:'Montserrat',sans-serif;font-size:11px;color:#666;margin:0 0 8px;">
                Stock disponible: <strong id="sd-size-stock-val">{{ (int) ($prod->stock ?? 0) }}</strong>
            </p>
            @endif

            <!-- Form with quantity + cart -->
            <form id="formAgregarProducto">
                @csrf
                <input type="hidden" name="codigo_producto" id="codigo_producto" value="{{ $prod->codigo_producto }}" />
                <input type="hidden" name="txt_id_producto" id="txt_id_producto" value="{{ $prod->id_producto }}" />
                <input type="hidden" name="talla" id="txt_talla" value="" />
                @php
                    $colorInicial = !empty($coloresDisponibles)
                        ? (string) (data_get(collect($coloresDisponibles)->first(), 'nombre') ?? '')
                        : '';
                @endphp
                <input type="hidden" name="color" id="txt_color" value="{{ $colorInicial }}" />

                <div class="sd-qty-row">
                    <span class="sd-qty-label">Cantidad</span>
                    <div class="sd-qty-ctrl">
                        <button type="button" class="btn-minus sd-qty-dec">&#8722;</button>
                        <input type="number" id="txt_cantidad" name="txt_cantidad" value="1" min="1" max="1000">
                        <button type="button" class="btn-plus sd-qty-inc">+</button>
                    </div>
                    <div class="detalle-linea-subtotal" style="display:none;margin-left:auto;font-size:12px;color:#555;font-family:'Montserrat',sans-serif;">
                        <span class="detalle-linea-subtotal-label">Subtotal &nbsp;</span>
                        <span class="detalle-linea-subtotal-valor" id="detalle_subtotal_linea">S/ {{ number_format($subtotalLineaInicial, 2) }}</span>
                    </div>
                </div>

                <button type="button" class="sd-add-btn" id="addToCart">
                    AÑADIR AL CARRITO
                </button>
            </form>

            <!-- Volver atrás + Compra Rápida (WhatsApp va en .sd-wa-card) -->
            <div class="sd-action-row" style="display:none;">{{-- No está en la plantilla --}}
                <button type="button" class="sd-back-btn" onclick="lvVolverAtras()">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver atrás</span>
                </button>
                <a href="javascript:void(0)" class="sd-quick-buy-btn clsComprarRapido">
                    <i class="fa fa-bolt"></i> Compra Rápida
                </a>
            </div>

            @php
            $waNum = \App\Support\ContactoValento::whatsappNumero();
            if (isset($footerData) && count($footerData) > 0 && !empty($footerData[0]->whatsapp_numero)) {
                $waNum = preg_replace('/\D/', '', (string) $footerData[0]->whatsapp_numero) ?: $waNum;
            }
            // Textos de la tarjeta: administrables en Admin → WhatsApp (web_whatsapp_config.card_*)
            $waCard = \App\Models\WebWhatsappConfig::first();
            $cardTxt = static fn (string $campo, string $defecto): string
                => trim((string) ($waCard?->{$campo} ?? '')) ?: $defecto;
            $cardActiva = ($waCard?->card_activo ?? 'S') !== 'N';
            // Mismo número que el botón flotante (Admin → WhatsApp)
            $waNumCfg = preg_replace('/\D/', '', (string) ($waCard?->wa_numero ?? ''));
            if ($waNumCfg !== '') {
                $waNum = $waNumCfg;
            }

            $cardMensaje = $cardTxt('card_mensaje', 'Hola, quiero cotizar el producto: {producto}');
            $waCotizarMsg = str_replace('{producto}', (string) ($prod->titulo ?? ''), $cardMensaje);
            $waCotizarUrl = 'https://wa.me/' . $waNum . '?text=' . rawurlencode($waCotizarMsg);
            $asesorNombre = $cardTxt('card_nombre', 'Valento');
            $asesorRol = $cardTxt('card_rol', 'Asesoría comercial');
            $asesorIniciales = mb_strtoupper(mb_substr(preg_replace('/\s+/', '', $asesorNombre) ?: 'V', 0, 2));
            @endphp

            @if($cardActiva)
            <div class="sd-wa-card">
                <div class="sd-wa-card__header">
                    <div class="sd-wa-card__avatar">{{ $asesorIniciales }}</div>
                    <div class="sd-wa-card__info">
                        <p class="sd-wa-card__name">{{ $asesorNombre }} · {{ $asesorRol }}</p>
                        <div class="sd-wa-card__status">
                            <span class="sd-wa-dot"></span>
                            <span>{{ $cardTxt('card_estado', 'Disponible ahora · responde en minutos') }}</span>
                        </div>
                    </div>
                    <div class="sd-wa-icon"><i class="fa fa-whatsapp"></i></div>
                </div>
                <div class="sd-wa-card__body">
                    <h4>{{ $cardTxt('card_titulo', 'Recibe asesoría personalizada y compra con seguridad') }}</h4>
                    <p>{{ $cardTxt('card_texto', 'Te ayudamos a elegir tallas, colores y cotización mayorista por WhatsApp.') }}</p>
                </div>
                <a href="{{ $waCotizarUrl }}" target="_blank" rel="noopener" class="sd-wa-btn-green" id="sd-wa-order-btn">
                    {{ $cardTxt('card_boton', 'Hablar con un asesor ahora') }}
                </a>
                <div class="sd-wa-response">{{ $cardTxt('card_respuesta', 'Tiempo promedio de respuesta: 1 minuto') }}</div>
            </div>
            @endif

        </div><!-- end sd-info -->
    </div><!-- end sd-main -->

    <p class="sd-img-note" style="display:block;margin-top:-20px;margin-bottom:30px;">La imagen del producto es referencial.</p>

    <!-- Hidden compact gallery (powers btnImagenPrecio JS variant system) -->
    <div style="display:none;" id="hidden-compact-gallery">
        @if(count($listaImagen) > 0)
        <div class="compact-image-gallery">
            <div class="compact-image-item">
                <a href="javascript:void(0)"
                   id="compact-main-trigger"
                   isPrecio="{{ $prod->precio }}"
                   isCodigoProducto="{{ $prod->codigo_producto }}"
                   isDescripcion="{{ $prod->descripcion }}"
                   isTitulo="{{ $prod->titulo }}"
                   isCantidadEstrellas="{{ $prod->numero_estrellas }}"
                   data-precio-base="{{ number_format($precioUiBase, 2, '.', '') }}"
                   data-volumen-cantidad="{{ $prod->oferta_maxima_cantidad ?? '' }}"
                   data-volumen-precio="{{ $omP !== null && $omP !== '' ? number_format((float)$omP, 2, '.', '') : '' }}"
                   class="btnImagenPrecio">
                    <input type="radio" name="product-thumbnail" class="compact-radio" checked>
                </a>
            </div>
            @foreach($listaImagen as $ck => $citem)
            <div class="compact-image-item">
                <a href="javascript:void(0)"
                   id="compact-trigger-{{ $ck }}"
                   isPrecio="{{ $citem->img_precio }}"
                   isCodigoProducto="{{ $citem->img_codigo_producto }}"
                   isDescripcion="{{ $citem->img_descripcion }}"
                   isTitulo="{{ $citem->img_titulo }}"
                   isCantidadEstrellas="{{ $citem->img_numero_estrellas }}"
                   class="btnImagenPrecio"
                   data-precio-base="{{ number_format((float)$citem->img_precio, 2, '.', '') }}"
                   data-volumen-cantidad=""
                   data-volumen-precio="">
                    <input type="radio" name="product-thumbnail" class="compact-radio">
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Calcular envío (collapsible) -->
    <details class="sd-shipping-toggle" style="display:none;" >
        <summary>Calcular costo de envío &nbsp;<i class="fas fa-truck" style="font-size:13px;"></i></summary>
        <div class="sd-shipping-body">
            <div class="costoEnvio"></div>
            <div class="lugarEnvio"></div>
            <form id="formCalcularEnvio" method="POST" enctype="multipart/form-data" class="needs-validation">
                @csrf
                <div class="mb-2 mt-2">
                    <label for="txt_listar_departamentos" class="form-label" style="font-size:13px;font-weight:600;color:#333;">Provincia</label>
                    <select class="form-select select2" id="txt_listar_departamentos" required>
                        @foreach($listaDepartamentos as $department)
                        <option value="{{ $department->address_departamento }}" {{ $department->address_departamento == 'Lima Metropolitana' ? 'selected' : '' }}>
                            {{ $department->address_departamento }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="txt_peso_kilogramo" value="{{ $prod->peso_kilogramo ?? 0 }}">
                <input type="hidden" name="txt_paqueta_medidas" value="{{ $prod->paquete_medidas ?? 0 }}">
                <input type="hidden" name="txt_paquete_dimencion" value="{{ $prod->paquete_dimencion }}">
                <input type="hidden" name="txt_id_producto" value="{{ $prod->id_producto }}">
                <input type="hidden" id="del_lat_cliente" name="lat_cliente" value="">
                <input type="hidden" id="del_lng_cliente" name="lng_cliente" value="">
                <div class="mb-2">
                    <label for="txt_listar_distritos" class="form-label" style="font-size:13px;font-weight:600;color:#333;">Distrito</label>
                    <select class="form-select" id="txt_listar_distritos" disabled>
                        <option value="" selected disabled>Seleccione ...</option>
                    </select>
                </div>
                <div class="mb-2" id="divMontoPagar">
                    <label for="del_monto_pagar" class="form-label" style="font-size:13px;font-weight:600;color:#333;">¿Cuánto vas a pagar?</label>
                    <input type="number" class="form-select" id="del_monto_pagar" name="del_monto_pagar" placeholder="Ej: 200.00" min="0" step="0.50">
                    <div id="del_resultado_vuelto" class="mt-1" style="display:none;"></div>
                </div>
                <button type="submit" class="btn btn-primary w-100" id="calculateBtn" style="border-radius:6px;">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    Calcular Costo de Envío
                </button>
                <div id="del_resultado_delivery" class="mt-2" style="display:none;"></div>
                <div class="mensajeLlevaloDesde"></div>
                <div id="del_aviso_minimo" class="mt-2" style="display:none;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:8px;padding:8px 12px;font-size:13px;color:#991b1b;">
                    <i class="fas fa-lock"></i> <strong>Compra mínima requerida:</strong>
                    <span id="del_aviso_minimo_texto"></span>
                </div>
            </form>
        </div>
    </details>

</div><!-- end container -->

<div class="container">
    <div class="sd-tabs">
        @php
            $fichasActivas = isset($fichasTecnicasList) ? $fichasTecnicasList : collect($obtenerFichaTecnica ?? []);
            if ($fichasActivas instanceof \Illuminate\Support\Collection) {
                $fichasActivas = $fichasActivas->values();
            } else {
                $fichasActivas = collect($fichasActivas)->values();
            }
        @endphp

        @if($fichasActivas->count() > 0)
        <div class="sd-tabs__nav">
            @foreach($fichasActivas as $idx => $fichaTab)
            <button class="sd-tabs__btn {{ $idx === 0 ? 'active' : '' }}"
                    data-tab="tab-ficha-{{ $idx }}"
                    type="button">
                {{ !empty($fichaTab->titulo) ? $fichaTab->titulo : ('Pestaña '.($idx + 1)) }}
            </button>
            @endforeach
        </div>

        @foreach($fichasActivas as $idx => $fichaTab)
        @php
            $tipoFicha = strtolower((string) ($fichaTab->tipo ?? ''));
            if ($tipoFicha !== 'html' && $tipoFicha !== 'tabla') {
                $tieneTabla = !empty($fichaTab->nuevo_caracteristicas)
                    && $fichaTab->nuevo_caracteristicas !== '[]';
                $tipoFicha = $tieneTabla ? 'tabla' : (empty($fichaTab->descripcion) ? 'tabla' : 'html');
            }
        @endphp
        <div class="sd-tab-panel {{ $idx === 0 ? 'active' : '' }}" id="tab-ficha-{{ $idx }}">
            @if($tipoFicha === 'tabla')
                @include('web.partials.liam.ficha_tecnica_tabla', ['ficha' => $fichaTab])
                @php
                    $rowsCheck = [];
                    if (!empty($fichaTab->nuevo_caracteristicas)) {
                        $decodedCheck = json_decode($fichaTab->nuevo_caracteristicas, true);
                        $rowsCheck = is_array($decodedCheck) ? $decodedCheck : [];
                    }
                @endphp
                @if(count($rowsCheck) === 0)
                <div class="sd-desc-text" style="color:#888;font-size:13px;">
                    <p>Sin especificaciones cargadas para esta pestaña.</p>
                </div>
                @endif
            @else
                @if(!empty($fichaTab->descripcion))
                <div class="sd-returns-text sd-returns-text--rich sd-desc-text">
                    {!! $fichaTab->descripcion !!}
                    @if(!empty($fichaTab->nuevo_titulo))
                    <p style="margin-top:12px;">
                        <a href="{{ $fichaTab->nuevo_titulo }}" target="_blank" rel="noopener" style="color:#c0392b;font-weight:600;">Ver más</a>
                    </p>
                    @endif
                </div>
                @else
                <div class="sd-desc-text" style="color:#888;font-size:13px;">
                    <p>Sin contenido cargado para esta pestaña.</p>
                </div>
                @endif
            @endif
        </div>
        @endforeach
        @else
        {{-- Sin ficha técnica: pestañas de la plantilla temp02 con los datos del producto --}}
        @php
            $descTab = trim((string) ($prod->descripcion ?? ''));
            $coloresTab = collect($coloresDisponibles ?? [])->pluck('nombre')->filter()->implode(' / ');
            $tallasTab = collect($tallasDisponibles ?? [])->map(fn ($t) => is_array($t) ? ($t['talla'] ?? '') : $t)->filter()->implode(' / ');
        @endphp
        <div class="sd-tabs__nav">
            <button class="sd-tabs__btn active" data-tab="tab-desc" type="button">Descripción</button>
            <button class="sd-tabs__btn" data-tab="tab-details" type="button">Detalles</button>
            <button class="sd-tabs__btn" data-tab="tab-returns" type="button">Cambios y Devoluciones</button>
        </div>
        <div class="sd-tab-panel active" id="tab-desc">
            <div class="sd-desc-text">
                @if($descTab !== '')
                    {{-- Líneas que empiezan con "•" o "-" se muestran como lista (como la plantilla) --}}
                    @php
                        $bloquesDesc = [];
                        foreach (preg_split('/\R/', $descTab) as $lineaDesc) {
                            $lineaDesc = trim($lineaDesc);
                            if ($lineaDesc === '') continue;
                            $esItem = (bool) preg_match('/^[•\-]\s*/u', $lineaDesc);
                            $texto = preg_replace('/^[•\-]\s*/u', '', $lineaDesc);
                            $ultimo = count($bloquesDesc) - 1;
                            if ($esItem && $ultimo >= 0 && $bloquesDesc[$ultimo]['lista']) {
                                $bloquesDesc[$ultimo]['items'][] = $texto;
                            } else {
                                $bloquesDesc[] = ['lista' => $esItem, 'items' => [$texto]];
                            }
                        }
                    @endphp
                    @foreach($bloquesDesc as $bloqueDesc)
                        @if($bloqueDesc['lista'])
                            <ul>@foreach($bloqueDesc['items'] as $itemDesc)<li>{{ $itemDesc }}</li>@endforeach</ul>
                        @else
                            <p>{{ $bloqueDesc['items'][0] }}</p>
                        @endif
                    @endforeach
                @else
                    <p style="color:#888;">Sin descripción cargada para este producto.</p>
                @endif
            </div>
        </div>
        <div class="sd-tab-panel" id="tab-details">
            <table class="sd-details-table">
                <tr><td>Marca</td><td>rossety</td></tr>
                <tr><td>Código</td><td>{{ $prod->codigo_producto }}</td></tr>
                @if($coloresTab !== '')<tr><td>Color</td><td>{{ $coloresTab }}</td></tr>@endif
                @if($tallasTab !== '')<tr><td>Talla</td><td>{{ $tallasTab }}</td></tr>@endif
            </table>
        </div>
        <div class="sd-tab-panel" id="tab-returns">
            <div class="sd-returns-text">
                <h5>Política de cambios</h5>
                <p>Aceptamos cambios dentro de los <strong>15 días calendario</strong> desde la fecha de compra, siempre que el producto esté sin uso, con etiquetas originales y en su empaque original.</p>
                <h5>¿Cómo realizar un cambio?</h5>
                <p>Escríbenos por WhatsApp o correo electrónico adjuntando tu número de pedido y el motivo del cambio. Nuestro equipo te guiará en el proceso.</p>
                <h5>Devoluciones</h5>
                <p>Para devoluciones por defecto de fabricación, el producto será reemplazado sin costo adicional. Los gastos de envío en cambios por talla son asumidos por el cliente.</p>
                <h5>Excepciones</h5>
                <p>No se aceptan cambios ni devoluciones en productos de liquidación o con descuento especial mayor al 50%.</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Productos relacionados -->
<div class="container">
<section class="sd-related">
    <h2 class="sd-related__title">Quizá también te guste...</h2>
    <div class="sd-related-carousel owl-carousel owl-simple" data-toggle="owl"
         data-owl-options='{
             "nav": true,
             "dots": true,
             "margin": 16,
             "loop": true,
             "responsive": {
                 "0":    {"items": 2},
                 "768":  {"items": 3},
                 "992":  {"items": 4},
                 "1200": {"items": 5}
             }
         }'>
        @foreach($listProductosQuePuedenIteresarte as $rdata)
        @php
        $rdescuento = (!empty($rdata->precio_old) && $rdata->precio_old > $rdata->precio)
            ? round((1 - $rdata->precio / $rdata->precio_old) * 100) : 0;
        @endphp
        @php $rslug = $rdata->slug ?: $rdata->id_producto; @endphp
        <div class="sd-rel-card" onclick="window.location='{{ url('/web_shopDetail') }}/{{ $rslug }}'">
            <div class="sd-rel-card__img-wrap">
                <img src="{{ URL::asset($rdata->url_imagen) }}" alt="{{ $rdata->nombre }}">
            </div>
            <p class="sd-rel-card__name">{{ Str::limit($rdata->nombre, 40) }}</p>
            <p class="sd-rel-card__brand">Valento</p>
            <div class="sd-rel-card__prices">
                <span class="sd-rel-card__price-new">S/ {{ number_format($rdata->precio, 2) }}</span>
                @if($rdescuento > 0)
                <span class="sd-rel-card__price-old">S/ {{ number_format($rdata->precio_old, 2) }}</span>
                @endif
            </div>
            @if($rdescuento > 0)
            <span class="sd-rel-card__badge">{{ $rdescuento }}% DSCTO</span>
            @endif
        </div>
        @endforeach
    </div>
</section>
</div>

</main>
</div><!-- end page-wrapper -->

<!-- ========================================
     LIGHTBOX  FULLSCREEN
     ======================================== -->
@include('web.partials.liam.footer')
@endsection

@section('footer_page')
<div id="productLightboxModal" class="lb-overlay">
    <button type="button" class="lightbox-close-btn" id="lbCloseBtn" aria-label="Cerrar">
        <i class="fas fa-times"></i>
    </button>
    <div class="lightbox-body">
        <div class="lightbox-main-area">
            <button class="lightbox-nav lightbox-prev" id="lbPrev"><i class="fas fa-chevron-left"></i></button>
            <div class="lightbox-img-container">
                <img id="lightboxMainImg" class="lightbox-main-img" src="" alt="Imagen ampliada">
            </div>
            <button class="lightbox-nav lightbox-next" id="lbNext"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="lightbox-footer">
            <span class="lightbox-counter" id="lbCounter">1 / 1</span>
            <div class="lightbox-thumbs-strip" id="lbThumbsStrip"></div>
        </div>
    </div>
</div>
@vite(['resources/js/web_shopDetail.js'])
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('assets/js/index/index.js') }}?v={{ is_file($f = public_path('assets/js/index/index.js')) ? filemtime($f) : time() }}"></script>
<script>
 // ============================================
 // CALCULO DE ENVIO
 // ============================================
    $('#txt_listar_distritos, #txt_listar_departamentos').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
 // ::::::::::::::::::::::::::::::START: DETALLE PRODUCTO: CONTADOR DE LLEVALO HOY O MAÑANA::::::::::::::::::::::::::::::::::::::::
    document.addEventListener('DOMContentLoaded', function() {
        const iframes = document.querySelectorAll('.ficha-tecnica-iframe-autoheight');

        iframes.forEach(function(iframe) {
 // Si ya está cargado, ajustar inmediatamente
            try {
                if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
                    ajustarAlturaFichaIframe();
                }
            } catch (e) {}

 // Evento load
            iframe.addEventListener('load', function() {
 // Ajustes múltiples para asegurar captura correcta
                ajustarAlturaFichaIframe();
                setTimeout(ajustarAlturaFichaIframe, 50);
                setTimeout(ajustarAlturaFichaIframe, 150);
                setTimeout(ajustarAlturaFichaIframe, 500);

 // Observar cambios de tamaño (ResizeObserver)
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc && doc.body) {
 // Observar el body para cambios de tamaño
                        if (typeof ResizeObserver !== 'undefined') {
                            const resizeObserver = new ResizeObserver(function() {
                                ajustarAlturaFichaIframe();
                            });
                            resizeObserver.observe(doc.body);

 // También observar contenedores principales
                            if (doc.body.firstElementChild) {
                                resizeObserver.observe(doc.body.firstElementChild);
                            }
                        }

 // Observar imágenes que cargan
                        const imgs = doc.querySelectorAll('img');
                        imgs.forEach(function(img) {
                            if (!img.complete) {
                                img.addEventListener('load', function() {
                                    ajustarAlturaFichaIframe();
                                });
                            }
                        });
                    }
                } catch (e) {}
            });
        });
    });

 // ::::::::::::::::::::::::::::::END: DETALLE PRODUCTO: CONTADOR DE LLEVALO HOY O MAÑANA::::::::::::::::::::::::::::::::::::::::
 // Iframe ficha técnica (solo ficha general): auto-altura sin scroll interno


 // ============================================
 // IFRAME FICHA TECNICA - AUTO ALTURA PERFECTO
 // ============================================
    function ajustarAlturaFichaIframe() {
        document.querySelectorAll('.ficha-tecnica-iframe-autoheight').forEach(function(iframe) {
            try {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc && doc.body) {
 // Resetear altura para cálculo correcto
                    iframe.style.height = 'auto';

 // Calcular altura REAL incluyendo márgenes, padding, etc.
                    const body = doc.body;
                    const html = doc.documentElement;

 // Altura real del contenido (método más preciso)
                    let height = Math.max(
                        body.scrollHeight,
                        body.offsetHeight,
                        html.scrollHeight,
                        html.offsetHeight,
                        body.getBoundingClientRect().height,
                        html.getBoundingClientRect().height
                    );

 // Añadir un buffer de seguridad (10px)
                    height += 10;

 // Aplicar altura SIN overflow hidden
                    iframe.style.height = height + 'px';
                    iframe.style.overflow = 'visible'; //   CAMBIO CRUCIAL
                    iframe.style.minHeight = '100px';
                }
            } catch (e) {
                console.log('Error ajustando iframe:', e);
            }
        });
    }










 // Reajustar en ventana redimensionada
    window.addEventListener('resize', function() {
        ajustarAlturaFichaIframe();
    });










    document.querySelectorAll('.ficha-tecnica-iframe-wrapper iframe').forEach(function(iframe) {
        iframe.onload = function() {
            ajustarAlturaFichaIframe();
            setTimeout(ajustarAlturaFichaIframe, 200);
            setTimeout(ajustarAlturaFichaIframe, 600);
            setTimeout(ajustarAlturaFichaIframe, 1500);

 // Observar cambios de tamaño en el contenido del iframe (ej. imágenes cargando)
            try {
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc && doc.body && typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(function() {
                        ajustarAlturaFichaIframe();
                    }).observe(doc.body);
                }
 // También re-ajustar cuando las imágenes dentro del iframe terminen de cargar
                var imgs = doc.querySelectorAll('img');
                imgs.forEach(function(img) {
                    img.addEventListener('load', ajustarAlturaFichaIframe);
                });
            } catch (e) {}
        };
    });
</script>
@if(Auth::check())
<script>
// ====================================================
// SISTEMA DE CALIFICACION - IMPLEMENTACION DEFINITIVA
// ====================================================

// 1. CERRAR POPOVER al hacer clic en X
$(document).ready(function() {
    $(document).on('click', '.popover-close', function(e) {
        e.stopPropagation();
        var popover = $(this).closest('.rating-popover');
        popover.addClass('force-hide');
        setTimeout(function(){ popover.removeClass('force-hide'); }, 2000);
    });
});


$(document).on('mouseenter', '.rating-popover', function() {
    $(this).css({
        'pointer-events': 'auto',
        'z-index': 2147483647
    });
});

$(document).on('click', '.rating-popover .star-click-zone, .rating-popover .star-button, .rating-popover .star-btn-manual, .rating-popover .user-rating-input .product-rating i', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var val = $(this).data('value');
    if (!val) {
        var $container = $(this).closest('.user-rating-input').find('.product-rating');
        if ($container.length) {
            var $icons = $container.find('i');
            var idx = $icons.index(this);
            if (idx >= 0) {
                val = idx + 1;
            }
        }
    }
    if (window.handleStarRatingClick) {
        window.handleStarRatingClick(val);
    }
});

// 2. FUNCION GLOBAL PARA CALIFICAR (Amazon Style)
window.handleStarRatingClick = function(clickedValue, productId, containerId) {
    var id_producto = productId || "{{ $obtenerDetalleProducto[0]->id_producto }}";
    var rating = parseInt(clickedValue);

    var $userRatingBox = $('.rating-popover .user-rating-input .product-rating');
    var prevRating = 0;
    if ($userRatingBox.length) {
        prevRating = parseInt($userRatingBox.attr('data-rating')) || 0;
    }

    if (!rating || rating < 1 || rating > 5) return;

    if (containerId) {
        var $container = $('#' + containerId);
        if ($container.length) {
            $container.find('.star-click-zone').each(function(index) {
                var starNum = index + 1;
                if (starNum <= rating) {
                    $(this).css('color', '#ffa41c');
                } else {
                    $(this).css('color', '#ccc');
                }
            });
        }
    } else {
        $('.star-click-zone').each(function(index) {
            var starNum = index + 1;
            if (starNum <= rating) {
                $(this).css('color', '#ffa41c');
            } else {
                $(this).css('color', '#ccc');
            }
        });
    }

    if ($userRatingBox.length) {
        $userRatingBox.attr('data-rating', rating);
        $userRatingBox.find('i').each(function(idx) {
            var starNum = idx + 1;
            if (starNum <= rating) {
                $(this).removeClass('fa-star-o text-muted').addClass('fa fa-star text-warning');
                $(this).css('color', '#ffa41c');
            } else {
                $(this).removeClass('fa-star text-warning').addClass('fa fa-star-o text-muted');
                $(this).css('color', '#ccc');
            }
        });
    }

    var msgEl = containerId ? document.querySelector('#' + containerId + ' .rating-message') : document.getElementById('rating-message');
    if (msgEl) {
        msgEl.textContent = 'Guardando...';
        msgEl.style.display = 'block';
    }

    $.ajax({
        url: '/producto/calificar',
        type: 'POST',
        dataType: 'json',
        data: {
            id_producto: id_producto,
            rating: rating,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (msgEl) {
                if (prevRating > 0 && prevRating !== rating) {
                    msgEl.innerHTML = 'Calificaci&oacute;n actualizada';
                } else {
                    msgEl.innerHTML = '&iexcl;Calificado!';
                }
                setTimeout(function() {
                    msgEl.style.display = 'none';
                }, 1500);
            }
            var summary = (response && (response.summary || response.data && response.data.summary)) ? (response.summary || response.data.summary) : null;
            if (summary) {
                window.updateRatingSummary(summary);
            }
        },
        error: function(xhr) {
            console.error('Error AJAX:', xhr.status);
            if (msgEl) msgEl.textContent = 'Error al guardar';
        }
    });
};

// 3. FUNCION PARA ACTUALIZAR EL RESUMEN VISUAL TRAS CALIFICAR
window.updateRatingSummary = function(summary) {
    if (!summary) return;
    
 // Actualizar promedio en el trigger y popover
    var prom = parseFloat(summary.promedio).toFixed(1);
    $('.rating-avg').text(prom);
    $('.popover-avg-text').text(prom + ' de 5');
    $('.rating-count').text('(' + summary.total + ')');
    $('.popover-total-count').text(summary.total + ' calificaciones globales');

 // Repintar estrellas promedio en trigger y header del popover (usa iconos FontAwesome)
 // Pintado con soporte de media estrella (coherente con el promedio mostrado a 1 decimal)
    var avg = parseFloat(summary.promedio) || 0;
    avg = Math.max(0, Math.min(5, avg));
    var full = Math.floor(avg);
    var hasHalf = (avg - full) >= 0.5;
    function paintAvgStars($root) {
        if (!$root || !$root.length) return;
        var $icons = $root.find('i');
        if (!$icons.length) return;

        var starIndex = 0;
        $icons.each(function() {
            var $i = $(this);
 // Solo manipular iconos de estrella (no tocar otros iconos del header)
            if (!($i.hasClass('fa-star') || $i.hasClass('fa-star-half-o'))) return;
            starIndex++;

            if (starIndex <= full) {
                $i.removeClass('fa-star-o fa-star-half-o text-muted')
                  .addClass('fa fa-star text-warning')
                  .css('color', '#ffa41c');
                return;
            }

            if (starIndex === (full + 1) && hasHalf) {
                $i.removeClass('fa-star fa-star-o text-muted')
                  .addClass('fa fa-star-half-o text-warning')
                  .css('color', '#ffa41c');
                return;
            }

            $i.removeClass('fa-star fa-star-half-o text-warning')
              .addClass('fa fa-star-o text-muted')
              .css('color', '#ccc');
        });
    }
    paintAvgStars($('.rating-trigger'));
    paintAvgStars($('.popover-stars'));
    
 // Actualizar barras de progreso en el popover
    if (summary.estrellas) {
        Object.keys(summary.estrellas).forEach(function(star) {
            var data = summary.estrellas[star];
 // Buscar la fila de la estrella específica
            $('.popover-bar-row').each(function() {
                if ($(this).find('.bar-label').text().includes(star + ' estrellas')) {
                    $(this).find('.bar-progress-fill').css('width', data.porcentaje + '%');
                    $(this).find('.bar-percent').text(data.porcentaje + '%');
                }
            });
        });
    }
};
</script>
@endif

@if(Session::has('messageDetalleProducto'))
<script>
    toastr.success("<?php echo Session::get('messageDetalleProducto'); ?>");
</script>
@endif

<script>
    function irACesta() {
        var badge = document.querySelector('#lv-cart-count');
        var cartCount = badge ? (parseInt(badge.textContent, 10) || 0) : 0;
        if (cartCount === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.info('Tu cesta está vacía. ¡Agrega productos para comenzar!', 'Cesta vacía', {
                    timeOut: 3000,
                    positionClass: 'toast-top-right',
                    progressBar: true,
                });
            } else {
                alert('Tu cesta está vacía. Agrega productos para comenzar.');
            }
            return;
        }
        window.location.href = '/shop-detail-lista';
    }
</script>

@php
    $delCfg = \App\Models\DelConfiguracionDelivery::obtenerActivo();
    $delDistritosStr = implode(' · ', array_slice($delCfg->distritos_especiales ?? ['Chorrillos','Barranco','Surco'], 0, 3));
@endphp
<!-- <div id="del-shipping-hint-template" class="sd-hidden" aria-hidden="true">
    <div class="sd-del-hint">
        <div class="sd-del-hint__title">
            <i class="fas fa-info-circle"></i> Costos de envío en Lima:
        </div>
        <div class="sd-del-hint__item">
            <i class="fas fa-circle sd-del-hint__dot"></i>
            <span><strong>GRATIS</strong> si estás a menos de {{ $delCfg->radio_metros_gratis }} m de la tienda</span>
        </div>
        <div class="sd-del-hint__item">
            <i class="fas fa-circle sd-del-hint__dot"></i>
            <span><strong>S/ {{ number_format($delCfg->costo_distritos_especiales, 2) }}</strong> — {{ $delDistritosStr }}</span>
        </div>
        <div class="sd-del-hint__item">
            <i class="fas fa-circle sd-del-hint__dot"></i>
            <span><strong>S/ {{ number_format($delCfg->costo_envio_baja_compra ?? 5, 2) }}</strong> si tu compra es menor a S/ {{ number_format($delCfg->monto_minimo_compra, 0) }}</span>
        </div>
        <div class="sd-del-hint__note">Selecciona tu distrito y calcula el costo exacto.</div>
    </div>
</div> -->
<script>
window.DEL_DELIVERY_CALCULADO = false;
(function initDelHint() {
    var el = document.getElementById('del-shipping-hint-template');
    window.DEL_HINT_DELIVERY = el ? el.innerHTML.trim() : '';
})();
(function () {
    'use strict';

    var DEL_ULTIMO_RESULTADO = null;

    var $montoPagar  = $('#del_monto_pagar');
    var $resVuelto   = $('#del_resultado_vuelto');
    var $resDelivery = $('#del_resultado_delivery');
    var $avisoMinimo = $('#del_aviso_minimo');
    var $avisoTexto  = $('#del_aviso_minimo_texto');

    function getPrecioUnitario() {
        return parseFloat($('#precio_unitario_base').val()) || 0;
    }
    function getCantidad() {
        return parseInt($('#txt_cantidad').val()) || 1;
    }
    function getSubtotal() {
        return getPrecioUnitario() * getCantidad();
    }

 // Geolocalización opcional (mejora Caso 3 de radio)
    function intentarGeolocalizacion() {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
            $('#del_lat_cliente').val(pos.coords.latitude.toFixed(7));
            $('#del_lng_cliente').val(pos.coords.longitude.toFixed(7));
        }, function () { /* permisos denegados: sin coords, aplica lógica por distrito */ });
    }

 // Calcula y muestra el VUELTO en tiempo real
    function calcularVuelto() {
        var montoPagar = parseFloat($montoPagar.val()) || 0;
        var subtotal   = getSubtotal();
        var costoEnvio = DEL_ULTIMO_RESULTADO ? parseFloat(DEL_ULTIMO_RESULTADO.delivery_costo || 0) : 0;
        var totalPagar = subtotal + costoEnvio;

        if (montoPagar <= 0) { $resVuelto.hide(); return; }

        if (montoPagar < totalPagar) {
            $resVuelto.show().html(
                '<div class="sd-del-vuelto sd-del-vuelto--error">' +
                '<i class="fas fa-exclamation-circle"></i> Monto ingresado ' +
                '<strong>S/ ' + montoPagar.toFixed(2) + '</strong> es insuficiente. ' +
                'Total a pagar: <strong>S/ ' + totalPagar.toFixed(2) + '</strong>' +
                '</div>'
            );
        } else {
            var vuelto = montoPagar - totalPagar;
            $resVuelto.show().html(
                '<div class="sd-del-vuelto sd-del-vuelto--ok">' +
                '<i class="fas fa-coins"></i> Su vuelto sería: <strong>S/ ' + vuelto.toFixed(2) + '</strong>' +
                ' &nbsp;|&nbsp; Total a pagar: <strong>S/ ' + totalPagar.toFixed(2) + '</strong>' +
                '</div>'
            );
        }
    }

 // Muestra el resultado del delivery
    function mostrarResultadoDelivery(data) {
        DEL_ULTIMO_RESULTADO = data;

        var costoEnvio   = parseFloat(data.delivery_costo || 0);
        var esGratis     = data.delivery_gratis;
        var esBajaCompra = data.delivery_compra_baja;
        var mensaje      = data.delivery_mensaje  || '';
        var mensaje2     = data.delivery_mensaje2 || '';
        var distanciaMts = data.delivery_distancia_m;
        var montoMin     = parseFloat(data.delivery_monto_minimo || 100);
        var falta        = parseFloat(data.delivery_falta || 0);

 // Estilos según tipo de resultado
        var iconoHTML, resultClass;
        if (esGratis) {
            iconoHTML  = '<i class="fas fa-shipping-fast"></i>';
            resultClass = 'sd-del-result sd-del-result--free';
        } else if (esBajaCompra) {
            iconoHTML  = '<i class="fas fa-info-circle"></i>';
            resultClass = 'sd-del-result sd-del-result--warn';
        } else {
            iconoHTML  = '<i class="fas fa-truck"></i>';
            resultClass = 'sd-del-result sd-del-result--normal';
        }

        var distanciaHtml = distanciaMts
            ? '<br><small>Distancia a tienda: ~' + distanciaMts + ' m</small>'
            : '';

        var msg2Html = mensaje2
            ? '<br><small><em>' + mensaje2 + '</em></small>'
            : '';

        var html = '<div class="' + resultClass + '">' +
                   iconoHTML + ' ' + mensaje + msg2Html + distanciaHtml +
                   '</div>';

        $resDelivery.show().html(html);

 // Aviso inferior (falta para compra mínima)
        if (esBajaCompra && falta > 0) {
            $avisoMinimo.show();
            $avisoTexto.text(' Agrega S/ ' + falta.toFixed(2) + ' más y el delivery será S/ ' +
                (data.delivery_costo_normal || '10.00') + '.');
        } else {
            $avisoMinimo.hide();
        }

 // mensajeLlevaloDesde
        if (esGratis) {
            $('.mensajeLlevaloDesde').html(
                '<div class="alert alert-success py-1 px-2 mt-1 sd-del-alert">' +
                '<i class="fas fa-check-circle"></i> <strong>¡Envío GRATIS!</strong></div>'
            );
        } else if (esBajaCompra) {
            $('.mensajeLlevaloDesde').html(
                '<div class="alert alert-warning py-1 px-2 mt-1 sd-del-alert">' +
                '<i class="fas fa-tag"></i> ' + mensaje2 + '</div>'
            );
        } else {
            $('.mensajeLlevaloDesde').html('');
        }

 // Recalcular vuelto con el nuevo costo de envío
        calcularVuelto();
    }

 // Exponer globalmente para que web_shopDetail.js la llame
    window.procesarResultadoDelivery = function(data) {
        window.DEL_DELIVERY_CALCULADO = true;
        mostrarResultadoDelivery(data);
    };

 // Vuelto en tiempo real al escribir el monto
    $montoPagar.on('input', calcularVuelto);

 // Recalcular vuelto al cambiar cantidad
    $(document).on('input change', '#txt_cantidad', function () {
        calcularVuelto();
    });

 // Recalcular al cambiar variante
    $(document).on('click', '.btnImagenPrecio', function () {
        setTimeout(calcularVuelto, 150);
    });

 // Init
    $(document).ready(function () {
        intentarGeolocalizacion();
    });

}());
</script>

<script>
// SD Tabs
document.querySelectorAll('.sd-tabs__btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.sd-tabs__btn').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.sd-tab-panel').forEach(function(p){ p.classList.remove('active'); });
        btn.classList.add('active');
        var target = document.getElementById(btn.getAttribute('data-tab'));
        if (target) { target.classList.add('active'); }
    });
});

// SD Thumbs
document.querySelectorAll('.sd-thumb').forEach(function(thumb) {
    thumb.addEventListener('click', function() {
        document.querySelectorAll('.sd-thumb').forEach(function(t){ t.classList.remove('active'); });
        thumb.classList.add('active');
        var src = thumb.getAttribute('data-full') || thumb.getAttribute('src');
        if (src) { document.getElementById('product-zoom').src = src; }
    });
});

// SD Color dots trigger hidden btnImagenPrecio
function sdSelectColor(el, key) {
    document.querySelectorAll('.sd-color-dot').forEach(function(d){ d.classList.remove('active'); });
    el.classList.add('active');
    var colorInput = document.getElementById('txt_color');
    if (colorInput) colorInput.value = el.getAttribute('data-name') || '';
    var img = el.getAttribute('data-img');
    if (img) {
        document.getElementById('product-zoom').src = img;
        document.querySelectorAll('.sd-thumb').forEach(function(t){ t.classList.remove('active'); });
    }
    var triggerId = el.getAttribute('data-trigger');
    if (triggerId) {
        var trigger = document.getElementById(triggerId);
        if (trigger) { trigger.click(); }
    }
}

// SD Size buttons — actualiza precio/stock y reinicia cantidad a 1
// Como la plantilla: talla L seleccionada por defecto (o la primera con stock)
document.addEventListener('DOMContentLoaded', function () {
    var btns = Array.prototype.slice.call(document.querySelectorAll('#sd-size-btns .sd-size-btn:not([disabled])'));
    if (!btns.length || document.querySelector('#sd-size-btns .sd-size-btn.active')) return;
    var porDefecto = btns.filter(function (b) { return (b.getAttribute('data-talla') || '').toUpperCase() === 'L'; })[0] || btns[0];
    sdSelectSize(porDefecto);
});

function sdSelectSize(btn) {
    if (!btn || btn.disabled) return;
    document.querySelectorAll('.sd-size-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');

    var talla = btn.getAttribute('data-talla') || btn.textContent.trim();
    var precio = parseFloat(btn.getAttribute('data-precio') || '0') || 0;
    var stock = parseInt(btn.getAttribute('data-stock') || '0', 10) || 0;

    var tallaInput = document.getElementById('txt_talla');
    if (tallaInput) tallaInput.value = talla;
    var tallaHidden = document.getElementById('sd_talla_selected');
    if (tallaHidden) tallaHidden.value = talla;

    var stockVal = document.getElementById('sd-size-stock-val');
    if (stockVal) stockVal.textContent = stock;

    var pickHint = document.getElementById('sd-size-pick-hint');
    if (pickHint) pickHint.style.display = 'none';

    var qty = document.getElementById('txt_cantidad');
    if (qty) {
        qty.max = Math.max(1, stock);
        // Al cambiar de medida, el contador siempre vuelve a 1
        qty.value = 1;
        qty.dispatchEvent(new Event('input', { bubbles: true }));
        qty.dispatchEvent(new Event('change', { bubbles: true }));
    }

    var precioEl = document.getElementById('txtPrecio');
    if (precioEl && precio > 0) {
        precioEl.textContent = 'S/ ' + precio.toFixed(2);
    }

    var baseEl = document.getElementById('precio_unitario_base');
    if (baseEl && precio > 0) {
        baseEl.value = precio.toFixed(2);
    }

    var subtotalEl = document.getElementById('detalle_subtotal_linea');
    if (subtotalEl && precio > 0) {
        subtotalEl.textContent = 'S/ ' + precio.toFixed(2);
    }
    if (typeof window.aplicarPrecioVolumenDetalle === 'function') {
        window.aplicarPrecioVolumenDetalle();
    }
}
</script>

<script>
$(document).ready(function () {
    if ($('.sd-related-carousel').length && $('.sd-related-carousel .sd-rel-card').length) {
        $('.sd-related-carousel').owlCarousel({
            nav: true,
            dots: false,
            margin: 16,
            loop: true,
            navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
            responsive: {
                0:    { items: 2 },
                768:  { items: 3 },
                992:  { items: 4 },
                1200: { items: 5 }
            }
        });
    }
});
</script>

<script>
(function () {
    // Recolectar todas las imágenes de la galería desde los thumbs
    function getLbImages() {
        var imgs = [];
        document.querySelectorAll('#product-zoom-gallery .modern-thumb-link').forEach(function (a) {
            var src = a.getAttribute('data-src');
            if (src) imgs.push(src);
        });
        // Si no hay thumbs, usar la imagen principal
        if (imgs.length === 0) {
            var main = document.getElementById('product-zoom');
            if (main && main.src) imgs.push(main.src);
        }
        return imgs;
    }

    var overlay   = document.getElementById('productLightboxModal');
    var lbImg     = document.getElementById('lightboxMainImg');
    var lbCounter = document.getElementById('lbCounter');
    var lbStrip   = document.getElementById('lbThumbsStrip');
    var lbPrev    = document.getElementById('lbPrev');
    var lbNext    = document.getElementById('lbNext');
    var lbClose   = document.getElementById('lbCloseBtn');
    var lbImages  = [];
    var lbIndex   = 0;

    function lbShow(idx) {
        if (!lbImages.length) return;
        lbIndex = ((idx % lbImages.length) + lbImages.length) % lbImages.length;
        lbImg.classList.add('lb-loading');
        var newSrc = lbImages[lbIndex];
        var tmp = new Image();
        tmp.onload = function () {
            lbImg.src = newSrc;
            lbImg.classList.remove('lb-loading');
        };
        tmp.src = newSrc;
        lbCounter.textContent = (lbIndex + 1) + ' / ' + lbImages.length;
        lbStrip.querySelectorAll('.lightbox-thumb-item').forEach(function (t, i) {
            t.classList.toggle('active', i === lbIndex);
        });
        lbStrip.querySelectorAll('.lb-thumb').forEach(function (t, i) {
            t.classList.toggle('lb-thumb-active', i === lbIndex);
        });
        lbPrev.classList.toggle('lb-hidden', lbImages.length <= 1);
        lbNext.classList.toggle('lb-hidden', lbImages.length <= 1);
    }

    function lbOpen(startIdx) {
        lbImages = getLbImages();
        // Rebuild thumbs strip (miniaturas, nunca a tamaño completo)
        lbStrip.innerHTML = '';
        lbImages.forEach(function (src, i) {
            var wrap = document.createElement('div');
            wrap.className = 'lightbox-thumb-item' + (i === (startIdx || 0) ? ' active' : '');
            wrap.setAttribute('data-lb-index', String(i));
            var t = document.createElement('img');
            t.src = src;
            t.alt = 'Miniatura ' + (i + 1);
            wrap.appendChild(t);
            wrap.addEventListener('click', function () { lbShow(i); });
            lbStrip.appendChild(wrap);
        });
        lbShow(startIdx || 0);
        overlay.classList.add('lb-active');
        document.body.classList.add('lb-lightbox-open');
        document.body.style.overflow = 'hidden';
        // Ocultar lente elevateZoom si quedó sobre el overlay
        document.querySelectorAll('.zoomContainer, .zoomWindowContainer').forEach(function (el) {
            el.style.display = 'none';
        });
    }

    function lbClose2() {
        overlay.classList.remove('lb-active');
        document.body.classList.remove('lb-lightbox-open');
        document.body.style.overflow = '';
    }

    // Abrir con el botón "Ver ampliado"
    var btnGallery = document.getElementById('btn-product-gallery');
    if (btnGallery) {
        btnGallery.addEventListener('click', function (e) {
            e.preventDefault();
            // Detectar qué imagen está activa en los thumbs
            var activeLink = document.querySelector('#product-zoom-gallery .modern-thumb-link.active-thumb');
            var startIdx = activeLink ? parseInt(activeLink.getAttribute('data-index') || 0, 10) : 0;
            lbOpen(startIdx);
        });
    }

    // También al hacer click en la imagen principal
    var mainImgWrap = document.querySelector('.sd-main-img-wrap');
    if (mainImgWrap) {
        mainImgWrap.addEventListener('click', function (e) {
            if (e.target.closest('#btn-product-gallery')) return;
            var activeLink = document.querySelector('#product-zoom-gallery .modern-thumb-link.active-thumb');
            var startIdx = activeLink ? parseInt(activeLink.getAttribute('data-index') || 0, 10) : 0;
            lbOpen(startIdx);
        });
    }

    // Lupa al pasar el cursor (solo con mouse): muestra la zona bajo el cursor ampliada
    var lens = mainImgWrap && mainImgWrap.querySelector('.sd-zoom-lens');
    var mainImg = document.getElementById('product-zoom');
    var ZOOM = 2.5;
    var zoomHint = mainImgWrap && mainImgWrap.querySelector('.sd-zoom-hint');
    if (zoomHint && window.matchMedia('(hover: none)').matches) zoomHint.textContent = 'Toca para ampliar';
    if (lens && mainImg && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        // Rectángulo donde realmente se dibuja la foto dentro de la caja (object-fit: contain)
        function drawnRect() {
            var box = mainImg.getBoundingClientRect();
            var nw = mainImg.naturalWidth || box.width, nh = mainImg.naturalHeight || box.height;
            var scale = Math.min(box.width / nw, box.height / nh);
            var w = nw * scale, h = nh * scale;
            return { left: box.left + (box.width - w) / 2, top: box.top + (box.height - h) / 2, width: w, height: h };
        }
        mainImgWrap.addEventListener('mousemove', function (e) {
            if (e.target.closest('#btn-product-gallery')) { mainImgWrap.classList.remove('lupa-on'); return; }
            var r = drawnRect();
            var x = e.clientX - r.left, y = e.clientY - r.top;
            if (x < 0 || y < 0 || x > r.width || y > r.height) { mainImgWrap.classList.remove('lupa-on'); return; }
            var wrapBox = mainImgWrap.getBoundingClientRect();
            var lw = lens.offsetWidth, lh = lens.offsetHeight;
            lens.style.transform = 'translate(' + (e.clientX - wrapBox.left - lw / 2) + 'px,' + (e.clientY - wrapBox.top - lh / 2) + 'px)';
            lens.style.backgroundImage = 'url("' + (mainImg.currentSrc || mainImg.src) + '")';
            lens.style.backgroundSize = (r.width * ZOOM) + 'px ' + (r.height * ZOOM) + 'px';
            lens.style.backgroundPosition = (lw / 2 - x * ZOOM) + 'px ' + (lh / 2 - y * ZOOM) + 'px';
            mainImgWrap.classList.add('lupa-on');
        });
        mainImgWrap.addEventListener('mouseleave', function () { mainImgWrap.classList.remove('lupa-on'); });
    }

    // Cerrar
    if (lbClose) lbClose.addEventListener('click', lbClose2);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) lbClose2();
    });

    // Navegación
    if (lbPrev) lbPrev.addEventListener('click', function () { lbShow(lbIndex - 1); });
    if (lbNext) lbNext.addEventListener('click', function () { lbShow(lbIndex + 1); });

    // Teclado
    document.addEventListener('keydown', function (e) {
        if (!overlay.classList.contains('lb-active')) return;
        if (e.key === 'Escape')     lbClose2();
        if (e.key === 'ArrowLeft')  lbShow(lbIndex - 1);
        if (e.key === 'ArrowRight') lbShow(lbIndex + 1);
    });
})();
</script>

<script>
// ── Comprar por WhatsApp (producto individual) ────────────────────────────────
window.sdComprarPorWhatsapp = function () {
    var btn = document.getElementById('sd-wa-order-btn');

    // Leer datos del producto desde la página
    var nombre    = document.getElementById('txtTitulo')?.textContent.trim()
                    || '{{ addslashes($prod->titulo) }}';
    var precio    = parseFloat(document.getElementById('txtPrecio')?.textContent.replace(/[^0-9.]/g,'')) || {{ $precioUiBase }};
    var cantidad  = parseInt(document.getElementById('txt_cantidad')?.value) || 1;
    var idProd    = {{ $prod->id_producto }};
    var codigoProd = @json($prod->codigo_producto ?? '');
    var imagen    = document.getElementById('product-zoom')?.src || '';
    var descripcion = '';

    // Talla y color seleccionados
    var tallaEl   = document.querySelector('.sd-size-btn.active');
    var talla     = tallaEl ? tallaEl.textContent.trim() : '';
    var colorEl   = document.querySelector('.sd-color-dot.active');
    var color     = colorEl ? colorEl.getAttribute('data-name') || '' : '';
    var subtotal  = precio * cantidad;

    if (btn) { btn.disabled = true; btn.style.opacity = '0.7'; }

    var fd = new FormData();
    fd.append('_token',      document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    fd.append('id_producto', idProd);
    fd.append('codigo_producto', codigoProd || '');
    fd.append('nombre',      nombre);
    fd.append('precio',      precio);
    fd.append('cantidad',    cantidad);
    fd.append('imagen',      imagen);
    fd.append('descripcion', descripcion);
    if (talla) fd.append('talla', talla);

    axios.post('/web_whatsapp_pedido_producto', fd)
        .then(function (res) {
            var codigo = res.data.codigo_pedido;
            var redirectUrl = res.data.redirect || '/thank-you';

            var detalleExtra = [];
            if (talla) detalleExtra.push('Talla: ' + talla);
            if (color) detalleExtra.push('Color: ' + color);
            var items = [{
                nombre: nombre,
                cantidad: cantidad,
                precio: precio,
                subtotal: subtotal,
                detalle: detalleExtra.join(' | ')
            }];

            var mensaje = window.LV_WA
                ? window.LV_WA.buildOrderMessage(codigo, items, subtotal)
                : ('PEDIDO rossety\nCodigo: ' + codigo + '\n1. ' + nombre + '\nTOTAL: S/ ' + subtotal.toFixed(2));

            if (typeof updateDiv === 'function') updateDiv();

            if (window.LV_WA) {
                window.LV_WA.open(mensaje);
            } else {
                window.open('https://web.whatsapp.com/send?phone={{ \App\Support\ContactoValento::whatsappNumero() }}&text=' + encodeURIComponent(mensaje), '_blank', 'noopener,noreferrer');
            }

            // Abrir WA en otra pestaña y actualizar esta con confirmación
            var irConfirmacion = function () {
                window.location.href = redirectUrl;
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Pedido enviado por WhatsApp',
                    html: 'Tu pedido <strong>' + codigo + '</strong> fue registrado.<br>Esperamos tu pronta respuesta.',
                    confirmButtonText: 'Ver confirmación',
                    timer: 2800,
                    timerProgressBar: true
                }).then(irConfirmacion);
            } else {
                setTimeout(irConfirmacion, 600);
            }
        })
        .catch(function (err) {
            var msg = (err.response && err.response.data && err.response.data.message)
                ? err.response.data.message : 'No se pudo registrar el pedido.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonText: 'Entendido' });
            } else { alert(msg); }
            if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
        });
};

</script>

@endsection
