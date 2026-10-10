<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detalle del producto | Detalles Rossety</title>
    <meta name="description" content="Conoce el detalle, precio y contenido de este producto de Detalles Rossety." />
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
        <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/cart-page.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/product.css') }}" />
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


    <section class="rs-pagehero rs-pagehero--slim">
        <div class="rs-container">
            <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><a href="{{ route('tienda') }}">Tienda</a><span>/</span><a class="js-crumb-cat" href="{{ route('tienda') }}">Categoría</a><span>/</span><span class="js-crumb-name">Producto</span></div>
        </div>
    </section>
    <main class="rs-section">
        <div class="rs-container js-product">
            <div class="rs-product js-view">
                <div class="rs-product__media">
                    <span class="rs-badge js-p-badge"></span>
                    <img class="js-p-img" src="{{ asset('rossety/assets/images/catalogo/placeholder.svg') }}" alt="" />
                </div>
                <div class="rs-product__info">
                    <span class="rs-product__cat js-p-cat"></span>
                    <h1 class="rs-product__name js-p-name"></h1>
                    <p class="rs-product__model js-p-model"></p>
                    <div class="rs-product__price js-p-price"></div>
                    <div class="rs-product__short js-p-short"></div>
                    <div class="rs-product__buy js-p-buybox">
                        <div class="rs-qty rs-qty--lg"><button class="rs-qty__btn js-qty-dec" type="button" aria-label="Quitar uno">&minus;</button><span class="rs-qty__val js-qty-val">1</span><button class="rs-qty__btn js-qty-inc" type="button" aria-label="Agregar uno">+</button></div>
                        <button class="rs-btn rs-btn--primary js-p-add" type="button"><i class="ph-bold ph-handbag"></i> Añadir al carrito</button>
                        <button class="rs-btn rs-btn--ghost js-p-buy" type="button">Comprar ahora</button>
                    </div>
                    <div class="rs-product__consult js-p-consult rs-hidden">
                        <p>Este producto se cotiza según tu pedido. Escríbenos y te enviamos el precio.</p>
                    </div>
                    <a class="rs-btn rs-btn--wa rs-product__wa js-p-wa" href="#"><i class="ph-fill ph-whatsapp-logo"></i>Pedir por WhatsApp</a>
                    <ul class="rs-product__perks">
                        <li><i class="ph-fill ph-heart"></i>Hecho a mano con amor</li>
                        <li><i class="ph-fill ph-gift"></i>Empaque especial para regalo</li>
                        <li><i class="ph-fill ph-truck"></i>Entrega coordinada por WhatsApp</li>
                    </ul>
                </div>
                <div class="rs-tabs">
                    <div class="rs-tabs__nav">
                        <button class="rs-tabs__btn rs-tabs__btn--active js-tab" type="button" data-tab="desc">Descripción</button>
                        <button class="rs-tabs__btn js-tab" type="button" data-tab="entrega">Entrega</button>
                        <button class="rs-tabs__btn js-tab" type="button" data-tab="pago">Pagos</button>
                    </div>
                    <div class="rs-tabs__panel rs-tabs__panel--active js-panel" data-panel="desc"><div class="js-p-desc"></div></div>
                    <div class="rs-tabs__panel js-panel" data-panel="entrega"><p>La entrega se coordina por WhatsApp según tu distrito y la fecha que necesites. Para pedidos del mismo día, escríbenos con anticipación.</p></div>
                    <div class="rs-tabs__panel js-panel" data-panel="pago"><p>Aceptamos Yape y transferencia BCP. Al enviar tu pedido, nuestra asesora te confirma el stock y te comparte los datos de pago.</p></div>
                </div>
            </div>
            <div class="rs-cartpage__empty js-missing rs-hidden">
                <i class="ph ph-magnifying-glass"></i>
                <h3>No encontramos este producto</h3>
                <p>Puede que ya no esté disponible. Mira el resto de nuestros detalles en la tienda.</p>
                <a class="rs-btn rs-btn--primary" href="{{ route('tienda') }}">Ir a la tienda</a>
            </div>
        </div>
    </main>
    <section class="rs-section rs-section--cream rs-related">
        <div class="rs-container">
            <h2 class="rs-related__title">También te puede gustar</h2>
            <div class="rs-grid js-related"></div>
        </div>
    </section>

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
    <script src="{{ asset('rossety/assets/js/rossety/product.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
