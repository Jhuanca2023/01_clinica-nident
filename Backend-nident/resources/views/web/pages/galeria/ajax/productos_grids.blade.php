@php
$deliveryInfo = \App\Helpers\Helper::corteTiempoPromocion($data->id_producto);

// ── PRECIO PÚBLICO ──
$descuento = (!empty($data->precio_old) && $data->precio_old > $data->precio)
    ? round((1 - $data->precio / $data->precio_old) * 100)
    : 0;

// ── PRECIO MAYORISTA ──
$tienePrecioMayorista = !empty($data->precio_mayorista) && $data->precio_mayorista > 0;
$precioMayorista      = $tienePrecioMayorista ? $data->precio_mayorista : $data->precio;
$descuentoMayorista   = ($tienePrecioMayorista && !empty($data->precio_mayorista_old) && $data->precio_mayorista_old > $precioMayorista)
    ? round((1 - $precioMayorista / $data->precio_mayorista_old) * 100)
    : 0;
@endphp

<div class="product-layout-1">

    {{-- Imagen con overlays --}}
    <div class="pc-image-wrapper">

        {{-- Badges overlay (entrega + yape) --}}
        @if($data->corte_tiempo_promocion != null || $data->precio_yape != null)
        <div class="pc-delivery-badge">
            @if($data->corte_tiempo_promocion != null)
                @if($deliveryInfo['dia_entrega'] === 'hoy')
                <p class="clsLlegaHoy"><i class="fas fa-bolt"></i> {{ $deliveryInfo['mensaje'] }}</p>
                @elseif($deliveryInfo['dia_entrega'] === 'lunes')
                <p class="clsLlegaLunes"><i class="fas fa-calendar"></i> {{ $deliveryInfo['mensaje'] }}</p>
                @else
                <p class="clsLlegaManana"><i class="fas fa-clock"></i> {{ $deliveryInfo['mensaje'] }}</p>
                @endif
            @endif
            @if($data->precio_yape != null)
            <div class="pc-yape-row">
                <img src="{{URL::asset('/imagenes/logo-yape.jpg')}}" alt="Yape" />
                <span>S/ {{ number_format($data->precio_yape, 2) }}</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Botón favorito --}}
        <div class="pc-favorite">
            @if(Auth::check() && ($data->es_favorito ?? false))
            <a href="javascript:void(0)" id="idActualizarDivFavorito{{$data->id_producto}}"
                class="clsBotonEliminarFavorito clsActionDelete"
                onclick="clsActionDelete('ESTADO_GALLERY_PRODUCT','{{$data->id_producto}}','{{Auth::id()}}',this)">
                <i class="fa-solid fa-check"></i>
            </a>
            @else
            <a href="javascript:void(0)" id="idActualizarDivFavorito{{$data->id_producto}}"
                class="clsBotonAgragarFavorito clsActionAdd"
                onclick="clsActionAdd('ESTADO_GALLERY_PRODUCT','{{$data->id_producto}}',this)">
                <i class="fa-regular fa-heart"></i>
            </a>
            @endif
        </div>

        {{-- Imagen del producto --}}
        <a href="{{url('/web_shopDetail')}}/{{$data->slug}}" style="display:block;width:100%;height:100%;">
            <img src="{{ URL::asset($data->url_imagen) }}" alt="{{ $data->titulo }}" />
        </a>

    </div>

    {{-- Cuerpo de la tarjeta --}}
    <div class="pc-body">

        {{-- Rating --}}
        @php 
            $ratingVal = $data->display_rating ?? 0;
            $ratingCount = $data->total_ratings ?? $data->count_resena ?? 0;
        @endphp
        <div class="pc-rating">
            <x-star-rating :rating="$ratingVal"
                label="{{ number_format((float)$ratingVal, 1) }} ({{ $ratingCount }})" />
        </div>

        {{-- Título --}}
        <a href="{{url('/web_shopDetail')}}/{{$data->slug}}" class="pc-title">
            {{ Str::limit($data->titulo, 50) }}
        </a>

        {{-- Precios --}}
        <div class="pc-prices">
            @if($isMayorista ?? false)
                {{-- === MAYORISTA === --}}
                @if($tienePrecioMayorista)
                    @if($descuentoMayorista > 0)
                    {{-- Mayorista: precio anterior + actual + % --}}
                    <div class="pc-price-row">
                        <span class="pc-price-old">S/ {{ number_format($data->precio_mayorista_old, 2) }}</span>
                        <span class="pc-price-current">S/ {{ number_format($precioMayorista, 2) }}</span>
                        <span class="pc-discount-badge">-{{ $descuentoMayorista }}%</span>
                        <span class="pc-mayorista-tag">May.</span>
                    </div>
                    @else
                    {{-- Mayorista: solo precio mayorista --}}
                    <div class="pc-price-row">
                        <span class="pc-price-only">S/ {{ number_format($precioMayorista, 2) }}</span>
                        <span class="pc-mayorista-tag">May.</span>
                    </div>
                    @endif
                @else
                    {{-- Sin precio mayorista → fallback precio público --}}
                    @if($descuento > 0)
                    <div class="pc-price-row">
                        <span class="pc-price-old">S/ {{ number_format($data->precio_old, 2) }}</span>
                        <span class="pc-price-current">S/ {{ number_format($data->precio, 2) }}</span>
                        <span class="pc-discount-badge">-{{ $descuento }}%</span>
                    </div>
                    @else
                    <div class="pc-price-row">
                        <span class="pc-price-only">S/ {{ number_format($data->precio, 2) }}</span>
                    </div>
                    @endif
                @endif
            @else
                {{-- === PÚBLICO === --}}
                @if($descuento > 0)
                {{-- Público: precio anterior + actual + % --}}
                <div class="pc-price-row">
                    <span class="pc-price-old">S/ {{ number_format($data->precio_old, 2) }}</span>
                    <span class="pc-price-current">S/ {{ number_format($data->precio, 2) }}</span>
                    <span class="pc-discount-badge">-{{ $descuento }}%</span>
                </div>
                @else
                {{-- Público: solo precio normal --}}
                <div class="pc-price-row">
                    <span class="pc-price-only">S/ {{ number_format($data->precio, 2) }}</span>
                </div>
                @endif
            @endif
        </div>

        {{-- Botón carrito --}}
        <a href="javascript:void(0)" 
           class="pc-btn-cart clsBtnAgregarCarritoGlobal" 
           data-codigo="{{ $data->codigo_producto }}" 
           data-id="{{ $data->id_producto }}">
            <i class="fas fa-shopping-cart" style="margin-right:6px;"></i>Añadir al carrito
        </a>

    </div>
</div>
