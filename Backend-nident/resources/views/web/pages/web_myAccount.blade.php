@extends('web.base')

@section('head_page')
@vite(['resources/sass/web_shopResultado.scss'])
@endsection


@section('content')
<div class="ocp-page">
    @include('web.partials.breadcrumb')

    @php
        $costoEnvioNumerico = ($costo_envio === 'gratis') ? 0 : floatval($costo_envio);
        $totalConEnvio = floatval($total) + $costoEnvioNumerico;
        $esLimaCallao = isset($result->hora_regresiva_descripcion) && ($result->hora_regresiva_descripcion == 'Retíralo por agencia shalom o marvisur');
    @endphp

    <div class="container mt-2">

        {{-- ═══ BANNER SUPERIOR ═══ --}}
        <div class="ocp-topbar" data-aos="fade-down">
            <div class="ocp-topbar__status">
                <span class="ocp-topbar__icon"><i class="fas fa-check-circle"></i></span>
                <div>
                    <strong>Pedido Confirmado</strong>
                    <span class="ocp-topbar__sub">Gracias por tu compra</span>
                </div>
            </div>
            <div class="ocp-topbar__order">
                <span class="ocp-topbar__label">N° Pedido</span>
                <span class="ocp-topbar__code">{{ $codigo_pedido }}</span>
            </div>
            @if($result->radio_metodo_pago == 1)
            <div class="ocp-topbar__badge ocp-topbar__badge--paid">
                <i class="fas fa-lock"></i> Pago Online
            </div>
            @else
            <div class="ocp-topbar__badge ocp-topbar__badge--cod">
                <i class="fas fa-hand-holding-usd"></i> Contra Entrega
            </div>
            @endif
        </div>

        {{-- ═══ GRID PRINCIPAL ═══ --}}
        <div class="ocp-grid" data-aos="fade-up" data-aos-delay="80">

            {{-- ── COLUMNA IZQUIERDA ── --}}
            <div class="ocp-left">

                {{-- Detalles del pedido --}}
                <div class="ocp-card">
                    <div class="ocp-card__head">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Detalles del Pedido</span>
                    </div>
                    <div class="ocp-card__body">
                        <table class="ocp-table">
                            <tbody>
                                <tr>
                                    <td class="ocp-table__label"><i class="fas fa-credit-card"></i> Pago</td>
                                    <td>
                                        @if($result->radio_metodo_pago == 1)
                                        <span class="ocp-badge ocp-badge--success"><i class="fas fa-check"></i> Online completado</span>
                                        @else
                                        <span class="ocp-badge ocp-badge--warn"><i class="fas fa-hand-holding-usd"></i> Contra entrega</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ocp-table__label"><i class="fas fa-truck"></i> Envío</td>
                                    <td>
                                        @if(!$esLimaCallao)
                                        <span>Lima — Delivery a domicilio</span>
                                        @else
                                        <span>{{ $result->hora_regresiva_descripcion }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ocp-table__label"><i class="fas fa-shipping-fast"></i> Costo envío</td>
                                    <td>
                                        @if($costo_envio === 'gratis')
                                        <span class="ocp-badge ocp-badge--success">¡GRATIS!</span>
                                        @else
                                        <span>S/ {{ number_format($costoEnvioNumerico, 2) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="ocp-table__total">
                                    <td class="ocp-table__label"><i class="fas fa-money-bill-wave"></i> Total</td>
                                    <td>
                                        <strong class="ocp-total-val">S/ {{ number_format($totalConEnvio, 2) }}</strong>
                                        @if($costoEnvioNumerico > 0)
                                        <small>(S/ {{ $total }} + envío S/ {{ number_format($costoEnvioNumerico, 2) }})</small>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @if($esLimaCallao)
                        <div class="ocp-alert">
                            <i class="fas fa-info-circle"></i>
                            El envío a provincia es pago a destino. El cobro actual es solo por llevar el pedido a la agencia.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Productos --}}
                @if(isset($productos) && count($productos) > 0)
                <div class="ocp-card">
                    <div class="ocp-card__head">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Productos <em class="ocp-count">{{ count($productos) }}</em></span>
                    </div>
                    <div class="ocp-card__body ocp-card__body--flush">
                        <div class="ocp-products">
                            @foreach($productos as $producto)
                            <div class="ocp-product">
                                <div class="ocp-product__name">{{ $producto->pro_nombre }}</div>
                                <div class="ocp-product__meta">
                                    <span class="ocp-product__qty">× {{ $producto->pro_cantidad }}</span>
                                    <span class="ocp-product__unit">S/ {{ number_format($producto->pro_precio, 2) }} c/u</span>
                                    <strong class="ocp-product__sub">S/ {{ number_format($producto->pro_precio * $producto->pro_cantidad, 2) }}</strong>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="ocp-summary">
                            <div class="ocp-summary__row">
                                <span>Subtotal</span><span>S/ {{ $total }}</span>
                            </div>
                            <div class="ocp-summary__row {{ $costoEnvioNumerico == 0 ? 'ocp-summary__row--free' : '' }}">
                                <span>Envío</span>
                                <span>{{ $costoEnvioNumerico > 0 ? 'S/ '.number_format($costoEnvioNumerico,2) : '¡GRATIS!' }}</span>
                            </div>
                            <div class="ocp-summary__total">
                                <span>Total del pedido</span>
                                <strong>S/ {{ number_format($totalConEnvio, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>{{-- /ocp-left --}}

            {{-- ── COLUMNA DERECHA ── --}}
            <div class="ocp-right">

                {{-- Pago online confirmado --}}
                @if($result->radio_metodo_pago == 1)
                <div class="ocp-card ocp-card--paid">
                    <div class="ocp-paid">
                        <i class="fas fa-check-circle ocp-paid__icon"></i>
                        <div>
                            <strong>Pago Completado</strong>
                            <p>Tu pago fue procesado correctamente. Recibirás la confirmación en tu correo.</p>
                        </div>
                    </div>
                </div>

                {{-- Instrucciones contra entrega --}}
                @else
                <div class="ocp-card">
                    <div class="ocp-card__head ocp-card__head--warn">
                        <i class="fas fa-university"></i>
                        <span>Instrucciones de Pago</span>
                    </div>
                    <div class="ocp-card__body">
                        <div class="ocp-amount">
                            <span class="ocp-amount__label">Pagar al recibir</span>
                            <span class="ocp-amount__val">S/ {{ number_format($totalConEnvio, 2) }}</span>
                            @if($costoEnvioNumerico > 0)
                            <span class="ocp-amount__breakdown">Productos S/ {{ $total }} + Envío S/ {{ number_format($costoEnvioNumerico, 2) }}</span>
                            @endif
                        </div>

                        {{-- Monto pagado y vuelto --}}
                        @php $montoPagadoCliente = floatval($result->monto_pagado_cliente ?? 0); @endphp
                        @if($montoPagadoCliente > 0)
                        @php $vueltoEstimado = $montoPagadoCliente - $totalConEnvio; @endphp
                        <div style="margin:12px 0;padding:12px 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:10px;">
                            <div style="font-size:13px;color:#374151;margin-bottom:6px;">
                                <i class="fas fa-money-bill-wave" style="color:#16a34a;"></i>
                                <strong>Monto que prepararás:</strong>
                                <span style="font-size:18px;font-weight:700;color:#16a34a;margin-left:6px;">S/ {{ number_format($montoPagadoCliente, 2) }}</span>
                            </div>
                            @if($vueltoEstimado > 0)
                            <div style="font-size:13px;color:#374151;">
                                <i class="fas fa-coins" style="color:#d97706;"></i>
                                <strong>Vuelto estimado:</strong>
                                <span style="font-size:16px;font-weight:700;color:#d97706;margin-left:6px;">S/ {{ number_format($vueltoEstimado, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="ocp-methods">
                            <div class="ocp-method ocp-method--yape">
                                <div class="ocp-method__header">
                                    <i class="fas fa-mobile-alt"></i> Yape / Plin
                                </div>
                                <div class="ocp-method__rows">
                                    <div class="ocp-method__row"><span>Número</span><strong>+51 970 088 031</strong></div>
                                    <div class="ocp-method__row"><span>A nombre de</span><strong>Valento</strong></div>
                                </div>
                            </div>

                            <div class="ocp-method ocp-method--bcp">
                                <div class="ocp-method__header">
                                    <i class="fas fa-university"></i> BCP
                                </div>
                                <div class="ocp-method__rows">
                                    <div class="ocp-method__row"><span>Cta. Soles</span><strong>192-7212444-068</strong></div>
                                    <div class="ocp-method__row"><span>CCI</span><strong>002-192-007212444-0-68-35</strong></div>
                                    <div class="ocp-method__row"><span>RUC</span><strong>20609730821</strong></div>
                                </div>
                            </div>

                            <div class="ocp-method ocp-method--interbank">
                                <div class="ocp-method__header">
                                    <i class="fas fa-university"></i> Interbank
                                </div>
                                <div class="ocp-method__rows">
                                    <div class="ocp-method__row"><span>Cta. Soles</span><strong>200-3007073818</strong></div>
                                    <div class="ocp-method__row"><span>RUC</span><strong>20609730821</strong></div>
                                </div>
                            </div>

                            <div class="ocp-method ocp-method--bbva">
                                <div class="ocp-method__header">
                                    <i class="fas fa-university"></i> BBVA
                                </div>
                                <div class="ocp-method__rows">
                                    <div class="ocp-method__row"><span>Cta. Soles</span><strong>0011-0214-0100009173</strong></div>
                                    <div class="ocp-method__row"><span>RUC</span><strong>20609730821</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Acciones --}}
                <div class="ocp-actions">
                    <a href="/web_contact" class="ocp-btn ocp-btn--primary">
                        <i class="fas fa-headset"></i> Contactar Soporte
                    </a>
                    <a href="/" class="ocp-btn ocp-btn--secondary">
                        <i class="fas fa-home"></i> Ir al Inicio
                    </a>
                    <a href="/web_shopDetail_lista" class="ocp-btn ocp-btn--outline">
                        <i class="fas fa-shopping-cart"></i> Seguir Comprando
                    </a>
                </div>

                {{-- CTA Registro --}}
                <div class="ocp-cta">
                    <div class="ocp-cta__icon"><i class="fas fa-user-plus"></i></div>
                    <div class="ocp-cta__body">
                        <strong>¿Aún no tienes cuenta?</strong>
                        <p>Guarda tus datos para compras más rápidas</p>
                        <a href="/registration" class="ocp-cta__btn">
                            Crear Cuenta Gratis <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>{{-- /ocp-right --}}
        </div>{{-- /ocp-grid --}}
    </div>
</div>
@endsection


@section('footer_page')
<script>
    toastr.success("Su pedido ya se encuentra en proceso!");
</script>
@endsection
