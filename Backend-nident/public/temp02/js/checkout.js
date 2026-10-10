/* =============================================
   Checkout Page — Valento
   ============================================= */
'use strict';

(function ($) {

    /* ── Leer carrito ──────────────────────── */
    function getCart() {
        if (window.LVCart && window.LVCart.cart.length > 0) return window.LVCart.cart;
        try {
            var s = JSON.parse(localStorage.getItem('lv_cart'));
            if (s && s.length) return s;
        } catch(e) {}
        return [];
    }

    /* ── Renderizar resumen lateral ────────── */
    function renderSummary() {
        var cart     = getCart();
        var $items   = $('#co-items');
        var subtotal = 0;
        var html     = '';

        if (cart.length === 0) {
            $items.html('<p style="font-family:Montserrat,sans-serif;font-size:12px;color:#bbb;text-align:center;padding:12px 0;">Sin productos</p>');
        } else {
            $.each(cart, function (i, item) {
                var line = parseFloat(item.price) * parseInt(item.qty);
                subtotal += line;
                html +=
                    '<div class="co-aside__item">' +
                        '<img class="co-aside__item-img" src="' + item.img + '" alt="' + item.name + '">' +
                        '<div class="co-aside__item-info">' +
                            '<p class="co-aside__item-name">' + item.name + '</p>' +
                            '<p class="co-aside__item-cant">Cant. ' + item.qty + '</p>' +
                        '</div>' +
                        '<span class="co-aside__item-price">S/ ' + line.toFixed(2) + '</span>' +
                    '</div>';
            });
            $items.html(html);
        }

        $('#co-subtotal').text('S/ ' + subtotal.toFixed(2));
        $('#co-total').text('S/ ' + subtotal.toFixed(2));
    }

    /* ── Validación email en tiempo real ───── */
    $(document).on('input', '#co-email', function () {
        var val   = $(this).val().trim();
        var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
        $(this).toggleClass('valid', valid).toggleClass('error', !valid && val.length > 0);
    });

    /* ── Validación campos obligatorios ────── */
    function validateRequired($input) {
        var empty = $input.val().trim() === '';
        $input.toggleClass('error', empty);
        $input.closest('.co-form-group').find('.co-error-msg').toggleClass('show', empty);
        return !empty;
    }

    $(document).on('blur', '.co-required', function () {
        validateRequired($(this));
    });

    /* ── Activar secciones progresivas ─────── */
    function checkEnvio() {
        var email    = $('#co-email').val().trim();
        var nombre   = $('#co-nombre').val().trim();
        var apellido = $('#co-apellido').val().trim();
        var doc      = $('#co-documento').val().trim();
        var tel      = $('#co-telefono').val().trim();

        if (email && nombre && apellido && doc && tel) {
            $('#co-envio-msg').text('Complete la dirección de envío a continuación.');
            $('#co-envio-form').slideDown(300);
            $('#co-pago-msg').text('Seleccione su método de pago.');
            $('#co-pago-form').slideDown(300);
        }
    }
    $(document).on('blur', '#co-email, #co-nombre, #co-apellido, #co-documento, #co-telefono', checkEnvio);

    /* ── Submit → guardar datos y redirigir ─── */
    $(document).on('click', '#co-submit', function (e) {
        e.preventDefault();
        var ok = true;
        $('.co-required').each(function () {
            if (!validateRequired($(this))) ok = false;
        });
        if (!$('#co-terms').is(':checked')) {
            alert('Debe aceptar los términos y condiciones.');
            ok = false;
        }
        if (!ok) return;

        /* Guardar datos del pedido en sessionStorage */
        var pagoSeleccionado = $('input[name="pago"]:checked').closest('label').text().trim() || 'Transferencia / Depósito Bancario';
        var orderData = {
            nombre   : $('#co-nombre').val().trim(),
            apellido : $('#co-apellido').val().trim(),
            email    : $('#co-email').val().trim(),
            telefono : $('#co-telefono').val().trim(),
            tipoDoc  : $('#co-tipo-doc').val(),
            documento: $('#co-documento').val().trim(),
            direccion: $('input[placeholder="Av. Ejemplo 123, Lima"]').val().trim() || 'No especificada',
            distrito : $('input[placeholder="Miraflores"]').val().trim() || 'No especificado',
            pago     : pagoSeleccionado
        };
        try { sessionStorage.setItem('lv_order', JSON.stringify(orderData)); } catch(e){}

        /* Feedback y redirección */
        var $btn = $(this);
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Procesando...').prop('disabled', true);
        setTimeout(function () {
            window.location.href = 'thank-you.html';
        }, 1200);
    });

    /* ── Init ───────────────────────────────── */
    $(document).ready(function () {
        setTimeout(renderSummary, 120);
    });

})(jQuery);
