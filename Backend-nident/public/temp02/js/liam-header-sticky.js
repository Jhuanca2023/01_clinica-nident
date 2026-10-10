(function () {
    'use strict';

    var stickyBar   = document.getElementById('lv-sticky-bar');
    var header      = document.getElementById('lv-header');
    var THRESHOLD   = 120; // px de scroll para activar

    /** Ajusta padding-top del body al borde inferior real del header fijo (quita huecos blancos). */
    function syncHeaderOffset() {
        if (!header) return;
        // Con header oculto por scroll, no recalcular (el layout del contenido no debe saltar)
        if (document.body.classList.contains('body-scrolled')) return;

        var bottom = Math.ceil(header.getBoundingClientRect().bottom);
        if (bottom > 0) {
            document.documentElement.style.setProperty('--lv-header-offset', bottom + 'px');
        }
    }

    function onScroll() {
        var scrollY = window.pageYOffset || document.documentElement.scrollTop;

        // Sticky bar: aparece cuando se scrollea más allá del threshold
        if (scrollY > THRESHOLD) {
            stickyBar && stickyBar.classList.add('visible');
            document.body.classList.add('body-scrolled');
        } else {
            stickyBar && stickyBar.classList.remove('visible');
            document.body.classList.remove('body-scrolled');
            syncHeaderOffset();
        }
    }

    if (stickyBar) {
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // ejecutar al cargar por si la página ya está scrolleada
    }

    window.addEventListener('resize', syncHeaderOffset, { passive: true });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncHeaderOffset);
    } else {
        syncHeaderOffset();
    }
    // Fuentes / imágenes del logo pueden cambiar la altura un instante después
    window.addEventListener('load', syncHeaderOffset);
})();
