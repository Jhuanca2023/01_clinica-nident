/* Detalles Rossety · Animaciones al hacer scroll (AOS).
   Aquí se define QUÉ se anima y CÓMO; el HTML no lleva atributos. AOS arranca cuando termina la pantalla de carga.
   Cada regla: [selector, efecto, { paso: ms entre elementos, retraso: ms iniciales, duracion: ms }]. */
(function () {
    if (typeof AOS === 'undefined') return;

    var RULES = [
        /* Cabecera de páginas internas */
        ['.rs-crumbs', 'fade-down', {}],
        ['.rs-pagehero__title', 'fade-up', { retraso: 100 }],
        ['.rs-pagehero__text', 'fade-up', { retraso: 250 }],

        /* Inicio · banner */
        ['.rs-hero__card', 'fade-left', { paso: 200, retraso: 300 }],
        ['.rs-strip__list > *', 'fade-up', { paso: 90 }],

        /* Inicio · categorías (entran desde izquierda, abajo y derecha) */
        ['.js-home-cats > *', 'spread', { paso: 100 }],

        /* Inicio · bloques de productos */
        ['.rs-slider', 'fade-up', { duracion: 1000 }],
        ['.rs-center', 'fade-up', {}],
        ['.rs-sun__intro > div:first-child', 'fade-right', { duracion: 1000 }],
        ['.rs-sun__grid', 'fade-left', { duracion: 1000, retraso: 150 }],
        ['.rs-wholesale > div:first-child', 'fade-right', { duracion: 1000 }],
        ['.js-grid-boxes', 'fade-left', { duracion: 1000, retraso: 150 }],
        ['.rs-race__badge', 'zoom-in', { paso: 120 }],

        /* Inicio · confianza y cierre */
        ['.rs-why', 'zoom-in-up', { duracion: 1000 }],
        ['.rs-steps > *', 'fade-up', { paso: 130 }],
        ['.rs-pay__card:nth-child(odd)', 'fade-right', {}],
        ['.rs-pay__card:nth-child(even)', 'fade-left', {}],
        ['.rs-quotes > *', 'flip-up', { paso: 150, duracion: 1000 }],
        ['.rs-cta__title', 'fade-up', {}],
        ['.rs-cta__text', 'fade-up', { retraso: 150 }],
        ['.rs-cta .rs-btn', 'zoom-in', { retraso: 300 }],

        /* Nosotros */
        ['.rs-about__media', 'fade-right', { duracion: 1100 }],
        ['.rs-about__text', 'fade-left', { duracion: 1100, retraso: 150 }],
        ['.rs-stats > *', 'zoom-in', { paso: 130 }],
        ['.rs-values > *', 'fade-up', { paso: 150 }],
        ['.rs-lines', 'fade-up', {}],

        /* Contacto */
        ['.rs-contact__cards > *', 'fade-right', { paso: 110 }],
        ['.rs-form', 'fade-left', { duracion: 1000, retraso: 150 }],
        ['.rs-map', 'zoom-in-up', { duracion: 1000 }],
        ['.rs-faq__item', 'fade-up', { paso: 100 }],

        /* Tienda (las tarjetas se redibujan al filtrar, por eso no se animan una a una) */
        ['.rs-shop__bar', 'fade-down', {}]
    ];

    var SPREAD = function (i, last) { return i === 0 ? 'fade-right' : (i === last ? 'fade-left' : (i % 2 ? 'fade-up' : 'zoom-in-up')); };

    RULES.forEach(function (rule) {
        var els = document.querySelectorAll(rule[0]), o = rule[2], last = els.length - 1;
        Array.prototype.forEach.call(els, function (el, i) {
            el.setAttribute('data-aos', rule[1] === 'spread' ? SPREAD(i, last) : rule[1]);
            el.setAttribute('data-aos-delay', String((o.retraso || 0) + (o.paso ? i * o.paso : 0)));
            if (o.duracion) el.setAttribute('data-aos-duration', String(o.duracion));
        });
    });

    var started = false;
    function start() {
        if (started) return;
        started = true;
        AOS.init({
            duration: 800, easing: 'ease-out-cubic', once: true, offset: 80,
            disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches
        });
    }
    document.addEventListener('rs:ready', start, { once: true });
    setTimeout(start, 4500); /* respaldo por si la pantalla de carga no avisa */
})();
