<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gracias por tu pedido | Detalles Rossety</title>
    <meta name="description" content="Tu pedido en Detalles Rossety fue registrado. Revisa el resumen y los medios de pago." />
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
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/components.css') }}" />
        <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/cart-page.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/checkout.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/order.css') }}" />
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


    <main class="rs-section rs-section--cream">
        <div class="rs-container js-order">
            <div class="rs-order js-ok">
                <div class="rs-order__hero">
                    <span class="rs-order__check"><i class="ph-fill ph-check-circle"></i></span>
                    <h1 class="rs-order__title">¡Gracias por tu pedido, <span class="js-name"></span>!</h1>
                    <p class="rs-order__text">Enviamos tu pedido a nuestra asesora por WhatsApp. En breve te escribirá para confirmar el stock, el costo de envío y el pago.</p>
                    <p class="rs-order__meta">Pedido <strong class="js-id"></strong> · <span class="js-date"></span></p>
                </div>
                <div class="rs-order__grid">
                    <section class="rs-co-card">
                        <h2 class="rs-co-card__title rs-co-card__title--plain">Tus productos</h2>
                        <div class="rs-co__items js-items"></div>
                        <div class="rs-summary__row"><span>Subtotal</span><strong class="js-sub"></strong></div>
                        <div class="rs-summary__row rs-summary__row--disc js-disc-row rs-hidden"><span>Descuento</span><strong class="js-disc"></strong></div>
                        <div class="rs-summary__row rs-summary__row--total"><span>Total</span><strong class="js-total"></strong></div>
                    </section>
                    <section class="rs-co-card">
                        <h2 class="rs-co-card__title rs-co-card__title--plain">Datos del pedido</h2>
                        <dl class="rs-order__list js-data"></dl>
                    </section>
                    <section class="rs-co-card">
                        <h2 class="rs-co-card__title rs-co-card__title--plain">Medios de pago</h2>
                        <p class="rs-co-card__hint">Cuando tu asesora confirme el pedido, puedes pagar por cualquiera de estas cuentas y enviar la captura por WhatsApp.</p>
                        <ul class="rs-order__pays js-pays"></ul>
                    </section>
                </div>
                <div class="rs-order__actions">
                    <a class="rs-btn rs-btn--wa js-resend" href="#"><i class="ph-fill ph-whatsapp-logo"></i>Reenviar pedido por WhatsApp</a>
                    <a class="rs-btn rs-btn--primary" href="{{ route('tienda') }}">Seguir comprando</a>
                    <a class="rs-btn rs-btn--ghost" href="{{ route('home') }}">Volver al inicio</a>
                </div>
            </div>
            <div class="rs-cartpage__empty js-none rs-hidden">
                <i class="ph ph-gift"></i>
                <h3>No encontramos un pedido reciente</h3>
                <p>Si ya hiciste tu pedido, revisa tu WhatsApp. También puedes armar uno nuevo desde la tienda.</p>
                <a class="rs-btn rs-btn--primary" href="{{ route('tienda') }}">Ir a la tienda</a>
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
    <script src="{{ asset('rossety/assets/js/rossety/order.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
