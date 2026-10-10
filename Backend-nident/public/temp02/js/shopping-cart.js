/* =============================================
   Shopping Cart Page — Valento
   Lee el carrito desde localStorage / LVCart
   ============================================= */
'use strict';

(function ($) {

    /* ── Obtener carrito (localStorage o LVCart) ── */
    function getCart() {
        // Primero intenta LVCart (misma pestaña)
        if (window.LVCart && window.LVCart.cart.length > 0) {
            return window.LVCart.cart;
        }
        // Si no, carga desde localStorage (navegación entre páginas)
        try {
            var stored = JSON.parse(localStorage.getItem('lv_cart'));
            if (stored && stored.length > 0) return stored;
        } catch(e) {}

        // Demo por defecto si no hay nada
        return [{
            name  : 'Polo Laboral Atlas',
            price : 33.00,
            img   : 'img/productos/polo/polo-azul-marino.jpg',
            talla : 'M',
            color : 'Azul Marino',
            qty   : 1
        }];
    }

    function saveCart(cart) {
        try { localStorage.setItem('lv_cart', JSON.stringify(cart)); } catch(e) {}
        // Sincronizar con LVCart si está disponible
        if (window.LVCart) {
            window.LVCart.cart.length = 0;
            cart.forEach(function(item) { window.LVCart.cart.push(item); });
            window.LVCart.renderCart();
        }
    }

    /* ── Renderizar tabla ────────────────────── */
    function renderCartPage() {
        var cart    = getCart();
        var $tbody  = $('#sc-tbody');
        var $empty  = $('#sc-empty');
        var $table  = $('#sc-table-wrap');

        if (!cart || cart.length === 0) {
            $table.hide();
            $empty.show();
            updateSummary(0);
            return;
        }

        $empty.hide();
        $table.show();

        var html     = '';
        var subtotal = 0;

        $.each(cart, function (i, item) {
            var lineTotal = parseFloat(item.price) * parseInt(item.qty);
            subtotal += lineTotal;
            var oldPrice  = (item.price * 2).toFixed(2);

            html +=
                '<tr data-idx="' + i + '">' +
                  '<td>' +
                    '<div class="sc-prod-cell">' +
                      '<img class="sc-prod-img" src="' + item.img + '" alt="' + item.name + '">' +
                      '<div>' +
                        '<span class="sc-prod-name">' + item.name + '</span>' +
                        '<br><small style="font-family:Montserrat,sans-serif;font-size:10px;color:#999;letter-spacing:1px;">Talla: ' + item.talla + ' &nbsp;|&nbsp; Color: ' + item.color + '</small>' +
                      '</div>' +
                    '</div>' +
                  '</td>' +
                  '<td class="td-price sc-price-cell">' +
                    '<span class="sc-price-from">De: <span class="sc-price-old">S/ ' + oldPrice + '</span></span>' +
                    '<br><span class="sc-price-por">Por <strong class="sc-price-new">S/ ' + parseFloat(item.price).toFixed(2) + '</strong></span>' +
                  '</td>' +
                  '<td class="td-qty sc-qty-cell">' +
                    '<div class="sc-qty-ctrl">' +
                      '<button class="sc-qty-dec" data-idx="' + i + '">&#8722;</button>' +
                      '<span>' + item.qty + '</span>' +
                      '<button class="sc-qty-inc" data-idx="' + i + '">+</button>' +
                    '</div>' +
                  '</td>' +
                  '<td class="sc-total-cell">S/ ' + lineTotal.toFixed(2) + '</td>' +
                  '<td class="sc-del-cell">' +
                    '<button class="sc-del-btn sc-row-del" data-idx="' + i + '" title="Eliminar">' +
                      '<i class="fa fa-trash-o"></i>' +
                    '</button>' +
                  '</td>' +
                '</tr>';
        });

        $tbody.html(html);
        updateSummary(subtotal);
    }

    function updateSummary(subtotal) {
        var txt = 'S/ ' + subtotal.toFixed(2);
        $('#sc-subtotal').text(txt);
        $('#sc-total').text(txt);
    }

    /* ── Incrementar ──────────────────────────── */
    $(document).on('click', '.sc-qty-inc', function () {
        var idx  = parseInt($(this).data('idx'));
        var cart = getCart();
        cart[idx].qty++;
        saveCart(cart);
        renderCartPage();
    });

    /* ── Decrementar ──────────────────────────── */
    $(document).on('click', '.sc-qty-dec', function () {
        var idx  = parseInt($(this).data('idx'));
        var cart = getCart();
        if (cart[idx].qty > 1) {
            cart[idx].qty--;
        } else {
            cart.splice(idx, 1);
        }
        saveCart(cart);
        renderCartPage();
    });

    /* ── Eliminar ítem ────────────────────────── */
    $(document).on('click', '.sc-row-del', function () {
        var idx  = parseInt($(this).data('idx'));
        var cart = getCart();
        cart.splice(idx, 1);
        saveCart(cart);
        renderCartPage();
    });

    /* ── Toggle cupón ─────────────────────────── */
    $(document).on('click', '#sc-coupon-btn', function () {
        $('#sc-coupon-wrap').toggleClass('open');
        $(this).text(
            $('#sc-coupon-wrap').hasClass('open')
                ? 'Cancelar cupón'
                : 'Añadir cupón de descuento'
        );
    });

    /* ── Init ─────────────────────────────────── */
    $(document).ready(function () {
        setTimeout(renderCartPage, 120);
    });

})(jQuery);
