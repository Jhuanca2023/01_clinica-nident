/* =============================================
   Categorías - Carrusel circular (Swiper)
   ============================================= */

(function () {
    'use strict';

    var root = document.querySelector('.lv-cat-carousel');
    if (!root || typeof Swiper === 'undefined') return; // sin Swiper queda una fila deslizable

    // Centra las flechas verticalmente respecto a la imagen circular
    function alignNav() {
        var img = root.querySelector('.lv-cat-card__img');
        if (img) root.style.setProperty('--cc-img', img.offsetHeight + 'px');
    }

    new Swiper(root.querySelector('.swiper'), {
        slidesPerView: 2.2,
        spaceBetween: 16,
        speed: 600,
        grabCursor: true,
        watchOverflow: true,
        keyboard: { enabled: true, onlyInViewport: true },
        navigation: {
            prevEl: root.querySelector('.lv-cat-nav--prev'),
            nextEl: root.querySelector('.lv-cat-nav--next')
        },
        breakpoints: {
            576:  { slidesPerView: 3,   spaceBetween: 20 },
            992:  { slidesPerView: 4,   spaceBetween: 24 },
            1200: { slidesPerView: 5,   spaceBetween: 26 }
        },
        a11y: {
            prevSlideMessage: 'Categorías anteriores',
            nextSlideMessage: 'Más categorías'
        },
        on: {
            init: function () {
                root.classList.add('is-ready');
                alignNav();
            },
            resize: alignNav
        }
    });
})();
