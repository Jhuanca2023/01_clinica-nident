<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $metaData->titulo_pagina ?? 'Valento | Ropa laboral y textil')</title>
    <meta name="description" content="@yield('meta_description', $metaData->descripcion_pagina ?? 'Valento - Ropa laboral y textil de calidad')">
    @include('web.partials.favicon')
    @include('web.partials.og_meta')
    @include('web.partials.liam.head')
    @yield('head_page')
    {{-- Aviso de cookies en todas las páginas --}}
    @cookieconsentscripts
</head>
<body>
    {{-- ── Preloader Valento Luxury ─────────────────── --}}
    <div id="preloder">
        <div class="loader">
            <img src="{{ asset('temp02/img/inicio/logo-valento.svg') }}" alt="Valento" class="loader-logo">
            <div class="loader-ring"></div>
            <span class="loader-tag">Semplicemente Valento</span>
        </div>
    </div>

    {{-- Cart Sidebar --}}
    <style>
    /* Nombre del producto con el estilo de la plantilla (sigue siendo enlace al detalle) */
    .lv-cart-item__name-link { color: inherit; text-decoration: none; }
    .lv-cart-item__name-link:hover { color: #8C6B2E; }
    </style>
    <div id="lv-cart-overlay" class="lv-cart-overlay"></div>
    <div id="lv-cart-sidebar" class="lv-cart-sidebar">
        <div class="lv-cart-sidebar__head">
            <i class="fa fa-shopping-bag"></i><h3>Carrito</h3>
            <button id="lv-cart-close" class="lv-cart-sidebar__close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="lv-cart-sidebar__body" id="lv-cart-body">
            <div class="lv-cart-empty">
                <i class="fa fa-shopping-bag"></i>
                <span>Tu carrito está vacío</span>
            </div>
        </div>
        <div class="lv-cart-sidebar__foot" id="lv-cart-foot" style="display:none;">
            <div class="lv-cart-subtotal-row">
                <span class="lv-subtotal-label">Subtotal</span>
                <span class="lv-subtotal-value" id="lv-cart-total-val">S/ 0.00</span>
            </div>
            <a href="{{ url('/shop-detail-lista') }}" class="lv-cart-btn-primary">IR AL CARRITO</a>
        </div>
    </div>

    @yield('content')
    @include('web.partials.liam.popup_promo')
    @include('web.partials.liam.scripts')
    @yield('footer_page')
    @stack('scripts')
    <script>
    (function () {
        function hidePreloader() {
            var el = document.getElementById('preloder');
            if (!el || el._hiding) return;
            el._hiding = true;
            el.style.transition = 'opacity 0.6s ease, visibility 0.6s ease';
            el.style.opacity = '0';
            el.style.visibility = 'hidden';
            setTimeout(function () {
                el.style.display = 'none';
            }, 600);
        }
        var minTime = 600;
        var startTime = Date.now();
        function tryHide() {
            var elapsed = Date.now() - startTime;
            var wait = Math.max(0, minTime - elapsed);
            setTimeout(hidePreloader, wait);
        }
        document.addEventListener('DOMContentLoaded', tryHide);
        window.addEventListener('load', tryHide);
        setTimeout(hidePreloader, 1500);

        // Mostrar el preloader al instante al navegar a otra página del sitio
        var showTimer = null;
        function showPreloader() {
            var el = document.getElementById('preloder');
            if (!el) return;
            el._hiding = false;
            el.style.transition = 'none';
            el.style.display = 'flex';
            el.style.visibility = 'visible';
            el.style.opacity = '1';
            var loader = el.querySelector('.loader');
            if (loader) loader.style.display = '';
            // Si la navegación no ocurre (descarga, error), no dejar la pantalla bloqueada
            clearTimeout(showTimer);
            showTimer = setTimeout(hidePreloader, 10000);
        }

        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (!a || a.hasAttribute('download')) return;
            if (a.target && a.target !== '_self') return;
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return;
            var url = new URL(a.href, location.href);
            if (url.origin !== location.origin) return;
            // Mismo documento, solo cambia el ancla
            if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
            // Esperar a que terminen los demás handlers (carrito, favoritos) por si cancelan la navegación
            setTimeout(function () {
                if (!e.defaultPrevented) showPreloader();
            }, 0);
        });

        // Al volver con el botón "atrás" (página en caché) ocultarlo
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                clearTimeout(showTimer);
                hidePreloader();
            }
        });
    })();
    </script>
@cookieconsentview
</body>
</html>
