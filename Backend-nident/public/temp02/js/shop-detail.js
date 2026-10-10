/* =============================================
   Shop Detail — Valento
   Interacciones de la página de producto
   ============================================= */

'use strict';

(function ($) {

    /* ── Galería: cambiar imagen principal ── */
    $(document).on('click', '.sd-thumb', function () {
        var src = $(this).data('full') || $(this).attr('src');
        $('.sd-main-img').attr('src', src);
        $('.sd-thumb').removeClass('active');
        $(this).addClass('active');
    });

    function resetAddBtn() {
        var $btn = $('#sd-add-to-cart');
        if ($btn.attr('data-added') === 'true') {
            $btn.html('AÑADIR AL CARRITO').attr('data-added', 'false');
        }
    }

    /* ── Selección de color ─────────────────── */
    $(document).on('click', '.sd-color-dot', function () {
        $('.sd-color-dot').removeClass('active');
        $(this).addClass('active');
        resetAddBtn();
        // Si tiene data-img, cambia imagen principal
        var img = $(this).data('img');
        if (img) {
            $('.sd-main-img').attr('src', img);
            $('.sd-thumb').first().attr('src', img);
        }
    });

    /* ── Selección de talla ─────────────────── */
    $(document).on('click', '.sd-size-btn:not(:disabled)', function () {
        $('.sd-size-btn').removeClass('active selected');
        $(this).addClass('active selected');
        resetAddBtn();
    });

    /* ── Cantidad +/- ───────────────────────── */
    $(document).on('click', '.sd-qty-inc', function () {
        var $span = $(this).siblings('span');
        $span.text(parseInt($span.text()) + 1);
    });
    $(document).on('click', '.sd-qty-dec', function () {
        var $span = $(this).siblings('span');
        var val = parseInt($span.text());
        if (val > 1) $span.text(val - 1);
    });

    /* ── Tabs ────────────────────────────────── */
    $(document).on('click', '.sd-tabs__btn', function () {
        var target = $(this).data('tab');
        $('.sd-tabs__btn').removeClass('active');
        $(this).addClass('active');
        $('.sd-tab-panel').removeClass('active');
        $('#' + target).addClass('active');
    });

    /* ── Botón Añadir / Actualizar ─────────── */
    $(document).on('click', '#sd-add-to-cart', function () {
        var $btn  = $(this);
        var name  = $('.sd-info__name').text().trim();
        var price = parseFloat($('.sd-price-new').text().replace('S/ ', '').replace(',', '.'));
        var img   = $('.sd-main-img').attr('src');
        var talla = $('.sd-size-btn.active').text().trim() || 'M';
        var color = $('.sd-color-dot.active').data('name') || 'Único';
        var qty   = parseInt($('.sd-qty-ctrl span').text()) || 1;

        // Usar API global expuesta por main.js
        if (window.LVCart) {
            window.LVCart.addItem({  // addItem ya llama saveCart internamente
                name  : name,
                price : price,
                img   : img,
                talla : talla,
                color : color,
                qty   : qty
            });
        }

        // Cambiar texto del botón a ACTUALIZAR
        $btn.html('<i class="fa fa-refresh"></i> ACTUALIZAR');
        $btn.attr('data-added', 'true');
    });

})(jQuery);
