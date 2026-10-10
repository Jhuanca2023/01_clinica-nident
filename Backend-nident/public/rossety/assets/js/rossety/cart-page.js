/* Detalles Rossety · Página del carrito: tabla de productos, cupón y resumen */
(function () {
    var RS = window.RS, root = document.querySelector('.js-cart-page');
    if (!RS || !RS.cart || !root) return;

    var $ = function (s) { return root.querySelector(s); };
    var el = {
        rows: $('.js-rows'), table: $('.js-table'), empty: $('.js-empty'), side: $('.js-side'),
        sub: $('.js-sub'), discRow: $('.js-disc-row'), disc: $('.js-disc'), total: $('.js-total'),
        form: $('.js-coupon-form'), input: $('.js-coupon-input'), msg: $('.js-coupon-msg'), toggle: $('.js-coupon-toggle'),
        code: $('.js-coupon-code')
    };

    function render() {
        var lines = RS.cart.lines(), has = lines.length > 0;
        el.table.classList.toggle('rs-hidden', !has);
        el.side.classList.toggle('rs-hidden', !has);
        el.empty.classList.toggle('rs-hidden', has);
        el.rows.innerHTML = lines.map(function (l) {
            var cat = RS.cat(l.p.cat);
            return '<tr class="rs-ctable__row">' +
                '<td data-label="Producto"><div class="rs-ctable__prod">' +
                '<a class="rs-ctable__img" href="/producto?id=' + l.id + '"><img src="' + l.p.imagen + '" alt="' + RS.esc(l.p.nombre) + '" /></a>' +
                '<div><a class="rs-ctable__name" href="/producto?id=' + l.id + '">' + RS.esc(l.p.nombre) + '</a>' +
                '<small class="rs-ctable__sub">' + RS.esc(cat.name) + (l.p.modelo ? ' · Modelo ' + RS.esc(l.p.modelo) : '') + '</small></div></div></td>' +
                '<td data-label="Precio"><span class="rs-ctable__price">' + RS.money(l.p.precio) + '</span></td>' +
                '<td data-label="Cantidad"><div class="rs-qty"><button class="rs-qty__btn js-cart-dec" type="button" data-id="' + l.id + '" aria-label="Quitar uno">&minus;</button>' +
                '<span class="rs-qty__val">' + l.qty + '</span>' +
                '<button class="rs-qty__btn js-cart-inc" type="button" data-id="' + l.id + '" aria-label="Agregar uno">+</button></div></td>' +
                '<td data-label="Total"><strong class="rs-ctable__total">' + RS.money(l.total) + '</strong></td>' +
                '<td><button class="rs-cart__del js-cart-del" type="button" data-id="' + l.id + '" aria-label="Eliminar"><i class="ph ph-trash"></i></button></td></tr>';
        }).join('');
        el.sub.textContent = RS.money(RS.cart.subtotal());
        el.total.textContent = RS.money(RS.cart.total());
        var coupon = RS.cart.coupon();
        el.discRow.classList.toggle('rs-hidden', !coupon);
        el.disc.textContent = '- ' + RS.money(RS.cart.discount());
        el.code.textContent = coupon;
    }

    el.toggle.addEventListener('click', function () {
        var open = el.form.classList.toggle('rs-hidden') === false;
        el.toggle.textContent = open ? 'Cancelar cupón' : 'Añadir cupón de descuento';
        if (open) el.input.focus();
    });
    el.form.addEventListener('submit', function (e) {
        e.preventDefault();
        var ok = RS.cart.applyCoupon(el.input.value);
        el.msg.textContent = ok ? '¡Cupón aplicado! 10% de descuento.' : 'Ese cupón no es válido.';
        el.msg.classList.toggle('rs-form-msg--ok', ok);
        el.msg.classList.toggle('rs-form-msg--error', !ok);
        if (ok) el.input.value = '';
    });
    root.addEventListener('click', function (e) {
        if (e.target.closest('.js-coupon-remove')) RS.cart.removeCoupon();
    });

    document.addEventListener('rs:cart', render);
    render();
})();
