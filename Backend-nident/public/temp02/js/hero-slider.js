/* =============================================
   Hero Slider - Valento (Swiper.js)
   Independiente de Owl Carousel (que sigue
   usándose en otros carruseles del template).
   ============================================= */

(function () {
    'use strict';

    var hero = document.querySelector('.vl-hero');
    if (!hero || typeof Swiper === 'undefined') return; // sin Swiper se ve el primer slide estático

    var counter = hero.querySelector('.vl-hero__current');
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };

    function setProgress(value) {
        var active = hero.querySelector('.swiper-pagination-bullet-active');
        if (active) active.style.setProperty('--vl-progress', value);
    }

    var swiper = new Swiper(hero.querySelector('.vl-hero__swiper'), {
        loop: true,
        speed: 1100,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        grabCursor: true,
        keyboard: { enabled: true, onlyInViewport: true },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
        },
        navigation: {
            prevEl: hero.querySelector('.vl-hero__nav--prev'),
            nextEl: hero.querySelector('.vl-hero__nav--next')
        },
        pagination: {
            el: hero.querySelector('.vl-hero__pagination'),
            clickable: true
        },
        a11y: {
            prevSlideMessage: 'Slide anterior',
            nextSlideMessage: 'Slide siguiente',
            paginationBulletMessage: 'Ir al slide {{index}}',
            slideLabelMessage: '{{index}} de {{slidesLength}}'
        },
        on: {
            init: function () {
                // Activa animaciones solo cuando Swiper ya marcó el slide activo
                requestAnimationFrame(function () { hero.classList.add('is-ready'); });
            },
            slideChange: function () {
                if (counter) counter.textContent = pad(this.realIndex + 1);
            },
            autoplayTimeLeft: function (s, time, progress) {
                setProgress(1 - progress);
            }
        }
    });

    // Ahorra recursos: detiene el autoplay cuando el hero no está a la vista
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) swiper.autoplay.start();
                else swiper.autoplay.stop();
            });
        }, { threshold: 0.15 }).observe(hero);
    }
})();
