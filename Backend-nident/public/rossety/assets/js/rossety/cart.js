/* Detalles Rossety · Carrito (simulación): estado en localStorage, panel lateral e insignias del header */
(function () {
    var RS = window.RS;
    if (!RS) return;

    var KEY = 'rs_cart', COUPON_KEY = 'rs_coupon';
    var COUPONS = { ROSSETY10: 0.10 };

    function read(key, fallback) {
        try { var v = JSON.parse(localStorage.getItem(key)); return v == null ? fallback : v; } catch (e) { return fallback; }
    }
    function write(key, value) {
        try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* sin almacenamiento */ }
    }
    function isValid(i) {
        var p = i && RS.byId[i.id];
        return !!p && p.precio != null && i.qty > 0;
    }

    var items = read(KEY, []).filter(isValid);
    var coupon = read(COUPON_KEY, '');
    if (!COUPONS[coupon]) coupon = '';

    var cart = RS.cart = {
        lines: function () {
            return items.map(function (i) {
                var p = RS.byId[i.id];
                return { id: i.id, p: p, qty: i.qty, total: p.precio * i.qty };
            });
        },
        count: function () { return items.reduce(function (n, i) { return n + i.qty; }, 0); },
        subtotal: function () { return cart.lines().reduce(function (s, l) { return s + l.total; }, 0); },
        coupon: function () { return coupon; },
        discount: function () { return coupon ? cart.subtotal() * COUPONS[coupon] : 0; },
        total: function () { return cart.subtotal() - cart.discount(); },
        add: function (id, qty) {
            var p = RS.byId[id];
            if (!p || p.precio == null) return false;
            qty = Math.max(1, parseInt(qty, 10) || 1);
            var found = items.filter(function (i) { return i.id === id; })[0];
            if (found) found.qty += qty; else items.push({ id: id, qty: qty });
            save();
            return true;
        },
        setQty: function (id, qty) {
            qty = parseInt(qty, 10) || 0;
            if (qty < 1) return cart.remove(id);
            items.forEach(function (i) { if (i.id === id) i.qty = qty; });
            save();
        },
        remove: function (id) {
            items = items.filter(function (i) { return i.id !== id; });
            save();
        },
        clear: function () { items = []; coupon = ''; write(COUPON_KEY, ''); save(); },
        applyCoupon: function (code) {
            code = String(code || '').trim().toUpperCase();
            if (!COUPONS[code]) return false;
            coupon = code;
            write(COUPON_KEY, coupon);
            save();
            return true;
        },
        removeCoupon: function () { coupon = ''; write(COUPON_KEY, ''); save(); },
        open: function () { render(); panel.classList.add('rs-cart--open'); document.body.classList.add('rs-no-scroll'); },
        close: function () { panel.classList.remove('rs-cart--open'); document.body.classList.remove('rs-no-scroll'); }
    };

    function save() {
        write(KEY, items);
        render();
        document.dispatchEvent(new CustomEvent('rs:cart'));
    }

    /* Panel lateral */
    var panel = document.createElement('div');
    panel.className = 'rs-cart';
    panel.innerHTML =
        '<div class="rs-cart__overlay js-cart-close"></div>' +
        '<aside class="rs-cart__panel" role="dialog" aria-modal="true" aria-label="Carrito de compras">' +
        '<div class="rs-cart__head"><i class="ph-fill ph-handbag"></i><h3 class="rs-cart__title">Carrito</h3>' +
        '<button class="rs-cart__close js-cart-close" type="button" aria-label="Cerrar carrito"><i class="ph-bold ph-x"></i></button></div>' +
        '<div class="rs-cart__body js-cart-body"></div>' +
        '<div class="rs-cart__foot js-cart-foot">' +
        '<div class="rs-cart__row"><span>Subtotal</span><strong class="js-cart-sub">S/ 0.00</strong></div>' +
        '<a class="rs-btn rs-btn--ghost rs-cart__btn" href="/carrito">Ir al carrito</a>' +
        '<a class="rs-btn rs-btn--primary rs-cart__btn" href="/checkout">Finalizar compra</a>' +
        '</div></aside>';
    document.body.appendChild(panel);
    var body = panel.querySelector('.js-cart-body'), foot = panel.querySelector('.js-cart-foot');

    function render() {
        var lines = cart.lines(), n = cart.count();
        document.querySelectorAll('.js-cart-count').forEach(function (el) {
            el.textContent = n;
            el.classList.toggle('rs-icon-btn__count--empty', n === 0);
        });
        foot.classList.toggle('rs-hidden', lines.length === 0);
        if (!lines.length) {
            body.innerHTML = '<div class="rs-cart__empty"><i class="ph ph-handbag"></i><span>Tu carrito está vacío</span>' +
                '<a class="rs-btn rs-btn--primary rs-btn--sm" href="/tienda">Ver la tienda</a></div>';
            return;
        }
        body.innerHTML = lines.map(function (l) {
            return '<div class="rs-cart__item">' +
                '<a class="rs-cart__img" href="/producto?id=' + l.id + '"><img src="' + l.p.imagen + '" alt="' + RS.esc(l.p.nombre) + '" /></a>' +
                '<div class="rs-cart__info">' +
                '<a class="rs-cart__name" href="/producto?id=' + l.id + '">' + RS.esc(l.p.nombre) + '</a>' +
                '<div class="rs-qty"><button class="rs-qty__btn js-cart-dec" type="button" data-id="' + l.id + '" aria-label="Quitar uno">&minus;</button>' +
                '<span class="rs-qty__val">' + l.qty + '</span>' +
                '<button class="rs-qty__btn js-cart-inc" type="button" data-id="' + l.id + '" aria-label="Agregar uno">+</button></div>' +
                '<p class="rs-cart__meta">' + RS.money(l.total) + '</p></div>' +
                '<button class="rs-cart__del js-cart-del" type="button" data-id="' + l.id + '" aria-label="Eliminar"><i class="ph ph-trash"></i></button></div>';
        }).join('') +
            '<div class="rs-cart__notes"><p>* El costo de envío se coordina por WhatsApp según tu distrito.</p>' +
            '<p>* Si tienes un cupón, ingrésalo en el carrito o al finalizar el pedido.</p></div>';
        panel.querySelector('.js-cart-sub').textContent = RS.money(cart.subtotal());
    }

    function qtyOf(id) {
        var l = cart.lines().filter(function (x) { return x.id === id; })[0];
        return l ? l.qty : 0;
    }

    document.addEventListener('click', function (e) {
        var b;
        if ((b = e.target.closest('.js-add-cart'))) {
            e.preventDefault();
            if (cart.add(b.dataset.id, b.dataset.qty)) cart.open();
            return;
        }
        if (e.target.closest('.js-cart-open')) { cart.open(); return; }
        if (e.target.closest('.js-cart-close')) { cart.close(); return; }
        if ((b = e.target.closest('.js-cart-inc'))) { cart.setQty(b.dataset.id, qtyOf(b.dataset.id) + 1); return; }
        if ((b = e.target.closest('.js-cart-dec'))) { cart.setQty(b.dataset.id, qtyOf(b.dataset.id) - 1); return; }
        if ((b = e.target.closest('.js-cart-del'))) cart.remove(b.dataset.id);
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cart.close(); });
    window.addEventListener('storage', function (e) {
        if (e.key !== KEY) return;
        items = read(KEY, []).filter(isValid);
        render();
        document.dispatchEvent(new CustomEvent('rs:cart'));
    });

    render();
})();
