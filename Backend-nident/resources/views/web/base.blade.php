<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- Google Tag Manager -->

  <!-- End Google Tag Manager -->

  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Site Metas -->
  <title>Valento | Servicios de Marketing Digital </title>
  <meta name="keywords" content="Template Valento">
  <!--<meta name="description" content="">-->
  <meta name="description" content="@yield('meta_description', '')">
  <meta name="author" content="p-themes">
  <meta name="google-site-verification" content="Wl-4gCHlNEksJSa3RWvPtzk3MB1yc6kkdjWZUCkgULs" />
  <!-- Site Icons (configurados en head partial) -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Default CSS -->
  @section('head')
  @include('web.partials.head')
  @show
  @vite([
  'resources/sass/customise_01.scss'
  ,'resources/sass/login.scss'
  ,'resources/sass/whatsApp.scss'
  ,'resources/js/customise_01.js'
  ,'resources/js/web_cart_global.js'
  ])
  <!-- CSS Page para las paginas a instanciar -->
  @yield('head_page')

  <!-- Personalizador de tema: aplica a todas las páginas -->
  @vite(['resources/sass/web_customizer.scss', 'resources/js/web_customizer.js'])
  <script>
    window.PM_IS_ADMIN          = {{ ($isAdmin ?? false) ? 'true' : 'false' }};
    window.PM_CUSTOMIZER_STYLES = {!! ($customizerStyles ?? null) ? $customizerStyles : 'null' !!};
    window.PM_CSRF_TOKEN        = '{{ csrf_token() }}';
  </script>

  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-7ZMVYDPQ24"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'G-7ZMVYDPQ24');
  </script>
  <style>
    .clsColorSliderTitulos h2 {
      background: #fff;
      padding: 11px 15px 10px 25px;
      color: #000 !important;
      border-radius: 1rem;
    }
  </style>
  <link rel="canonical" href="{{ url()->current() }}">
  <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "WebSite",
      "name": "Valento",
      "legalName": "Valento Stile S.A.C.",
      "url": "https://rossety.pe/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "{search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
  </script>

  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Valento",
      "legalName": "Valento Stile S.A.C.",
      "url": "https://rossety.pe/",
      "logo": "https://rossety.pe/imagenes/logo-principal.png",
      "email": "{{ \App\Support\ContactoValento::ventasEmail() }}"
    }
  </script>



    {{-- Aviso de cookies en todas las páginas --}}
    @cookieconsentscripts
</head>

<body class="clsFondoGris">
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K9X6N4XB" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <!-- Header para todas las paginas -->
  @include('web.partials.header.menu')

  <div id="pm-content-area">
  @yield('content')
  </div>
  <!-- Footer para todas las paginas -->
  @include('web.partials.footer')

  <!-- Modal Carrito Global -->
  @include('web.partials.modal_cart_success')

  <!-- Codigo JS para todas las paginas -->
  @include('sweetalert::alert')
  @include('web.partials.footer_js')
  @stack('scripts')
  @yield('footer_page')


@cookieconsentview
</body>

</html>