$(document).ready(function () {

    // FIX: sticky-header para header tipo intro-clearance (overlay sobre el slider).
    //
    // PROBLEMA RAÍZ: el header.header-intro-clearance tiene position:absolute sobre
    // el intro-slider, por lo que anchor.offset().top ≈ 0.
    // Con triggerPoint = 0, la condición scrollTop(0) >= 0 se cumple al cargar
    // la página → .fixed se aplica de inmediato → top:0 sube la barra al tope
    // del viewport cortando el menú.
    //
    // SOLUCIÓN: calcular triggerPoint como la altura real de las secciones del
    // header que están SOBRE la barra sticky (header-top + header-middle).
    // Así el sticky sólo se activa cuando el usuario ha desplazado la página
    // lo suficiente para que esas secciones salgan de la vista.

    var $sticky = $('.header-bottom.sticky-header');
    if (!$sticky.length || $(window).width() < 992) return;

    var stickyHeight = 0;
    var triggerPoint = 0;

    // Obtiene la posición de scroll de forma universal
    // (cubre los casos en que el scroll container sea body, html o window)
    function getScrollTop() {
        return window.pageYOffset
            || document.documentElement.scrollTop
            || document.body.scrollTop
            || 0;
    }

    function recalculate() {
        stickyHeight = $sticky.outerHeight(true);

        var $wrapper = $sticky.closest('.sticky-wrapper');
        var anchor   = $wrapper.length ? $wrapper : $sticky;
        var rawTop   = anchor.offset().top;

        // Si el header es overlay (position:absolute sobre el slider),
        // offset().top es ≈ 0. En ese caso usamos la altura acumulada de
        // las secciones del header que están sobre el sticky.
        if (rawTop < 20) {
            triggerPoint = ($('.header-top').outerHeight(true) || 0)
                         + ($('.header-middle').outerHeight(true) || 0);
        } else {
            triggerPoint = rawTop;
        }

        // Fallback: si aún es 0, usar altura del header completo
        if (!triggerPoint) {
            triggerPoint = $('.header').outerHeight(true) || 80;
        }
    }

    function handleScroll() {
        var scrollTop = getScrollTop();
        var $wrapper  = $sticky.closest('.sticky-wrapper');

        if (scrollTop >= triggerPoint) {
            if (!$sticky.hasClass('fixed')) {
                stickyHeight = $sticky.outerHeight(true);
                $sticky.addClass('fixed');
                if ($wrapper.length) $wrapper.height(stickyHeight);
            }
        } else {
            if ($sticky.hasClass('fixed')) {
                $sticky.removeClass('fixed');
                if ($wrapper.length) $wrapper.height('');
            }
        }
    }

    // Recalcular y corregir estado inicial tan pronto como el DOM esté listo
    recalculate();
    handleScroll(); // corrige cualquier .fixed que el Waypoint haya aplicado prematuramente

    // Recalcular de nuevo al cargar todo (imágenes → alturas definitivas)
    $(window).on('load.stickyFix', function () {
        recalculate();
        handleScroll();
    });

    // Escuchar scroll en window Y en body (cubre ambos casos de scroll container)
    $(window).on('scroll.stickyFix', handleScroll);
    $('body').on('scroll.stickyFix', handleScroll);

    $(window).on('resize.stickyFix', function () {
        if ($(window).width() < 992) {
            $sticky.removeClass('fixed');
            var $w = $sticky.closest('.sticky-wrapper');
            if ($w.length) $w.height('');
        } else {
            recalculate();
            handleScroll();
        }
    });

});
