<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Finalizar compra | Detalles Rossety</title>
    <meta name="description" content="Completa tus datos de entrega y envía tu pedido de Detalles Rossety por WhatsApp." />
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
    <link rel="stylesheet" href="{{ asset('rossety/assets/css/rossety/checkout.css') }}" />
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
            <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><a href="/carrito">Carrito</a><span>/</span><span>Finalizar compra</span></div>
            <h1 class="rs-pagehero__title">Finalizar la compra</h1>
            <p class="rs-pagehero__text">Completa tus datos y envía tu pedido por WhatsApp.</p>
            <ol class="rs-steps"><li class="rs-steps__item rs-steps__item--done">Carrito</li><li class="rs-steps__item rs-steps__item--active">Tus datos</li><li class="rs-steps__item">Pedido enviado</li></ol>
        </div>
    </section>
    <main class="rs-section">
        <div class="rs-container js-checkout">
            <div class="rs-cartpage__empty js-no-items rs-hidden">
                <i class="ph ph-handbag"></i>
                <h3>No tienes productos en tu carrito</h3>
                <p>Agrega al menos un detalle para poder finalizar tu pedido.</p>
                <a class="rs-btn rs-btn--primary" href="{{ route('tienda') }}">Ir a la tienda</a>
            </div>
            <div class="rs-co js-has-items">
                <form class="rs-co__form js-form" id="co-form" novalidate>
                    <section class="rs-co-card">
                        <div class="rs-co-card__head"><span class="rs-co-card__icon"><i class="ph-fill ph-user"></i></span><h2 class="rs-co-card__title">Identificación</h2></div>
                        <p class="rs-co-card__hint">Solicitamos únicamente la información esencial para la finalización de la compra.</p>
                        <div class="rs-co-row"><div class="rs-co-field rs-co-field--full"><label class="rs-co-label" for="co-correo">Correo*</label><input class="rs-co-input" id="co-correo" name="correo" data-req type="email" placeholder="cliente@ejemplo.com" autocomplete="email" /><span class="rs-co-error">Este campo es obligatorio.</span></div></div>
                        <div class="rs-co-row">
                            <div class="rs-co-field"><label class="rs-co-label" for="co-nombre">Nombre*</label><input class="rs-co-input" id="co-nombre" name="nombre" data-req type="text" autocomplete="given-name" /><span class="rs-co-error">Este campo es obligatorio.</span></div>
                            <div class="rs-co-field"><label class="rs-co-label" for="co-apellidos">Apellidos*</label><input class="rs-co-input" id="co-apellidos" name="apellidos" data-req type="text" autocomplete="family-name" /><span class="rs-co-error">Este campo es obligatorio.</span></div>
                        </div>
                        <div class="rs-co-row">
                            <div class="rs-co-field"><label class="rs-co-label" for="co-tipodoc">Tipo del documento*</label>
                                <select class="rs-co-input" id="co-tipodoc" name="tipoDoc"><option>DNI</option><option>Carné de extranjería</option><option>Pasaporte</option></select>
                                <span class="rs-co-error"></span></div>
                            <div class="rs-co-field"><label class="rs-co-label" for="co-documento">Documento*</label><input class="rs-co-input" id="co-documento" name="documento" data-req type="text" maxlength="12" autocomplete="off" /><span class="rs-co-error">Este campo es obligatorio.</span></div>
                        </div>
                        <div class="rs-co-row"><div class="rs-co-field rs-co-field--full"><label class="rs-co-label" for="co-telefono">Teléfono / Móvil*</label><input class="rs-co-input" id="co-telefono" name="telefono" data-req type="tel" placeholder="9XXXXXXXX" maxlength="12" autocomplete="tel" /><span class="rs-co-error">Este campo es obligatorio.</span></div></div>
                    </section>

                    <section class="rs-co-card">
                        <div class="rs-co-card__head"><span class="rs-co-card__icon"><i class="ph-fill ph-house"></i></span><h2 class="rs-co-card__title">Envío</h2></div>
                        <p class="rs-co-card__hint">¿Dónde quieres recibir tu pedido?</p>
                        <div class="rs-co-row"><div class="rs-co-field rs-co-field--full"><label class="rs-co-label" for="co-direccion">Dirección completa*</label><input class="rs-co-input" id="co-direccion" name="direccion" data-req type="text" placeholder="Av. Principal 123, referencia" autocomplete="street-address" /><span class="rs-co-error">Este campo es obligatorio.</span></div></div>
                        <button class="rs-co-geo js-geo" type="button"><i class="ph-fill ph-map-pin"></i><span>Compartir mi ubicación</span></button>
                        <input type="hidden" name="ubicacion" value="" />
                        <div class="rs-co-row rs-co-row--3">
                            <div class="rs-co-field"><label class="rs-co-label" for="co-departamento">Departamento*</label>
                                <select class="rs-co-input" id="co-departamento" name="departamento" data-req></select>
                                <span class="rs-co-error">Este campo es obligatorio.</span></div>
                            <div class="rs-co-field"><label class="rs-co-label" for="co-provincia">Provincia*</label>
                                <select class="rs-co-input" id="co-provincia" name="provincia" data-req></select>
                                <input class="rs-co-input rs-hidden" id="co-provincia-txt" name="provinciaTxt" type="text" placeholder="Escribe tu provincia" data-req />
                                <span class="rs-co-error">Este campo es obligatorio.</span></div>
                            <div class="rs-co-field"><label class="rs-co-label" for="co-distrito">Distrito*</label>
                                <select class="rs-co-input" id="co-distrito" name="distrito" data-req></select>
                                <input class="rs-co-input rs-hidden" id="co-distrito-txt" name="distritoTxt" type="text" placeholder="Escribe tu distrito" data-req />
                                <span class="rs-co-error">Este campo es obligatorio.</span></div>
                        </div>
                        <div class="rs-co-row">
                            <div class="rs-co-field rs-co-field--full"><label class="rs-co-label" for="co-fecha">Fecha de entrega deseada</label>
                                <input class="rs-co-input" id="co-fecha" name="fecha" type="date" /><span class="rs-co-error"></span></div>
                        </div>
                        <p class="rs-co-note"><i class="ph-fill ph-info"></i>El costo de envío se coordina contigo por WhatsApp según tu distrito y la fecha de entrega.</p>
                    </section>

                    <section class="rs-co-card">
                        <h2 class="rs-co-card__title rs-co-card__title--plain">Tipo de comprobante *</h2>
                        <div class="rs-co-choices">
                            <label class="rs-co-choice rs-co-choice--active"><input type="radio" name="comprobante" value="Boleta" checked /><span class="rs-co-choice__dot"></span><span><strong><i class="ph ph-file-text"></i>Boleta</strong><small>Persona natural</small></span></label>
                            <label class="rs-co-choice"><input type="radio" name="comprobante" value="Factura" /><span class="rs-co-choice__dot"></span><span><strong><i class="ph ph-buildings"></i>Factura</strong><small>Empresa con RUC</small></span></label>
                        </div>
                        <div class="rs-co-row js-factura rs-hidden">
                            <div class="rs-co-field"><label class="rs-co-label" for="co-ruc">RUC*</label><input class="rs-co-input" id="co-ruc" name="ruc" data-req type="text" maxlength="11" /><span class="rs-co-error">Este campo es obligatorio.</span></div>
                            <div class="rs-co-field"><label class="rs-co-label" for="co-razon">Razón social*</label><input class="rs-co-input" id="co-razon" name="razon" data-req type="text" /><span class="rs-co-error">Este campo es obligatorio.</span></div>
                        </div>
                        <p class="rs-co-required">(*) Datos Obligatorios.</p>
                        <label class="rs-co-check"><input type="checkbox" name="autorizo" /><span>Autorizo el tratamiento de mis datos para fines adicionales.</span></label>
                        <div class="rs-co-field rs-co-field--full">
                            <label class="rs-co-check"><input type="checkbox" name="terminos" /><span>He leído y acepto los <a href="{{ route('contacto.pagina') }}#faq">términos y condiciones</a> y las <a href="{{ route('contacto.pagina') }}#faq">Políticas de Privacidad</a>.*</span></label>
                            <span class="rs-co-error js-terms-error">Debes aceptar los términos y condiciones.</span>
                        </div>
                    </section>

                    <section class="rs-co-card">
                        <div class="rs-co-card__head"><span class="rs-co-card__icon rs-co-card__icon--wa"><i class="fa fa-whatsapp" aria-hidden="true"></i></span><h2 class="rs-co-card__title">Detalles del pedido</h2></div>
                        <p class="rs-co-card__hint">Tu pedido se enviará a nuestra asesora por WhatsApp para coordinar el pago y la entrega.</p>
                        <div class="rs-co-field rs-co-field--full"><label class="rs-co-label" for="co-dedicatoria">Dedicatoria (opcional)</label>
                            <textarea class="rs-co-input rs-co-input--area" id="co-dedicatoria" name="dedicatoria" rows="3" placeholder="Mensaje que irá en la tarjeta del regalo..."></textarea><span class="rs-co-error"></span></div>
                        <div class="rs-co-field rs-co-field--full"><label class="rs-co-label" for="co-obs">Observaciones / Notas adicionales</label>
                            <textarea class="rs-co-input rs-co-input--area" id="co-obs" name="obs" rows="4" placeholder="Especifica colores, modelo del peluche, horario de entrega u otros detalles importantes..."></textarea><span class="rs-co-error"></span></div>
                    </section>
                </form>

                <aside class="rs-co__aside">
                    <h2 class="rs-summary__title">Resumen de la compra</h2>
                    <div class="rs-co__items js-items"></div>
                    <a class="rs-co__back" href="/carrito"><i class="ph-bold ph-arrow-left"></i>Volver al carrito</a>
                    <div class="rs-summary__row"><span>Subtotal</span><strong class="js-sub">S/ 0.00</strong></div>
                    <div class="rs-summary__row rs-summary__row--disc js-disc-row rs-hidden"><span>Cupón <b class="js-coupon-code"></b></span><strong class="js-disc">- S/ 0.00</strong></div>
                    <div class="rs-summary__row"><span><i class="ph-fill ph-truck"></i> Costo de envío</span><em class="rs-summary__note">Por coordinar</em></div>
                    <div class="rs-summary__row rs-summary__row--total"><span>Total</span><strong class="js-total">S/ 0.00</strong></div>
                    <button class="rs-btn rs-btn--wa rs-co__submit js-submit" type="submit" form="co-form">Enviar pedido por WhatsApp</button>
                    <p class="rs-summary__secure"><i class="ph-fill ph-lock-simple"></i>Tu información es segura. El pedido se enviará directamente a nuestro WhatsApp de ventas.</p>
                    <div class="rs-co__box"><i class="fa fa-whatsapp" aria-hidden="true"></i><p>Sin pagos en línea: coordinas el pago y la entrega por WhatsApp con nuestra asesora, quien también confirmará el stock.</p></div>
                    <ul class="rs-co__trust">
                        <li><i class="ph-fill ph-heart"></i>Hecho con amor</li>
                        <li><i class="ph-fill ph-gift"></i>Empaque especial</li>
                        <li><i class="ph-fill ph-truck"></i>Entrega coordinada</li>
                    </ul>
                </aside>
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
    <script src="{{ asset('rossety/assets/js/rossety/data/ubigeo.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/checkout.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
</body>
</html>
