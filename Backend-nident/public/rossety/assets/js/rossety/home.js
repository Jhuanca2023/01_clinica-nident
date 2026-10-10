/* Detalles Rossety · Inicio: categorías, carruseles y bloques de productos */
(function () {
    var RS = window.RS;
    if (!RS) return;

    /* Tarjetas de categoría */
    var cats = document.querySelector('.js-home-cats');
    if (cats) {
        cats.innerHTML = RS.data.categorias.map(function (c, i) {
            var total = RS.data.productos.filter(function (p) { return p.cat === c.slug; }).length;
            var wide = i === 0 ? ' rs-catcard--wide' : '';
            return '<a class="rs-catcard' + wide + '" href="/tienda?cat=' + c.slug + '">' +
                '<img src="' + window.RS_BASE + '/images/inicio/cat-' + c.slug + '.jpg" alt="' + RS.esc(c.name) + '" loading="lazy" />' +
                '<div class="rs-catcard__body"><h3 class="rs-catcard__name">' + RS.esc(c.name) + '</h3>' +
                '<p class="rs-catcard__text">' + RS.esc(c.blurb) + '</p>' +
                '<span class="rs-catcard__link">' + total + ' productos <i class="ph-bold ph-arrow-right"></i></span></div></a>';
        }).join('');
    }

    /* Bloques con productos */
    function fill(selector, list) {
        var el = document.querySelector(selector);
        if (el) RS.renderCards(el, list, { wa: false });
    }
    function fillSlides(selector, list) {
        var el = document.querySelector(selector);
        if (!el) return;
        el.innerHTML = list.map(function (p) { return '<div class="swiper-slide">' + RS.cardHTML(p, { wa: false }) + '</div>'; }).join('');
    }
    fillSlides('.js-slides-featured', RS.pick(function (p) { return p.tag === 'destacado'; }, 12));
    fillSlides('.js-slides-new', RS.pick(function (p) { return p.cat === 'ramos'; }, 10));
    fill('.js-grid-sun', RS.pick(function (p) { return p.tag === 'girasol'; }, 4));
    fillSlides('.js-slides-hw', RS.pick(function (p) { return p.cat === 'hotwheels'; }, 8));
    fill('.js-grid-boxes', RS.pick(function (p) { return p.cat === 'cajas' && p.precioNota; }, 4));

    /* Carruseles */
    if (typeof Swiper === 'undefined') return;
    function product(selector, extra) {
        var root = document.querySelector(selector);
        if (!root) return;
        var opts = {
            slidesPerView: 1.4, spaceBetween: 14, loop: false,
            navigation: { prevEl: root.querySelector('.swiper-button-prev'), nextEl: root.querySelector('.swiper-button-next') },
            pagination: { el: root.querySelector('.swiper-pagination'), clickable: true },
            breakpoints: { 560: { slidesPerView: 2.2 }, 800: { slidesPerView: 3, spaceBetween: 18 }, 1100: { slidesPerView: 4, spaceBetween: 22 } }
        };
        new Swiper(root.querySelector('.swiper'), Object.assign(opts, extra || {}));
    }
    product('.js-slider-featured');
    product('.js-slider-new');
    product('.js-slider-hw');

    var quotes = document.querySelector('.js-quotes');
    if (quotes) {
        var total = quotes.querySelectorAll('.swiper-slide').length;
        new Swiper(quotes, {
            slidesPerView: 1, spaceBetween: 22, loop: total > 3, watchOverflow: true, autoHeight: false,
            autoplay: total > 1 ? { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
            pagination: { el: document.querySelector('.js-quotes-wrap .swiper-pagination'), clickable: true },
            breakpoints: { 760: { slidesPerView: 2 }, 1100: { slidesPerView: 3 } }
        });
    }

    var hero = document.querySelector('.js-hero');
    if (hero) new Swiper(hero, { loop: true, speed: 800, autoplay: { delay: 6000, disableOnInteraction: false } });
})();
