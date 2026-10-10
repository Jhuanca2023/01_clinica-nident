@extends('web.base_shop')
@section('title', 'Pedido confirmado | Valento')
@section('head_page')
<link rel="stylesheet" href="{{ asset('temp02/css/thank-you.css') }}">
@endsection
@section('content')
@include('web.partials.liam.header')

@php
    $pedido = $pedido ?? null;
    $productos = $productos ?? collect();
    $costoEnvioRaw = $costo_envio ?? ($pedido->pasarela_costo_envio ?? 'gratis');
    $costoEnvioNumerico = ($costoEnvioRaw === 'gratis' || $costoEnvioRaw === null || floatval($costoEnvioRaw) == 0)
        ? 0
        : floatval($costoEnvioRaw);
    $subtotal = floatval(str_replace(',', '', (string) $total));
    $totalConEnvio = $subtotal + $costoEnvioNumerico;
    $esOnline = (int) ($pedido->radio_metodo_pago ?? 0) === 1;
    $esWhatsapp = strtolower((string) ($pedido->radio_tipo_pago ?? '')) === 'whatsapp'
        || strcasecmp((string) ($pedido->metodo_pago ?? ''), 'WhatsApp') === 0
        || (($order_session['origen'] ?? '') === 'whatsapp');
    $nombreCliente = trim(($pedido->direccion_envio_nombre ?? '') . ' ' . ($pedido->direccion_envio_apellido ?? ''));
    $documento = !empty($pedido->direccion_envio_ruc)
        ? 'RUC: ' . $pedido->direccion_envio_ruc
        : (!empty($pedido->direccion_envio_dni) ? 'DNI: ' . $pedido->direccion_envio_dni : '—');
    $distrito = collect([
        $pedido->direccion_envio_distrito ?? null,
        $pedido->direccion_envio_provincia ?? null,
        $pedido->direccion_envio_ciudad ?? null,
    ])->filter()->implode(', ');
    $metodoPago = 'No especificado';
    if ($esWhatsapp) {
        $metodoPago = 'WhatsApp';
    } elseif ($esOnline) {
        $metodoPago = match ($pedido->pasarela_metodo ?? '') {
            'paypal' => 'Tarjeta online (PayPal)',
            'stripe' => 'Tarjeta online (Stripe)',
            'izipay' => 'Tarjeta online (Izipay)',
            'culqi'  => 'Tarjeta / Yape / Plin (Culqi)',
            default  => 'Pago online',
        };
    } else {
        $metodoPago = 'Pago contra entrega';
    }
    $estadoPago = $esWhatsapp
        ? 'Enviado por WhatsApp — esperamos tu respuesta'
        : ($esOnline
            ? (($pedido->pasarela_estado_pago ?? '') === 'completado' ? 'Completado' : 'En verificación')
            : 'Pendiente (contra entrega)');
    $waNum = optional(\App\Models\WebWhatsappConfig::first())->wa_numero ?: \App\Support\ContactoValento::whatsappNumero();
    $waMsg = rawurlencode(
        $esWhatsapp
            ? ('Hola, acabo de enviar el pedido ' . ($codigo_pedido ?? '') . ' por WhatsApp y quisiera continuar la atención.')
            : ('Hola, hice el pedido ' . ($codigo_pedido ?? '') . ' y quisiera información.')
    );
    $waUrl = 'https://wa.me/' . preg_replace('/\D/', '', $waNum) . '?text=' . $waMsg;
    $esProvincia = isset($pedido->hora_regresiva_descripcion)
        && $pedido->hora_regresiva_descripcion === 'Retíralo por agencia shalom o marvisur';
    $montoPagadoCliente = floatval($pedido->monto_pagado_cliente ?? 0);
    $vueltoEstimado = $montoPagadoCliente > 0 ? $montoPagadoCliente - $totalConEnvio : 0;
@endphp

<main class="ty-page">
    <div class="container">

        <div class="ty-hero">
            <div class="ty-check-circle" @if($esWhatsapp) style="background:#25d366;" @endif>
                <i class="fa {{ $esWhatsapp ? 'fa-whatsapp' : 'fa-check' }}"></i>
            </div>
            <h1 class="ty-hero__title">
                @if($esWhatsapp)
                    ¡Pedido enviado por WhatsApp!
                @else
                    ¡Pedido confirmado!
                @endif
            </h1>
            <p class="ty-hero__sub">
                @if($esWhatsapp)
                    Tu pedido ha sido registrado y enviado por WhatsApp.<br>
                    <strong>Esperamos tu pronta respuesta</strong> para coordinar el pago y la entrega.
                @else
                    Gracias por tu compra. Hemos recibido tu pedido y lo estamos procesando.<br>
                    Recibirás un correo de confirmación en breve.
                @endif
            </p>
            <span class="ty-order-badge">
                <i class="fa fa-hashtag"></i> N° de pedido: <strong>{{ $codigo_pedido }}</strong>
            </span>
            <div style="margin-top:14px;">
                @if($esWhatsapp)
                    <span style="display:inline-block;background:#e8f5e9;color:#128c7e;padding:6px 14px;border-radius:16px;font-family:Montserrat,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.5px;">
                        <i class="fa fa-whatsapp"></i> Pedido por WhatsApp
                    </span>
                @elseif($esOnline)
                    <span style="display:inline-block;background:#e8f5e9;color:#2e7d32;padding:6px 14px;border-radius:16px;font-family:Montserrat,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.5px;">
                        <i class="fa fa-lock"></i> Pago online
                    </span>
                @else
                    <span style="display:inline-block;background:#fff8e1;color:#f57f17;padding:6px 14px;border-radius:16px;font-family:Montserrat,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.5px;">
                        <i class="fa fa-money"></i> Contra entrega
                    </span>
                @endif
            </div>
        </div>

        <div class="ty-wa-card">
            <i class="fa fa-whatsapp"></i>
            <div>
                @if($esWhatsapp)
                    <h4>¿No se abrió WhatsApp?</h4>
                    <p>Puedes volver a escribirnos para continuar con tu pedido {{ $codigo_pedido }}.</p>
                @else
                    <h4>¿Tienes dudas sobre tu pedido?</h4>
                    <p>Contáctanos por WhatsApp y te ayudamos al instante.</p>
                @endif
            </div>
            <a href="{{ $waUrl }}" target="_blank" rel="noopener">{{ $esWhatsapp ? 'Abrir WhatsApp' : 'Escribir ahora' }}</a>
        </div>

        <div class="ty-layout">

            <div>
                <div class="ty-card">
                    <div class="ty-card__head">
                        <i class="fa fa-shopping-bag"></i>
                        <h3>Productos del pedido ({{ count($productos) }})</h3>
                    </div>
                    <div class="ty-card__body">
                        @forelse($productos as $producto)
                            @php
                                $lineTotal = floatval($producto->pro_precio) * (int) $producto->pro_cantidad;
                                $img = trim((string) ($producto->pro_url_imagen ?? ''));
                                if ($img !== '' && ! preg_match('#^https?://#i', $img)) {
                                    $img = \App\Support\LiamPageData::img($img);
                                }
                            @endphp
                            <div class="ty-item">
                                @if($img !== '')
                                    <img class="ty-item__img" src="{{ $img }}" alt="{{ $producto->pro_nombre }}">
                                @else
                                    <div class="ty-item__img" style="display:flex;align-items:center;justify-content:center;color:#ccc;">
                                        <i class="fa fa-image"></i>
                                    </div>
                                @endif
                                <div class="ty-item__info">
                                    <p class="ty-item__name">{{ $producto->pro_nombre }}</p>
                                    <p class="ty-item__meta">
                                        Cant: {{ $producto->pro_cantidad }}
                                        &nbsp;|&nbsp; S/ {{ number_format((float) $producto->pro_precio, 2) }} c/u
                                        @if(!empty($producto->pro_descripcion))
                                            <br>{{ Str::limit($producto->pro_descripcion, 80) }}
                                        @endif
                                    </p>
                                </div>
                                <span class="ty-item__price">S/ {{ number_format($lineTotal, 2) }}</span>
                            </div>
                        @empty
                            <p style="font-family:Montserrat,sans-serif;font-size:12px;color:#aaa;text-align:center;">Sin productos registrados</p>
                        @endforelse
                    </div>
                </div>

                <div class="ty-card">
                    <div class="ty-card__head">
                        <i class="fa fa-user-o"></i>
                        <h3>Datos del cliente</h3>
                    </div>
                    <div class="ty-card__body">
                        <table class="ty-data-table">
                            <tr><td>Nombre</td><td>{{ $nombreCliente ?: '—' }}</td></tr>
                            <tr><td>Correo</td><td>{{ $pedido->direccion_envio_correo ?? '—' }}</td></tr>
                            <tr><td>Teléfono</td><td>{{ $pedido->direccion_envio_telefono ?? '—' }}</td></tr>
                            <tr><td>Documento</td><td>{{ $documento }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="ty-card">
                    <div class="ty-card__head">
                        <i class="fa fa-truck"></i>
                        <h3>Información de envío</h3>
                    </div>
                    <div class="ty-card__body">
                        <table class="ty-data-table">
                            <tr><td>Dirección</td><td>{{ $pedido->direccion_envio_ubicacion ?? '—' }}</td></tr>
                            <tr><td>Ubicación</td><td>{{ $distrito ?: '—' }}</td></tr>
                            @if(!empty($pedido->hora_regresiva_descripcion))
                            <tr><td>Modalidad</td><td>{{ $pedido->hora_regresiva_descripcion }}</td></tr>
                            @endif
                            @if(!empty($order_session['delivery_mensaje'] ?? null))
                            <tr><td>Entrega</td><td>{{ $order_session['delivery_mensaje'] }}</td></tr>
                            @endif
                            <tr><td>Tiempo estimado</td><td><span style="color:#27ae60;font-weight:700;">2–4 días hábiles</span></td></tr>
                        </table>
                        @if($esProvincia)
                        <div style="margin-top:14px;padding:12px 14px;background:#fff8e1;border:1px solid #ffe082;border-radius:6px;font-family:Montserrat,sans-serif;font-size:11px;color:#555;line-height:1.6;">
                            <i class="fa fa-info-circle" style="color:#f57f17;"></i>
                            El envío a provincia es pago a destino. El cobro actual es solo por llevar el pedido a la agencia.
                        </div>
                        @endif
                    </div>
                </div>

                <div class="ty-card">
                    <div class="ty-card__head">
                        <i class="fa fa-credit-card"></i>
                        <h3>Método de pago</h3>
                    </div>
                    <div class="ty-card__body">
                        <table class="ty-data-table">
                            <tr><td>Método</td><td>{{ $metodoPago }}</td></tr>
                            <tr>
                                <td>Estado</td>
                                <td>
                                    @if($estadoPago === 'Completado')
                                        <span style="background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">{{ $estadoPago }}</span>
                                    @else
                                        <span style="background:#fff8e1;color:#f57f17;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">{{ $estadoPago }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if(!$esOnline && $montoPagadoCliente > 0)
                            <tr><td>Monto a preparar</td><td>S/ {{ number_format($montoPagadoCliente, 2) }}</td></tr>
                            @if($vueltoEstimado > 0)
                            <tr><td>Vuelto estimado</td><td>S/ {{ number_format($vueltoEstimado, 2) }}</td></tr>
                            @endif
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="ty-card" style="margin-bottom:16px;">
                    <div class="ty-card__head">
                        <i class="fa fa-file-text-o"></i>
                        <h3>Resumen de compra</h3>
                    </div>
                    <div class="ty-card__body">
                        <div class="ty-summary-row">
                            <span class="label">Subtotal</span>
                            <span class="value">S/ {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="ty-summary-row">
                            <span class="label">Envío</span>
                            <span class="value" style="{{ $costoEnvioNumerico == 0 ? 'color:#27ae60;' : '' }}">
                                {{ $costoEnvioNumerico > 0 ? 'S/ ' . number_format($costoEnvioNumerico, 2) : 'Gratis' }}
                            </span>
                        </div>
                        <div class="ty-summary-row total">
                            <span class="label">{{ $esWhatsapp ? 'Total del pedido' : 'Total pagado' }}</span>
                            <span class="value">S/ {{ number_format($totalConEnvio, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="ty-actions">
                    @auth
                    <a href="{{ url('/ope_pedidos/listar_pedidos') }}" class="ty-btn-primary">
                        <i class="fa fa-list"></i> Ver mis compras
                    </a>
                    @endauth
                    <a href="{{ url('/tienda') }}" class="ty-btn-{{ auth()->check() ? 'secondary' : 'primary' }}">
                        <i class="fa fa-shopping-bag"></i> Seguir comprando
                    </a>
                    <a href="{{ url('/') }}" class="ty-btn-secondary">
                        <i class="fa fa-home"></i> Volver al inicio
                    </a>
                </div>

                <div style="margin-top:20px;padding:16px;background:{{ $esWhatsapp ? '#e8f8ef' : '#f0f7f0' }};border-radius:6px;border:1px solid {{ $esWhatsapp ? '#a5d6a7' : '#c8e6c9' }};">
                    <p style="font-family:'Montserrat',sans-serif;font-size:11px;color:#2e7d32;font-weight:600;margin:0 0 6px;">
                        @if($esWhatsapp)
                            <i class="fa fa-whatsapp"></i> &nbsp;Tu pedido fue enviado por WhatsApp
                        @else
                            <i class="fa fa-check-circle"></i> &nbsp;Tu pedido está siendo procesado
                        @endif
                    </p>
                    <p style="font-family:'Montserrat',sans-serif;font-size:11px;color:#555;margin:0;line-height:1.6;">
                        @if($esWhatsapp)
                            Esperamos tu pronta respuesta para confirmar el pago y coordinar la entrega. También puedes revisar el pedido en Mis Compras si iniciaste sesión.
                        @else
                            Recibirás actualizaciones del estado de tu envío por correo. Para consultas, comunícate con nosotros.
                        @endif
                    </p>
                </div>
            </div>

        </div>
    </div>
</main>
@include('web.partials.liam.footer')
@endsection
