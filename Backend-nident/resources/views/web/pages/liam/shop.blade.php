@extends('web.base_shop')
@section('title', $metaData->titulo_pagina ?? 'Tienda | Valento')
@section('head_page')
@php
    $catCss = public_path('temp02/css/categories.css');
    $catCssVer = file_exists($catCss) ? filemtime($catCss) : time();
@endphp
<link rel="stylesheet" href="{{ asset('temp02/css/categories.css') }}?v={{ $catCssVer }}">
{{-- fonts.css después de categories.css, como en la plantilla (título "Catálogo" en Cinzel) --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ $catCssVer }}">
<style>
/* Override crítico: 4 columnas en tienda (evita caché de reglas antiguas) */
.cat-page .cat-main #product-list.cat-grid {
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 32px 36px !important;
}
@media (max-width: 991px) {
    .cat-page .cat-main #product-list.cat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
}
/* En la tienda de la plantilla las tarjetas no llevan etiqueta NUEVO / POPULAR */
.cat-page .cat-card__badge-top { display: none; }
@media (max-width: 650px) {
    .cat-page .cat-main #product-list.cat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}
</style>
@endsection
@section('content')
@include('web.partials.liam.header')
<main class="cat-page">
    <div class="container-fluid" style="padding:0 30px;">
        <div class="cat-breadcrumb wow fadeInDown" data-wow-duration="0.5s" data-wow-delay="0.05s" style="margin-bottom: 1rem; margin-top: 1rem;">
            <a href="{{ url('/') }}">Inicio</a><span class="sep">&rsaquo;</span><span>Tienda</span>
        </div>
        <h1 class="cat-title wow fadeInUp" data-wow-duration="0.6s" data-wow-delay="0.1s">{{ $tituloCategoria ?? 'Catálogo' }}</h1>
        <div class="cat-layout">
            <aside class="cat-sidebar">
                <div id="galSidebar">
                    @include('web.pages.galeria.partials.filtros', [
                        'estructuraGaleriaCategoria' => $estructuraGaleriaCategoria ?? [],
                        'estructuraPromoGaleria' => $estructuraPromoGaleria ?? [],
                        'filtrosSeleccionados' => $filtrosSeleccionados ?? [],
                        'precioMin' => $precioMin ?? 0,
                        'precioMax' => $precioMax ?? 500,
                        'precioSelMin' => $precioSelMin ?? ($precioMin ?? 0),
                        'precioSelMax' => $precioSelMax ?? ($precioMax ?? 500),
                        'filtrosTalla' => $filtrosTalla ?? [],
                        'filtrosColor' => $filtrosColor ?? [],
                        'tallasSeleccionadas' => $tallasSeleccionadas ?? [],
                        'coloresSeleccionados' => $coloresSeleccionados ?? [],
                    ])
                </div>
            </aside>
            <div class="cat-main">
                {{-- Barra superior: contador + ordenar --}}
                <div class="cat-topbar">
                    <p class="cat-results-count">
                        <strong id="cat-total">{{ ($productosDeGaleria ?? null)?->total() ?? 0 }}</strong> productos encontrados
                    </p>
                    <div class="cat-sort-wrap">
                        <span class="cat-sort-label">Ordenar por:</span>
                        <div style="position:relative">
                            <select class="cat-sort-select" id="cat-order-select">
                                <option value="relevancia" {{ ($ordenSeleccionado??'relevancia') === 'relevancia' ? 'selected' : '' }}>Ordenar por</option>
                                <option value="precio_asc"  {{ ($ordenSeleccionado??'') === 'precio_asc'  ? 'selected' : '' }}>Menor Precio</option>
                                <option value="precio_desc" {{ ($ordenSeleccionado??'') === 'precio_desc' ? 'selected' : '' }}>Mayor Precio</option>
                                <option value="az"          {{ ($ordenSeleccionado??'') === 'az'          ? 'selected' : '' }}>A - Z</option>
                                <option value="za"          {{ ($ordenSeleccionado??'') === 'za'          ? 'selected' : '' }}>Z - A</option>
                            </select>
                            <i class="fa fa-chevron-down cat-sort-icon"></i>
                        </div>
                    </div>
                </div>
                <div id="productosDeGaleriaContainer">
                    @include('web.pages.liam.partials.shop_products', [
                        'productosDeGaleria' => $productosDeGaleria ?? collect(),
                        'promoBloqueId' => $promoBloqueId ?? null,
                    ])
                </div>
                <div id="paginationContainer">{!! ($productosDeGaleria ?? null)?->links() ?? '' !!}</div>
            </div>
        </div>
    </div>
</main>
@include('web.partials.liam.footer')
@endsection

@section('footer_page')
<script>
(function ($) {
    'use strict';

    var currentPage = parseInt('{{ request("page", 1) }}', 10) || 1;
    var currentOrder = @json($ordenSeleccionado ?? 'relevancia');
    var loadSeq = 0;
    var ajaxReq = null;
    var urlLibresForced = (function () {
        var params = new URLSearchParams(window.location.search);
        var f = (params.get('filtros') || '').split(',').map(function (s) { return s.trim(); });
        return f.indexOf('libres') !== -1;
    })();

    function urlHasLibres() {
        var params = new URLSearchParams(window.location.search);
        var f = (params.get('filtros') || '').split(',').map(function (s) { return s.trim(); });
        return f.indexOf('libres') !== -1;
    }

    function readFilters() {
        var active = document.querySelector('.filtro-nav-item.is-active');
        if (active) {
            var value = (active.getAttribute('data-value') || '').trim();
            if (value) return [value];
            // "Todos" activo: no forzar libres
            return [];
        }
        // Landing del popup (?filtros=libres) sin ítem de sidebar equivalente
        if (urlHasLibres() || urlLibresForced) return ['libres'];
        return [];
    }

    function setActiveNavItem(value) {
        var target = value || '';
        // Al elegir un filtro del menú, salimos del modo "libres" forzado
        if (target !== 'libres') {
            urlLibresForced = false;
        }
        document.querySelectorAll('.filtro-nav-item').forEach(function (el) {
            var v = (el.getAttribute('data-value') || '').trim();
            el.classList.toggle('is-active', v === target);
        });
    }

    function syncFiltrosInUrl(filters, attrs) {
        try {
            var url = new URL(window.location.href);
            if (filters && filters.length) {
                url.searchParams.set('filtros', filters.join(','));
            } else {
                url.searchParams.delete('filtros');
            }
            attrs = attrs || {};
            if (attrs.talla && attrs.talla.length) {
                url.searchParams.set('talla', attrs.talla.join(','));
            } else {
                url.searchParams.delete('talla');
            }
            if (attrs.color && attrs.color.length) {
                url.searchParams.set('color', attrs.color.join(','));
            } else {
                url.searchParams.delete('color');
            }
            if (attrs.precio_min != null && attrs.precio_min !== '') {
                url.searchParams.set('precio_min', attrs.precio_min);
            } else {
                url.searchParams.delete('precio_min');
            }
            if (attrs.precio_max != null && attrs.precio_max !== '') {
                url.searchParams.set('precio_max', attrs.precio_max);
            } else {
                url.searchParams.delete('precio_max');
            }
            history.replaceState(null, '', url.pathname + (url.search || '') + (url.hash || ''));
        } catch (e) {}
    }

    function loadProducts(page, resetPage, opts) {
        opts = opts || {};
        var restoreScroll = (opts.restoreScroll != null) ? opts.restoreScroll : null;
        var seq = ++loadSeq;
        if (ajaxReq && ajaxReq.readyState !== 4) {
            try { ajaxReq.abort(); } catch (e) {}
        }

        currentPage = resetPage ? 1 : (page || currentPage);
        var filters = readFilters();
        var ajaxUrl = @json(url('/tienda'));
        var pathMatch = window.location.pathname.match(/\/tienda\/([^/]+)(?:\/([^/]+))?/);
        if (pathMatch && pathMatch[1] && pathMatch[1] !== 'ofertas') {
            if (filters.length === 0) {
                ajaxUrl = @json(url('/tienda')) + '/' + pathMatch[1] + (pathMatch[2] ? '/' + pathMatch[2] : '');
            }
        }

        var $container = $('#productosDeGaleriaContainer');
        var $pagination = $('#paginationContainer');
        var paginationEl = $pagination.get(0);
        var lockPager = !resetPage && restoreScroll == null;
        var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        var finalScroll = (restoreScroll != null) ? restoreScroll : scrollY;
        var pagerTop = (lockPager && paginationEl) ? paginationEl.getBoundingClientRect().top : null;
        var lockHeight = Math.ceil($container.outerHeight() || 0);

        // Evita el “scroll anchoring” del navegador (empuja la vista hacia abajo)
        document.documentElement.style.overflowAnchor = 'none';
        document.body.style.overflowAnchor = 'none';
        if (lockHeight > 0) {
            $container.css({ minHeight: lockHeight + 'px', overflowAnchor: 'none' });
        }
        $container.css({ opacity: 0.45, pointerEvents: 'none' });

        var data = {
            page: currentPage,
            orden: currentOrder,
            ajax: 1
        };
        if (filters.length) {
            data.filtros = filters.join(',');
        }
        var tallas = [];
        document.querySelectorAll('.cat-chk-talla:checked').forEach(function (el) {
            if (el.value) tallas.push(el.value);
        });
        var colores = [];
        document.querySelectorAll('.cat-chk-color:checked').forEach(function (el) {
            if (el.value) colores.push(el.value);
        });
        if (tallas.length) data.talla = tallas.join(',');
        if (colores.length) data.color = colores.join(',');
        var pMinEl = document.getElementById('cat-precio-min');
        var pMaxEl = document.getElementById('cat-precio-max');
        var rangeEl = document.getElementById('cat-price-range');
        if (rangeEl) {
            data.precio_min = rangeEl.getAttribute('data-precio-min') || (pMinEl ? pMinEl.value : '');
            data.precio_max = rangeEl.value;
            if (pMaxEl) pMaxEl.value = rangeEl.value;
            var label = document.getElementById('cat-price-max');
            if (label) label.textContent = 'S/ ' + rangeEl.value;
        } else {
            if (pMinEl && pMinEl.value !== '') data.precio_min = pMinEl.value;
            if (pMaxEl && pMaxEl.value !== '') data.precio_max = pMaxEl.value;
        }
        var params = new URLSearchParams(window.location.search);
        var q = params.get('q');
        if (q) data.q = q;

        if (!opts.skipUrlSync) {
            syncFiltrosInUrl(filters, {
                talla: tallas,
                color: colores,
                precio_min: data.precio_min,
                precio_max: data.precio_max
            });
        }

        function pinPager() {
            if (pagerTop === null) return;
            var el = $pagination.get(0);
            if (!el) return;
            var nowTop = el.getBoundingClientRect().top;
            var delta = nowTop - pagerTop;
            if (Math.abs(delta) > 0.5) {
                window.scrollTo(0, Math.max(0, (window.pageYOffset || 0) + delta));
            }
        }

        ajaxReq = $.ajax({
            url: ajaxUrl,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: data,
            success: function (res) {
                if (seq !== loadSeq) return;
                var r = (typeof res === 'string') ? JSON.parse(res) : res;
                if (!(r && r.productosDeGaleria)) return;

                $container.html(r.productosDeGaleria);
                $pagination.html(r.pagination || '');
                if (r.total !== undefined) {
                    $('#cat-total').text(r.total);
                }

                // Si la nueva página es más baja, mantiene la altura previa
                // para que el documento NO encoja y el scroll no salte al footer
                var newH = Math.ceil($container.outerHeight() || 0);
                if (lockPager && lockHeight > 0 && newH < lockHeight) {
                    $container.css('min-height', lockHeight + 'px');
                } else if (!lockPager) {
                    $container.css('min-height', '');
                } else {
                    $container.css('min-height', '');
                }

                // Congela el scroll en la misma posición visual (sin animación)
                window.scrollTo(0, finalScroll);
                pinPager();
                requestAnimationFrame(function () {
                    window.scrollTo(0, finalScroll);
                    pinPager();
                    requestAnimationFrame(pinPager);
                });
            },
            complete: function () {
                if (seq !== loadSeq) return;
                $container.css({ opacity: 1, pointerEvents: '' });
                // Re-pin por si imágenes/lazy cambian el layout un instante después
                if (lockPager) {
                    setTimeout(pinPager, 50);
                    setTimeout(pinPager, 200);
                }
                // Restauración de estado (volver desde el detalle) o guardado normal
                if (restoreScroll != null) {
                    setTimeout(function () { window.scrollTo(0, restoreScroll); }, 60);
                    setTimeout(function () { window.scrollTo(0, restoreScroll); saveState(); }, 220);
                } else {
                    saveState();
                }
            }
        });
    }

    $('#cat-order-select').on('change', function () {
        currentOrder = $(this).val();
        loadProducts(1, true);
    });

    // Menú vertical: una sola opción activa por clic
    $(document).on('click', '.filtro-nav-item', function (e) {
        e.preventDefault();
        var value = ($(this).attr('data-value') || '').trim();
        setActiveNavItem(value);
        loadProducts(1, true);
    });

    $('#cat-clear-filtros').on('click', function () {
        setActiveNavItem('');
        document.querySelectorAll('.cat-chk-talla, .cat-chk-color').forEach(function (el) {
            el.checked = false;
        });
        var rangeEl = document.getElementById('cat-price-range');
        if (rangeEl) {
            var max = rangeEl.getAttribute('data-precio-max') || rangeEl.max;
            rangeEl.value = max;
            var label = document.getElementById('cat-price-max');
            if (label) label.textContent = 'S/ ' + max;
            var pMaxEl = document.getElementById('cat-precio-max');
            if (pMaxEl) pMaxEl.value = max;
            var pMinEl = document.getElementById('cat-precio-min');
            if (pMinEl) pMinEl.value = rangeEl.getAttribute('data-precio-min') || rangeEl.min;
        }
        loadProducts(1, true);
    });

    $(document).on('change', '.cat-chk-talla, .cat-chk-color', function () {
        loadProducts(1, true);
    });

    var priceTimer = null;
    $(document).on('input change', '#cat-price-range', function () {
        var v = this.value;
        var label = document.getElementById('cat-price-max');
        if (label) label.textContent = 'S/ ' + v;
        var pMaxEl = document.getElementById('cat-precio-max');
        if (pMaxEl) pMaxEl.value = v;
        clearTimeout(priceTimer);
        priceTimer = setTimeout(function () {
            loadProducts(1, true);
        }, 280);
    });

    $(document).on('click', '#paginationContainer a', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var href = $(this).attr('href');
        if (!href || href === '#') return;
        var page = 1;
        try {
            var url = new URL(href, window.location.origin);
            page = parseInt(url.searchParams.get('page') || '1', 10) || 1;
        } catch (err) {
            var m = String(href).match(/[?&]page=(\d+)/);
            page = m ? parseInt(m[1], 10) : 1;
        }
        loadProducts(page, false);
    });

    // Por si el navegador intenta restaurar scroll al cambiar history
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    $('.cat-filter__head').on('click', function () {
        $(this).closest('.cat-filter').toggleClass('open');
    });

    // ============================================================
    //  PERSISTENCIA DE ESTADO DE LA GALERÍA (filtro / orden / página / scroll)
    //  Permite "Volver atrás" desde el detalle conservando la selección,
    //  y recordar el filtro al regresar a la tienda más tarde.
    // ============================================================
    var LV_STATE_KEY  = 'lvShopState';
    var LV_RETURN_KEY = 'lvShopReturnUrl';

    function normPath(p) { return String(p || '').replace(/\/+$/, '') || '/'; }

    function saveState() {
        try {
            var filters = readFilters();
            var st = {
                filter: filters.length ? filters[0] : '',
                order: currentOrder,
                page: currentPage,
                scrollY: window.pageYOffset || document.documentElement.scrollTop || 0,
                path: normPath(window.location.pathname)
            };
            sessionStorage.setItem(LV_STATE_KEY, JSON.stringify(st));
            sessionStorage.setItem(LV_RETURN_KEY, window.location.pathname + window.location.search);
        } catch (e) {}
    }

    function restoreState() {
        var raw;
        try { raw = sessionStorage.getItem(LV_STATE_KEY); } catch (e) { raw = null; }
        if (!raw) return;
        var st;
        try { st = JSON.parse(raw); } catch (e) { return; }
        if (!st) return;

        var here      = normPath(window.location.pathname);
        var savedPath = normPath(st.path);
        var isGeneric = /^\/tienda$/.test(here);
        // Solo restauramos el filtro si estamos en el mismo listado (o en la tienda genérica),
        // para no pisar una navegación intencional a otra categoría por URL.
        if (savedPath !== here && !isGeneric) return;

        // Landing del popup: ?filtros=libres no debe pisarse con un filtro promo guardado
        if (urlHasLibres() || st.filter === 'libres') {
            urlLibresForced = true;
            document.querySelectorAll('.filtro-nav-item').forEach(function (el) {
                el.classList.remove('is-active');
            });
            try {
                st.filter = 'libres';
                sessionStorage.setItem(LV_STATE_KEY, JSON.stringify(st));
            } catch (e) {}
            // Solo orden/página/scroll; el listado ya viene filtrado por el servidor
            var needReloadLibres = false;
            if (st.order) {
                currentOrder = st.order;
                $('#cat-order-select').val(st.order);
            }
            var targetPageL = parseInt(st.page, 10) || 1;
            var targetScrollL = parseInt(st.scrollY, 10) || 0;
            if (targetPageL > 1) needReloadLibres = true;
            if (needReloadLibres) {
                loadProducts(targetPageL, false, { restoreScroll: targetScrollL, skipUrlSync: true });
            } else if (targetScrollL > 0) {
                requestAnimationFrame(function () { window.scrollTo(0, targetScrollL); });
                setTimeout(function () { window.scrollTo(0, targetScrollL); }, 60);
            }
            return;
        }

        var activeEl = document.querySelector('.filtro-nav-item.is-active');
        var serverFilter = (activeEl ? (activeEl.getAttribute('data-value') || '') : '').trim();
        var serverOrder = currentOrder;
        var needReload  = false;

        if (st.order) {
            currentOrder = st.order;
            $('#cat-order-select').val(st.order);
            if (st.order !== serverOrder) needReload = true;
        }

        var targetFilter = (typeof st.filter === 'string') ? st.filter : '';
        // Nunca restaurar un filtro promo/categoría sobre una URL con libres
        if (targetFilter === 'libres') {
            urlLibresForced = true;
            return;
        }
        var filterExists = targetFilter === '' ||
            document.querySelector('.filtro-nav-item[data-value="' + targetFilter.replace(/"/g, '') + '"]');
        if (filterExists && targetFilter !== serverFilter) {
            setActiveNavItem(targetFilter);
            needReload = true;
        }

        var targetPage = parseInt(st.page, 10) || 1;
        if (targetPage > 1) needReload = true;
        var targetScroll = parseInt(st.scrollY, 10) || 0;

        if (needReload) {
            loadProducts(targetPage, false, { restoreScroll: targetScroll });
        } else if (targetScroll > 0) {
            requestAnimationFrame(function () { window.scrollTo(0, targetScroll); });
            setTimeout(function () { window.scrollTo(0, targetScroll); }, 60);
            setTimeout(function () { window.scrollTo(0, targetScroll); }, 220);
        }
    }

    // Guardar al salir hacia el detalle de un producto (o cualquier navegación)
    $(document).on('mousedown', '.cat-card a[href], #product-list a[href]', function () { saveState(); });
    window.addEventListener('pagehide', saveState);

    // Restaurar scroll si el navegador restaura la página desde bfcache
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        try {
            var st = JSON.parse(sessionStorage.getItem(LV_STATE_KEY) || 'null');
            if (st && st.scrollY) { window.scrollTo(0, st.scrollY); }
        } catch (err) {}
    });

    // Restaurar al cargar la tienda
    $(function () { restoreState(); });
})(jQuery);
</script>
@endsection
