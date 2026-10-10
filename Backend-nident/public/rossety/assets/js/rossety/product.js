/* Detalles Rossety · Página de detalle de producto (/producto?id=...) */
(function () {
    var RS = window.RS, root = document.querySelector('.js-product');
    if (!RS || !root) return;

    var id = new URLSearchParams(window.location.search).get('id');
    var p = RS.byId[id];
    var view = root.querySelector('.js-view'), missing = root.querySelector('.js-missing');
    if (!p) { view.classList.add('rs-hidden'); missing.classList.remove('rs-hidden'); return; }

    var c = RS.cat(p.cat), canBuy = p.precio != null;
    var $ = function (s) { return root.querySelector(s); };

    document.title = p.nombre + ' | Detalles Rossety';
    var crumb = document.querySelector('.js-crumb-name');
    if (crumb) crumb.textContent = p.nombre;
    var crumbCat = document.querySelector('.js-crumb-cat');
    if (crumbCat) { crumbCat.textContent = c.name; crumbCat.href = '/tienda?cat=' + p.cat; }

    var img = $('.js-p-img');
    img.src = p.imagen;
    img.alt = p.nombre;
    $('.js-p-cat').textContent = c.name + ' · ' + p.sub;
    $('.js-p-name').textContent = p.nombre;
    $('.js-p-model').textContent = p.modelo ? 'Modelo: ' + p.modelo : '';
    $('.js-p-price').innerHTML = RS.priceHTML(p);
    var wa = root.querySelectorAll('.js-p-wa');
    wa.forEach(function (a) { a.href = RS.waLink(RS.productMessage(p)); a.target = '_blank'; a.rel = 'noopener'; });

    var badge = $('.js-p-badge');
    var tags = { destacado: 'Más pedido', nuevo: 'Nuevo', temporada: 'Temporada', girasol: 'Girasol' };
    if (tags[p.tag]) badge.textContent = tags[p.tag]; else badge.classList.add('rs-hidden');

    var info = '';
    if (p.medida) info += '<p class="rs-pinfo__line"><strong>Medida:</strong> ' + RS.esc(p.medida) + '</p>';
    if (p.incluye) info += '<p class="rs-pinfo__label">Incluye:</p><ul class="rs-modal__list">' + p.incluye.map(function (i) { return '<li>' + RS.esc(i) + '</li>'; }).join('') + '</ul>';
    if (p.colores) info += '<p class="rs-pinfo__label">Colores:</p><div class="rs-modal__chips">' + p.colores.map(function (i) { return '<span class="rs-tag">' + RS.esc(i) + '</span>'; }).join('') + '</div>';
    if (p.nota) info += '<p class="rs-modal__note">' + RS.esc(p.nota) + '</p>';
    $('.js-p-desc').innerHTML = info || '<p>Consúltanos por WhatsApp para conocer todos los detalles de este producto.</p>';
    $('.js-p-short').innerHTML = p.incluye ? '<ul class="rs-modal__list">' + p.incluye.slice(0, 4).map(function (i) { return '<li>' + RS.esc(i) + '</li>'; }).join('') + '</ul>' : '';

    /* Compra */
    var qtyVal = $('.js-qty-val'), addBtn = $('.js-p-add'), buyBtn = $('.js-p-buy');
    if (!canBuy) {
        $('.js-p-buybox').classList.add('rs-hidden');
        $('.js-p-consult').classList.remove('rs-hidden');
    }
    function qty() { return Math.max(1, parseInt(qtyVal.textContent, 10) || 1); }
    root.addEventListener('click', function (e) {
        if (e.target.closest('.js-qty-dec')) qtyVal.textContent = Math.max(1, qty() - 1);
        if (e.target.closest('.js-qty-inc')) qtyVal.textContent = qty() + 1;
        var tab = e.target.closest('.js-tab');
        if (tab) {
            root.querySelectorAll('.js-tab').forEach(function (t) { t.classList.toggle('rs-tabs__btn--active', t === tab); });
            root.querySelectorAll('.js-panel').forEach(function (pn) { pn.classList.toggle('rs-tabs__panel--active', pn.dataset.panel === tab.dataset.tab); });
        }
    });
    addBtn.addEventListener('click', function () {
        RS.cart.add(p.id, qty());
        addBtn.innerHTML = '<i class="ph-bold ph-check"></i> Agregado';
        addBtn.classList.add('rs-btn--done');
        setTimeout(function () { addBtn.innerHTML = '<i class="ph-bold ph-handbag"></i> Añadir al carrito'; addBtn.classList.remove('rs-btn--done'); }, 1600);
        RS.cart.open();
    });
    buyBtn.addEventListener('click', function () {
        RS.cart.add(p.id, qty());
        window.location.href = '/checkout';
    });

    /* Relacionados */
    var relBox = document.querySelector('.js-related');
    if (relBox) RS.renderCards(relBox, RS.pick(function (x) { return x.cat === p.cat && x.id !== p.id; }, 4));
})();
