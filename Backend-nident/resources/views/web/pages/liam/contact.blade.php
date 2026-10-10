@extends('web.base_shop')

@section('title', $metaData->titulo_pagina ?? 'Contacto | Valento')

@section('head_page')

<link rel="stylesheet" href="{{ asset('temp02/css/contact.css') }}?v={{ filemtime(public_path('temp02/css/contact.css')) }}">
{{-- Mismo orden que temp02/contact.html: fonts.css después de contact.css para que los títulos usen Cinzel --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ filemtime(public_path('temp02/css/fonts.css')) }}">

@endsection

@section('content')

@include('web.partials.liam.header')

@php

    $pc = $paginaContacto ?? null;

    $h = $webHeader ?? null;

    // Botón "Chatear ahora": número del CMS de contacto (admin), fallback header/footer
    $urlWaContacto = \App\Support\LiamPageData::whatsappUrlFromPhone($pc->telefono ?? null);
    if ($urlWaContacto === '') {
        $urlWaContacto = $urlWhatsapp ?? '';
    }

@endphp



<section class="ct-hero">

    <div class="ct-hero__bg" style="background-image:url('{{ \App\Support\LiamPageData::img($pc->banner_url_imagen ?? null, 'temp02/img/inicio/2.jpg') }}')"></div>

    <div class="ct-hero__overlay"></div>

    <div class="ct-hero__content">

        <span class="ct-hero__tag">{{ $pc->form_subtitulo ?? 'Valento — Lima, Perú' }}</span>

        <h1 class="ct-hero__title text-white">{!! $pc->banner_titulo ?? 'Hablemos de <span>moda</span>' !!}</h1>

        <p class="ct-hero__sub">{{ $pc->hero_subtitulo ?? 'Estamos aquí para ayudarte' }}</p>

    </div>

</section>



<main class="ct-main">

    <div class="container">

        @if(session('success'))

            <div class="alert alert-success">{{ session('success') }}</div>

        @endif

        @if($errors->any())

            <div class="alert alert-danger" style="margin-bottom:20px;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        @endif



        {{-- Ancla menú: /contacto#direcciones --}}
        <div id="direcciones" class="ct-anchor-section">
        <div class="ct-info-row">

            <div class="ct-info-card wow fadeInUp" data-wow-duration="0.6s" data-wow-delay="0.1s">

                <div class="ct-info-card__icon"><i class="fa fa-map-marker"></i></div>

                <p class="ct-info-card__title">{{ $pc->ubicacion_etiqueta ?? 'Dirección' }}</p>

                <p class="ct-info-card__text">{!! nl2br(e($pc->ubicacion ?? 'Lima, Perú')) !!}</p>

            </div>

            <div class="ct-info-card wow fadeInUp" data-wow-duration="0.6s" data-wow-delay="0.2s">

                <div class="ct-info-card__icon"><i class="fa fa-phone"></i></div>

                <p class="ct-info-card__title">{{ $pc->telefono_etiqueta ?? 'Teléfono' }}</p>

                <p class="ct-info-card__text">

                    @if($pc->telefono ?? null)

                        <a href="tel:{{ preg_replace('/\s+/', '', $pc->telefono) }}">{{ $pc->telefono }}</a><br>

                    @endif

                    @if(($pc->telefonos_texto ?? null) && $urlWaContacto)

                        <a href="{{ $urlWaContacto }}" target="_blank" rel="noopener">{{ $pc->telefonos_texto }}</a>

                    @elseif($pc->telefonos_texto ?? null)

                        {!! nl2br(e($pc->telefonos_texto)) !!}

                    @else

                        <span>Llamada disponible</span>

                    @endif

                </p>

            </div>

            <div class="ct-info-card wow fadeInUp" data-wow-duration="0.6s" data-wow-delay="0.3s">

                <div class="ct-info-card__icon"><i class="fa fa-envelope-o"></i></div>

                <p class="ct-info-card__title">{{ $pc->email_etiqueta ?? 'Correo' }}</p>

                <p class="ct-info-card__text">

                    @php
                        $emailsContacto = preg_split('/\r\n|\r|\n/', $pc->emails_texto ?? $pc->email ?? '') ?: [];
                        $emailsContacto = array_values(array_filter(array_map('trim', $emailsContacto)));
                        if (count($emailsContacto) === 0) {
                            $emailsContacto = [\App\Support\ContactoValento::ventasEmail()];
                        }
                    @endphp

                    @foreach($emailsContacto as $mail)

                        <a href="mailto:{{ $mail }}">{{ $mail }}</a><br>

                    @endforeach

                </p>

            </div>

            <div class="ct-info-card wow fadeInUp" data-wow-duration="0.6s" data-wow-delay="0.4s">

                <div class="ct-info-card__icon"><i class="fa fa-clock-o"></i></div>

                <p class="ct-info-card__title">{{ $pc->horario_etiqueta ?? 'Horario' }}</p>

                <p class="ct-info-card__text">

                    {{ $pc->horario_linea1 ?? '' }}<br>

                    {{ $pc->horario_linea2 ?? '' }}<br>

                    {{ $pc->horario_dias ?? '' }}

                </p>

            </div>

        </div>
        </div>{{-- /#direcciones --}}



        {{-- Ancla menú: /contacto#formulario --}}
        <div id="formulario" class="ct-anchor-section ct-form-layout">

            <div class="ct-form-wrap wow fadeInLeft" data-wow-duration="0.75s" data-wow-delay="0.15s">

                <h2 class="ct-form-title">{{ $pc->form_titulo ?? 'Envíanos un mensaje' }}</h2>

                <p class="ct-form-subtitle">{{ $pc->form_descripcion ?? '' }}</p>



                <form action="{{ url('/contacto') }}" method="POST" id="ct-form" novalidate>

                    @csrf

                    <div class="ct-row">

                        <div class="ct-field">

                            <label class="ct-label" for="nombre">Nombre *</label>

                            <input type="text" name="nombre" id="nombre" class="ct-input" placeholder="Tu nombre completo" value="{{ old('nombre') }}" required>

                        </div>

                        <div class="ct-field">

                            <label class="ct-label" for="email">Correo *</label>

                            <input type="email" name="email" id="email" class="ct-input" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>

                        </div>

                    </div>

                    <div class="ct-row">

                        <div class="ct-field">

                            <label class="ct-label" for="telefono">Teléfono</label>

                            <input type="tel" name="telefono" id="telefono" class="ct-input" placeholder="+51 9XX XXX XXX" value="{{ old('telefono') }}">

                        </div>

                        <div class="ct-field">

                            <label class="ct-label" for="producto_interes">Asunto *</label>

                            @php
                                $asuntos = ['Consulta sobre pedido', 'Información de producto', 'Guía de tallas', 'Cambios y devoluciones', 'Venta por mayor', 'Otro'];
                            @endphp
                            <div class="ct-select-wrap">
                                <select name="producto_interes" id="producto_interes" class="ct-select ct-input" required>
                                    <option value="">Selecciona un asunto</option>
                                    @foreach($asuntos as $asunto)
                                        <option value="{{ $asunto }}" {{ old('producto_interes') === $asunto ? 'selected' : '' }}>{{ $asunto }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>

                    {{-- El controlador exige fecha_mensaje; el template no muestra el campo --}}
                    <input type="hidden" name="fecha_mensaje" value="{{ old('fecha_mensaje', date('Y-m-d')) }}">

                    <div class="ct-row single">

                        <div class="ct-field">

                            <label class="ct-label" for="ct-message">
                                Mensaje *
                                <span id="ct-char-count" style="font-weight:400;color:#aaa;margin-left:6px;">{{ mb_strlen(old('mensaje', '')) }} / 500</span>
                            </label>

                            <textarea name="mensaje" id="ct-message" class="ct-textarea" maxlength="500" placeholder="Escribe tu mensaje aquí..." required>{{ old('mensaje') }}</textarea>

                        </div>

                    </div>

                    <div class="ct-check-row">
                        <input type="checkbox" name="acepto_privacidad" id="ct-terms" value="1" {{ old('acepto_privacidad') ? 'checked' : '' }} required>
                        <label for="ct-terms">
                            He leído y acepto la <a href="{{ url('/politica-privacidad') }}" target="_blank" rel="noopener">Política de Privacidad</a>
                            y los <a href="{{ url('/terminos-condiciones') }}" target="_blank" rel="noopener">Términos y Condiciones</a>.
                        </label>
                    </div>

                    <button type="submit" class="ct-submit-btn" id="ct-send-btn"><i class="fa fa-paper-plane"></i> {{ $pc->btn_texto ?: 'Enviar Mensaje' }}</button>

                </form>

            </div>



            <div class="ct-side-panel wow fadeInRight" data-wow-duration="0.75s" data-wow-delay="0.25s">

                <div class="ct-social-card">

                    <h4>Síguenos</h4>

                    {{-- Redes: misma fuente que header y footer (web_footer.footer_redes, editable en el admin) --}}
                    @php
                        $redesContacto = \App\Support\LiamPageData::redesSociales($footerCorp ?? null) ?? [];
                        $claseRed = ['facebook' => 'fb', 'instagram' => 'ig', 'tiktok' => 'tt', 'whatsapp' => 'wa', 'youtube' => 'yt'];
                        $iconoRed = ['facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'tiktok' => 'fa-music', 'whatsapp' => 'fa-whatsapp', 'youtube' => 'fa-youtube', 'linkedin' => 'fa-linkedin', 'twitter' => 'fa-twitter'];
                        // Redes sin clase en contact.css: color de fondo en línea
                        $fondoRed = ['twitter' => '#000000', 'linkedin' => '#0a66c2', 'link' => '#8C6B2E'];
                    @endphp
                    <div class="ct-social-list">
                        @foreach($redesContacto as $red)
                        <a href="{{ $red['url'] }}" class="ct-social-btn {{ $claseRed[$red['tipo']] ?? '' }}" @if(!isset($claseRed[$red['tipo']])) style="background: {{ $fondoRed[$red['tipo']] ?? '#8C6B2E' }};" @endif title="{{ $red['label'] }}" target="_blank" rel="noopener"><i class="fa {{ $iconoRed[$red['tipo']] ?? 'fa-link' }}"></i></a>
                        @endforeach
                    </div>

                </div>

                <div class="ct-hours-card">

                    <h4>Horario de atención</h4>

                    {{-- Mismas líneas que la tarjeta "Horario": "Lun–Vie: 9am – 7pm" → día | hora --}}
                    @foreach(array_filter([$pc->horario_linea1 ?? null, $pc->horario_linea2 ?? null, $pc->horario_dias ?? null]) as $lineaHorario)
                        @php [$diaHorario, $horaHorario] = array_pad(array_map('trim', explode(':', $lineaHorario, 2)), 2, ''); @endphp
                        <div class="ct-hours-row"><span class="day">{{ $diaHorario }}</span><span class="time">{{ $horaHorario }}</span></div>
                    @endforeach

                </div>

            </div>

        </div>

    </div>

</main>



{{-- Ancla menú: /contacto#mapa --}}
<section id="mapa" class="ct-map-section ct-anchor-section">

    <div class="container">

        <div class="ct-map-header">

            <span>Encuéntranos</span>

            <h2>Nuestra ubicación</h2>

        </div>

    </div>

    <div style="position:relative;">
        <div class="ct-map-wrap">
            @if(!empty($pc->mapa_embed_url))
                {!! $pc->mapa_embed_url !!}
            @else
                <iframe src="https://maps.google.com/maps?q=La%20Victoria%2C%20Lima%2C%20Per%C3%BA&z=15&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación Valento"></iframe>
            @endif
            <div class="ct-map-info">
                <h4><i class="fa fa-map-marker" style="color:#8C6B2E;"></i> Valento Stile S.A.C.</h4>
                <p><i class="fa fa-map-marker"></i> {!! nl2br(e($pc->ubicacion ?? 'La Victoria, Lima — Perú')) !!}</p>
                <p style="margin-top:6px;"><i class="fa fa-clock-o"></i> Lun–Sáb: 9am – 7pm</p>
            </div>
        </div>
    </div>

</section>




@include('web.partials.liam.footer')

{{-- Modal éxito --}}
@if(session('success'))
<div id="ct-success-modal" style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.55);">
    <div style="background:#fff;border-radius:16px;padding:48px 40px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;">
        <div style="width:72px;height:72px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 style="font-family:'Montserrat',sans-serif;font-size:20px;font-weight:700;color:#1a1a1a;margin:0 0 10px;">¡Mensaje enviado!</h3>
        <p style="font-family:'Montserrat',sans-serif;font-size:14px;color:#555;line-height:1.7;margin:0 0 28px;">Tu información fue enviada correctamente a nuestro equipo. Te responderemos a la brevedad.</p>
        <button onclick="document.getElementById('ct-success-modal').remove()" style="background:#c0392b;color:#fff;border:none;border-radius:8px;padding:13px 36px;font-family:'Montserrat',sans-serif;font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;">Aceptar</button>
    </div>
</div>
@endif

@endsection

@push('scripts')

<script src="{{ asset('temp02/js/contact.js') }}"></script>

@endpush


