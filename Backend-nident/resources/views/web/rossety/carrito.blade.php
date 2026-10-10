<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Carrito de compras | Detalles Rossety</title>
    <meta name="description" content="Revisa los productos de tu carrito en Detalles Rossety antes de finalizar tu pedido." />
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
            <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><span>Carrito</span></div>
            <h1 class="rs-pagehero__title">Carrito de compras</h1>
            <ol class="rs-steps"><li class="rs-steps__item rs-steps__item--active">Carrito</li><li class="rs-steps__item">Tus datos</li><li class="rs-steps__item">Pedido enviado</li></ol>
        </div>
    </section>
    <main class="rs-section">
        <div class="rs-container rs-cartpage js-cart-page">
            <div class="rs-cartpage__main">
                <div class="js-table">
                    <div class="rs-ctable-wrap">
                        <table class="rs-ctable">
                            <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Total</th><th><span class="rs-sr">Eliminar</span></th></tr></thead>
                            <tbody class="js-rows"></tbody>
                        </table>
                    </div>
                    <div class="rs-cartpage__actions">
                        <a class="rs-btn rs-btn--ghost" href="{{ route('tienda') }}"><i class="ph-bold ph-arrow-left"></i>Seguir comprando</a>
                    </div>
                </div>
                <div class="rs-cartpage__empty js-empty rs-hidden">
                    <i class="ph ph-handbag"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p>Aún no agregaste ningún detalle. Explora la tienda y elige el regalo perfecto.</p>
                    <a class="rs-btn rs-btn--primary" href="{{ route('tienda') }}">Ir a la tienda</a>
                </div>
            </div>
            <aside class="rs-summary js-side">
                <h2 class="rs-summary__title">Resumen del pedido</h2>
                <div class="rs-summary__row"><span>Subtotal</span><strong class="js-sub">S/ 0.00</strong></div>
                <div class="rs-summary__row rs-summary__row--disc js-disc-row rs-hidden">
                    <span>Cupón <b class="js-coupon-code"></b> <button class="rs-summary__link js-coupon-remove" type="button">Quitar</button></span>
                    <strong class="js-disc">- S/ 0.00</strong>
                </div>
                <div class="rs-summary__row"><span>Envío</span><em class="rs-summary__note">Se coordina por WhatsApp</em></div>
                <div class="rs-summary__row rs-summary__row--total"><span>Total</span><strong class="js-total">S/ 0.00</strong></div>
                <button class="rs-summary__toggle js-coupon-toggle" type="button">Añadir cupón de descuento</button>
                <form class="rs-coupon js-coupon-form rs-hidden">
                    <input class="rs-coupon__input js-coupon-input" type="text" placeholder="Código de cupón" aria-label="Código de cupón" />
                    <button class="rs-btn rs-btn--primary rs-btn--sm" type="submit">Aplicar</button>
                </form>
                <p class="rs-form-msg js-coupon-msg"></p>
                <a class="rs-btn rs-btn--primary rs-summary__btn" href="/checkout">Finalizar compra</a>
                <p class="rs-summary__secure"><i class="ph-fill ph-lock-simple"></i>Tu pedido se confirma por WhatsApp. No se cobra nada en línea.</p>
            </aside>
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
    <script src="{{ asset('rossety/assets/js/rossety/cart-page.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
