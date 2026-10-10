<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nosotros | Detalles Rossety</title>
    <meta name="description" content="{{ \App\Support\RossetyNosotros::config()['hero']['subtitulo'] }}" />
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
    $n = \App\Support\RossetyNosotros::config();
    $h = $n['hero']; $hi = $n['historia']; $va = $n['valores']; $li = $n['lineas'];
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
            <div class="rs-container rs-about">
                <div class="rs-about__media">
                    @if($hi['imagen_url'] !== '')<img src="{{ $hi['imagen_url'] }}" alt="{{ $hi['imagen_alt'] }}" />@endif
                    @if(trim($hi['badge_num'].$hi['badge_txt']) !== '')<div class="rs-about__badge"><strong>{{ $hi['badge_num'] }}</strong><span>{{ $hi['badge_txt'] }}</span></div>@endif
                </div>
                <div class="rs-about__text">
                    <span class="rs-head__eyebrow">{{ $hi['eyebrow'] }}</span>
                    <h2 class="rs-head__title">{{ $hi['titulo'] }}</h2>
                    @foreach($hi['parrafos'] as $parrafo)@if(trim($parrafo) !== '')<p>{{ $parrafo }}</p>@endif @endforeach
                    <ul class="rs-about__list">
                        @foreach($hi['lista'] as $item)@if(trim($item) !== '')<li><i class="ph-fill ph-check-circle"></i>{{ $item }}</li>@endif @endforeach
                    </ul>
                </div>
            </div>
        </section>

        @if(count($n['stats']))
        <section class="rs-section rs-section--red">
            <div class="rs-container rs-stats">
                @foreach($n['stats'] as $s)<div class="rs-stat"><strong>{{ $s['num'] ?? '' }}</strong><span>{{ $s['label'] ?? '' }}</span></div>@endforeach
            </div>
        </section>
        @endif

        @if(count($va['items']))
        <section class="rs-section rs-section--cream">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">{{ $va['eyebrow'] }}</span><h2 class="rs-head__title">{{ $va['titulo'] }}</h2></div>
                <div class="rs-values">
                    @foreach($va['items'] as $v)<div class="rs-value"><i class="ph {{ $v['icono'] ?? 'ph-heart' }}"></i><h3 class="rs-value__title">{{ $v['titulo'] ?? '' }}</h3><p class="rs-value__text">{{ $v['texto'] ?? '' }}</p></div>@endforeach
                </div>
            </div>
        </section>
        @endif

        @if(count($li['items']))
        <section class="rs-section">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">{{ $li['eyebrow'] }}</span><h2 class="rs-head__title">{{ $li['titulo'] }}</h2></div>
                <div class="rs-lines">
                    @foreach($li['items'] as $l)<a class="rs-line" href="{{ $l['url'] ?? '/tienda' }}"><i class="ph {{ $l['icono'] ?? 'ph-gift' }}"></i><div><strong>{{ $l['titulo'] ?? '' }}</strong><span>{{ $l['sub'] ?? '' }}</span></div></a>@endforeach
                </div>
            </div>
        </section>
        @endif

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
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
