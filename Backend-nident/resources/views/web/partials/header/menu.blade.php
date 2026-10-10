@php
$footerData = Helpers::footer_();
$headerData = Helpers::cart_heder();
$menuLogoSrc = (count($footerData) > 0 && !empty($footerData[0]->logo_menu))
    ? asset($footerData[0]->logo_menu)
    : asset('imagenes/baner/mercado_pago_capre.jpg');
@endphp
<style>
    /* ========== ESTILOS BUSCADOR DESKTOP ========== */
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
    }

    .search-results .search-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .search-results .search-item:hover {
        background-color: #f8f9fa;
    }

    .search-results .search-item:last-child {
        border-bottom: none;
    }

    .search-results .product-thumb {
        width: 10rem;
        height: 10rem;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 10px;
    }

    .search-results .product-name {
        font-weight: 500;
        color: #333;
        font-size: 14px;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .search-results .product-price {
        color: #922b21;
        font-weight: 600;
        font-size: 13px;
    }

    .search-results .search-no-results {
        padding: 15px;
        text-align: center;
        color: #666;
        font-style: italic;
    }

    /* ========== ESTILOS BUSCADOR MÓVIL ========== */
    .searchResultsx {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }

    .searchResultsx .search-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .searchResultsx .search-item:hover {
        background-color: #f8f9fa;
    }

    .searchResultsx .search-item:last-child {
        border-bottom: none;
    }

    .searchResultsx .product-thumb {
        width: 8rem;
        height: 8rem;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 8px;
    }

    .searchResultsx .product-name {
        font-weight: 500;
        color: #333;
        font-size: 13px;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .searchResultsx .product-price {
        color: #922b21;
        font-weight: 600;
        font-size: 12px;
    }

    .searchResultsx .search-no-results {
        padding: 12px;
        text-align: center;
        color: #666;
        font-style: italic;
        font-size: 13px;
    }

    /* ========== CONTENEDORES CON POSICIÓN RELATIVA ========== */
    .header-search-wrapper {
        position: relative;
    }

    .mobile-search {
        position: relative;
    }

    /* ========== UTILIDADES COMUNES ========== */
    .d-flex {
        display: flex !important;
    }

    .align-items-center {
        align-items: center !important;
    }

    .me-3 {
        margin-right: 10px !important;
    }

    /* ========== RESPONSIVE ADICIONAL ========== */
    @media (max-width: 480px) {
        .searchResultsx .search-item {
            padding: 6px 10px;
        }

        .searchResultsx .product-thumb {
            width: 8rem;
            height: 8rem;
        }

        .searchResultsx .product-name {
            font-size: 12px;
        }

        .searchResultsx .product-price {
            font-size: 11px;
        }
    }

    /* ========== HEADER-TOP: una sola fila horizontal ordenada ========== */
    .header.header-2 .header-top .container.clsMovilContainer {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 12px;
    }
    .header.header-2 .header-top .header-left {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 8px;
        min-width: 0;
    }
    .header.header-2 .header-top .header-left > p,
    .header.header-2 .header-top .header-left > a {
        flex-shrink: 0;
        white-space: nowrap;
    }
    .header.header-2 .header-top .header-left .social-links {
        flex-shrink: 0;
    }
    @media (max-width: 576px) {
        .header.header-2 .header-top .header-left .social-links {
            display: none !important;
        }
    }
    .header.header-2 .header-top .header-right {
        display: flex !important;
        flex-shrink: 0;
    }
    .header.header-2 .header-top .header-right .top-menu {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 10px;
        margin: 0 !important;
    }
    .header.header-2 .header-top .top-menu a.nav-link {
        white-space: nowrap;
    }

    /* Header: en tablet/móvil no mostrar logo ni segunda barra (evitar menú + logo + "Seleccionar Categoría" juntos) */
    .mobile-category-dropdown {
        display: none;
    }
    @media (max-width: 991px) {
        .header.header-2 .header-middle .header-left .logo {
            display: none !important;
        }
        .header.header-2 .header-middle .header-left {
            display: flex !important;
            align-items: center;
            gap: 10px;
        }
        .mobile-category-dropdown {
            display: none !important;
        }
        .header.header-2 .header-bottom {
            display: none !important;
        }
    }

    /* === MEJORAS MOBILE HEADER-TOP === */
    @media (max-width: 767px) {
        .header.header-2 .header-top .header-left p, 
        .header.header-2 .header-top .header-left a {
            font-size: 10px !important;
        }
        
        .header.header-2 .header-top .header-left {
            gap: 4px !important;
        }

        .header-dropdown > a {
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
            vertical-align: middle;
            font-size: 11px !important;
        }

        /* Corregir doble flecha en el dropdown de usuario */
        .header-dropdown .text-white::after {
            display: none !important;
        }
        
        .header.header-2 .header-top .container.clsMovilContainer {
            gap: 5px !important;
        }
    }
</style>


<header class="header header-2 header-intro-clearance">
    <div class="header-top">
        <div class="container clsMovilContainer">


            <div class="header-left">
                <p class="text-white p_fsize_12"><i class="fa-solid fa-phone mr-2"></i> Cell: <a href="https://web.whatsapp.com/send?phone={{$footerData[0]->contacto_telefono}}&text=Bienvenidos%20a%20lucdesoft%2C%20%C2%BFNecesito%20asesoramiento%20o%20consultar%3F" onclick="window.open(this.href,'lucdesoft_wa');return false;" rel="noopener noreferrer" class="text-white"> {{ $footerData[0]->contacto_telefono }} </a></p>
                @isset($footerData[0]->url_mapa)
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($footerData[0]->contacto_direccion) }}" target="_blank" class="ml-1 text-white"> / <i class="fa-solid fa-location-dot mr-2"></i> Ubicación</a>
                @endisset

                <ul class="social-links clsRedesSocialesAlign" style="margin-left: 1rem;">
                    @isset($footerData[0]->red_social_facebook)
                    <li><a href="{{ $footerData[0]->red_social_facebook }}" title="Facebook" target="_blank" class="clsMovilRedesColor"><i class="fab fa-facebook-f"></i></a></li>
                    @endisset

                    @isset($footerData[0]->red_social_twitter)
                    <li><a href="{{ $footerData[0]->red_social_twitter }}" title="Twitter" target="_blank" class="clsMovilRedesColor"><i class="fab fa-twitter"></i></a></li>
                    @endisset

                    @isset($footerData[0]->red_social_instagram)
                    <li><a href="{{ $footerData[0]->red_social_instagram }}" title="Instagram" target="_blank" class="clsMovilRedesColor"><i class="fab fa-instagram"></i></a></li>
                    @endisset

                    @isset($footerData[0]->red_social_linkedin)
                    <li><a href="{{ $footerData[0]->red_social_linkedin }}" title="Linkedin" target="_blank" class="clsMovilRedesColor"><i class="fab fa-linkedin-in"></i></a></li>
                    @endisset

                    @isset($footerData[0]->red_social_youtobe)
                    <li><a href="{{ $footerData[0]->red_social_youtobe }}" title="Youtube" target="_blank" class="clsMovilRedesColor"><i class="fab fa-youtube"></i></a></li>
                    @endisset
                </ul>
            </div>

            <div class="header-right" style="padding-bottom: 4px;padding-top: 3px;">
                <ul class="top-menu" style="gap: unset;">

                    @guest
                    @if (Route::has('login'))
                    <a class="nav-link text-white" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @endif
                    @if (Route::has('register'))
                    <a class="nav-link text-white" href="{{ route('register') }}">{{ __('Registro') }}</a>
                    @endif
                    @else
                    <li class="nav-item">
                        <div class="header-dropdown">
                            <a href="#" class="text-white">Hola, {{ strtolower(Auth::user()->name) }}</a>
                            <div class="header-menu">
                                <ul>
                                    <!-- Opciones visibles solo en móvil -->
                                    <li class="d-lg-none">
                                        <a href="/ope_pedidos/listar_pedidos?tab=link-miPerfil" style="color: black !important;">
                                            <i class="fa fa-user" style="margin-top: 7px;"></i> {{ __('Mi Perfil') }}
                                        </a>
                                    </li>
                                    <li class="d-lg-none">
                                        <a href="/ope_pedidos/listar_pedidos?tab=link-misPedidos" style="color: black !important;">
                                            <i class="fa fa-shopping-bag" style="margin-top: 7px;"></i> {{ __('Mis Compras') }}
                                        </a>
                                    </li>
                                    <li class="d-lg-none">
                                        <a href="/ope_pedidos/listar_pedidos?tab=tab-direccion" style="color: black !important;">
                                            <i class="fa fa-location-arrow" style="margin-top: 7px;"></i> {{ __('Direcciones') }}
                                        </a>
                                    </li>
                                    
                                    <!-- Cerrar Sesión (Mobile) / Salir (Desktop) -->
                                    <li>
                                        <a href="{{ route('logout') }}" class="clsLogout" style="color: black !important;">
                                            <i class="fa fa-power-off" style="margin-top: 7px;"></i> 
                                            <span class="d-lg-none">{{ __('Cerrar Sesión') }}</span>
                                            <span class="d-none d-lg-inline">{{ __('Salir') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>

                    @endguest
                </ul>
            </div><!-- End .header-right -->

        </div><!-- End .container -->
    </div><!-- End .header-top -->

    <div class="header-middle">
        <div class="container-fluid">
            <div class="header-left clsHeigtHeader">
                <button class="mobile-menu-toggler">
                    <span class="sr-only">Toggle mobile menu</span>
                    <i class="icon-bars"></i>
                </button>
                {{-- En móvil: "Seleccionar Categoría" junto al ícono hamburguesa (misma barra blanca) --}}
                <div class="dropdown category-dropdown mobile-category-dropdown">
                    <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false" data-display="static"
                        title="Seleccionar Categoría">
                        Seleccionar Categoría
                    </a>
                    <div class="dropdown-menu">
                        <nav class="side-nav">
                            <ul class="menu-vertical sf-arrows">
                                @foreach(CategoryHelper::getFullMenu() as $category)
                                <li class="item-lead">
                                    <a href="{{ url('/web_gallery/' . (int)$category['id']) }}">
                                        {{ $category['name'] }}
                                    </a>
                                    @if(count($category['subcategories']) > 0)
                                    <ul>
                                        @foreach($category['subcategories'] as $subcategory)
                                        <li>
                                            <a href="{{ url('/web_gallery/' . (int)$category['id'] . '/' . (int)$subcategory['id']) }}">
                                                {{ $subcategory['name'] }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                </div>

                <a href="/" class="logo">
                    <img src="{{ $menuLogoSrc }}" alt="Valento" class="clsLoginHeader">
                </a>
            </div><!-- End .header-left -->

            <div class="header-center clsHeigtHeader">
                <div class="header-search header-search-extended header-search-visible header-search-no-radius d-none d-lg-block">
                    <a href="#" class="search-toggle" role="button"><i class="icon-search"></i></a>
                    <form id="searchForm" action="{{ route('web_gallery') }}" method="GET" class="search-form searchForm">
                        <div class="header-search-wrapper header-search header-search-extended" style="margin-bottom: unset;">
                            <label for="q" class="sr-only">Search</label>
                            <input type="search"
                                class="form-control productSearch"
                                name="q"
                                id="productSearch"
                                placeholder="Buscar productos..."
                                required
                                value="{{ request('q') }}"
                                autocomplete="off">
                            <button class="btn btn-primary search-btn searchButton" type="submit" id="searchButton">
                                <i class="icon-search"></i>
                            </button>
                            <div id="searchResults" class="search-results searchResults"></div>
                        </div>
                    </form>
                </div>
            </div>


            <div class="header-right clsHeigtHeader">

                <div class="account">
                    @guest
                    @if (Route::has('register'))
                    <a href="{{ route('login') }}" title="My account">
                        <div class="icon">
                            <i class="icon-user"></i>
                        </div>
                        <p>Mi cuenta</p>
                    </a>
                    @endif
                    @else
                    <a href="/ope_pedidos/listar_pedidos" title="My account">
                        <div class="icon">
                            <i class="icon-opencart"></i>
                        </div>
                        <p>Mis Compras</p>
                    </a>
                    @endguest

                </div>

                <div class="wishlist">
                    <a href="/web_wishlist" title="Wishlist">
                        <div class="icon">
                            <i class="icon-heart-o"></i>
                        </div>
                        <p>Favoritos</p>
                    </a>
                </div><!-- End .compare-dropdown -->

                <div class="dropdown cart-dropdown" id="idActualizarDivMenuHeader">
                    <a href="#" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false" data-display="static" id="cartDropdownToggle" data-cart-count="{{ Cart::count() }}">
                        <div class="icon">
                            <i class="icon-shopping-cart"></i>
                            <span class="cart-count clsDetalle_CountCart">{{ Cart::count() }}</span>
                        </div>
                        <p>Cart</p>
                    </a>
                    @if (Cart::count() > 0)
                    <div class="dropdown-menu dropdown-menu-right" style="max-height: 33rem; overflow-y: auto;">
                        <div class="dropdown-cart-products">

                            @foreach($headerData as $cartItem)
                            <div class="product">
                                <div class="product-cart-details">
                                    <h4 class="product-title">
                                        <a href="/web_shopDetail/{{ $cartItem->options->slug ?? $cartItem->options->id_producto }}">{{$cartItem->name}}</a>
                                    </h4>
                                    <span>
                                        <p>Precio: S/ {{$cartItem->price}}</p>
                                    </span>
                                    <span>
                                        <p>Cantidad: {{$cartItem->qty}}</p>
                                    </span>

                                </div>

                                <figure class="product-image-container">
                                    <a href="/web_shopDetail/{{ $cartItem->options->slug ?? $cartItem->options->id_producto }}" class="product-image">
                                        <img src="../{{$cartItem->options->img}}" alt="product">
                                    </a>
                                </figure>
                                <a href="#" class="btn-remove" title="Remove Product" id="idRemoveCart{{$cartItem->rowId}}"><i class="icon-close"></i></a>
                            </div>
                            @endforeach

                        </div><!-- End .cart-product -->

                        <div class="dropdown-cart-total">
                            <span>Total</span>

                            <div>
                                <span> S/ </span>
                                <span class="cart-total-price" id="txtPrecioTotal"> {{Cart::total()}} </span>
                            </div>
                        </div><!-- End .dropdown-cart-total -->

                        <div class="dropdown-cart-action clsFontMenuAgregar">
                            <a href="/web_shopDetail_lista" class="btn btn-primary">Ver cesta</a>
                            <a href="/web_checkout/index" class="btn btn-primary ml-4">Comprar<i class="icon-long-arrow-right"></i></a>
                        </div><!-- End .dropdown-cart-total -->
        
                    </div>
                    @endif
                </div>


            </div><!-- End .header-right -->
        </div><!-- End .container -->
    </div><!-- End .header-middle -->

    <div class="header-bottom sticky-header" style="height: 54px; display: flex; align-items: center;">
        <div class="container-fluid">


            <div class="header-left">
                <div class="dropdown category-dropdown">
                    <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false" data-display="static"
                        title="Browse Categories">
                        Seleccionar Categoría
                    </a>

                    <div class="dropdown-menu">
                        <nav class="side-nav">
                            <ul class="menu-vertical sf-arrows">
                                @foreach(CategoryHelper::getFullMenu() as $category)
                                <li class="item-lead">
                                    <!-- Enlace para la categoría principal -->
                                    <a href="{{ url('/web_gallery/' . (int)$category['id']) }}">
                                        {{ $category['name'] }}
                                    </a>

                                    @if(count($category['subcategories']) > 0)
                                    <ul>
                                        @foreach($category['subcategories'] as $subcategory)
                                        <li>
                                            <!-- Enlace para subcategoría: /web_gallery/{categoria}/{subcategoria} -->
                                            <a href="{{ url('/web_gallery/' . (int)$category['id'] . '/' . (int)$subcategory['id']) }}">
                                                {{ $subcategory['name'] }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>


            <div class="header-center clsRigthMenuHeader">
                <nav class="main-nav">
                    <ul class="menu sf-arrows">
                        <li class="{{ request()->is('/') ? 'active' : '' }} megamenu-container">
                            <a href="/" class="">Inicio</a>
                        </li>
                        <li class="{{ request()->is('web_about') || request()->is('about') ? 'active' : '' }}">
                            <a href="/about" class="">Nosotros</a>
                        </li>

                        <li class="{{ request()->is('web_contact')  || request()->is('contact') ? 'active' : '' }}">
                            <a href="/contact" class="">Contáctenos</a>
                        </li>
                    </ul><!-- End .menu -->
                </nav><!-- End .main-nav -->
            </div><!-- End .header-center -->

 
        </div><!-- End .container -->
    </div><!-- End .header-bottom -->
</header><!-- End .header -->
@include('web.pages.index.ajax.footermenulogin')
@push('scripts')
@if(session('error'))
<!-- para el mensage de los errores en general para este documento -->
<script>
    toastr.warning("{{ session('error') }}");
</script>
@endif
<script>
    // ============================================
    // VALIDAR CESTA VACÍA AL HACER CLIC EN CART
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const cartToggle = document.getElementById('cartDropdownToggle');
        if (cartToggle) {
            cartToggle.addEventListener('click', function(e) {
                const cartCount = parseInt(this.getAttribute('data-cart-count')) || 0;
                if (cartCount === 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    toastr.info('Tu cesta está vacía. ¡Agrega productos para comenzar!', 'Cesta vacía', {
                        timeOut: 3000,
                        positionClass: 'toast-top-right',
                        progressBar: true,
                    });
                }
            });
        }
    });
</script>
<script>
    //==================== BUSCADOR DEL MENU ====================
    document.addEventListener('DOMContentLoaded', function() {

        // ========== BUSCADOR DESKTOP ==========
        const searchInput = document.querySelector('.productSearch');
        const searchResults = document.querySelector('.searchResults');
        const searchForm = document.querySelector('.searchForm');
        const searchButton = document.querySelector('.searchButton');

        // ========== BUSCADOR MÓVIL ==========
        const searchInputMobile = document.querySelector('.productSearchx');
        const searchResultsMobile = document.querySelector('.searchResultsx');
        const searchFormMobile = document.querySelector('.searchFormx');
        const searchButtonMobile = document.querySelector('.searchButtonx');

        let timeoutId;
        let timeoutIdMobile;
        let isSearching = false;
        let isSearchingMobile = false;

        // ========== FUNCIONES PARA DESKTOP ==========
        function hideResults() {
            if (searchResults) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
            }
        }

        function showResults() {
            if (searchResults) {
                searchResults.style.display = 'block';
            }
        }

        function performSearch(query) {
            if (query.length < 2) {
                hideResults();
                return;
            }

            if (isSearching) {
                return;
            }

            isSearching = true;

            fetch(`{{ route('products.search') }}?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(products => {
                    isSearching = false;

                    if (products.length > 0) {
                        let html = '';
                        products.forEach(product => {
                            const imageUrl = product.url_imagen ?
                                `{{ URL::asset('') }}${product.url_imagen}` :
                                '{{ URL::asset("imagenes/default-product.jpg") }}';

                            // Sistema de precios integrado
                            let priceHtml = '';
                            if (product.precio_old && parseFloat(product.precio_old) > parseFloat(product.precio)) {
                                // Con descuento
                                priceHtml = `
                                <div class="price-container">
                                    <div class="price-wrapper">
                                        <span class="price-old">S/${parseFloat(product.precio_old).toFixed(2)}</span>
                                        <span class="price-current">S/${parseFloat(product.precio).toFixed(2)}</span>
                                    </div>
                                </div>
                            `;
                            } else {
                                // Sin descuento
                                priceHtml = `
                                <div class="price-container">
                                    <div class="price-wrapper">
                                        <span class="price-current-only">S/${parseFloat(product.precio).toFixed(2)}</span>
                                    </div>
                                </div>
                            `;
                            }

                            html += `
                            <div class="search-item" data-slug="${product.slug}">
                                <div class="d-flex align-items-center">
                                    <img src="${imageUrl}" 
                                         alt="${product.nombre || 'Producto'}" 
                                         class="product-thumb me-3"
                                         style="object-fit: cover;">
                                    <div>
                                        <div class="product-name">${product.nombre || 'Sin nombre'}</div>
                                        ${priceHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                        });
                        searchResults.innerHTML = html;
                        showResults();
                    } else {
                        searchResults.innerHTML = '<div class="search-no-results">No se encontraron productos</div>';
                        showResults();
                    }
                })
                .catch(error => {
                    isSearching = false;
                    console.error('Error en la búsqueda:', error);
                    searchResults.innerHTML = '<div class="search-no-results">Error al buscar productos</div>';
                    showResults();
                });
        }

        function handleMainSearch(query) {
            hideResults();
            window.location.href = `{{ route('web_gallery') }}?q=${encodeURIComponent(query)}`;
        }

        // ========== FUNCIONES PARA MÓVIL ==========
        function hideResultsMobile() {
            if (searchResultsMobile) {
                searchResultsMobile.style.display = 'none';
                searchResultsMobile.innerHTML = '';
            }
        }

        function showResultsMobile() {
            if (searchResultsMobile) {
                searchResultsMobile.style.display = 'block';
            }
        }

        function performSearchMobile(query) {
            if (query.length < 2) {
                hideResultsMobile();
                return;
            }

            if (isSearchingMobile) {
                return;
            }

            isSearchingMobile = true;

            fetch(`{{ route('products.search') }}?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(products => {
                    isSearchingMobile = false;

                    if (products.length > 0) {
                        let html = '';
                        products.forEach(product => {
                            const imageUrl = product.url_imagen ?
                                `{{ URL::asset('') }}${product.url_imagen}` :
                                '{{ URL::asset("imagenes/default-product.jpg") }}';

                            // Sistema de precios integrado para móvil
                            let priceHtml = '';
                            if (product.precio_old && parseFloat(product.precio_old) > parseFloat(product.precio)) {
                                // Con descuento
                                priceHtml = `
                                <div class="price-container">
                                    <div class="price-wrapper">
                                        <span class="price-old">S/${parseFloat(product.precio_old).toFixed(2)}</span>
                                        <span class="price-current">S/${parseFloat(product.precio).toFixed(2)}</span>
                                    </div>
                                </div>
                            `;
                            } else {
                                // Sin descuento
                                priceHtml = `
                                <div class="price-container">
                                    <div class="price-wrapper">
                                        <span class="price-current-only">S/${parseFloat(product.precio).toFixed(2)}</span>
                                    </div>
                                </div>
                            `;
                            }

                            html += `
                            <div class="search-item" data-slug="${product.slug}">
                                <div class="d-flex align-items-center">
                                    <img src="${imageUrl}" 
                                         alt="${product.nombre || 'Producto'}" 
                                         class="product-thumb me-3"
                                         style="object-fit: cover;">
                                    <div>
                                        <div class="product-name">${product.nombre || 'Sin nombre'}</div>
                                        ${priceHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                        });
                        searchResultsMobile.innerHTML = html;
                        showResultsMobile();
                    } else {
                        searchResultsMobile.innerHTML = '<div class="search-no-results">No se encontraron productos</div>';
                        showResultsMobile();
                    }
                })
                .catch(error => {
                    isSearchingMobile = false;
                    console.error('Error en la búsqueda móvil:', error);
                    searchResultsMobile.innerHTML = '<div class="search-no-results">Error al buscar productos</div>';
                    showResultsMobile();
                });
        }

        function handleMainSearchMobile(query) {
            hideResultsMobile();
            window.location.href = `{{ route('web_gallery') }}?q=${encodeURIComponent(query)}`;
        }

        // ========== EVENT LISTENERS DESKTOP ==========
        if (searchInput && searchResults) {
            // Resto del código igual que tienes...
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const query = this.value.trim();

                timeoutId = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value.trim();
                    if (query.length >= 2) {
                        handleMainSearch(query);
                    }
                }

                if (e.key === 'Escape') {
                    hideResults();
                    this.blur();
                }
            });

            if (searchButton) {
                searchButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        handleMainSearch(query);
                    }
                });
            }

            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        handleMainSearch(query);
                    }
                });
            }

            searchResults.addEventListener('click', function(e) {
                const item = e.target.closest('.search-item');
                if (item) {
                    const slug = item.getAttribute('data-slug');
                    window.location.href = `/web_shopDetail/${slug}`;
                }
            });

            searchInput.addEventListener('focus', function() {
                const query = this.value.trim();
                if (query.length >= 2) {
                    performSearch(query);
                }
            });

            searchInput.addEventListener('blur', function() {
                setTimeout(() => {
                    if (!searchResults.matches(':hover')) {
                        hideResults();
                    }
                }, 200);
            });
        }

        // ========== EVENT LISTENERS MÓVIL (igual estructura) ==========
        if (searchInputMobile && searchResultsMobile) {
            searchInputMobile.addEventListener('input', function() {
                clearTimeout(timeoutIdMobile);
                const query = this.value.trim();

                timeoutIdMobile = setTimeout(() => {
                    performSearchMobile(query);
                }, 300);
            });

            searchInputMobile.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value.trim();
                    if (query.length >= 2) {
                        handleMainSearchMobile(query);
                    }
                }

                if (e.key === 'Escape') {
                    hideResultsMobile();
                    this.blur();
                }
            });

            if (searchButtonMobile) {
                searchButtonMobile.addEventListener('click', function(e) {
                    e.preventDefault();
                    const query = searchInputMobile.value.trim();
                    if (query.length >= 2) {
                        handleMainSearchMobile(query);
                    }
                });
            }

            if (searchFormMobile) {
                searchFormMobile.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const query = searchInputMobile.value.trim();
                    if (query.length >= 2) {
                        handleMainSearchMobile(query);
                    }
                });
            }

            searchResultsMobile.addEventListener('click', function(e) {
                const item = e.target.closest('.search-item');
                if (item) {
                    const slug = item.getAttribute('data-slug');
                    window.location.href = `/web_shopDetail/${slug}`;
                }
            });

            searchInputMobile.addEventListener('focus', function() {
                const query = this.value.trim();
                if (query.length >= 2) {
                    performSearchMobile(query);
                }
            });

            searchInputMobile.addEventListener('blur', function() {
                setTimeout(() => {
                    if (!searchResultsMobile.matches(':hover')) {
                        hideResultsMobile();
                    }
                }, 200);
            });
        }

        // ========== EVENT LISTENERS GLOBALES ==========
        document.addEventListener('click', function(e) {
            if (searchInput && searchResults) {
                const isClickInsideSearch = searchInput.contains(e.target) ||
                    searchResults.contains(e.target) ||
                    (searchButton && searchButton.contains(e.target));

                if (!isClickInsideSearch) {
                    hideResults();
                }
            }

            if (searchInputMobile && searchResultsMobile) {
                const isClickInsideSearchMobile = searchInputMobile.contains(e.target) ||
                    searchResultsMobile.contains(e.target) ||
                    (searchButtonMobile && searchButtonMobile.contains(e.target));

                if (!isClickInsideSearchMobile) {
                    hideResultsMobile();
                }
            }
        });
    });
    //==================== END BUSCADOR DEL MENU ====================


    $(document).on('click', 'a[href*="logout"]', function(e) {
        e.preventDefault();
        const logoutUrl = $(this).attr('href');

        Swal.fire({
            title: '¿Cerrar sesión?',
            html: '<p class="mb-0">Tu sesión actual se cerrará</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-logout"></i> Sí, cerrar sesión',
            cancelButtonText: '<i class="mdi mdi-close"></i> Cancelar',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary',
                actions: 'clsCerrarSession idRef5444' // <-- Agregar esta línea
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando sesión...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                window.location.href = logoutUrl;
            }
        });

    });


    // ============================================
    // ACTUALIZAR DIV DEL HEADER
    // ============================================
    function updateDiv() {
        
        $.get("/", function(data) {
            const $newContent = $(data).find('#idActualizarDivMenuHeader');
            if ($newContent.length > 0) {
                $('#idActualizarDivMenuHeader').html($newContent.html());
                console.log("Carrito del header actualizado correctamente.");
            }
        }).fail(function() {
            console.error("No se pudo actualizar el carrito del header.");
        });
    }

    // ============================================
    // ELIMINAR DEL CARRITO
    // ============================================
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.btn-remove');
        if (button) {
            e.preventDefault();
            const rowId = button.id.replace('idRemoveCart', '');
            const formData = new FormData();
            formData.append('id_producto', rowId);

            fetch('/web_shopDetail_eliminar', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Sincronizar con la página de cesta si está abierta
                        const rowOnPage = document.getElementById('clsEliminarRowa_' + rowId);
                        if (rowOnPage) {
                            $(rowOnPage).fadeOut(300, function() {
                                $(this).remove();
                                
                                // Recalcular totales en la página principal
                                if (typeof window.calcularTotales === 'function') {
                                    window.calcularTotales();
                                }
                                
                                // Si carrito vacío y estamos en cesta o checkout, redirigir a productos
                                if ($('#idTableRow tr').length === 0) {
                                    const path = window.location.pathname;
                                    if (path.includes('shopDetail_lista') || path.includes('checkout')) {
                                        window.location.href = '/web_gallery';
                                        return;
                                    }
                                }
                            });
                        }

                       
                        if (data.count === 0) {
                            const path = window.location.pathname;
                            if (path.includes('shopDetail_lista') || path.includes('checkout')) {
                                window.location.href = '/web_gallery';
                                return;
                            }
                        }

                        updateDiv();
                    } else {
                        alert('Error al eliminar el producto.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud.');
                });
        }
    });
</script>
@endpush