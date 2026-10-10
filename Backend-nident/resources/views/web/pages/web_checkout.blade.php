@extends('web.base_shop')

@section('head_page')
@vite(['resources/sass/web_checkout.scss'])
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* ── Izipay Modal ─────────────────────────────────── */
.izipay-swal-popup {
    border-radius: 14px !important;
    padding: 0 !important;
    width: min(380px, 95vw) !important;
    max-height: 96vh !important;
    box-sizing: border-box !important;
    background: #e8e8e8 !important;
    overflow: hidden !important;
}
.izipay-swal-popup .swal2-title { display: none !important; }
.izipay-swal-popup .swal2-html-container {
    margin: 0 !important;
    padding: 0 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    display: block !important;
    width: 100% !important;
    max-height: 96vh !important;
}
.izipay-swal-popup .swal2-close {
    top: 10px !important;
    right: 10px !important;
    width: 26px !important;
    height: 26px !important;
    border-radius: 50% !important;
    background: rgba(0,0,0,.28) !important;
    color: #fff !important;
    border: none !important;
    font-size: 1.1rem !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 9999 !important;
}
/* ── Header branded del modal Izipay ── */
.izipay-modal-wrap { display: flex; flex-direction: column; width: 100%; }
.izipay-modal-header {
    background: #e8e8e8;
    text-align: center;
    padding: 28px 20px 18px;
    border-bottom: 1px solid #d0d0d0;
}
.izipay-cart-circle {
    width: 72px;
    height: 72px;
    background: #3EC9C6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    box-shadow: 0 4px 18px rgba(62,201,198,.40);
}
.izipay-cart-circle i { color: #fff; font-size: 28px; }
.izipay-merchant-name {
    font-size: 14px;
    font-weight: 700;
    color: #222;
    line-height: 1.4;
    letter-spacing: .01em;
}
.izipay-merchant-name small {
    display: block;
    font-size: 11px;
    font-weight: 400;
    color: #666;
    margin-top: 2px;
}
#izipay-iframe {
    width: 100% !important;
    min-height: 430px !important;
    border: none !important;
    background: #e8e8e8 !important;
    display: block !important;
}
@media (max-width: 420px) {
    .izipay-swal-popup { width: 96vw !important; border-radius: 10px !important; }
    .izipay-modal-header { padding: 22px 14px 14px; }
    .izipay-cart-circle { width: 60px; height: 60px; }
    .izipay-cart-circle i { font-size: 22px; }
}

/* ── Badge Yape en Resumen del Pedido ── */
.product-thumb { position: relative; }
.yape-thumb-badge {
    position: absolute;
    bottom: 0px; left: 50%;
    transform: translateX(-50%);
    background: #6c2bd9;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .3px;
    padding: 2px 5px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(108,43,217,.35);
}
.yape-price-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    font-weight: 700;
    color: #6c2bd9;
    background: rgba(108,43,217,.08);
    border: 1px solid rgba(108,43,217,.25);
    border-radius: 4px;
    padding: 1px 5px;
    margin-top: 2px;
}
.product-price {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 3px;
    white-space: nowrap;
}
</style>
<link rel="stylesheet" href="{{ asset('temp02/css/checkout.css') }}">
@endsection


@section('content')
@include('web.partials.liam.header')
<style>
/* ── Select2 visibilidad en co-layout ── */
.shipping-address-section, .shipping-address-section .co-form-group { overflow: visible; }
.select2-container--open { z-index: 9999; }
.select2-dropdown { z-index: 9999; }
.co-select-wrap .select2-container { width: 100% !important; }
.co-select-wrap .select2-selection--single {
    border: 1.5px solid #ddd !important;
    border-radius: 6px !important;
    height: 42px !important;
    background: #fff !important;
}
.co-select-wrap .select2-selection--single .select2-selection__rendered {
    line-height: 40px !important;
    padding-left: 14px !important;
    padding-right: 28px !important;
    font-family: 'Montserrat', sans-serif !important;
    font-size: 13px !important;
    color: #333 !important;
}
.co-select-wrap .select2-selection--single .select2-selection__arrow { display: none !important; }
.co-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: none !important;
}
.co-select:disabled {
    appearance: none !important;
    -webkit-appearance: none !important;
    opacity: 1;
    background: #fff;
    color: #333;
}
/* ── Método pago: opción radio estilo template ── */
.co-pay-option {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 16px;
    border: 1.5px solid #ddd; border-radius: 4px;
    cursor: pointer; margin-bottom: 10px;
    font-family: 'Montserrat', sans-serif; font-size: 13px; color: #333;
    transition: border-color .2s, background .2s;
}
.co-pay-option input[type="radio"] { accent-color: #8C6B2E; flex-shrink: 0; }
.co-pay-option:has(input:checked) { border-color: #8C6B2E; background: rgba(140, 107, 46, .05); }
.co-pay-option i { color: #8C6B2E; font-size: 15px; width: 18px; text-align: center; }
/* ── Monto a pagar ── */
#divMontoPagar { margin-top: 12px; }
/* ── Co-aside responsive sticky ── */
.co-aside { position: sticky; top: 100px; }
/* ── Vuelto feedback ── */
#del_resultado_vuelto { margin-top: 6px; font-size: 12px; font-family: 'Montserrat',sans-serif; }
/* ── Compra bloqueada por el mínimo de delivery ── */
.co-submit-btn.is-bloqueado, .co-submit-btn.is-bloqueado:hover { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; }
/* ── Pedido por WhatsApp ── */
.co-section__icon--wa, .co-card:hover .co-section__icon--wa { background: #25D366; font-size: 18px; }
.co-textarea { min-height: 96px; resize: vertical; line-height: 1.5; padding-top: 10px; }
.co-submit-btn--wa { background: #25D366; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3); }
.co-submit-btn--wa .fa { font-size: 17px; vertical-align: -1px; }
.co-submit-btn--wa:hover { background: #1da851; box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4); }
.co-submit-btn--wa:disabled { opacity: .7; cursor: wait; transform: none; }
.co-wa-secure { margin: 10px 0 0; text-align: center; font-family: 'Montserrat',sans-serif; font-size: 11px; color: #888; line-height: 1.5; }
.co-wa-secure .fa { color: #25D366; }
.co-wa-info {
    margin-top: 14px; padding: 12px 14px; text-align: center;
    background: rgba(37, 211, 102, .06); border: 1px solid rgba(37, 211, 102, .25); border-radius: 8px;
}
.co-wa-info .fa { font-size: 24px; color: #25D366; display: block; margin-bottom: 6px; }
.co-wa-info p { margin: 0; font-family: 'Montserrat',sans-serif; font-size: 12px; font-weight: 600; color: #555; line-height: 1.5; }
</style>

@php
$total = Cart::total();
$subtotal = $total / 1.18;
$igv = $subtotal * 0.18;
$subtotal = is_numeric($subtotal) ? $subtotal : 0;
$igv = is_numeric($igv) ? $igv : 0;
$totalCarrito = floatval(str_replace(',', '', Cart::total()));
// Pasarela "WhatsApp" activa en el admin: sin pago en línea, el pedido se envía por WhatsApp
$esPedidoWhatsapp = ($pasarela_activa ?? 'culqi') === 'whatsapp';
$delCfg = \App\Models\DelConfiguracionDelivery::obtenerActivo();
$delHabilitado = $delCfg->deliveryHabilitado();
// Reglas del módulo Delivery en texto para el cliente (solo las que aplican)
$delMin = (float) $delCfg->monto_minimo_compra;
$delDesdeMin = $delMin > 0 ? ' desde S/ ' . number_format($delMin, 0) : '';
$delReglas = [];
if ($delHabilitado) {
    if ((int) $delCfg->radio_metros_gratis > 0) {
        $delReglas[] = '<strong>GRATIS</strong> a menos de ' . (int) $delCfg->radio_metros_gratis . ' m de la tienda'
            . ((float) $delCfg->monto_minimo_radio > 0 ? ' (compras desde S/ ' . number_format($delCfg->monto_minimo_radio, 0) . ')' : '');
    }
    if (!empty($delCfg->distritos_gratis)) {
        $delReglas[] = '<strong>GRATIS</strong> en ' . e(implode(' · ', $delCfg->distritos_gratis)) . $delDesdeMin;
    }
    if (!empty($delCfg->distritos_especiales)) {
        $delReglas[] = '<strong>S/ ' . number_format($delCfg->costo_distritos_especiales, 2) . '</strong> — '
            . e(implode(' · ', $delCfg->distritos_especiales))
            . ($delCfg->distritos_especiales_sin_minimo ? '' : $delDesdeMin);
    }
    $delReglas[] = '<strong>S/ ' . number_format($delCfg->costo_otros_distritos, 2) . '</strong> — otros distritos' . $delDesdeMin;
    if ($delMin > 0) {
        $delReglas[] = $delCfg->bloquear_bajo_minimo
            ? 'Compra mínima de <strong>S/ ' . number_format($delMin, 0) . '</strong> para delivery'
            : '<strong>S/ ' . number_format($delCfg->costo_envio_baja_compra ?? 5, 2) . '</strong> si tu compra es menor a S/ ' . number_format($delMin, 0);
    }
}
@endphp

<main class="co-page">
<div class="container">

    <h1 class="co-page__title">
        <i class="fa fa-shopping-cart"></i> Finalizar la compra
    </h1>

    <form id="saveService" novalidate>
    @csrf
    <input type="hidden" id="txt_hora_regresiva_descripcion" name="txt_hora_regresiva_descripcion" value="">

    <div class="co-layout">

        {{-- Columna 1: formulario completo (Identificación → Envío → Pago) --}}
        <div class="co-form-sheet">

        {{-- ══ BLOQUE 1: Identificación ══ --}}
        <div class="co-block co-block--ident">
            <div class="co-card co-section">
                <div class="co-section__head">
                    <div class="co-section__icon"><i class="fa fa-user"></i></div>
                    <h2 class="co-section__title">Identificación</h2>
                </div>
                <p class="co-section__subtitle">
                    Solicitamos únicamente la información esencial para la finalización de la compra.
                </p>

                {{-- Correo --}}
                <div class="co-form-row">
                    <div class="co-form-group full">
                        <label class="co-label" for="txt_email">Correo<span>*</span></label>
                        <div class="co-input-wrap">
                            <input type="email" id="txt_email" name="txt_email"
                                   class="co-input" placeholder="cliente@ejemplo.com"
                                   maxlength="120" autocomplete="email" required>
                            <i class="fa fa-check co-valid-icon"></i>
                        </div>
                        <span class="co-error-msg">Este campo es obligatorio.</span>
                    </div>
                </div>

                {{-- Nombre + Apellido --}}
                <div class="co-form-row">
                    <div class="co-form-group">
                        <label class="co-label" for="txt_nombre">Nombre<span>*</span></label>
                        <input type="text" id="txt_nombre" name="txt_nombre"
                               class="co-input" maxlength="80" autocomplete="given-name" required>
                        <span class="co-error-msg">Este campo es obligatorio.</span>
                    </div>
                    <div class="co-form-group">
                        <label class="co-label" for="txt_apellido">Apellidos<span>*</span></label>
                        <input type="text" id="txt_apellido" name="txt_apellido"
                               class="co-input" maxlength="80" autocomplete="family-name" required>
                        <span class="co-error-msg">Este campo es obligatorio.</span>
                    </div>
                </div>

                {{-- Tipo doc + Documento --}}
                <div class="co-form-row">
                    <div class="co-form-group">
                        <label class="co-label" for="txt_tipo_documento">Tipo del documento<span>*</span></label>
                        <div class="co-select-wrap">
                            <select id="txt_tipo_documento" name="txt_tipo_documento" class="co-select">
                                <option value="DNI" selected>DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carnet de Extranjería</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="co-form-group">
                        <label class="co-label" for="txt_dni">Documento<span>*</span></label>
                        <input type="text" id="txt_dni" name="txt_dni"
                               class="co-input" inputmode="numeric"
                               minlength="8" maxlength="12" autocomplete="off" required>
                        <span class="co-error-msg">Este campo es obligatorio.</span>
                    </div>
                </div>

                {{-- Teléfono --}}
                <div class="co-form-row">
                    <div class="co-form-group full">
                        <label class="co-label" for="txt_telefono">Teléfono / Móvil<span>*</span></label>
                        <input type="tel" id="txt_telefono" name="txt_telefono"
                               class="co-input" placeholder="9XXXXXXXX"
                               maxlength="16" autocomplete="tel" required>
                        <span class="co-error-msg">Este campo es obligatorio.</span>
                    </div>
                </div>

            </div>{{-- /.co-card --}}
        </div>

        {{-- ══ BLOQUE 2: Envío ══ --}}
        <div class="co-block co-block--ship">
            <div class="co-card co-section shipping-address-section">
                <div class="co-section__head">
                    <div class="co-section__icon"><i class="fa fa-home"></i></div>
                    <h2 class="co-section__title">Envío</h2>
                </div>
                <p class="co-section__subtitle">¿Dónde quieres recibir tu pedido?</p>

                <div class="co-form-row">
                    <div class="co-form-group full">
                        <label class="co-label" for="txt_direccion">Dirección completa<span>*</span></label>
                        <input type="text" id="txt_direccion" name="txt_direccion"
                               class="co-input" placeholder="Av. Principal 123, Dpto 456"
                               autocomplete="street-address" required>
                        <span class="co-error-msg">Este campo es obligatorio.</span>
                        <button type="button" class="btn-share-location" id="btnShareLocation" style="margin-top:8px;">
                            <i class="fa fa-map-marker"></i>
                            <span id="btnShareLocationText">Compartir mi ubicación</span>
                            <div class="spinner-border spinner-border-sm d-none" id="locationSpinner" role="status"></div>
                        </button>
                    </div>
                </div>

                <div class="co-form-row">
                    <div class="co-form-group">
                        <label class="co-label" for="txt_listar_departamentos">Departamento<span>*</span></label>
                        <div class="co-select-wrap">
                            <select class="co-select select2"
                                    id="txt_listar_departamentos"
                                    name="txt_listar_departamentos"
                                    autocomplete="new-password" required>
                                <option value="" disabled>Seleccione departamento...</option>
                                @foreach($listarDepartamentos_simple as $department)
                                @php $depName = trim((string) ($department->name ?? '')); @endphp
                                <option value="{{ $depName }}"
                                        data-id="{{ $department->id }}"
                                        {{ strcasecmp($depName, 'Lima') === 0 ? 'selected' : '' }}>
                                    {{ $depName }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @if($utimoMayorPeso)
                        <input type="hidden" name="txt_peso_kilogramo"    value="{{ $utimoMayorPeso->peso_kilogramo ?? 0 }}">
                        <input type="hidden" name="txt_paqueta_medidas"   value="{{ $utimoMayorPeso->paquete_medidas ?? 0 }}">
                        <input type="hidden" name="txt_paquete_dimencion" value="{{ $utimoMayorPeso->paquete_dimencion ?? 0 }}">
                        @else
                        <input type="hidden" name="txt_peso_kilogramo"    value="0">
                        <input type="hidden" name="txt_paqueta_medidas"   value="0">
                        <input type="hidden" name="txt_paquete_dimencion" value="0">
                        @endif
                    </div>
                    <div class="co-form-group">
                        <label class="co-label" for="txt_listar_provincias">Provincia<span>*</span></label>
                        <div class="co-select-wrap">
                            <select class="co-select select2"
                                    id="txt_listar_provincias"
                                    name="txt_listar_provincias"
                                    disabled required>
                                <option value="" selected disabled>Seleccione provincia...</option>
                            </select>
                        </div>
                    </div>
                    <div class="co-form-group">
                        <label class="co-label" for="txt_listar_distritos">Distrito<span>*</span></label>
                        <div class="co-select-wrap">
                            <select class="co-select select2" id="txt_listar_distritos" name="txt_listar_distritos" disabled>
                                <option value="" selected disabled>Seleccione distrito...</option>
                            </select>
                        </div>
                        <span class="co-error-msg" id="error_distrito" style="display: none; margin-top: 5px; color: #8C6B2E;">Este campo es obligatorio.</span>
                    </div>
                </div>

                <div class="mensajeLlevaloDesde" style="margin-top:10px;"></div>

                {{-- Campos ocultos delivery --}}
                <input type="hidden" id="del_lat_cliente" name="del_lat_cliente" value="">
                <input type="hidden" id="del_lng_cliente" name="del_lng_cliente" value="">
                <input type="hidden" id="del_costo_envio" name="del_costo_envio" value="0">
                <input type="hidden" id="del_razon_envio" name="del_razon_envio" value="">
            </div>{{-- /.co-card --}}
        </div>

        {{-- ══ BLOQUE 3: Comprobante + Pago ══ --}}
        <div class="co-block co-block--pay">

            <div class="co-card co-section co-section--comprobante">
                <div class="co-comprobante-wrap">
                    <label class="co-label co-comprobante__label">Tipo de comprobante *</label>
                    <div class="co-comprobante-grid">

                        <label for="co-tipo-boleta" class="co-comprobante-option is-selected" id="lbl-boleta">
                            <input type="radio" id="co-tipo-boleta" name="co_tipo_comprobante" value="B" checked>
                            <span class="co-comprobante-option__dot is-selected" id="dot-boleta">
                                <span class="co-comprobante-option__fill" id="fill-boleta" style="display:block;"></span>
                            </span>
                            <span class="co-comprobante-option__text">
                                <span class="co-comprobante-option__title"><i class="fa fa-file-text-o"></i> Boleta</span>
                                <span class="co-comprobante-option__sub">Persona natural</span>
                            </span>
                        </label>

                        <label for="co-tipo-factura" class="co-comprobante-option" id="lbl-factura">
                            <input type="radio" id="co-tipo-factura" name="co_tipo_comprobante" value="F">
                            <span class="co-comprobante-option__dot" id="dot-factura">
                                <span class="co-comprobante-option__fill" id="fill-factura"></span>
                            </span>
                            <span class="co-comprobante-option__text">
                                <span class="co-comprobante-option__title"><i class="fa fa-building-o"></i> Factura</span>
                                <span class="co-comprobante-option__sub">Empresa con RUC</span>
                            </span>
                        </label>

                    </div>
                    <span class="co-obligatorio co-comprobante__note">(*) Datos Obligatorios.</span>
                </div>

                <div id="co-ruc-wrap" class="co-ruc-wrap" style="display:none;">
                    <div class="co-form-row">
                        <div class="co-form-group full">
                            <label class="co-label" for="txt_ruc">Número de RUC *</label>
                            <input type="text" id="txt_ruc" name="txt_ruc"
                                   class="co-input" placeholder="20123456789"
                                   inputmode="numeric" maxlength="11">
                        </div>
                    </div>
                    <div class="co-form-row">
                        <div class="co-form-group full">
                            <label class="co-label" for="txt_razon_social">Razón Social *</label>
                            <input type="text" id="txt_razon_social" name="txt_razon_social"
                                   class="co-input" placeholder="Nombre de la empresa">
                        </div>
                    </div>
                </div>
                <input type="hidden" id="cb_metodo_pago" name="cb_metodo_pago" value="B">

                <div class="co-checkbox-row">
                    <input type="checkbox" id="check_privacidad_cliente" name="check_privacidad_cliente">
                    <label for="check_privacidad_cliente">
                        Autorizo el tratamiento de mis datos para fines adicionales.
                    </label>
                </div>

                <div class="co-checkbox-row">
                    <input type="checkbox" id="check_terminos" name="check_terminos" required>
                    <label for="check_terminos">
                        He leído y acepto los
                        <a href="{{ route('liam.terminos') }}" target="_blank">términos y condiciones</a>
                        y
                        <a href="{{ route('liam.privacidad') }}" target="_blank">las Políticas de Privacidad.</a><span class="co-required">*</span>
                    </label>
                </div>
                <span class="co-error-msg" id="co-terms-error">Debe aceptar los términos y condiciones.</span>
            </div>

            @if($esPedidoWhatsapp)
            {{-- Pedido por WhatsApp: el pago se coordina con el asesor --}}
            <div class="co-card co-section">
                <div class="co-section__head">
                    <div class="co-section__icon co-section__icon--wa"><i class="fa fa-whatsapp"></i></div>
                    <h2 class="co-section__title">Detalles del pedido</h2>
                </div>
                <p class="co-section__subtitle">Tu pedido{{ $delHabilitado ? ' (productos + delivery)' : '' }} se enviará a nuestro asesor de ventas por WhatsApp para coordinar el pago y la entrega.</p>
                <div class="co-form-row" style="margin-top:0;">
                    <div class="co-form-group full">
                        <label class="co-label" for="txt_observaciones">Observaciones / Notas adicionales</label>
                        <textarea id="txt_observaciones" name="txt_observaciones" class="co-input co-textarea" rows="4" maxlength="1000"
                                  placeholder="Especifica tallas, colores, personalización con logo u otros detalles importantes para tu pedido..."></textarea>
                    </div>
                </div>
            </div>
            @else
            {{-- Tipo de Pago Yape (si aplica) --}}
            @if($yapeDisponible)
            <div class="co-card co-section">
                <div class="co-section__head">
                    <div class="co-section__icon"><i class="fa fa-tag"></i></div>
                    <h2 class="co-section__title">Tipo de Pago</h2>
                </div>
                <p class="co-section__subtitle">Solo aplica para productos con precio Yape.</p>
                <input type="hidden" id="tipo_pago_seleccionado" name="tipo_pago_seleccionado" value="normal">
                <label class="co-pay-option">
                    <input type="radio" name="radio_tipo_pago" id="rd_tipo_yape" value="yape">
                    <i class="fa fa-mobile"></i> <span>Yape — precio con descuento</span>
                </label>
                <label class="co-pay-option">
                    <input type="radio" name="radio_tipo_pago" id="rd_tipo_normal" value="normal" checked>
                    <i class="fa fa-credit-card"></i> <span>Normal</span>
                </label>
            </div>
            @else
            <input type="hidden" name="tipo_pago_seleccionado" value="normal">
            @endif

            {{-- Método de Pago --}}
            <div class="co-card co-section">
                <div class="co-section__head">
                    <div class="co-section__icon"><i class="fa fa-credit-card"></i></div>
                    <h2 class="co-section__title">Método de Pago</h2>
                </div>
                <p class="co-section__subtitle">Selecciona cómo quieres pagar.</p>

                <label class="co-pay-option">
                    <input type="radio" name="radio_metodo_pago" id="rd_pago_credito" value="1" checked required>
                    <i class="fa fa-credit-card"></i>
                    <span>
                        @if(($pasarela_activa ?? 'culqi') === 'paypal') PayPal (Tarjetas / Saldo)
                        @elseif($pasarela_activa === 'izipay') Pago con Tarjeta
                        @elseif($pasarela_activa === 'stripe') Stripe (Tarjeta / Saldo)
                        @else Tarjeta / Yape / Plin
                        @endif
                        <small style="display:block;color:#999;font-size:11px;margin-top:2px;">Pago seguro en línea</small>
                    </span>
                </label>

                <label class="co-pay-option">
                    <input type="radio" name="radio_metodo_pago" id="rd_pago_contra_entrega" value="2" required>
                    <i class="fa fa-money"></i>
                    <span>Pago contra entrega
                        <small style="display:block;color:#999;font-size:11px;margin-top:2px;">Paga cuando recibas tu pedido</small>
                    </span>
                </label>

                {{-- Monto a pagar (contra entrega) --}}
                <div id="divMontoPagar" style="display:none;">
                    <div class="co-form-row" style="margin-top:0;">
                        <div class="co-form-group full">
                            <label class="co-label" for="del_monto_pagar">
                                ¿Con cuánto va a pagar?<span>*</span>
                                <small style="font-weight:normal;color:#999;"> — Para su vuelto</small>
                            </label>
                            <input type="number" id="del_monto_pagar" name="del_monto_pagar"
                                   class="co-input" placeholder="Ej: 150.00" min="0" step="0.50">
                            <div id="del_resultado_vuelto" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /.co-block--pay --}}

        </div>{{-- /.co-form-sheet --}}

        {{-- Columna 2: resumen --}}
        <div class="co-block co-block--aside co-aside">
            <p class="co-aside__title">Resumen de la Compra</p>

            <div class="co-aside__items" id="co-items">
                @foreach($cartItemsListaCheckout as $cartItem)
                @php
                    $idProd         = $cartItem->options->id_producto ?? null;
                    $tieneYape      = $idProd && isset($preciosYape[$idProd]);
                    $precioYapeItem = $tieneYape ? $preciosYape[$idProd] : null;
                    $imgSrc = $cartItem->options->img ?? '';
                    if ($imgSrc && !preg_match('/^https?:\/\//', $imgSrc)) {
                        $imgSrc = url('/') . '/' . ltrim($imgSrc, '/');
                    }
                @endphp
                <div class="co-aside__item">
                    <div class="product-thumb" style="position:relative;flex-shrink:0;">
                        <img class="co-aside__item-img"
                             src="{{ $imgSrc }}"
                             alt="{{ $cartItem->name }}"
                             onerror="this.style.background='#f0f0f0';this.src='';">
                        @if($tieneYape)
                        <span class="yape-thumb-badge">Yape</span>
                        @endif
                    </div>
                    <div class="co-aside__item-info">
                        <p class="co-aside__item-name">{{ Str::limit($cartItem->name, 22) }}</p>
                        <p class="co-aside__item-cant" style="color:#888;font-size:11px;">Cant. {{ $cartItem->qty }}</p>
                        @if($tieneYape)
                        <span class="yape-price-tag">Yape S/ {{ number_format($precioYapeItem, 2) }}</span>
                        @endif
                    </div>
                    <span class="co-aside__item-price">S/ {{ number_format($cartItem->price * $cartItem->qty, 2) }}</span>
                </div>
                @endforeach
            </div>

            <div class="co-back-cart">
                <a href="{{ url('/web_shopDetail_lista') }}">
                    <i class="fa fa-arrow-left"></i> Volver al carrito
                </a>
            </div>

            <div class="co-aside__row">
                <span class="label">Subtotal</span>
                <span class="value">S/ {{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="co-aside__row">
                <span class="label">IGV (18%)</span>
                <span class="value">S/ {{ number_format($igv, 2) }}</span>
            </div>
            <div class="co-aside__row">
                <span class="label"><i class="fa fa-truck" style="color:#8C6B2E;"></i> Costo de envío</span>
                <span class="value clsCostoEnvio" style="font-style:italic;font-weight:500;color:#999;">Elige tu distrito</span>
            </div>

            {{-- Envío gratis real según el módulo Delivery (o aviso si no hay delivery) --}}
            @if(!$delHabilitado)
            <div style="padding:8px 0;font-family:'Montserrat',sans-serif;font-size:11px;color:#888;">
                <i class="fa fa-info-circle" style="color:#8C6B2E;"></i> Sin delivery por ahora: coordinaremos la entrega o el recojo contigo.
            </div>
            @elseif((int) $delCfg->radio_metros_gratis > 0 || !empty($delCfg->distritos_gratis))
            @php
                $delGratisPartes = [];
                if ((int) $delCfg->radio_metros_gratis > 0) {
                    $delGratisPartes[] = 'a menos de ' . (int) $delCfg->radio_metros_gratis . ' m de nuestra tienda (comparte tu ubicación)';
                }
                if (!empty($delCfg->distritos_gratis)) {
                    $delGratisPartes[] = 'en ' . implode(', ', $delCfg->distritos_gratis)
                        . ((float) $delCfg->monto_minimo_compra > 0 ? ' desde S/ ' . number_format($delCfg->monto_minimo_compra, 0) : '');
                }
            @endphp
            <div style="padding:8px 0;font-family:'Montserrat',sans-serif;font-size:11px;color:#888;">
                <i class="fa fa-gift" style="color:#8C6B2E;"></i>
                Envío <strong style="color:#8C6B2E;">gratis</strong> {{ implode(' o ', $delGratisPartes) }}.
            </div>
            @endif

            <div class="co-aside__row total">
                <span class="label">Total</span>
                <span class="value" style="color:#8C6B2E;">S/ <span id="total-price">{{ number_format($total, 2) }}</span></span>
            </div>

            <!-- <p class="co-aside__note">
                <strong>IMPORTANTE:</strong> Asegúrese que su carrito incluye
                TERNO + CAMISA + CORBATA en la promoción de ARMA TU PACK
            </p> -->

            @if($esPedidoWhatsapp)
            <button type="submit" class="co-submit-btn co-submit-btn--wa">
                <i class="fa fa-whatsapp"></i> &nbsp;Enviar pedido por WhatsApp
            </button>
            <p class="co-wa-secure"><i class="fa fa-lock"></i> Tu información es segura. El pedido se enviará directamente a nuestro WhatsApp de ventas.</p>

            <div class="co-wa-info">
                <i class="fa fa-whatsapp"></i>
                <p>
                    @if($delHabilitado)
                        El total ya incluye el delivery de tu distrito.
                    @else
                        Sin delivery por ahora: la entrega o el recojo se coordina por WhatsApp.
                    @endif
                    No se realiza ningún cobro en línea: pagas al coordinar con nuestro asesor por WhatsApp, quien también te confirmará el stock.
                </p>
            </div>
            @else
            <button type="submit" class="co-submit-btn"
                    style="{{ (($pasarela_activa ?? 'culqi') === 'paypal') ? 'display:none;' : '' }}">
                <i class="fa fa-lock"></i> &nbsp;Confirmar Pedido
            </button>

            @if(($pasarela_activa ?? 'culqi') === 'paypal')
            <div id="paypal-button-container" style="margin-top:14px;display:block;"></div>
            @endif
            @endif

            <div class="co-aside__note" style="margin-top:14px;display:flex;justify-content:space-around;text-align:center;gap:8px;">
                <span style="font-size:10px;color:#aaa;"><i class="fa fa-shield" style="font-size:18px;display:block;color:#8C6B2E;margin-bottom:4px;"></i> Compra Segura</span>
                <span style="font-size:10px;color:#aaa;"><i class="fa fa-undo" style="font-size:18px;display:block;color:#8C6B2E;margin-bottom:4px;"></i> Devolución Fácil</span>
                <span style="font-size:10px;color:#aaa;"><i class="fa fa-truck" style="font-size:18px;display:block;color:#8C6B2E;margin-bottom:4px;"></i> Envío Rápido</span>
            </div>
        </div>

    </div>{{-- fin co-layout --}}
    </form>

</div>
</main>

<script>
(function () {
    var boleta  = document.getElementById('co-tipo-boleta');
    var factura = document.getElementById('co-tipo-factura');
    var lblB    = document.getElementById('lbl-boleta');
    var lblF    = document.getElementById('lbl-factura');
    var dotB    = document.getElementById('dot-boleta');
    var dotF    = document.getElementById('dot-factura');
    var fillB   = document.getElementById('fill-boleta');
    var fillF   = document.getElementById('fill-factura');
    var rucWrap = document.getElementById('co-ruc-wrap');
    var hidden  = document.getElementById('cb_metodo_pago');

    function updateUI(tipo) {
        var isBoleta = tipo === 'B';
        lblB.classList.toggle('is-selected', isBoleta);
        lblF.classList.toggle('is-selected', !isBoleta);
        dotB.classList.toggle('is-selected', isBoleta);
        dotF.classList.toggle('is-selected', !isBoleta);
        fillB.style.display = isBoleta  ? 'block' : 'none';
        fillF.style.display = !isBoleta ? 'block' : 'none';
        rucWrap.style.display = isBoleta ? 'none' : '';
        hidden.value = tipo;
        boleta.checked  = isBoleta;
        factura.checked = !isBoleta;
    }

    /* Estado inicial: boleta seleccionada */
    updateUI('B');

    lblB.addEventListener('click', function () { updateUI('B'); });
    lblF.addEventListener('click', function () { updateUI('F'); });
})();
</script>

@include('web.partials.liam.footer')
@endsection


@section('footer_page')
@vite(['resources/js/web_checkout.js'])
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@if(($pasarela_activa ?? 'culqi') === 'culqi')
<script src="https://checkout.culqi.com/js/v4"></script>
@endif
@if(($pasarela_activa ?? 'culqi') === 'paypal')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypal_client_id }}&currency=USD"></script>
@endif

@if(($pasarela_activa ?? 'culqi') === 'stripe')
<script src="https://js.stripe.com/v3/"></script>
@endif
 
<script>
    // El script de Culqi (checkout.culqi.com/js/v4) deja un `$` global que no es jQuery: usar jQuery explícito.
    var pasarelaActiva = @json($pasarela_activa ?? 'culqi');

    // ── Globals delivery 4 casos ───────────────────────────────────────────
    window.DEL_CHECKOUT_CALCULADO = false;
    window._checkoutTotalCarrito  = @json($totalCarrito);
    // Reglas del módulo Delivery (admin) o aviso de "sin delivery"
    window.DEL_HABILITADO = @json($delHabilitado);
    window.DEL_HINT_CHECKOUT = @if($delHabilitado)
        '<div style="margin-top:8px;padding:8px 10px;background:#f0f9ff;border:1px solid #bae6fd;'
        + 'border-radius:8px;font-size:11.5px;color:#0369a1;line-height:1.6;">'
        + '<div style="font-weight:600;margin-bottom:3px;"><i class="fa fa-info-circle"></i> Costos de envío:</div>'
        + @json(implode('', array_map(fn ($r) => '<div>• ' . $r . '</div>', $delReglas)))
        + '<div style="font-style:italic;opacity:0.8;">Selecciona tu distrito para calcular el costo exacto.</div>'
        + '</div>';
    @else
        '<div style="margin-top:8px;padding:10px 14px;background:#fbf7ef;border:1px solid #8C6B2E;border-left-width:3px;'
        + 'border-radius:4px;font-size:12.5px;color:#374151;line-height:1.6;">'
        + '<div style="font-weight:600;"><i class="fa fa-info-circle" style="color:#8C6B2E"></i> ' + @json($delCfg->mensajeSinDelivery()) + '</div>'
        + '</div>';
    @endif

    // ── Tipo de Pago Yape ──────────────────────────────────────────────────
    var yapeDisponible  = @json($yapeDisponible ?? false);
    var totalBaseNormal = @json($totalNormal ?? 0);
    var totalBaseYape   = @json($totalYape ?? 0);

    /** Devuelve el monto correcto en CENTAVOS según selección Yape/Normal */
    function obtenerMontoActual() {
        if (yapeDisponible) {
            var rdYape = document.getElementById('rd_tipo_yape');
            if (rdYape && rdYape.checked) {
                return Math.round(totalBaseYape * 100);
            }
        }
        return Math.round(totalBaseNormal * 100);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // ── Mostrar hint de delivery al cargar ────────────────────────────
        if (!window.DEL_CHECKOUT_CALCULADO && window.DEL_HINT_CHECKOUT) {
            var msgDiv = document.querySelector('.mensajeLlevaloDesde');
            if (msgDiv) msgDiv.innerHTML = window.DEL_HINT_CHECKOUT;
        }

        // ── Lógica ¿Cuánto vas a pagar? ───────────────────────────────────
        function toggleMontoPagar() {
            var metodoPago = document.querySelector('input[name="radio_metodo_pago"]:checked');
            var divMonto   = document.getElementById('divMontoPagar');
            var inputMonto = document.getElementById('del_monto_pagar');
            if (!divMonto || !inputMonto) return;
            if (metodoPago && metodoPago.value == '2') {
                divMonto.style.display = '';
            } else {
                divMonto.style.display = 'none';
                inputMonto.value = '';
                var vueltoDiv = document.getElementById('del_resultado_vuelto');
                if (vueltoDiv) vueltoDiv.style.display = 'none';
            }
        }

        document.querySelectorAll('input[name="radio_metodo_pago"]').forEach(function(radio) {
            radio.addEventListener('change', toggleMontoPagar);
        });
        toggleMontoPagar(); // estado inicial

        // ── Cálculo de vuelto en tiempo real ──────────────────────────────
        var inputMontoPagar = document.getElementById('del_monto_pagar');
        if (inputMontoPagar) {
            inputMontoPagar.addEventListener('input', function() {
                var totalEl      = document.getElementById('total-price');
                var totalConEnvio = totalEl ? (parseFloat(totalEl.textContent.replace(/,/g, '')) || 0) : 0;
                var montoPagado  = parseFloat(this.value) || 0;
                var vueltoDiv    = document.getElementById('del_resultado_vuelto');
                if (!vueltoDiv) return;
                if (montoPagado > 0) {
                    if (montoPagado < totalConEnvio) {
                        vueltoDiv.innerHTML = '<span style="color:#dc2626;font-weight:500;">'
                            + '<i class="fa fa-exclamation-circle"></i> Monto insuficiente. '
                            + 'Total a pagar: S/ ' + totalConEnvio.toFixed(2) + '</span>';
                    } else {
                        var vuelto = montoPagado - totalConEnvio;
                        vueltoDiv.innerHTML = '<span style="color:#16a34a;font-weight:500;">'
                            + '<i class="fa fa-check-circle"></i> Vuelto estimado: S/ ' + vuelto.toFixed(2) + '</span>';
                    }
                    vueltoDiv.style.display = '';
                } else {
                    vueltoDiv.style.display = 'none';
                }
            });
        }

        if (!yapeDisponible) return;

        document.querySelectorAll('input[name="radio_tipo_pago"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var tipoPago = this.value;
                var nuevoBase = (tipoPago === 'yape') ? totalBaseYape : totalBaseNormal;

                // Actualizar hidden input
                var hidden = document.getElementById('tipo_pago_seleccionado');
                if (hidden) hidden.value = tipoPago;

                // Actualizar total mostrado (respetando el envío ya calculado)
                var totalEl = document.getElementById('total-price');
                if (totalEl) {
                    var envio = parseFloat(window._precioEnvioActual) || 0;
                    totalEl.textContent = (nuevoBase + envio).toFixed(2);
                }

                // Actualizar totalBase interno del checkout.js
                window._totalBaseYapeNormal = nuevoBase;
            });
        });
    });
    // ──────────────────────────────────────────────────────────────────────

    //=============================== VALIDACIONES ==============================

    // Función para mostrar alertas de campos obligatorios
    function showRequiredAlerts() {
        let missingFields = [];
        const unwantedTexts = ['q', 'Search', 'I agree to the', 'privacy policy'];

        // Verificar campos de texto obligatorios
        const requiredInputs = document.querySelectorAll('input[required], select[required]');
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                const label = document.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    let labelText = label.innerText.replace(/\*/g, '').trim();
                    unwantedTexts.forEach(text => {
                        labelText = labelText.replace(text, '').trim();
                    });
                    if (labelText) missingFields.push(labelText);
                } else if (!unwantedTexts.includes(input.name)) {
                    missingFields.push(input.name);
                }
            }
        });

        // Verificar checkboxes obligatorios
        const requiredCheckboxes = document.querySelectorAll('input[type="checkbox"][required]');
        requiredCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                const label = document.querySelector(`label[for="${checkbox.id}"]`);
                if (label) {
                    if (checkbox.id === 'check_terminos') {
                        missingFields.push('Acepto los términos y condiciones');
                    } else {
                        let labelText = label.innerText.replace(/\*/g, '').trim();
                        unwantedTexts.forEach(text => {
                            labelText = labelText.replace(text, '').trim();
                        });
                        if (labelText) missingFields.push(labelText);
                    }
                } else if (!unwantedTexts.includes(checkbox.name)) {
                    missingFields.push(checkbox.name);
                }
            }
        });

        // Verificar radios obligatorios
        const requiredRadios = document.querySelectorAll('input[type="radio"][required]');
        if (requiredRadios.length > 0 && !document.querySelector(`input[name="${requiredRadios[0].name}"]:checked`)) {
            const cardHeader = document.querySelector(`input[name="${requiredRadios[0].name}"]`).closest('.card').querySelector('.card-header h4');
            if (cardHeader) {
                missingFields.push(cardHeader.textContent.trim());
            }
        }

        // Verificar provincia y distrito (selects dependientes)
        const provinceSelect = document.getElementById('txt_listar_provincias');
        if (provinceSelect && !provinceSelect.disabled && !provinceSelect.value) {
            missingFields.push('Provincia');
        }
        const districtSelect = document.getElementById('txt_listar_distritos');
        if (districtSelect && !districtSelect.disabled && !districtSelect.value) {
            missingFields.push('Distrito');
        }

        // Filtrar y formatear resultados finales
        missingFields = [...new Set(missingFields)]
            .filter(text => text && !unwantedTexts.includes(text))
            .map(text => `• ${text}`);

        // Mostrar alerta si faltan campos
        if (missingFields.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Campos obligatorios faltantes',
                html: `Por favor complete los siguientes campos obligatorios:<br><br>${missingFields.join('<br>')}`,
                confirmButtonText: 'Entendido',
                scrollbarPadding: false
            });

            // Desplazarse al primer campo faltante
            const firstMissing = document.querySelector('input:invalid, select:invalid, input[type="checkbox"][required]:not(:checked)');
            if (firstMissing) {
                firstMissing.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                firstMissing.focus();
                firstMissing.style.boxShadow = '0 0 0 0.25rem rgba(220,53,69,.25)';
                setTimeout(() => firstMissing.style.boxShadow = '', 2000);
            }

            return false;
        }

        return true;
    }

    // Marca cada campo del formulario con is-valid / is-invalid según su estado real
    function marcarCampos() {
        const _form = document.getElementById('saveService');
        if (!_form) return;
        _form.querySelectorAll('input, select, textarea').forEach(function(el) {
            if (el.disabled) return;
            if (el.checkValidity()) {
                el.classList.remove('is-invalid');
                if (el.value.trim() !== '' || el.type === 'checkbox' || el.type === 'radio') {
                    el.classList.add('is-valid');
                }
            } else {
                el.classList.remove('is-valid');
                el.classList.add('is-invalid');
            }
        });
    }

    // Validación mejorada del formulario
    (function() {
        'use strict';

        const form = document.getElementById('saveService');
        const txtNombre = document.getElementById('txt_nombre');
        const txtApellido = document.getElementById('txt_apellido');
        const txtEmail = document.getElementById('txt_email');
        const txtTelefono = document.getElementById('txt_telefono');
        const txtDni = document.getElementById('txt_dni');
        const txtRuc = document.getElementById('txt_ruc');

        const regexSoloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        const regexTelefono = /^\+?\d[\d\s]{7,14}$/;
        const regexSoloNumeros = /^\d+$/;

        function validarSoloLetras(input, campoLabel) {
            if (!input) return;
            input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
            const valor = input.value.trim();
            if (!valor) {
                input.setCustomValidity('');
                return;
            }
            input.setCustomValidity(regexSoloLetras.test(valor) ? '' : `${campoLabel} solo permite letras.`);
        }

        function validarEmail(input) {
            if (!input) return;
            const valor = input.value.trim();
            if (!valor) {
                input.setCustomValidity('');
                return;
            }
            input.setCustomValidity(regexEmail.test(valor) ? '' : 'Ingrese un correo electrónico válido.');
        }

        function validarTelefono(input) {
            if (!input) return;
            // Permite solo dígitos, espacios y "+" al inicio.
            let valor = input.value.replace(/[^\d+\s]/g, '');
            if (valor.includes('+')) {
                valor = (valor.startsWith('+') ? '+' : '') + valor.replace(/\+/g, '');
            }
            valor = valor.replace(/\s{2,}/g, ' ').trimStart();
            input.value = valor;

            const soloDigitos = valor.replace(/[^\d]/g, '');
            if (!valor) {
                input.setCustomValidity('');
                return;
            }
            if (soloDigitos.length < 9 || soloDigitos.length > 12 || !regexTelefono.test(valor)) {
                input.setCustomValidity('Ingrese un teléfono válido. Ejemplo: +51 970048451');
                return;
            }
            input.setCustomValidity('');
        }

        function validarDocumentoNumerico(input, minLen, maxLen, campoLabel, obligatorio = true) {
            if (!input) return;
            input.value = input.value.replace(/\D/g, '');
            const valor = input.value.trim();

            if (!valor) {
                input.setCustomValidity(obligatorio ? '' : '');
                return;
            }

            if (!regexSoloNumeros.test(valor) || valor.length < minLen || valor.length > maxLen) {
                input.setCustomValidity(`${campoLabel} debe contener solo números (${minLen}-${maxLen} dígitos).`);
                return;
            }

            input.setCustomValidity('');
        }

        if (form) {
            form.addEventListener('submit', function(event) {
                // Validar distrito manualmente
                const districtSelect = document.getElementById('txt_listar_distritos');
                if (districtSelect && !districtSelect.disabled && !districtSelect.value) {
                    districtSelect.setCustomValidity('Por favor seleccione un distrito.');
                } else if (districtSelect) {
                    districtSelect.setCustomValidity('');
                }

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    showRequiredAlerts();
                }

                marcarCampos();
            }, false);

            // Validación en tiempo real para campos requeridos
            document.querySelectorAll('input[required], select[required]').forEach(el => {
                el.addEventListener('change', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
                el.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
            });

            // Validación en tiempo real para checkboxes requeridos
            document.querySelectorAll('input[type="checkbox"][required]').forEach(el => {
                el.addEventListener('change', function() {
                    const container = this.closest('.option-item');
                    if (container) {
                        if (this.checked) {
                            container.classList.remove('border-danger');
                            container.classList.add('border-success');
                        } else {
                            container.classList.remove('border-success');
                            container.classList.add('border-danger');
                        }
                    }
                });
            });

            // Validación en tiempo real para radios requeridos
            document.querySelectorAll('input[type="radio"][required]').forEach(el => {
                el.addEventListener('change', function() {
                    const groupName = this.name;
                    const card = this.closest('.card');
                    if (card) {
                        if (document.querySelector(`input[name="${groupName}"]:checked`)) {
                            card.classList.remove('border-danger');
                            card.classList.add('border-success');
                        } else {
                            card.classList.remove('border-success');
                            card.classList.add('border-danger');
                        }
                    }
                });
            });

            if (txtNombre) {
                txtNombre.addEventListener('input', () => validarSoloLetras(txtNombre, 'Nombre'));
                txtNombre.addEventListener('blur', () => validarSoloLetras(txtNombre, 'Nombre'));
            }

            if (txtApellido) {
                txtApellido.addEventListener('input', () => validarSoloLetras(txtApellido, 'Apellido'));
                txtApellido.addEventListener('blur', () => validarSoloLetras(txtApellido, 'Apellido'));
            }

            if (txtEmail) {
                txtEmail.addEventListener('input', () => validarEmail(txtEmail));
                txtEmail.addEventListener('blur', () => validarEmail(txtEmail));
            }

            if (txtTelefono) {
                txtTelefono.addEventListener('input', () => validarTelefono(txtTelefono));
                txtTelefono.addEventListener('blur', () => validarTelefono(txtTelefono));
            }

            if (txtDni) {
                txtDni.addEventListener('input', () => validarDocumentoNumerico(txtDni, 8, 12, 'DNI/CE'));
                txtDni.addEventListener('blur', () => validarDocumentoNumerico(txtDni, 8, 12, 'DNI/CE'));
            }

            if (txtRuc) {
                txtRuc.addEventListener('input', () => validarDocumentoNumerico(txtRuc, 11, 11, 'RUC', false));
                txtRuc.addEventListener('blur', () => validarDocumentoNumerico(txtRuc, 11, 11, 'RUC', false));
            }
        }
    })();

    // Actualizar el costo de envío y el total dinámicamente
    function updateOrderSummary() {
        const envioEstandarCost = 0;
        const envioUrgenteCost = 10.00;
        const envioDiaHabilCost = 15.00;
        const subtotal = parseFloat('{{ Cart::subtotal() }}'.replace(/,/g, '')) || 0;
        const tax = parseFloat('{{ Cart::tax() }}'.replace(/,/g, '')) || 0;
        let shippingCost = 0;

        const selectedShipping = document.querySelector('input[name="radio_metodo_envio"]:checked');
        const envioElement = document.querySelector('.envio-costo');
        const totalElement = document.getElementById('total-price');

        if (selectedShipping && envioElement && totalElement) {
            if (selectedShipping.value === 'standard') {
                shippingCost = envioEstandarCost;
                envioElement.textContent = 'GRATIS';
                envioElement.classList.add('text-success');
                envioElement.classList.remove('text-dark');
            } else if (selectedShipping.value === 'urgent') {
                shippingCost = envioUrgenteCost;
                envioElement.textContent = `S/ ${shippingCost.toFixed(2)}`;
                envioElement.classList.remove('text-success');
                envioElement.classList.add('text-dark');
            } else if (selectedShipping.value === 'next_day') {
                shippingCost = envioDiaHabilCost;
                envioElement.textContent = `S/ ${shippingCost.toFixed(2)}`;
                envioElement.classList.remove('text-success');
                envioElement.classList.add('text-dark');
            }

            const total = subtotal + tax + shippingCost;
            totalElement.textContent = total.toFixed(2);

        }
    }

    // Mostrar/ocultar información de transferencia bancaria
    function toggleTransferInfo() {
        const transferInfo = document.getElementById('transferInfo');
        const transferRadio = document.getElementById('rd_pago_transfer_bancaria');

        if (transferInfo && transferRadio) {
            if (transferRadio.checked) {
                transferInfo.style.display = 'block';
            } else {
                transferInfo.style.display = 'none';
            }
        }
    }

    //=============================== PROCESOS DE PAGO DIFERENCIADOS ===============================

    // Manejo del envío del formulario con lógica diferenciada
    jQuery('#saveService').on('submit', function(e) {
        e.preventDefault();

        const form = this;

        // Validar distrito manualmente (select inicia disabled sin required)
        const districtSelect = document.getElementById('txt_listar_distritos');
        if (districtSelect && !districtSelect.disabled && !districtSelect.value) {
            districtSelect.setCustomValidity('Por favor seleccione un distrito.');
        } else if (districtSelect) {
            districtSelect.setCustomValidity('');
        }

        if (!form.checkValidity()) {
            e.stopPropagation();
            marcarCampos();
            showRequiredAlerts();
            return;
        }

        // Módulo Delivery: compra bajo el mínimo bloqueada para este distrito
        if (window.DEL_BLOQUEADO) {
            Swal.fire({
                icon: 'info',
                title: 'Compra mínima para delivery',
                html: (document.querySelector('.mensajeLlevaloDesde') || {}).innerText || 'Agrega más productos para continuar.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Pasarela "WhatsApp": sin pago en línea, el pedido va al WhatsApp de ventas
        if (pasarelaActiva === 'whatsapp') {
            enviarPedidoWhatsapp(form);
            return;
        }

        const metodoPago = jQuery('input[name="radio_metodo_pago"]:checked').val();

        // Validar monto a pagar si es contra entrega
        if (metodoPago == '2') {
            var montoPagarVal   = parseFloat(jQuery('#del_monto_pagar').val()) || 0;
            var totalConEnvioVal = parseFloat(jQuery('#total-price').text().replace(/,/g, '')) || 0;
            if (montoPagarVal <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Monto requerido',
                    text: '¿Con cuánto va a pagar? Ingrese el monto con el que pagará al recibir su pedido.',
                    confirmButtonText: 'Entendido'
                });
                jQuery('#del_monto_pagar').focus();
                return;
            }
            if (montoPagarVal < totalConEnvioVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Monto insuficiente',
                    html: 'El monto ingresado (<strong>S/ ' + montoPagarVal.toFixed(2) + '</strong>) es menor al total a pagar (<strong>S/ ' + totalConEnvioVal.toFixed(2) + '</strong>).<br>Ingrese un monto igual o mayor.',
                    confirmButtonText: 'Entendido'
                });
                jQuery('#del_monto_pagar').focus();
                return;
            }
        }

        // 1: Pago con tarjeta (Stripe, Culqi o Izipay según pasarela activa) | 2: Pago contra entrega
        if (metodoPago == '2') {
            procesarPedidoNormal(form);
        } else if (metodoPago == '1') {
            if (pasarelaActiva === 'izipay') {
                procesarPagoConIzipay(form);
            } else if (pasarelaActiva === 'stripe') {
                procesarPagoConStripe(form);
            } else {
                procesarPagoConCulqi(form);
            }
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Método de pago',
                text: 'Por favor seleccione un método de pago válido',
                confirmButtonText: 'Entendido'
            });
        }
    });

    // Flujo Izipay: obtener formToken y abrir el formulario Krypton en un modal (sin salir del checkout).
    function procesarPagoConIzipay(form) {
        Swal.fire({
            title: 'Preparando pago Izipay',
            html: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(form);
        const formObject = {};
        formData.forEach((value, key) => { formObject[key] = value; });

        const amount = obtenerMontoActual();
        const email = jQuery('#txt_email').val();

        axios.post('/web_checkout/generar-formulario-izipay', {
            amount: amount,
            email: email,
            form_data: formObject,
            _token: jQuery('meta[name="csrf-token"]').attr('content')
        }).then(function(response) {
            Swal.close();
            var formToken = response.data.formToken;
            if (!formToken) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo iniciar el pago. Intenta de nuevo.', confirmButtonText: 'Entendido' });
                return;
            }
            var url = '/web_checkout/izipay-form?formToken=' + encodeURIComponent(formToken);
            var _izipayMsgHandler = null;
            var _modalWidth = Math.min(window.innerWidth * 0.95, 500);
            Swal.fire({
                title: '',
                html: '<div class="izipay-modal-wrap">'
                    + '<div class="izipay-modal-header">'
                    +   '<div class="izipay-cart-circle"><i class="fa fa-shopping-cart"></i></div>'
                    +   '<div class="izipay-merchant-name">Valento<small>Pago seguro con tarjeta</small></div>'
                    + '</div>'
                    + '<iframe id="izipay-iframe" src="' + url + '" scrolling="no" frameborder="0" style="width:100%;min-height:430px;border:none;background:#e8e8e8;display:block;"></iframe>'
                    + '</div>',
                width: _modalWidth + 'px',
                padding: '0',
                showConfirmButton: false,
                showCloseButton: true,
                allowOutsideClick: false,
                customClass: { popup: 'izipay-swal-popup' },
                didOpen: function() {
                    _izipayMsgHandler = function(e) {
                        if (e.data && e.data.type === 'izipay-resize') {
                            var iframe = document.getElementById('izipay-iframe');
                            if (iframe && e.data.height > 100) {
                                iframe.style.height = (e.data.height + 16) + 'px';
                            }
                        }
                    };
                    window.addEventListener('message', _izipayMsgHandler);
                },
                didDestroy: function() {
                    if (_izipayMsgHandler) { window.removeEventListener('message', _izipayMsgHandler); _izipayMsgHandler = null; }
                }
            });
        }).catch(function(error) {
            Swal.close();
            console.error('Izipay init error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'No se pudo cargar el formulario de pago. Intenta de nuevo.',
                confirmButtonText: 'Entendido'
            });
        });
    }

    // Flujo Stripe: obtener client_secret → mostrar formulario de tarjeta → confirmar pago
    function procesarPagoConStripe(form) {
        Swal.fire({
            title: 'Preparando pago Stripe',
            html: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
 
        const formData = new FormData(form);
        const formObject = {};
        formData.forEach((value, key) => { formObject[key] = value; });
 
        const amount = obtenerMontoActual();
        const email  = document.getElementById('txt_email').value;
 
        // Paso 1: Obtener el client_secret del backend
        axios.post('/web_checkout/generar-formulario-stripe', {
            amount:    amount,
            email:     email,
            form_data: formObject,
            _token:    document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }).then(function(response) {
            const clientSecret   = response.data.clientSecret;
            const publicKey = response.data.publicKey;
            const stripeOrderId = response.data.order;

            if (!clientSecret || !publicKey) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo iniciar el pago con Stripe.', confirmButtonText: 'Entendido' });
                return;
            }
 
            // Paso 2: Inicializar Stripe.js y mostrar el formulario de tarjeta
            const stripe   = Stripe(publicKey);
            const elements = stripe.elements({ clientSecret });
 
            const paymentElement = elements.create('payment', {
                layout: 'tabs',
            });
 
            Swal.fire({
                title: '<strong>Pago seguro con Stripe</strong>',
                html: `
                    <div style="text-align:left; padding: 8px 0;">
                        <div id="stripe-payment-element" style="margin-bottom: 16px;"></div>
                        <div id="stripe-error-msg" style="color:#dc3545; font-size:13px; margin-bottom:8px; display:none;"></div>
                        <button id="btn-stripe-pagar"
                            style="width:100%; padding:12px; background:#635BFF; color:white;
                                   border:none; border-radius:8px; font-size:15px;
                                   font-weight:700; cursor:pointer; letter-spacing:0.03em;">
                            Pagar S/ ${(amount / 100).toFixed(2)}
                        </button>
                    </div>
                `,
                width: '480px',
                showConfirmButton: false,
                showCloseButton: true,
                allowOutsideClick: false,
                didOpen: function() {
                    // Montar el elemento de pago de Stripe
                    paymentElement.mount('#stripe-payment-element');
 
                    // Manejar el botón de pago
                    document.getElementById('btn-stripe-pagar').addEventListener('click', async function() {
                        const btn = this;
                        btn.disabled = true;
                        btn.textContent = 'Procesando...';
 
                        const errorDiv = document.getElementById('stripe-error-msg');
                        errorDiv.style.display = 'none';
 
                        // Confirmar el pago con Stripe.js
                        const { error, paymentIntent } = await stripe.confirmPayment({
                            elements,
                            redirect: 'if_required',
                        });
 
                        if (error) {
                            // Error en el pago (tarjeta rechazada, etc.)
                            errorDiv.textContent = error.message || 'Error al procesar el pago.';
                            errorDiv.style.display = 'block';
                            btn.disabled = false;
                            btn.textContent = 'Reintentar pago';
                            return;
                        }
 
                        if (paymentIntent && paymentIntent.status === 'succeeded') {
                            // Pago exitoso → confirmar en el backend
                            btn.textContent = 'Confirmando pedido...';

                            // Releer el formulario (los <select> disabled o Select2 a veces no van en FormData).
                            var confirmForm = document.getElementById('saveService');
                            var formDataPayload = Object.assign({}, formObject);
                            if (confirmForm) {
                                var fresh = {};
                                new FormData(confirmForm).forEach(function (value, key) { fresh[key] = value; });
                                formDataPayload = Object.assign({}, formObject, fresh);
                            }
                            if (typeof window.jQuery !== 'undefined') {
                                var $dep = window.jQuery('#txt_listar_departamentos');
                                var $prov = window.jQuery('#txt_listar_provincias');
                                var $dis = window.jQuery('#txt_listar_distritos');
                                if ($dep.length) {
                                    formDataPayload.txt_listar_departamentos = $dep.val() || formDataPayload.txt_listar_departamentos || '';
                                }
                                if ($prov.length && !$prov.prop('disabled')) {
                                    formDataPayload.txt_listar_provincias = $prov.val() || formDataPayload.txt_listar_provincias || '';
                                }
                                if ($dis.length && !$dis.prop('disabled')) {
                                    formDataPayload.txt_listar_distritos = $dis.val() || formDataPayload.txt_listar_distritos || '';
                                }
                            }
                            var emailConfirm = (document.getElementById('txt_email') && document.getElementById('txt_email').value)
                                ? document.getElementById('txt_email').value.trim()
                                : email;

                            axios.post('/web_checkout/stripe/confirmar-pago', {
                                payment_intent_id: paymentIntent.id,
                                stripe_order_id:   stripeOrderId,
                                email:             emailConfirm,
                                amount:            amount,
                                form_data:         formDataPayload,
                                _token:            document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }).then(function(res) {
                                Swal.close();
                                if (res.data.success && res.data.result && res.data.result.length >= 2) {
                                    window.location.href = '/thank-you';
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: res.data.message || 'No se pudo completar el pedido.',
                                        confirmButtonText: 'Entendido'
                                    });
                                }
                            }).catch(function(err) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: err.response?.data?.message || 'Error al confirmar el pedido.',
                                    confirmButtonText: 'Entendido'
                                });
                            });
                        } else {
                            errorDiv.textContent = 'El pago no fue completado. Intenta nuevamente.';
                            errorDiv.style.display = 'block';
                            btn.disabled = false;
                            btn.textContent = 'Reintentar pago';
                        }
                    });
                }
            });
        }).catch(function(error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'No se pudo iniciar el pago. Intenta de nuevo.',
                confirmButtonText: 'Entendido'
            });
        });
    }
 
    // Flujo Culqi igual al proyecto que funcionaba: script v4 (checkout.culqi.com/js/v4), sin RSA.
    function procesarPagoConCulqi(form) {
        if (typeof Culqi === 'undefined') {
            if (pasarelaActiva === 'culqi') {
                Swal.fire({ icon: 'error', title: 'Sistema de pagos no disponible', text: 'El sistema de pagos no cargó correctamente. Recarga la página e intenta nuevamente.', confirmButtonText: 'Entendido' });
            }
            return;
        }

        Swal.fire({
            title: 'Preparando pago seguro',
            html: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(form);
        const formObject = {};
        formData.forEach((value, key) => { formObject[key] = value; });

        axios.post('/web_checkout/generar-formulario-culqi', {
            amount: obtenerMontoActual(),
            email: jQuery('#txt_email').val(),
            form_data: formObject,
            metadata: { origen: 'web_checkout' },
            _token: jQuery('meta[name="csrf-token"]').attr('content')
        }).then(function(response) {
            Swal.close();

            const publicKey = response.data.publicKey;
            if (!publicKey) {
                Swal.fire({ icon: 'error', title: 'Error de configuración', text: 'Clave pública no configurada. Contacte soporte.', confirmButtonText: 'Entendido' });
                return;
            }

            Culqi.publicKey = publicKey;
            Culqi.options({
                lang: 'auto',
                modal: true,
                installments: false,
                paymentMethods: {
                    tarjeta: true,
                    yape: true,
                    billetera: true,
                    bancaMovil: true,
                    agente: true,
                    cuotealo: true,
                },
                style: {
                logo: "{{ URL::asset('temp2/img/logos/logo-1.png') }}",
                maincolor: '#000000', 
                buttontext: '#ffffff',
                maintext: '#1a1a1a',
                desctext: '#4A4A4A'
                },
            });
            Culqi.settings({
                title: 'Valento - Proceso de pago',
                currency: response.data.currency,
                description: 'Compra de prendas exclusivas',
                amount: response.data.amount,
                order: response.data.order,
                metadata: response.data.metadata,
            });

            Culqi.open();
        }).catch(function(error) {
            Swal.close();
            console.error('Culqi init error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'No se pudo inicializar el método de pago. Recarga la página e intenta nuevamente.',
                confirmButtonText: 'Entendido'
            });
        });
    }

    // Callback global que Culqi v4 llama al terminar (éxito o error)
    window.culqi = function() {
        if (Culqi.token) {
            procesarTokenCulqi(Culqi.token);
        } else if (Culqi.error) {
            var culqiMsg = Culqi.error.user_message || Culqi.error.merchant_message || 'Ocurrió un error al procesar la tarjeta';
            Swal.fire({
                icon: 'error',
                title: 'Pago rechazado',
                text: culqiMsg,
                confirmButtonText: 'Entendido'
            });
        }
    };

    // Función para procesar el token de Culqi (envía al backend)
    function procesarTokenCulqi(token) {
        const orderId = Culqi.settings.order;
        Culqi.close();

        const formData = new FormData(document.getElementById('saveService'));
        const formObject = {};
        formData.forEach((value, key) => { formObject[key] = value; });

        Swal.fire({
            title: 'Procesando pago',
            html: 'Por favor espere mientras procesamos su pago...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        var emailFormulario = (jQuery('#txt_email').val() || formObject.txt_email || token.email || '').trim();
        var montoCaptura = (Culqi.settings && Culqi.settings.amount)
            ? Culqi.settings.amount
            : obtenerMontoActual();

        axios.post('/web_checkout/enviar_pago_con_culqi', {
            token: token.id,
            order_id: orderId,
            email: emailFormulario,
            amount: montoCaptura,
            form_data: formObject,
            _token: jQuery('meta[name="csrf-token"]').attr('content')
        }).then(function(response) {
            if (response.data.success) {
                window.location.href = '/thank-you';
                return;
            }
            Swal.fire({
                icon: response.data.pago_capturado ? 'warning' : 'error',
                title: response.data.pago_capturado ? 'Pago recibido' : 'Pago rechazado',
                text: response.data.message || 'Ocurrió un error al procesar el pago',
                confirmButtonText: 'Entendido'
            });
        }).catch(function(error) {
            console.error('Error en pago Culqi:', error);
            var data = error.response?.data || {};
            Swal.fire({
                icon: data.pago_capturado ? 'warning' : 'error',
                title: data.pago_capturado ? 'Pago recibido' : 'Pago rechazado',
                text: data.message || 'Ocurrió un error al procesar el pago. Si no ves un cargo, puedes intentar de nuevo.',
                confirmButtonText: 'Entendido'
            });
        });
    }

    // Pedido por WhatsApp: registra el pedido (con su código) y abre el chat de ventas
    function enviarPedidoWhatsapp(form) {
        const btn = form.querySelector('.co-submit-btn--wa');
        if (btn && btn.disabled) return;

        // La pestaña se abre ya (dentro del clic) para que el navegador no la bloquee;
        // cuando llega la respuesta se le asigna la URL de WhatsApp.
        const waWin = window.open('', '_blank');
        if (btn) btn.disabled = true;

        Swal.fire({
            title: 'Registrando tu pedido',
            html: 'En un momento se abrirá WhatsApp con el detalle...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        axios.post('/web_checkout/enviar_pedido_whatsapp', new FormData(form), {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        }).then(function(response) {
            const data = response.data || {};
            if (data.success !== true || !data.whatsapp_url) {
                throw { response: { data: data } };
            }

            if (waWin && !waWin.closed) {
                waWin.location.href = data.whatsapp_url;
                window.location.href = data.redirect || '/thank-you';
                return;
            }

            // Ventanas emergentes bloqueadas: el cliente abre WhatsApp con un clic
            Swal.fire({
                icon: 'success',
                title: 'Pedido ' + data.result[0] + ' registrado',
                html: 'Envía el detalle a nuestro asesor para completar tu pedido.',
                confirmButtonText: '<i class="fa fa-whatsapp"></i> Abrir WhatsApp',
                confirmButtonColor: '#25D366',
                allowOutsideClick: false
            }).then(function() {
                window.open(data.whatsapp_url, '_blank');
                window.location.href = data.redirect || '/thank-you';
            });
        }).catch(function(error) {
            if (waWin && !waWin.closed) waWin.close();
            if (btn) btn.disabled = false;

            const body = (error && error.response && error.response.data) || {};
            const errores = body.errors ? Object.values(body.errors).flat() : [];
            Swal.fire({
                icon: 'error',
                title: 'No se pudo registrar el pedido',
                html: errores.length ? errores.join('<br>') : (body.message || 'Inténtelo de nuevo en unos segundos.'),
                confirmButtonText: 'Entendido'
            });
        });
    }

    // Función para procesar pedidos normales (envía FormData)
    function procesarPedidoNormal(form) {
        let formData = new FormData(form);

        Swal.fire({
            title: 'Procesando pedido',
            html: 'Por favor espere mientras procesamos su pedido...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar como FormData al endpoint normal
        axios.post('/web_checkout/enviar_pago_sin_culqi', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        }).then(function(response) {
            if (response.data.success === true) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pedido creado',
                    text: 'Su pedido ha sido registrado correctamente',
                    confirmButtonText: 'Ver pedido',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/thank-you';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.data.message || 'Ocurrió un error al procesar el pedido',
                    confirmButtonText: 'Entendido'
                });
            }
        }).catch(function(error) {
            console.error('Error en pedido normal:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'Hubo un problema al procesar su pedido. Inténtelo de nuevo.',
                confirmButtonText: 'Entendido'
            });
        });
    }

    function toggleTransferInfo() {
        const metodoPagoEl = document.querySelector('input[name="radio_metodo_pago"]:checked');
        if (!metodoPagoEl) return;
        
        const metodoPago = metodoPagoEl.value;
        const paypalBtn = document.getElementById('paypal-button-container');
        const submitBtn = document.querySelector('.btn-submit-order');

        if (pasarelaActiva === 'paypal' && metodoPago === '1') {
            if (paypalBtn) {
                paypalBtn.style.setProperty('display', 'block', 'important');
            }
            if (submitBtn) {
                submitBtn.style.setProperty('display', 'none', 'important');
            }
        } else {
            if (paypalBtn) {
                paypalBtn.style.setProperty('display', 'none', 'important');
            }
            if (submitBtn) {
                submitBtn.style.setProperty('display', 'block', 'important');
            }
        }
    }

    // =============================== LÓGICA PAYPAL ==============================
    if (pasarelaActiva === 'paypal' && typeof paypal !== 'undefined') {
        paypal.Buttons({
            createOrder: function(data, actions) {
                // Validación previa del formulario
                if (!showRequiredAlerts()) {
                    return false;
                }
                
                marcarCampos();
                
                // Obtenemos los datos del formulario actual
                const form = document.getElementById('saveService');
                const formData = new FormData(form);
                
                // Agregar el monto actual al FormData por si acaso
                formData.append('amount', obtenerMontoActual());

                return axios.post('/web_checkout/generar-formulario-paypal', formData)
                    .then(function(response) {
                        if (response.data.success) {
                            return response.data.order_id;
                        } else {
                            throw new Error(response.data.message || 'No se pudo generar la orden de PayPal');
                        }
                    })
                    .catch(function(error) {
                        console.error('PayPal Order Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de PayPal',
                            text: error.response?.data?.message || error.message || 'No se pudo generar la orden de pago',
                            confirmButtonText: 'Entendido'
                        });
                        return null;
                    });
            },
            onApprove: function(data, actions) {
                Swal.fire({
                    title: 'Procesando pago con PayPal',
                    html: 'Por favor espere mientras confirmamos su pago...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const formData = new FormData(document.getElementById('saveService'));
                formData.set('paypal_order_id', data.orderID);
                formData.set('radio_metodo_pago', '1'); // Forzar método online
                formData.set('radio_tipo_pago', 'normal'); // PayPal no usa tipo de pago Yape/Normal dinámico en este flujo

                return axios.post('/web_checkout/enviar_pago_con_paypal', formData)
                    .then(function(response) {
                        if (response.data.success) {
                            Swal.close();
                            window.location.href = '/thank-you';
                        } else {
                            throw new Error(response.data.message || 'Ocurrió un error al procesar el pago');
                        }
                    }).catch(function(error) {
                        console.error('PayPal Capture Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la captura',
                            text: error.response?.data?.message || error.message || 'Ocurrió un error al capturar el pago con PayPal',
                            confirmButtonText: 'Entendido'
                        });
                    });
            },
            onError: function(err) {
                console.error('PayPal Buttons Error:', err);
                // No mostrar alerta aquí si es un error manejado (como cancelar)
            }
        }).render('#paypal-button-container');
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof updateOrderSummary === 'function') updateOrderSummary();
        toggleTransferInfo();

        // Event listeners
        document.querySelectorAll('input[name="radio_metodo_envio"]').forEach(radio => {
            radio.addEventListener('change', updateOrderSummary);
        });

        document.querySelectorAll('input[name="radio_metodo_pago"]').forEach(radio => {
            radio.addEventListener('change', toggleTransferInfo);
        });
    });
</script>

<script>
window.__liamCheckoutUbigeo = true;
(function () {
    function ready(fn) {
        if (window.jQuery) {
            window.jQuery(fn);
            return;
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function optionId($sel) {
        return ($sel.find('option:selected').attr('data-id') || '').toString();
    }

    function refreshSelect2($el) {
        if (!window.jQuery || typeof window.jQuery.fn.select2 !== 'function') return;
        var $ = window.jQuery;
        var placeholders = {
            txt_listar_distritos: 'Seleccione un distrito',
            txt_listar_provincias: 'Seleccione una provincia',
            txt_listar_departamentos: 'Seleccione departamento'
        };
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $(document.body),
            placeholder: placeholders[$el.attr('id')] || 'Seleccione...',
            minimumResultsForSearch: 0
        });
    }

    ready(function () {
        var $ = window.jQuery;
        if (!$) return;

        var $dep = $('#txt_listar_departamentos');
        var $prov = $('#txt_listar_provincias');
        var $dist = $('#txt_listar_distritos');
        if (!$dep.length || !$prov.length) return;

        function fillSelect($el, placeholder, rows, enable) {
            var html = '<option value="" selected disabled>' + placeholder + '</option>';
            (rows || []).forEach(function (row) {
                var name = $.trim(row.name || '');
                html += '<option value="' + name.replace(/"/g, '&quot;') + '" data-id="' + (row.id || '') + '">' + name + '</option>';
            });
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.html(html).prop('disabled', !enable);
            refreshSelect2($el);
        }

        function cargarProvincias(departmentId) {
            fillSelect($dist, 'Seleccione distrito...', [], false);
            if (!departmentId) {
                fillSelect($prov, 'Seleccione provincia...', [], false);
                return;
            }
            $prov.prop('disabled', true);
            if ($prov.hasClass('select2-hidden-accessible')) {
                $prov.select2('destroy');
            }
            $prov.html('<option value="">Cargando provincias...</option>');
            refreshSelect2($prov);
            $.getJSON('/web_checkout/ubigeo/provincias', { department_id: departmentId })
                .done(function (result) {
                    fillSelect($prov, 'Seleccione provincia...', result.provincias || [], true);
                })
                .fail(function () {
                    fillSelect($prov, 'Error al cargar', [], true);
                });
        }

        function cargarDistritos(departmentId, provinceId) {
            if (!departmentId || !provinceId) {
                fillSelect($dist, 'Seleccione distrito...', [], false);
                return;
            }
            $dist.prop('disabled', true);
            if ($dist.hasClass('select2-hidden-accessible')) {
                $dist.select2('destroy');
            }
            $dist.html('<option value="">Cargando distritos...</option>');
            refreshSelect2($dist);
            $.getJSON('/web_checkout/ubigeo/distritos', {
                department_id: departmentId,
                province_id: provinceId
            })
                .done(function (result) {
                    fillSelect($dist, 'Seleccione distrito...', result.distritos || [], true);
                })
                .fail(function () {
                    fillSelect($dist, 'Error al cargar', [], true);
                });
        }

        $dep.on('change.liamUbigeo', function () {
            cargarProvincias(optionId($dep));
        });
        $prov.on('change.liamUbigeo', function () {
            cargarDistritos(optionId($dep), optionId($prov));
        });

        if ($dep.val()) {
            cargarProvincias(optionId($dep));
        } else {
            refreshSelect2($dep);
            refreshSelect2($prov);
            refreshSelect2($dist);
        }
    });
})();
</script>

@endsection