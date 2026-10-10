<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Valento')</title>
    <meta name="description" content="@yield('meta_description', 'Valento — espirales PVC, micas, enmicados y selladoras. Encuadernación profesional a nivel nacional.')">
    <meta name="keywords" content="@yield('meta_keywords', 'marketing digital, redes sociales, Valento, Lima, Perú')">

    @include('web.partials.favicon')
    @include('web.partials.og_meta')
    <link rel="canonical" href="{{ url()->current() }}">

    @include('web.partials.head_landing')
    @yield('head_page')
    <style>
        html { scroll-behavior: smooth; }
        [id] { scroll-margin-top: 100px; }
        header .header-right > a.btn,
        .footer-top > a.btn {
            position: relative;
            z-index: 20;
        }
        a.btn--whatsapp {
            color: #fff;
        }
    </style>
    {{-- Aviso de cookies en todas las páginas --}}
    @cookieconsentscripts
</head>

<body>
    @yield('content')

    @include('web.partials.footer_js_landing')
    @stack('scripts')
    @yield('footer_page')
@cookieconsentview
</body>

</html>
