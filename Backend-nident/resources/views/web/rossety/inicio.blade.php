<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detalles Rossety | Detalles y regalos que enamoran</title>
    <meta name="description" content="Detalles Rossety: ramos, peluches, cajas de regalo, accesorios, Hot Wheels y temporada de girasoles. Pide por WhatsApp." />
    <link rel="shortcut icon" href="{{ asset('rossety/assets/images/fav.png') }}" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/fill/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/base.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/components.css') }}" />
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/home.css') }}" />
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


    <main>
        <section class="rs-hero">
            <div class="rs-container rs-hero__grid">
                <div class="rs-hero__main">
                    <div class="swiper js-hero">
                        <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="rs-hero__item">
                                <div class="rs-hero__text">
                                    <div class="rs-hero__eyebrow">Catálogo 2026</div>
                                    <h2 class="rs-hero__title">Ramos y Detalles<br />Que Enamoran</h2>
                                    <p class="rs-hero__desc">Rosas, tulipanes y peluches<br />con luces, globos y chocolates</p>
                                    <a class="rs-hero__btn" href="/tienda?cat=ramos">Ver ramos</a>
                                </div>
                                <div class="rs-hero__media"><img src="{{ asset('rossety/assets/images/inicio/hero-catalogo-2026.jpg') }}" alt="Ramos y detalles Rossety" /></div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="rs-hero__item">
                                <div class="rs-hero__text">
                                    <div class="rs-hero__eyebrow">Temporada girasoles</div>
                                    <h2 class="rs-hero__title">La Temporada De Flores<br />Amarillas Ya Llegó</h2>
                                    <p class="rs-hero__desc">Peluches, cajas y regalos de girasol<br />hechos con amor para ti</p>
                                    <a class="rs-hero__btn" href="/tienda?cat=girasoles">Ver temporada</a>
                                </div>
                                <div class="rs-hero__media"><img src="{{ asset('rossety/assets/images/inicio/hero-girasoles.jpg') }}" alt="Temporada de girasoles" /></div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="rs-hero__item">
                                <div class="rs-hero__text">
                                    <div class="rs-hero__eyebrow">Pasión en cada detalle</div>
                                    <h2 class="rs-hero__title">Hot Wheels<br />Y Motos Miniatura</h2>
                                    <p class="rs-hero__desc">Productos 100% originales,<br />ideales para coleccionar</p>
                                    <a class="rs-hero__btn" href="/tienda?cat=hotwheels">Ver colección</a>
                                </div>
                                <div class="rs-hero__media"><img src="{{ asset('rossety/assets/images/inicio/hero-hotwheels.jpg') }}" alt="Hot Wheels y motos miniatura" /></div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="rs-hero__side">
                    <a class="rs-hero__card" href="/tienda?tag=destacado">
                        <div class="rs-hero__card-text">
                            <span class="rs-hero__badge">Más pedido</span>
                            <h3 class="rs-hero__card-title">Ramo Rubi<br />30 Rosas</h3>
                            <p class="rs-hero__card-price">Desde <span>S/ 220.00</span></p>
                        </div>
                        <img src="{{ asset('rossety/assets/images/catalogo/ramos/ramo-rubi.jpg') }}" alt="Ramo Rubi" />
                    </a>
                    <a class="rs-hero__card" href="/tienda?cat=cajas">
                        <div class="rs-hero__card-text">
                            <span class="rs-hero__badge">Nuevo</span>
                            <h3 class="rs-hero__card-title">Cajas<br />Temáticas</h3>
                            <p class="rs-hero__card-price">Desde <span>S/ 12.00</span></p>
                        </div>
                        <img src="{{ asset('rossety/assets/images/catalogo/cajas/caja-snopy.jpg') }}" alt="Cajas temáticas" />
                    </a>
                </div>
            </div>
        </section>

        <section class="rs-strip">
            <div class="rs-container rs-strip__list">
                <div class="rs-strip__item"><i class="ph ph-gift"></i>Detalles únicos</div>
                <div class="rs-strip__item"><i class="ph ph-paw-print"></i>Peluches de calidad</div>
                <div class="rs-strip__item"><i class="ph ph-flower"></i>Arreglos florales</div>
                <div class="rs-strip__item"><i class="ph ph-heart"></i>Hechos con amor</div>
                <div class="rs-strip__item"><i class="ph ph-confetti"></i>Para cada ocasión</div>
            </div>
        </section>

        <section class="rs-section">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">Nuestras categorías</span><h2 class="rs-head__title">Encuentra el detalle perfecto</h2><p class="rs-head__text">Ramos, peluches, cajas, accesorios, Hot Wheels y mucho más. Todo lo que necesitas para sorprender.</p></div>
                <div class="rs-cats js-home-cats"></div>
            </div>
        </section>

        <section class="rs-section rs-section--cream">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">Los favoritos</span><h2 class="rs-head__title">Más pedidos</h2><p class="rs-head__text">Los detalles que más enamoran a nuestros clientes.</p></div>
                <div class="rs-slider js-slider-featured">
                <div class="swiper"><div class="swiper-wrapper js-slides-featured"></div></div>
                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
                <div class="rs-center"><a class="rs-btn rs-btn--primary" href="/tienda?tag=destacado">Ver todos los más pedidos</a></div>
            </div>
        </section>

        <section class="rs-section rs-sun">
            <div class="rs-container rs-sun__intro">
                <div>
                    <span class="rs-head__eyebrow">Temporada girasoles</span>
                    <h2 class="rs-sun__title">La temporada de flores amarillas ya llegó</h2>
                    <p class="rs-sun__text">Peluches abeja, cajas amarillas, collares, cuadros 3D y ramos de girasol. Detalles únicos hechos con amor para alegrar su día.</p>
                    <a class="rs-btn rs-btn--primary" href="/tienda?cat=girasoles">Ver toda la temporada</a>
                </div>
                <div class="rs-sun__grid js-grid-sun"></div>
            </div>
        </section>

        <section class="rs-section">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">Recién arreglados</span><h2 class="rs-head__title">Ramos y detalles</h2><p class="rs-head__text">Ramos de rosas, tulipanes y sorpresas con peluche, chocolates, globos y luces.</p></div>
                <div class="rs-slider js-slider-new">
                <div class="swiper"><div class="swiper-wrapper js-slides-new"></div></div>
                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
                <div class="rs-center"><a class="rs-btn rs-btn--ghost" href="/tienda?cat=ramos">Ver todos los ramos</a></div>
            </div>
        </section>

        <section class="rs-section rs-section--cream">
            <div class="rs-container rs-wholesale">
                <div>
                    <span class="rs-head__eyebrow">Cajas de regalo</span>
                    <h2 class="rs-head__title">Cajas temáticas con precio por cantidad</h2>
                    <p class="rs-head__text">Snoopy, Kitty, Stich, Spiderman, equipos, circulares, birrete, cartera y más. Mientras más llevas, menos pagas por unidad.</p>
                    <div class="rs-tiers">
                        <div class="rs-tiers__row rs-tiers__row--head"><span>Caja</span><span>Unidad</span><span>Por cantidad</span></div>
                        <div class="rs-tiers__row"><span>Snoopy / Kitty</span><span class="rs-tiers__price">S/ 15.00</span><span>Desde 3: S/ 12.00</span></div>
                        <div class="rs-tiers__row"><span>Circular 15x15</span><span class="rs-tiers__price">S/ 15.00</span><span>Desde 3: S/ 12.00</span></div>
                        <div class="rs-tiers__row"><span>Cartera</span><span class="rs-tiers__price">S/ 18.00</span><span>Desde 6: S/ 15.00</span></div>
                        <div class="rs-tiers__row"><span>Natural 30x30x10</span><span class="rs-tiers__price">S/ 12.00</span><span>Desde 3: S/ 10.00</span></div>
                        <div class="rs-tiers__row"><span>Personajes 30x30x10</span><span class="rs-tiers__price">S/ 20.00</span><span>Docena: S/ 120.00</span></div>
                    </div>
                    <p class="rs-center"><a class="rs-btn rs-btn--primary" href="/tienda?cat=cajas">Ver todas las cajas</a></p>
                </div>
                <div class="rs-grid js-grid-boxes"></div>
            </div>
        </section>

        <section class="rs-section rs-race">
            <div class="rs-container">
                <div class="rs-head rs-head--light"><span class="rs-head__eyebrow">Pasión en cada detalle</span><h2 class="rs-head__title">Hot Wheels y motos miniatura</h2><p class="rs-head__text">Venta de motos miniatura y carros Hot Wheels. Ideales para regalar o coleccionar.</p></div>
                <div class="rs-race__badges">
                    <span class="rs-race__badge"><i class="ph-fill ph-seal-check"></i>Productos de calidad</span>
                    <span class="rs-race__badge"><i class="ph-fill ph-shield-check"></i>100% originales</span>
                    <span class="rs-race__badge"><i class="ph-fill ph-gift"></i>Ideales para coleccionar</span>
                </div>
                <div class="rs-slider js-slider-hw">
                <div class="swiper"><div class="swiper-wrapper js-slides-hw"></div></div>
                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
                <div class="rs-center"><a class="rs-btn rs-btn--gold" href="/tienda?cat=hotwheels">Ver Hot Wheels y motos</a></div>
            </div>
        </section>

        <section class="rs-section">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">Por qué elegirnos</span><h2 class="rs-head__title">Hechos con amor, entregados con cariño</h2></div>
                <div class="rs-why">
                    <div class="rs-why__item"><div class="rs-why__icon"><i class="ph ph-gift"></i></div><h3 class="rs-why__title">Detalles únicos</h3><p class="rs-why__text">Cada arreglo es diseñado a tu medida.</p></div>
                    <div class="rs-why__item"><div class="rs-why__icon"><i class="ph ph-paw-print"></i></div><h3 class="rs-why__title">Peluches de calidad</h3><p class="rs-why__text">Suaves, duraderos y de personajes favoritos.</p></div>
                    <div class="rs-why__item"><div class="rs-why__icon"><i class="ph ph-flower-lotus"></i></div><h3 class="rs-why__title">Arreglos florales</h3><p class="rs-why__text">Rosas, tulipanes, girasoles y lirios.</p></div>
                    <div class="rs-why__item"><div class="rs-why__icon"><i class="ph ph-heart"></i></div><h3 class="rs-why__title">Hechos con amor</h3><p class="rs-why__text">Cuidamos cada detalle y cada lazo.</p></div>
                    <div class="rs-why__item"><div class="rs-why__icon"><i class="ph ph-confetti"></i></div><h3 class="rs-why__title">Para cada ocasión</h3><p class="rs-why__text">Cumpleaños, aniversarios, graduaciones y más.</p></div>
                </div>
            </div>
        </section>


        <section class="rs-section" id="pagos">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">Métodos de pago</span><h2 class="rs-head__title">Paga de forma segura</h2><p class="rs-head__text">Aceptamos transferencias y Yape. Gracias por tu confianza.</p></div>
                <div class="rs-pay">
                    <div class="rs-pay__card"><div class="rs-pay__logo rs-pay__logo--bcp">BCP</div><div><p class="rs-pay__label">Banco · N° de cuenta</p><p class="rs-pay__number">19120693956084</p><p class="rs-pay__owner">A nombre de: Patricia Hancco</p></div></div>
                    <div class="rs-pay__card"><div class="rs-pay__logo rs-pay__logo--yape">Yape</div><div><p class="rs-pay__label">Billetera · N° de celular</p><p class="rs-pay__number">{{ $rsTelefono }}</p><p class="rs-pay__owner">A nombre de: Patricia Hancco</p></div></div>
                </div>
            </div>
        </section>

        @php($rsOp = \App\Support\RossetyTestimonios::seccion())
        @if(count($rsOp['items']))
        <section class="rs-section rs-section--cream">
            <div class="rs-container">
                <div class="rs-head"><span class="rs-head__eyebrow">{{ $rsOp['eyebrow'] }}</span><h2 class="rs-head__title">{{ $rsOp['titulo'] }}</h2></div>
                <div class="rs-quotes-wrap js-quotes-wrap">
                    <div class="swiper rs-quotes js-quotes"><div class="swiper-wrapper">
                        @foreach($rsOp['items'] as $q)
                        <div class="swiper-slide"><div class="rs-quote"><div class="rs-quote__stars">{{ str_repeat('★', $q['estrellas']) }}</div><p class="rs-quote__text">"{{ $q['texto'] }}"</p><p class="rs-quote__who">@if($q['avatar'] !== '')<img class="rs-quote__avatar" src="{{ $q['avatar'] }}" alt="{{ $q['nombre'] }}" loading="lazy" />@endif<span>{{ $q['nombre'] }}<small>{{ $q['ciudad'] }}</small></span></p></div></div>
                        @endforeach
                    </div></div>
                    <div class="swiper-pagination rs-quotes__dots"></div>
                </div>
            </div>
        </section>
        @endif
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
    <script src="{{ asset('rossety/assets/js/swiper-bundle.min.js') }}"></script>
    @include('web.rossety.partials.catalog')
    <script src="{{ asset('rossety/assets/js/rossety/core.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cms.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cart.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/home.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
