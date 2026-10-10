@extends('web.base')

@section('head_page')
@section('title', $metaData->titulo_pagina ?? 'Valento')
@section('meta_description', $metaData->descripcion_pagina ?? '')
@vite(['resources/sass/web_gallery.scss'])
@endsection
<!-- COOKIES AND POLICE : HEADER-->

@section('content')
@php $activeView = in_array(request('view'), ['grid1','grid2','grid3','grid4']) ? request('view') : 'grid3'; @endphp
@include('web.partials.breadcrumb')

<div id="main_content" class="gal-root">
    <div class="container">
        <div class="row gal-layout">

            {{-- ══ SIDEBAR DE FILTROS ══ --}}
            <aside class="col-lg-3 gal-sidebar-col">

                {{-- Toggle mobile --}}
                <button class="gal-sidebar-toggle d-lg-none" id="galSidebarToggle">
                    <i class="fas fa-sliders-h"></i> Filtros
                    <span class="gal-active-count" id="activeFilterCount" style="display:none">0</span>
                </button>

                <div class="gal-sidebar" id="galSidebar">
                    <div class="gal-sidebar__header">
                        <span class="gal-sidebar__title"><i class="fas fa-filter"></i> Filtrar por</span>
                        <a href="#" id="limpiar-filtros" class="gal-sidebar__clear">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>

                    @include('web.pages.galeria.ajax.filtro_categoria')
                </div>
            </aside>

            {{-- ══ ÁREA DE PRODUCTOS ══ --}}
            <div class="col-lg-9 gal-content-col">

                {{-- Toolbar --}}
                <div class="gal-toolbar">
                    <div class="gal-toolbar__views">
                        <button type="button" class="gal-view-btn {{ $activeView == 'grid1' ? 'active' : '' }}" data-view="grid1" title="Lista">
                            <i class="fas fa-list"></i>
                        </button>
                        <button type="button" class="gal-view-btn {{ $activeView == 'grid2' ? 'active' : '' }}" data-view="grid2" title="2 columnas">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="gal-view-btn {{ $activeView == 'grid3' ? 'active' : '' }}" data-view="grid3" title="3 columnas">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="gal-view-btn {{ $activeView == 'grid4' ? 'active' : '' }}" data-view="grid4" title="4 columnas">
                            <i class="fas fa-border-all"></i>
                        </button>
                    </div>

                    <div class="gal-toolbar__count">
                        <i class="fas fa-box-open"></i>
                        <span class="cantidadTxt">{{ $productosDeGaleria->total() }}</span> productos
                    </div>

                    <div class="gal-toolbar__sort">
                        <label for="orden"><i class="fas fa-sort-amount-down"></i></label>
                        <select id="orden" name="sortby">
                            <option value="relevancia" {{ request('orden') == 'relevancia' ? 'selected' : '' }}>Relevancia</option>
                            <option value="precio_asc" {{ request('orden') == 'precio_asc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                            <option value="precio_desc" {{ request('orden') == 'precio_desc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                        </select>
                    </div>
                </div>

                {{-- Chips de filtros activos --}}
                <div class="gal-active-filters" id="activeFiltersBar" style="display:none"></div>

                {{-- Spinner --}}
                <div id="loading" class="gal-loading" style="display:none">
                    <div class="gal-spinner"></div>
                    <span>Cargando...</span>
                </div>

                {{-- Productos --}}
                <div id="productos-container" class="gal-products view-{{ $activeView }}">
                    @include('web.pages.galeria.ajax.productos_' . $activeView)
                </div>

                {{-- Paginación --}}
                <div id="pagination-container" class="gal-pagination">
                    {{ $productosDeGaleria->appends(request()->query())->links() }}
                </div>

            </div>{{-- /col-lg-9 --}}

        </div>{{-- /row --}}
    </div>
</div>

@endsection
<!-- COOKIES AND POLICE: FOOTER -->

@section('footer_page')
<script src="{{ URL::asset('assets/js/index/index.js') }}?v={{ is_file($f = public_path('assets/js/index/index.js')) ? filemtime($f) : time() }}"></script>
<script>
(function () {
    'use strict';

    // ── Estado ──────────────────────────────────────────────
    var currentView    = '{{ $activeView }}';
    var currentOrder   = '{{ request("orden", "relevancia") }}';
    var currentPage    = parseInt('{{ request("page", 1) }}', 10);
    var currentFilters = [];
    /** Evita condiciones de carrera: cada load incrementa; solo la respuesta con seq actual aplica. */
    var galleryLoadSeq   = 0;
    var galleryAjaxReq = null;

    // ── Helpers URL ─────────────────────────────────────────
    function getCatSubFromURL() {
        var m = window.location.pathname.match(/\/web_gallery\/(\d+)(?:\/(\d+))?/);
        return m ? { cat: m[1] || null, sub: m[2] || null } : { cat: null, sub: null };
    }

    // ── Leer filtros activos desde el DOM ───────────────────
    function readFilters() {
        var checked = [];
        document.querySelectorAll('.filtro-subcategoria:checked').forEach(function (el) {
            checked.push(el.value);
        });
        document.querySelectorAll('.filtro-titulo.active').forEach(function (el) {
            checked.push(el.getAttribute('data-value'));
        });
        return checked;
    }

    // ── Actualizar chips de filtros activos ─────────────────
    function renderActiveFilters(filters) {
        var bar = document.getElementById('activeFiltersBar');
        var cnt = document.getElementById('activeFilterCount');
        if (!filters.length) {
            bar.style.display = 'none';
            if (cnt) cnt.style.display = 'none';
            return;
        }
        bar.style.display = 'flex';
        if (cnt) { cnt.textContent = filters.length; cnt.style.display = 'inline-flex'; }

        var html = '<span class="gal-af-label">Activos:</span>';
        filters.forEach(function (f) {
            var esc = (window.CSS && CSS.escape) ? CSS.escape(f) : f.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
            var el = document.querySelector('.filtro-subcategoria[value="' + esc + '"]') ||
                     document.querySelector('.filtro-titulo[data-value="' + esc + '"]');
            var label = f;
            if (el) {
                if (el.tagName === 'INPUT' && el.id) {
                    var idEsc = (window.CSS && CSS.escape) ? CSS.escape(el.id) : el.id.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
                    var lb = document.querySelector('label[for="' + idEsc + '"]');
                    if (lb) label = lb.textContent.trim();
                } else if (el.closest('label')) {
                    label = el.closest('label').textContent.trim();
                } else {
                    var t = el.querySelector('span:not(.badge)');
                    label = t ? t.textContent.trim() : el.textContent.trim();
                }
            }
            if (!label) label = f;
            html += '<span class="gal-af-chip" data-filter="' + f + '">' +
                    label + ' <i class="fas fa-times gal-af-remove"></i></span>';
        });
        bar.innerHTML = html;
    }

    // ── Cargar productos vía AJAX ───────────────────────────
    function loadProducts(page, resetPage) {
        var seq = ++galleryLoadSeq;
        if (galleryAjaxReq && galleryAjaxReq.readyState !== 4) {
            try { galleryAjaxReq.abort(); } catch (e) {}
        }

        currentPage = resetPage ? 1 : (page || currentPage);
        currentFilters = readFilters();

        var cs = getCatSubFromURL();
        var ajaxUrl = '{{ route("web_gallery") }}';
        if (cs.cat) ajaxUrl = '{{ url("/web_gallery") }}/' + cs.cat + (cs.sub ? '/' + cs.sub : '');

        document.getElementById('loading').style.display = 'flex';
        var container = document.getElementById('productos-container');
        container.style.opacity = '0.4';
        container.style.pointerEvents = 'none';
        document.getElementById('pagination-container').style.visibility = 'hidden';

        var ajaxData = {
            page:  currentPage,
            view:  currentView,
            orden: currentOrder,
            ajax:  1
        };
        if (currentFilters.length) {
            ajaxData.filtros = currentFilters.join(',');
        }
        var urlQ = new URLSearchParams(window.location.search).get('q');
        if (urlQ) {
            ajaxData.q = urlQ;
        }

        galleryAjaxReq = $.ajax({
            url: ajaxUrl,
            type: 'GET',
            data: ajaxData,
            success: function (res) {
                if (seq !== galleryLoadSeq) return;
                try {
                    var r = (typeof res === 'string') ? JSON.parse(res) : res;
                    if (r && r.productosDeGaleria) {
                        container.innerHTML = r.productosDeGaleria;
                        document.getElementById('pagination-container').innerHTML = r.pagination || '';
                        if (r.total !== undefined) {
                            document.querySelectorAll('.cantidadTxt').forEach(function (el) {
                                el.textContent = r.total;
                            });
                        }
                    }
                } catch (e) {
                    container.innerHTML = res;
                }
                currentFilters = readFilters();
                updateURL();
                renderActiveFilters(currentFilters);
            },
            error: function (xhr, status) {
                if (seq !== galleryLoadSeq) return;
                if (status === 'abort') return;
                container.innerHTML = '<div class="gal-no-results"><i class="fas fa-exclamation-circle"></i><p>Error al cargar productos. Intenta nuevamente.</p></div>';
            },
            complete: function (xhr, status) {
                if (seq !== galleryLoadSeq) return;
                document.getElementById('loading').style.display = 'none';
                container.style.opacity = '1';
                container.style.pointerEvents = '';
                document.getElementById('pagination-container').style.visibility = 'visible';

                if (status !== 'abort' && typeof add_additional_img === 'function') {
                    add_additional_img();
                }
            }
        });
    }

    // ── Actualizar URL sin recargar ──────────────────────────
    function updateURL() {
        var params = new URLSearchParams();
        if (currentFilters.length) params.set('filtros', currentFilters.join(','));
        if (currentPage > 1)        params.set('page',    currentPage);
        if (currentView !== 'grid3') params.set('view',   currentView);
        if (currentOrder !== 'relevancia') params.set('orden', currentOrder);

        var base = '{{ url("/web_gallery") }}';
        var cs = getCatSubFromURL();
        if (cs.cat) base += '/' + cs.cat + (cs.sub ? '/' + cs.sub : '');

        var newUrl = base + (params.toString() ? '?' + params.toString() : '');
        if (newUrl !== window.location.href) history.pushState({ path: newUrl }, '', newUrl);
    }

    // ── Init vista ──────────────────────────────────────────
    function initView() {
        document.querySelectorAll('.gal-view-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-view') === currentView);
        });
        var c = document.getElementById('productos-container');
        c.className = c.className.replace(/view-grid\d/g, '').trim() + ' view-' + currentView;
    }

    // ── Init desde URL ──────────────────────────────────────
    function initFromURL() {
        var params = new URLSearchParams(window.location.search);
        var cs = getCatSubFromURL();

        var urlFilters = [];
        if (params.get('filtros')) {
            urlFilters = params.get('filtros').split(',').filter(Boolean);
        } else if (cs.cat) {
            urlFilters = [cs.cat + (cs.sub ? '-' + cs.sub : '-null')];
        }

        urlFilters.forEach(function (f) {
            var cb = document.querySelector('.filtro-subcategoria[value="' + f + '"]');
            if (cb) { cb.checked = true; return; }
            var tl = document.querySelector('.filtro-titulo[data-value="' + f + '"]');
            if (tl) tl.classList.add('active');
        });

        currentView  = params.get('view')  || currentView;
        currentOrder = params.get('orden') || currentOrder;
        currentPage  = parseInt(params.get('page') || currentPage, 10);

        var sel = document.getElementById('orden');
        if (sel) sel.value = currentOrder;

        currentFilters = readFilters();
        renderActiveFilters(currentFilters);
        initView();
    }

    // ── Eventos ─────────────────────────────────────────────
    $(document).on('click', '.filtro-titulo', function (e) {
        if ($(this).attr('data-toggle') === 'collapse') return;
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).closest('.widget').find('.filtro-subcategoria').prop('checked', false);
        loadProducts(1, true);
    });

    $(document).on('change', '.filtro-subcategoria', function () {
        $(this).closest('.widget-collapsible').find('.filtro-titulo').removeClass('active');
        loadProducts(1, true);
    });

    $(document).on('click', '.gal-view-btn', function () {
        currentView = $(this).attr('data-view');
        initView();
        loadProducts(currentPage, false);
    });

    $('#orden').on('change', function () {
        currentOrder = this.value;
        loadProducts(1, true);
    });

    $('#limpiar-filtros').on('click', function (e) {
        e.preventDefault();
        $('.filtro-subcategoria').prop('checked', false);
        $('.filtro-titulo').removeClass('active');
        currentFilters = [];
        renderActiveFilters([]);
        window.location.href = '{{ route("web_gallery") }}';
    });

    $(document).on('click', '.gal-af-remove', function () {
        var chip = $(this).closest('.gal-af-chip');
        var fval = chip.data('filter');
        var cb   = document.querySelector('.filtro-subcategoria[value="' + fval + '"]');
        var tl   = document.querySelector('.filtro-titulo[data-value="' + fval + '"]');
        if (cb) cb.checked = false;
        if (tl) $(tl).removeClass('active');
        loadProducts(1, true);
    });

    $(document).on('click', '#pagination-container a', function (e) {
        e.preventDefault();
        var href  = $(this).attr('href') || '';
        var match = href.match(/page=(\d+)/);
        var page  = match ? parseInt(match[1], 10) : 1;
        currentPage = page;
        loadProducts(page, false);
        window.scrollTo({ top: document.getElementById('productos-container').offsetTop - 80, behavior: 'smooth' });
    });

    window.addEventListener('popstate', function () {
        initFromURL();
        loadProducts(currentPage, false);
    });

    document.getElementById('galSidebarToggle').addEventListener('click', function () {
        document.getElementById('galSidebar').classList.toggle('gal-sidebar--open');
        this.classList.toggle('active');
    });

    // ── Arranque: NO llamar loadProducts — productos ya renderizados por el servidor ──
    initFromURL();

})();
</script>
@endsection
