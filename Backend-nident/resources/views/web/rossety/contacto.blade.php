<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contacto | Detalles Rossety</title>
    <meta name="description" content="{{ \App\Support\RossetyContacto::config()['hero']['subtitulo'] }}" />
    <link rel="shortcut icon" href="{{ asset('rossety/assets/images/fav.png') }}" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/fill/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/base.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/pagehero.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/components.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/shop.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/home.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/pages.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/cursor.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/loader.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/cart.css') }}" />
</head>
<body>
    <div class="rs-loader" role="status" aria-live="polite" aria-label="Cargando Detalles Rossety">
        <span class="rs-loader__panel rs-loader__panel--l"></span>
        <span class="rs-loader__panel rs-loader__panel--r"></span>
        <span class="rs-loader__bg"></span>
        <div class="js-loader-hearts"></div>
        <div class="rs-loader__box">
            <div class="rs-loader__logo-wrap"><span class="rs-loader__ring"></span><img class="rs-loader__logo" src="{{ $rsLogo }}" alt="Detalles Rossety" /></div>
            <h2 class="rs-loader__title">Detalles Rossety</h2>
            <p class="rs-loader__msg js-loader-msg">Preparando tus detalles...</p>
            <div class="rs-loader__bar"><span class="rs-loader__fill"></span><span class="rs-loader__runner"><i class="ph-fill ph-gift"></i></span></div>
            <p class="rs-loader__count js-loader-count">0%</p>
        </div>
    </div>

    @include('web.rossety.partials.header')


@php
    $c = \App\Support\RossetyContacto::config();
    $redes = \App\Support\RossetyContacto::redes();
    $h = $c['hero']; $fm = $c['form']; $mp = $c['mapa'];
@endphp
    <section class="rs-pagehero">
        <div class="rs-container">
            <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><span>{{ $h['crumb'] }}</span></div>
            <h1 class="rs-pagehero__title">{{ $h['titulo'] }}</h1>
            <p class="rs-pagehero__text">{{ $h['subtitulo'] }}</p>
        </div>
    </section>
    <main>
        <section class="rs-section">
            <div class="rs-container rs-contact">
                <div class="rs-contact__cards">
                    @foreach($c['cards'] as $card)
                    @php($lineas = preg_split('/\r\n|\r|\n/', trim((string) ($card['texto'] ?? ''))))
                    <div class="rs-info"><div class="rs-info__icon"><i class="ph-fill {{ $card['icono'] ?? 'ph-info' }}"></i></div><div><h3 class="rs-info__title">{{ $card['titulo'] ?? '' }}</h3><p class="rs-info__text">@foreach($lineas as $i => $linea)@if($i === 0 && !empty($card['url']))<a href="{{ $card['url'] }}">{{ $linea }}</a>@else{{ $linea }}@endif @if(!$loop->last)<br>@endif @endforeach</p></div></div>
                    @endforeach
                    @if(count($redes))
                    <div class="rs-info"><div class="rs-info__icon"><i class="ph-fill ph-share-network"></i></div><div><h3 class="rs-info__title">{{ $c['redes_titulo'] }}</h3><p class="rs-info__text">@foreach($redes as $red)<a href="{{ $red['url'] }}" target="_blank" rel="noopener">{{ $red['etiqueta'] ?? ucfirst($red['tipo'] ?? '') }}</a>@if(!$loop->last) · @endif @endforeach</p></div></div>
                    @endif
                </div>
                <form class="rs-form js-contact-form" data-saludo="{{ $fm['wa_saludo'] }}">
                    <h2 class="rs-form__title">{{ $fm['titulo'] }}</h2>
                    <p class="rs-form__sub">{{ $fm['subtitulo'] }}</p>
                    <div class="rs-form__grid">
                        <div class="rs-form__field"><label class="rs-form__label" for="f-nombre">{{ $fm['label_nombre'] }}</label><input class="rs-form__control" id="f-nombre" name="nombre" required placeholder="{{ $fm['ph_nombre'] }}" /></div>
                        <div class="rs-form__field"><label class="rs-form__label" for="f-tel">{{ $fm['label_telefono'] }}</label><input class="rs-form__control" id="f-tel" name="telefono" type="tel" placeholder="{{ $fm['ph_telefono'] }}" /></div>
                        <div class="rs-form__field"><label class="rs-form__label" for="f-motivo">{{ $fm['label_motivo'] }}</label>
                            <select class="rs-form__control" id="f-motivo" name="motivo" required>
                                @foreach($fm['motivos'] as $motivo)<option>{{ $motivo }}</option>@endforeach
                            </select></div>
                        <div class="rs-form__field"><label class="rs-form__label" for="f-fecha">{{ $fm['label_fecha'] }}</label><input class="rs-form__control" id="f-fecha" name="fecha" type="date" /></div>
                        <div class="rs-form__field rs-form__field--full"><label class="rs-form__label" for="f-msg">{{ $fm['label_mensaje'] }}</label><textarea class="rs-form__control rs-form__area" id="f-msg" name="mensaje" required placeholder="{{ $fm['ph_mensaje'] }}"></textarea></div>
                    </div>
                    <div class="rs-form__actions"><button class="rs-btn rs-btn--wa rs-btn--block" type="submit"><i class="ph-fill ph-whatsapp-logo"></i>{{ $fm['boton'] }}</button></div>
                    <p class="rs-form__hint">{{ $fm['hint'] }}</p>
                </form>
            </div>
            @if(trim($mp['src']) !== '')
            <div class="rs-container">
                <div class="rs-map"><iframe title="{{ $mp['titulo'] }}" loading="lazy" src="{{ $mp['src'] }}"></iframe></div>
            </div>
            @endif
        </section>

    </main>

 
    @include('web.rossety.partials.footer')
    <a class="rs-wa-float js-wa js-wa-float" href="#" aria-label="Escríbenos por WhatsApp">
        <span class="rs-wa-float__pulse"></span>
        <span class="rs-wa-float__icon"><i class="fa fa-whatsapp" aria-hidden="true"></i></span>
        <span class="rs-wa-float__label"><b>¿Buscas un detalle especial?</b><small>Escríbenos y te atendemos al instante.</small></span>
    </a>
    <button class="rs-top-btn js-top" type="button" aria-label="Subir"><i class="ph-bold ph-caret-up"></i></button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    @include('web.rossety.partials.catalog')
    <script src="{{ asset('rossety/assets/js/rossety/core.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cms.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cart.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/contact.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
