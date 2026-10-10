@extends('web.base_shop')

@section('head_page')
<link rel="stylesheet" href="{{ asset('temp02/css/shopping-cart.css') }}">
{{-- fonts.css después de shopping-cart.css, como en la plantilla --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ filemtime(public_path('temp02/css/fonts.css')) }}">
<style>
/* ── Botón Asesor WhatsApp ── */
.sc-advisor-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 16px 16px 4px;
    width: calc(100% - 32px);
    padding: 14px 20px;
    background: linear-gradient(135deg, #0f2027 0%, #1a3a4a 50%, #0f2027 100%);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    cursor: pointer;
    text-decoration: none;
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.25);
}
.sc-advisor-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(37,211,102,.08), transparent);
    transform: translateX(-100%);
    transition: transform .6s ease;
}
.sc-advisor-btn:hover::before { transform: translateX(100%); }
.sc-advisor-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(37,211,102,.2);
    text-decoration: none;
}
.sc-advisor-avatar {
    position: relative;
    flex-shrink: 0;
    width: 46px;
    height: 46px;
}
.sc-advisor-avatar svg.advisor-face {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: 2px solid #25d366;
}
.sc-advisor-online {
    position: absolute;
    bottom: 1px;
    right: 1px;
    width: 12px;
    height: 12px;
    background: #25d366;
    border-radius: 50%;
    border: 2px solid #0f2027;
    animation: sc-pulse 2s infinite;
}
@keyframes sc-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(37,211,102,.5); }
    50%      { box-shadow: 0 0 0 5px rgba(37,211,102,0); }
}
.sc-advisor-info { flex: 1; text-align: left; }
.sc-advisor-label {
    display: block;
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #25d366;
    margin-bottom: 2px;
}
.sc-advisor-name {
    display: block;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 1px;
}
.sc-advisor-sub {
    display: block;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,.5);
}
.sc-advisor-arrow {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    background: #25d366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s;
}
.sc-advisor-btn:hover .sc-advisor-arrow { background: #1abe58; }

/* Botón Comprar por WhatsApp — idéntico a .sc-continue-btn en forma y tamaño */
.sc-wa-order-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 0 16px 12px;
    width: calc(100% - 32px);
    padding: 15px;
    background: #25d366;
    color: #fff;
    border: none;
    border-radius: 30px;
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-align: center;
    cursor: pointer;
    transition: background .2s;
}
.sc-wa-order-btn:hover { background: #1abe58; }
</style>
@endsection


@section('content')
@include('web.partials.liam.header')

@php
$isMayorista  = Auth::check() ? \App\Helpers\Helper::validateIsMayorista(Auth::id()) : false;
$descuentoPackTotal = 0.0;
$totalCarritoPack = 0.0;
foreach ($cartItems as $ci) {
    $lista = (float) ($ci->options->precio_lista ?? $ci->price);
    $qty = (int) $ci->qty;
    $packQty = $ci->options->pack_qty ?? null;
    $packPrice = $ci->options->pack_price ?? null;
    if ($lista > 0 && $qty > 0 && $packQty && $packPrice) {
        $calc = \App\Helpers\Helper::calcularOfertaPackLinea($packQty, $packPrice, $lista, $qty);
        $totalCarritoPack += (float) $calc['line_total'];
        $descuentoPackTotal += (float) $calc['discount'];
    } else {
        $line = round((float) $ci->price * $qty, 2);
        $totalCarritoPack += $line;
        if ($lista > 0) {
            $descuentoPackTotal += max(0, round(($lista * $qty) - $line, 2));
        }
    }
}
$descuentoPackTotal = round($descuentoPackTotal, 2);
$totalCarrito = round($totalCarritoPack, 2);

// ── Configuración editable del carrito (módulo admin "Lista Detalle") ──
$carritoCfg = \App\Models\WebCarritoConfig::obtenerConfig();
$igvPct     = (float) ($carritoCfg->igv_porcentaje ?: 18);
$igvFactor  = 1 + ($igvPct / 100);

$subtotalCalc = $totalCarrito / $igvFactor;
$igvCalc      = $totalCarrito - $subtotalCalc;
$umbralEnvioGratis = (float) ($carritoCfg->umbral_envio_gratis ?: 199);
$esEnvioGratis     = $umbralEnvioGratis > 0 && $totalCarrito >= $umbralEnvioGratis;
$faltante          = max(0, $umbralEnvioGratis - $totalCarrito);
@endphp

<main class="sc-page">
    <div class="container">

        <h1 class="sc-page__title">Mi Carrito</h1>

        <div class="sc-layout">

            <!-- ── Columna principal: tabla de productos ── -->
            <div class="sc-layout__main">

                <!-- Estado vacío -->
                <div id="sc-empty" class="sc-empty" @if(count($cartItems) > 0) style="display:none;" @endif>
                    <i class="fa fa-shopping-bag"></i>
                    Tu carrito está vacío.<br>
                    <a href="{{ url('/tienda') }}">Seguir comprando &rarr;</a>
                </div>

                <!-- Tabla de productos -->
                <div id="sc-table-wrap" @if(count($cartItems) === 0) style="display:none;" @endif>
                    <table class="sc-table" id="sc-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="th-price">Precio</th>
                                <th class="th-qty">Cantidad</th>
                                <th class="th-total">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="idTableRow">
                            @foreach($cartItems as $cartItem)
                            @php
                                $imgSrc = $cartItem->options->img ?? '';
                                if ($imgSrc && !preg_match('/^https?:\/\//', $imgSrc)) {
                                    $imgSrc = url('/') . '/' . ltrim($imgSrc, '/');
                                }
                                $talla = $cartItem->options->talla ?? null;
                                $color = $cartItem->options->color ?? null;
                                $precioListaRow = (float) ($cartItem->options->precio_lista ?? $cartItem->price);
                                $packQtyRow = (int) ($cartItem->options->pack_qty ?? 0);
                                $packPriceRow = (float) ($cartItem->options->pack_price ?? 0);
                                if ($packQtyRow >= 1 && $packPriceRow > 0 && $precioListaRow > 0) {
                                    $calcRow = \App\Helpers\Helper::calcularOfertaPackLinea(
                                        $packQtyRow,
                                        $packPriceRow,
                                        $precioListaRow,
                                        (int) $cartItem->qty
                                    );
                                    $lineTotalRow = (float) $calcRow['line_total'];
                                    $descRow = (float) $calcRow['discount'];
                                    $unitDisplay = (float) $calcRow['unit_price'];
                                } else {
                                    $lineTotalRow = round($cartItem->price * $cartItem->qty, 2);
                                    $descRow = max(0, round(($precioListaRow * $cartItem->qty) - $lineTotalRow, 2));
                                    $unitDisplay = (float) $cartItem->price;
                                }
                            @endphp
                            <tr id="clsEliminarRowa_{{ $cartItem->rowId }}"
                                data-precio-lista="{{ number_format($precioListaRow, 2, '.', '') }}"
                                data-descuento-pack="{{ number_format($descRow, 2, '.', '') }}"
                                data-pack-qty="{{ $packQtyRow }}"
                                data-pack-price="{{ number_format($packPriceRow, 2, '.', '') }}"
                                data-line-total="{{ number_format($lineTotalRow, 2, '.', '') }}">
                                <td class="product-col">
                                    <div class="sc-prod-cell">
                                        <a href="/web_shopDetail/{{ $cartItem->options->slug ?? $cartItem->options->id_producto }}">
                                            <img class="sc-prod-img"
                                                 src="{{ $imgSrc }}"
                                                 alt="{{ $cartItem->name }}"
                                                 onerror="this.style.background='#f5f5f5';this.src='';">
                                        </a>
                                        <div>
                                            <p class="sc-prod-name">
                                                <a href="/web_shopDetail/{{ $cartItem->options->slug ?? $cartItem->options->id_producto }}" style="color:inherit;text-decoration:none;">
                                                    {{ $talla ? preg_replace('/\s*·\s*Talla\s+\S+$/iu', '', $cartItem->name) : $cartItem->name }}
                                                </a>
                                            </p>
                                            @if($talla || $color)
                                            <p style="font-family:'Montserrat',sans-serif;font-size:11px;color:#999;margin:4px 0 0;">
                                                @if($talla) Talla: {{ $talla }} @endif
                                                @if($talla && $color) &nbsp;|&nbsp; @endif
                                                @if($color) Color: {{ $color }} @endif
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="td-price sc-price-cell" id="precio_unitario_cell_{{ $cartItem->rowId }}">
                                    @if($precioListaRow > $unitDisplay + 0.001)
                                    <span class="sc-price-from">De:</span>
                                    <span class="sc-price-old">S/ {{ number_format($precioListaRow, 2) }}</span>
                                    @endif
                                    <span class="sc-price-por">Por</span>
                                    <span class="sc-price-new">
                                        S/ {{ number_format($unitDisplay, 2) }}
                                        @if($isMayorista)<span style="font-size:10px;color:#c0392b;font-weight:700;"> May.</span>@endif
                                    </span>
                                </td>

                                <td class="td-qty sc-qty-cell">
                                    <div class="sc-qty-ctrl">
                                        <button type="button" class="qty-btn-minus" data-row-id="{{ $cartItem->rowId }}">−</button>
                                        <span class="sc-qty-val" id="qty_{{ $cartItem->rowId }}">{{ $cartItem->qty }}</span>
                                        <input type="hidden" class="clsChangeLista"
                                               value="{{ $cartItem->qty }}"
                                               isPrecio="{{ $unitDisplay }}"
                                               isRowId="{{ $cartItem->rowId }}">
                                        <button type="button" class="qty-btn-plus" data-row-id="{{ $cartItem->rowId }}">+</button>
                                    </div>
                                </td>

                                <td class="sc-total-cell">
                                    <span class="txt_total_{{ $cartItem->rowId }}">
                                        S/ {{ number_format($lineTotalRow, 2) }}
                                    </span>
                                    @if($descRow > 0 && $packQtyRow > 0)
                                    <small class="sc-pack-save" style="display:block;font-size:10px;color:#27ae60;font-weight:600;margin-top:2px;">
                                        Oferta {{ $packQtyRow }}x · ahorras S/ {{ number_format($descRow, 2) }}
                                    </small>
                                    @endif
                                </td>

                                <td class="sc-del-cell">
                                    <button type="button" class="sc-del-btn"
                                            onclick="getRemoveProduct('{{ $cartItem->rowId }}')"
                                            title="Eliminar">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


            </div>
            <!-- fin columna principal -->

            <!-- ── Columna lateral: resumen ── -->
            <div class="sc-layout__aside">
                <div class="sc-aside-card">

                    {{-- TODO: Activar cupón de descuento cuando esté listo --}}
                    {{-- <button class="sc-coupon-btn" id="sc-coupon-btn">
                        Añadir cupón de descuento
                    </button>
                    <div class="sc-coupon-wrap" id="sc-coupon-wrap">
                        <input type="text" class="sc-coupon-input" placeholder="Código de cupón">
                        <button class="sc-coupon-apply">Aplicar</button>
                    </div>
                    <hr class="sc-aside-divider"> --}}

                    <p class="sc-summary-title">{{ $carritoCfg->resumen_titulo }}</p>

                    <div class="sc-summary-row">
                        <span class="label">{{ $carritoCfg->label_subtotal }}</span>
                        <span class="value" id="txtSubtotal">S/ {{ number_format($totalCarrito, 2) }}</span>
                    </div>
                    <div class="sc-summary-row" style="display:none;">{{-- La plantilla no desglosa IGV (precios con IGV incluido) --}}
                        <span class="label">{{ $carritoCfg->label_igv }}</span>
                        <span class="value" id="txtIgv">S/ {{ number_format($igvCalc, 2) }}</span>
                    </div>

                    <div class="sc-summary-row" id="sc-discount-row" style="{{ $descuentoPackTotal > 0 ? '' : 'display:none;' }}">
                        <span class="label" style="color:#27ae60;">{{ $carritoCfg->label_descuento }}</span>
                        <span class="value" id="txtDescuentoPack" style="color:#27ae60;">- S/ {{ number_format($descuentoPackTotal, 2) }}</span>
                    </div>

                    <!-- Barra envío gratis -->
                    <div id="sc-shipping-block" style="display:none;padding:0 16px 4px;">
                        @if($esEnvioGratis)
                        <div id="sc-shipping-msg" style="font-family:'Montserrat',sans-serif;font-size:11px;color:#27ae60;font-weight:700;text-align:center;padding:8px 0;">
                            <i class="fa fa-gift"></i> {{ $carritoCfg->envio_gratis_texto }}
                        </div>
                        @else
                        <div id="sc-shipping-msg" style="font-family:'Montserrat',sans-serif;font-size:11px;color:#888;padding:8px 0;">
                            {!! str_replace('{monto}', '<strong style="color:#c0392b;">S/ ' . number_format($faltante, 2) . '</strong>', e($carritoCfg->envio_falta_texto)) !!}
                        </div>
                        <div style="height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden;margin-bottom:8px;">
                            <div id="sc-shipping-bar" style="height:100%;border-radius:3px;background:#c0392b;width:{{ min(100, ($totalCarrito / $umbralEnvioGratis) * 100) }}%;transition:width .4s;"></div>
                        </div>
                        @endif
                    </div>

                    <div class="sc-summary-row total-row">
                        <span class="label">{{ $carritoCfg->label_total }}</span>
                        <span class="value" id="txtTotal">S/ {{ number_format($totalCarrito, 2) }}</span>
                    </div>

                    @if($carritoCfg->nota_activa === 'S' && trim((string) $carritoCfg->nota_texto) !== '')
                    <p class="sc-important-note">
                        <strong>{{ $carritoCfg->nota_titulo }}</strong> {{ $carritoCfg->nota_texto }}
                    </p>
                    @endif

                    <a href="{{ url('/web_checkout/index') }}" class="sc-continue-btn">{{ $carritoCfg->btn_continuar_texto }}</a>

                    <div class="sc-keep-shopping-wrap" id="sc-keep-shopping-wrap" @if(count($cartItems) === 0) style="display:none;" @endif>
                        <a href="{{ url('/tienda') }}" class="sc-keep-shopping">
                            <i class="fa fa-share"></i> {{ $carritoCfg->btn_seguir_texto }}
                        </a>
                    </div>

                    <!-- Botón Comprar por WhatsApp — independiente de la pasarela de pago -->
                    <!-- <button type="button" id="sc-wa-order-btn" class="sc-wa-order-btn" onclick="comprarPorWhatsapp()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18" height="18" style="flex-shrink:0"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Comprar por WhatsApp
                    </button> -->



                    {{-- Botón Asesor de Compra --}}
                    @if($carritoCfg->asesor_activo === 'S')
                    @php
                        $waAsesor = \App\Models\WebWhatsappConfig::first();
                        $waNumAsesor = preg_replace('/\D/', '', $waAsesor?->wa_numero ?: \App\Support\ContactoValento::whatsappNumero());
                        $waMsgAsesor = urlencode(trim((string) $carritoCfg->asesor_mensaje) ?: 'Hola! Me gustaría recibir asesoría para completar mi compra.');
                        $waUrlAsesor = 'https://wa.me/' . $waNumAsesor . '?text=' . $waMsgAsesor;
                    @endphp
                    <a href="{{ $waUrlAsesor }}" target="_blank" rel="noopener noreferrer" class="sc-advisor-btn">
                        <div class="sc-advisor-avatar">
                            <svg class="advisor-face" viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="23" cy="23" r="23" fill="#1a3a4a"/>
                                <!-- cabeza -->
                                <circle cx="23" cy="19" r="9" fill="#f5cba7"/>
                                <!-- cuerpo / camisa -->
                                <path d="M8 46 Q8 34 23 32 Q38 34 38 46Z" fill="#25d366"/>
                                <!-- cuello -->
                                <rect x="19" y="27" width="8" height="6" rx="3" fill="#f5cba7"/>
                                <!-- ojos -->
                                <circle cx="19.5" cy="18.5" r="1.3" fill="#2d3436"/>
                                <circle cx="26.5" cy="18.5" r="1.3" fill="#2d3436"/>
                                <!-- sonrisa -->
                                <path d="M19 22 Q23 25.5 27 22" stroke="#c0392b" stroke-width="1.3" fill="none" stroke-linecap="round"/>
                                <!-- auricular headset -->
                                <path d="M13 20 Q13 10 23 10 Q33 10 33 20" stroke="#25d366" stroke-width="2" fill="none"/>
                                <rect x="11" y="19" width="4" height="7" rx="2" fill="#25d366"/>
                                <rect x="31" y="19" width="4" height="7" rx="2" fill="#25d366"/>
                            </svg>
                            <span class="sc-advisor-online"></span>
                        </div>
                        <div class="sc-advisor-info">
                            <span class="sc-advisor-label">{{ $carritoCfg->asesor_label }}</span>
                            <span class="sc-advisor-name">{{ $carritoCfg->asesor_titulo }}</span>
                            <span class="sc-advisor-sub">{{ $carritoCfg->asesor_subtitulo }}</span>
                        </div>
                        <div class="sc-advisor-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                    </a>
                    @endif

                </div>
            </div>
            <!-- fin columna lateral -->

        </div>
        <!-- fin sc-layout -->

    </div>
</main>

@include('web.partials.liam.footer')
@endsection


@section('footer_page')
<script>
$(document).ready(function () {
    var umbral = @json((float) $umbralEnvioGratis);
    var igvFactor = @json((float) $igvFactor);
    var envioGratisTexto = @json($carritoCfg->envio_gratis_texto);
    var envioFaltaTpl = @json($carritoCfg->envio_falta_texto);
    var isMayoristaLista = @json($isMayorista);

    // ── Calcular totales ─────────────────────────────
    window.calcularTotales = function () {
        var total = 0;
        var descuento = 0;
        $('#idTableRow tr').each(function () {
            var cantidad = parseInt($(this).find('.clsChangeLista').val()) || 0;
            var lista    = parseFloat($(this).attr('data-precio-lista')) || 0;
            var packQty  = parseInt($(this).attr('data-pack-qty') || '0', 10) || 0;
            var packPrice = parseFloat($(this).attr('data-pack-price')) || 0;
            var precio   = parseFloat($(this).find('.clsChangeLista').attr('isPrecio')) || 0;
            var lineTotal;

            if (packQty >= 1 && packPrice > 0 && lista > 0 && cantidad > 0) {
                var packs = Math.floor(cantidad / packQty);
                var resto = cantidad % packQty;
                lineTotal = (packs * packPrice) + (resto * lista);
                descuento += Math.max(0, (lista * cantidad) - lineTotal);
            } else {
                lineTotal = precio * cantidad;
                if (lista > precio) {
                    descuento += (lista - precio) * cantidad;
                }
            }
            total += lineTotal;
            $(this).attr('data-line-total', lineTotal.toFixed(2));
            $(this).attr('data-descuento-pack', Math.max(0, (lista * cantidad) - lineTotal).toFixed(2));
        });
        total = Math.round(total * 100) / 100;
        descuento = Math.round(descuento * 100) / 100;
        var subtotal = total / igvFactor;
        var igv      = total - subtotal;

        $('#txtSubtotal').text('S/ ' + total.toFixed(2));
        $('#txtIgv').text('S/ ' + igv.toFixed(2));
        $('#txtTotal').text('S/ ' + total.toFixed(2));
        if (descuento > 0.009) {
            $('#sc-discount-row').show();
            $('#txtDescuentoPack').text('- S/ ' + descuento.toFixed(2));
        } else {
            $('#sc-discount-row').hide();
        }
        actualizarEnvio(total);
    };

    function actualizarEnvio(total) {
        var barra = $('#sc-shipping-bar');
        var msg   = $('#sc-shipping-msg');
        var pct   = Math.min(100, (total / umbral) * 100);
        if (barra.length) barra.css('width', pct + '%');
        if (umbral > 0 && total >= umbral) {
            msg.html('<i class="fa fa-gift"></i> ' + envioGratisTexto).css('color', '#27ae60');
            barra.closest('div').hide();
        } else {
            var falta = (umbral - total).toFixed(2);
            var montoHtml = '<strong style="color:#c0392b;">S/ ' + falta + '</strong>';
            msg.html(envioFaltaTpl.replace('{monto}', montoHtml)).css('color', '#888');
        }
    }

    // ── Botones +/- ──────────────────────────────────
    $(document).on('click', '.qty-btn-plus', function () {
        var rowId = $(this).data('row-id');
        var input = $('input.clsChangeLista[isRowId="' + rowId + '"]');
        var val   = parseInt(input.val()) || 1;
        input.val(val + 1).trigger('change');
        $('#qty_' + rowId).text(val + 1);
    });

    $(document).on('click', '.qty-btn-minus', function () {
        var rowId = $(this).data('row-id');
        var input = $('input.clsChangeLista[isRowId="' + rowId + '"]');
        var val   = parseInt(input.val()) || 1;
        if (val > 1) {
            input.val(val - 1).trigger('change');
            $('#qty_' + rowId).text(val - 1);
        }
    });

    $(document).on('change', '.clsChangeLista', function () {
        var rowId    = $(this).attr('isRowId');
        var cantidad = parseInt($(this).val()) || 1;
        if (cantidad < 1) { cantidad = 1; $(this).val(1); }

        clearTimeout(window._updateTimeout);
        window._updateTimeout = setTimeout(function () {
            actualizarEnServidor(rowId, cantidad);
        }, 500);
    });

    function actualizarEnServidor(rowId, cantidad) {
        var fd = new FormData();
        fd.append('id_producto', rowId);
        fd.append('cantidad', cantidad);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        axios.post('/web_shopDetail_actualizar', fd)
            .then(function (res) {
                if (res.data.success === false) {
                    Swal.fire({ icon: 'error', title: 'Stock', text: res.data.message || 'No se pudo actualizar.', confirmButtonText: 'Entendido' })
                        .then(function () { location.reload(); });
                    return;
                }
                // Si el backend regeneró rowId, remapeamos IDs del DOM
                var newRowId = res.data.row_id || rowId;
                if (newRowId && newRowId !== rowId) {
                    remapCartRowId(rowId, newRowId);
                    rowId = newRowId;
                }
                var pu = res.data.precio_unitario;
                var lt = res.data.line_total;
                var pl = res.data.precio_lista;
                var dp = res.data.descuento_pack;
                if (pu !== undefined && pu !== null) {
                    var html = '';
                    if (pl !== undefined && pl !== null && parseFloat(pl) > parseFloat(pu) + 0.001) {
                        html += '<span class="sc-price-from">De:</span><span class="sc-price-old">S/ ' + parseFloat(pl).toFixed(2) + '</span>';
                    }
                    html += '<span class="sc-price-por">Por</span><span class="sc-price-new">S/ ' + parseFloat(pu).toFixed(2);
                    if (isMayoristaLista) html += ' <span style="font-size:10px;color:#c0392b;font-weight:700;"> May.</span>';
                    html += '</span>';
                    $('#precio_unitario_cell_' + rowId).html(html);
                    // Precisión completa (no redondear a 2) para no romper totales
                    $('input.clsChangeLista[isRowId="' + rowId + '"]').attr('isPrecio', pu);
                }
                if (lt !== undefined && lt !== null) {
                    var $tot = $('.txt_total_' + rowId);
                    $tot.text('S/ ' + parseFloat(lt).toFixed(2));
                    $('#clsEliminarRowa_' + rowId).attr('data-line-total', parseFloat(lt).toFixed(2));
                    var $save = $tot.siblings('.sc-pack-save');
                    if (dp && parseFloat(dp) > 0) {
                        var pq = parseInt($('#clsEliminarRowa_' + rowId).attr('data-pack-qty') || '0', 10);
                        if (res.data.pack_qty) pq = parseInt(res.data.pack_qty, 10) || pq;
                        var saveHtml = 'Oferta' + (pq > 0 ? (' ' + pq + 'x') : '') + ' · ahorras S/ ' + parseFloat(dp).toFixed(2);
                        if ($save.length) {
                            $save.text(saveHtml).show();
                        } else {
                            $tot.after('<small class="sc-pack-save" style="display:block;font-size:10px;color:#27ae60;font-weight:600;margin-top:2px;">' + saveHtml + '</small>');
                        }
                    } else if ($save.length) {
                        $save.hide();
                    }
                }
                if (pl !== undefined && pl !== null) {
                    $('#clsEliminarRowa_' + rowId).attr('data-precio-lista', parseFloat(pl).toFixed(2));
                }
                if (dp !== undefined && dp !== null) {
                    $('#clsEliminarRowa_' + rowId).attr('data-descuento-pack', parseFloat(dp).toFixed(2));
                }
                if (res.data.pack_qty !== undefined) {
                    $('#clsEliminarRowa_' + rowId).attr('data-pack-qty', res.data.pack_qty);
                }
                if (res.data.pack_price !== undefined) {
                    $('#clsEliminarRowa_' + rowId).attr('data-pack-price', parseFloat(res.data.pack_price).toFixed(2));
                }
                calcularTotales();
                if (typeof updateDiv === 'function') updateDiv();
            })
            .catch(function (err) {
                var msg = err.response && err.response.data && err.response.data.message
                    ? err.response.data.message : 'No se pudo actualizar la cantidad.';
                var reload = err.response && err.response.data && err.response.data.reload;
                Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonText: 'Entendido' })
                    .then(function () { if (reload) location.reload(); });
            });
    }

    function remapCartRowId(oldId, newId) {
        if (!oldId || !newId || oldId === newId) return;
        var $row = $('#clsEliminarRowa_' + oldId);
        if ($row.length) $row.attr('id', 'clsEliminarRowa_' + newId);
        var $price = $('#precio_unitario_cell_' + oldId);
        if ($price.length) $price.attr('id', 'precio_unitario_cell_' + newId);
        var $qty = $('#qty_' + oldId);
        if ($qty.length) $qty.attr('id', 'qty_' + newId);
        $('.txt_total_' + oldId).removeClass('txt_total_' + oldId).addClass('txt_total_' + newId);
        $('[data-row-id="' + oldId + '"]').attr('data-row-id', newId);
        $('input.clsChangeLista[isRowId="' + oldId + '"]').attr('isRowId', newId);
        $('[onclick*="' + oldId + '"]').each(function () {
            var oc = $(this).attr('onclick') || '';
            $(this).attr('onclick', oc.split(oldId).join(newId));
        });
    }

    // ── Eliminar producto ────────────────────────────
    window.getRemoveProduct = function (rowId) {
        $('#clsEliminarRowa_' + rowId).fadeOut(300, function () {
            $(this).remove();
            calcularTotales();
            if ($('#idTableRow tr').length === 0) {
                $('#sc-table-wrap').hide();
                $('#sc-keep-shopping-wrap').hide();
                $('#sc-empty').show();
            }
        });

        var fd = new FormData();
        fd.append('id_producto', rowId);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        axios.post('/web_shopDetail_eliminar', fd)
            .then(function () { if (typeof updateDiv === 'function') updateDiv(); })
            .catch(function () {});
    };

    // ── Cupón toggle ─────────────────────────────────
    $('#sc-coupon-btn').on('click', function () {
        $('#sc-coupon-wrap').toggleClass('open');
    });

    // ── Init ─────────────────────────────────────────
    calcularTotales();
});
</script>

<script>
// ── Comprar por WhatsApp ──────────────────────────────────────────────────────
window.comprarPorWhatsapp = function () {
    // Recolectar items del carrito desde la tabla visible
    var items = [];
    var total = 0;

    $('#idTableRow tr:visible').each(function () {
        var nombre   = $(this).find('.sc-prod-name a').text().trim();
        var precio   = parseFloat($(this).find('.clsChangeLista').attr('isPrecio')) || 0;
        var cantidad = parseInt($(this).find('.clsChangeLista').val()) || 1;
        var talla    = $(this).find('.sc-prod-cell p').text().trim();
        var subtotal = precio * cantidad;
        total += subtotal;
        if (nombre) items.push({ nombre: nombre, precio: precio, cantidad: cantidad, subtotal: subtotal, detalle: talla });
    });

    if (items.length === 0) {
        if (typeof toastr !== 'undefined') toastr.warning('Tu carrito está vacío.');
        else alert('Tu carrito está vacío.');
        return;
    }

    // Deshabilitar botón mientras se procesa
    var btn = document.getElementById('sc-wa-order-btn');
    if (btn) { btn.disabled = true; btn.style.opacity = '0.7'; }

    // 1. Registrar pedido en el servidor
    var fd = new FormData();
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    axios.post('/web_whatsapp_pedido', fd)
        .then(function (res) {
            var codigo = res.data.codigo_pedido;
            var redirectUrl = res.data.redirect || '/thank-you';

            // 2. Mensaje en texto plano (sin entidades HTML &#...; / &iexcl;)
            var mensaje = window.LV_WA
                ? window.LV_WA.buildOrderMessage(codigo, items, total)
                : ('PEDIDO rossety\nCodigo: ' + codigo + '\nTOTAL: S/ ' + total.toFixed(2));

            // 3. Vaciar carrito visualmente
            $('#idTableRow').empty();
            $('#sc-table-wrap').hide();
            $('#sc-keep-shopping-wrap').hide();
            $('#sc-empty').show();
            if (typeof updateDiv === 'function') updateDiv();

            // 4. Abrir WhatsApp y luego confirmación en esta ventana
            if (window.LV_WA) {
                window.LV_WA.open(mensaje);
            } else {
                var waNum = '{{ \App\Support\ContactoValento::whatsappNumero() }}';
                window.open('https://web.whatsapp.com/send?phone=' + waNum + '&text=' + encodeURIComponent(mensaje), '_blank', 'noopener,noreferrer');
            }

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
