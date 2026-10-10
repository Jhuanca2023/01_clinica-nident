'use strict';

(function () {
    var sidebar  = null;
    var overlay  = null;
    var bodyEl   = null;
    var totalEl  = null;
    var footEl   = null;

    function init() {
        sidebar  = document.getElementById('lv-cart-sidebar');
        overlay  = document.getElementById('lv-cart-overlay');
        bodyEl   = document.getElementById('lv-cart-body');
        totalEl  = document.getElementById('lv-cart-total-val');
        footEl   = document.getElementById('lv-cart-foot');
        if (!sidebar) return;

        overlay.addEventListener('click', closeCart);
        document.getElementById('lv-cart-close').addEventListener('click', closeCart);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeCart();
        });

        syncCartButtonHref();

        document.querySelectorAll('#lv-cart-icon, #lv-cart-icon-sticky').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openCart();
            });
        });

        // Cargar ítems al iniciar para persistir en refresh de página
        loadItems();
    }

    function syncCartButtonHref() {
        if (!footEl) return;
        var cartBtn = footEl.querySelector('.lv-cart-btn-primary');
        if (cartBtn && window.LIAM_SHOP && LIAM_SHOP.routes.carrito) {
            cartBtn.href = LIAM_SHOP.routes.carrito;
        }
    }

    function openCart() {
        if (!sidebar) init();
        if (!sidebar) return;
        loadItems();
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        if (!sidebar) return;
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function loadItems() {
        if (!window.LIAM_SHOP || !window.LIAM_SHOP.routes.cartItems) return;
        fetch(LIAM_SHOP.routes.cartItems)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) renderItems(data.items, data.total);
            })
            .catch(function () {});
    }

    function renderItems(items, total) {
        if (!bodyEl) return;
        if (!items || items.length === 0) {
            bodyEl.innerHTML = '<div class="lv-cart-empty"><i class="fa fa-shopping-bag"></i><span>Tu carrito está vacío</span></div>';
            if (totalEl) totalEl.textContent = 'S/ 0.00';
            if (footEl) footEl.style.display = 'none';
            return;
        }

        var cartBtn = footEl ? footEl.querySelector('.lv-cart-btn-primary') : null;
        if (cartBtn && window.LIAM_SHOP && LIAM_SHOP.routes.carrito) {
            cartBtn.href = LIAM_SHOP.routes.carrito;
        }

        var baseUrl = (window.LIAM_SHOP && window.LIAM_SHOP.baseUrl) ? window.LIAM_SHOP.baseUrl.replace(/\/$/, '') : '';
        var html = '';
        items.forEach(function (item) {
            var imgSrc = item.imagen
                ? (item.imagen.match(/^https?:\/\//) ? item.imagen : baseUrl + '/' + item.imagen.replace(/^\//, ''))
                : '';
            var detailUrl = productDetailUrl(item, baseUrl);
            var nameHtml = detailUrl
                ? '<a href="' + escHtml(detailUrl) + '" class="lv-cart-item__name-link">' + escHtml(item.nombre) + '</a>'
                : escHtml(item.nombre);
            var img = imgSrc
                ? '<img class="lv-cart-item__img" src="' + imgSrc + '" alt="' + escHtml(item.nombre) + '">'
                : '<div class="lv-cart-item__img" style="background:#f0f0f0;"></div>';
            html += '<div class="lv-cart-item">'
                + img
                + '<div class="lv-cart-item__info">'
                + '<p class="lv-cart-item__name">' + nameHtml + '</p>'
                + '<p class="lv-cart-item__meta">Cantidad: ' + item.cantidad + ' - S/ ' + item.subtotal + '</p>'
                + '</div>'
                + '<button class="lv-cart-item__delete" title="Eliminar" onclick="LVCart.deleteItem(\'' + item.rowId + '\')">'
                + '<i class="fa fa-trash-o"></i></button>'
                + '</div>';
        });

        // Notas al pie (plantilla temp02)
        html += '<div class="lv-cart-notes">'
            + '<p>* Envío calculado en la finalización del pedido.</p>'
            + '<p>* Si usted tiene un cupón escríbalo en la finalización de la solicitud.</p>'
            + '</div>';

        bodyEl.innerHTML = html;
        if (footEl) footEl.style.display = 'block';
        if (totalEl) totalEl.textContent = 'S/ ' + total;
    }

    function deleteItem(rowId) {
        if (!window.LIAM_SHOP) return;
        var fd = new FormData();
        fd.append('_token', LIAM_SHOP.csrf);
        fd.append('id_producto', rowId);
        fetch(LIAM_SHOP.routes.eliminar, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                loadItems();
                if (data && data.count !== undefined) {
                    ['#lv-cart-count', '#lv-cart-count-sticky'].forEach(function (sel) {
                        var el = document.querySelector(sel);
                        if (!el) return;
                        var n = parseInt(data.count, 10) || 0;
                        if (n > 0) {
                            el.textContent = n;
                            el.style.display = '';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                }
            })
            .catch(function () {});
    }

    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function productDetailUrl(item, baseUrl) {
        if (item.url) return item.url;
        var slug = item.slug || item.id_producto;
        if (!slug) return '';
        var root = (baseUrl || '').replace(/\/$/, '');
        return root + '/web_shopDetail/' + encodeURIComponent(slug);
    }

    window.LVCart = { openCart: openCart, closeCart: closeCart, deleteItem: deleteItem };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
