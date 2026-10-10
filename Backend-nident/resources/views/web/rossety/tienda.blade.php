<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tienda | Detalles Rossety</title>
    <meta name="description" content="Galería de productos de Detalles Rossety con filtros por categoría, precio y temporada." />
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


    <section class="rs-pagehero">
        <div class="rs-container">
            <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><span>Tienda</span></div>
            <h1 class="rs-pagehero__title js-shop-title">Toda la tienda</h1>
            <p class="rs-pagehero__text">Filtra por categoría, subcategoría, precio o temporada y pide por WhatsApp.</p>
        </div>
    </section>
    <main class="rs-section">
        <div class="rs-container rs-shop js-shop">
            <aside class="rs-filters" aria-label="Filtros">
                <div class="rs-filters__head">
                    <h2 class="rs-filters__title">Filtros</h2>
                    <div>
                        <button class="rs-filters__reset js-reset" type="button">Limpiar</button>
                        <button class="rs-filters__close js-filters-close" type="button" aria-label="Cerrar filtros"><i class="ph-bold ph-x"></i></button>
                    </div>
                </div>
                <div class="rs-filter">
                    <label class="rs-filter__label" for="shop-q">Buscar</label>
                    <input class="rs-filter__input js-q" id="shop-q" type="search" placeholder="Nombre, modelo, personaje..." />
                </div>
                <div class="rs-filter">
                    <span class="rs-filter__label">Categoría</span>
                    <ul class="rs-filter__list js-cats"></ul>
                </div>
                <div class="rs-filter rs-hidden">
                    <span class="rs-filter__label">Subcategoría</span>
                    <ul class="rs-filter__list js-subs"></ul>
                </div>
                <div class="rs-filter">
                    <span class="rs-filter__label">Precio (S/)</span>
                    <div class="rs-filter__range">
                        <input class="rs-filter__input js-min" type="number" min="0" placeholder="Mín." aria-label="Precio mínimo" />
                        <span>-</span>
                        <input class="rs-filter__input js-max" type="number" min="0" placeholder="Máx." aria-label="Precio máximo" />
                    </div>
                </div>
                <div class="rs-filter">
                    <span class="rs-filter__label">Etiqueta</span>
                    <div class="rs-filter__chips js-tags"></div>
                </div>
            </aside>
            <div class="rs-filters-overlay"></div>
            <div>
                <div class="rs-shop__bar">
                    <div class="rs-shop__views" role="group" aria-label="Vista de productos">
                        <button class="rs-shop__view" type="button" data-shop-view="list" aria-label="Vista de lista" title="Lista" aria-pressed="false"><i class="ph-bold ph-list-bullets"></i></button>
                        <button class="rs-shop__view" type="button" data-shop-view="2" aria-label="Vista de dos columnas" title="2 columnas" aria-pressed="false"><i class="ph-bold ph-squares-four"></i></button>
                        <button class="rs-shop__view rs-shop__view--active" type="button" data-shop-view="3" aria-label="Vista de tres columnas" title="3 columnas" aria-pressed="true"><i class="ph-bold ph-grid-nine"></i></button>
                        <button class="rs-shop__view" type="button" data-shop-view="4" aria-label="Vista de cuatro columnas" title="4 columnas" aria-pressed="false"><i class="ph-bold ph-columns"></i></button>
                    </div>
                    <p class="rs-shop__count js-count" role="status" aria-live="polite"></p>
                    <div class="rs-shop__tools">
                        <button class="rs-btn rs-btn--ghost rs-btn--sm rs-shop__toggle js-filters-open" type="button"><i class="ph-bold ph-funnel"></i>Filtros</button>
                        <i class="ph-bold ph-sort-descending rs-shop__sort-icon" aria-hidden="true"></i>
                        <select class="rs-shop__sort js-sort" aria-label="Ordenar">
                            <option value="relevancia">Relevancia</option>
                            <option value="menor">Precio: menor a mayor</option>
                            <option value="mayor">Precio: mayor a menor</option>
                            <option value="nombre">Nombre: A - Z</option>
                        </select>
                    </div>
                </div>
                <div class="rs-shop__active js-active"></div>
                <div class="rs-grid js-grid" data-view="3"></div>
                <div class="rs-shop__empty js-empty rs-hidden">
                    <i class="ph ph-magnifying-glass"></i>
                    <h3>No encontramos productos</h3>
                    <p>Prueba con otro nombre o limpia los filtros para ver todos los productos.</p><button class="rs-btn rs-btn--ghost rs-btn--sm js-reset" type="button">Limpiar filtros</button>
                    <p class="rs-center"><a class="rs-btn rs-btn--wa js-wa" href="#"><i class="ph-fill ph-whatsapp-logo"></i>Consultar por WhatsApp</a></p>
                </div>
                <div class="rs-shop__more"><button class="rs-btn rs-btn--primary js-more" type="button">Mostrar más productos</button></div>
            </div>
        </div>
    </main>

    <section class="rs-news">
        <div class="rs-container rs-news__inner">
            <div class="rs-news__text">
                <h2 class="rs-news__title"><i class="ph-fill ph-heart"></i>Suscríbete Y Obtén 10% De Descuento</h2>
                <p class="rs-news__desc">Recibe ofertas exclusivas, novedades y códigos especiales antes que nadie</p>
            </div>
            <form class="rs-news__form js-newsletter">
                <input class="rs-news__input" type="email" name="correo" placeholder="Ingresa tu correo electrónico" aria-label="Correo electrónico" required />
                <button class="rs-news__btn" type="submit">Suscribirme</button>
            </form>
        </div>
    </section>

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
    <script src="{{ asset('rossety/assets/js/rossety/shop.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
