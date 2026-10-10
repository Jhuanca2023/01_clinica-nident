/* Detalles Rossety · Núcleo: utilidades, tarjetas de producto, modal de detalle y comportamiento del layout */
(function () {
    var DATA = window.ROSSETY;
    var PLACEHOLDER = (window.RS_BASE || '') + '/images/catalogo/placeholder.svg';
    var TAGS = { destacado: ['Más pedido', ''], nuevo: ['Nuevo', 'rs-badge--new'], temporada: ['Temporada', 'rs-badge--season'], girasol: ['Girasol', 'rs-badge--sun'] };

    var RS = window.RS = {
        data: DATA,
        byId: {},
        money: function (n) { return 'S/ ' + Number(n).toFixed(2); },
        esc: function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); },
        cat: function (slug) { return DATA.categorias.filter(function (c) { return c.slug === slug; })[0] || { name: '', slug: slug, subs: [] }; },
        waLink: function (text) { return 'https://wa.me/' + DATA.negocio.whatsapp + '?text=' + encodeURIComponent(text); },
        productMessage: function (p) {
            var precio = p.precio == null ? 'consultar precio' : RS.money(p.precio);
            return 'Hola Detalles Rossety, quiero información de: ' + p.nombre + (p.modelo ? ' (Modelo ' + p.modelo + ')' : '') + ' - ' + precio;
        },
        priceHTML: function (p) {
            if (p.precio == null) return '<strong>Consultar</strong>';
            return '<strong>' + RS.money(p.precio) + '</strong>' + (p.precioNota ? '<small>' + RS.esc(p.precioNota) + '</small>' : '');
        },
        cardHTML: function (p, opts) {
            var wa = !!opts && opts.wa === true;
            var c = RS.cat(p.cat), tag = TAGS[p.tag];
            var badge = tag ? '<span class="rs-badge ' + tag[1] + '">' + tag[0] + '</span>' : '';
            return '<article class="rs-card">' +
                '<button class="rs-card__media js-open-detail" type="button" data-id="' + p.id + '" aria-label="Ver detalle de ' + RS.esc(p.nombre) + '">' +
                badge + '<img src="' + p.imagen + '" alt="' + RS.esc(p.nombre) + '" loading="lazy" /></button>' +
                '<div class="rs-card__body">' +
                '<span class="rs-card__cat">' + RS.esc(c.name) + ' · ' + RS.esc(p.sub) + '</span>' +
                '<h3 class="rs-card__name">' + RS.esc(p.nombre) + '</h3>' +
                (p.modelo ? '<p class="rs-card__model">Modelo ' + RS.esc(p.modelo) + '</p>' : '') +
                '<div class="rs-card__price">' + RS.priceHTML(p) + '</div>' +
                '<div class="rs-card__actions">' +
                '<a class="rs-btn rs-btn--ghost rs-btn--sm" href="/producto?id=' + p.id + '">Ver detalle</a>' +
                (p.precio == null ? '' : '<button class="rs-btn rs-btn--primary rs-btn--sm js-add-cart" type="button" data-id="' + p.id + '" aria-label="Agregar ' + RS.esc(p.nombre) + ' al carrito"><i class="ph-bold ph-handbag"></i></button>') +
                (wa ? '<a class="rs-btn rs-btn--wa rs-btn--sm" target="_blank" rel="noopener" href="' + RS.waLink(RS.productMessage(p)) + '" aria-label="Pedir por WhatsApp"><i class="ph-fill ph-whatsapp-logo"></i></a>' : '') +
                '</div></div></article>';
        },
        renderCards: function (container, list, opts) {
            container.innerHTML = list.map(function (p) { return RS.cardHTML(p, opts); }).join('');
        },
        pick: function (fn, n) { return DATA.productos.filter(fn).slice(0, n); }
    };
    RS.normalizeSearch = function (value) {
        return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    };
    RS.matchesSearch = function (product, query) {
        var words = RS.normalizeSearch(query).split(' ').filter(Boolean);
        var text = RS.normalizeSearch([product.nombre, product.sub, product.modelo, product.codigo, RS.cat(product.cat).name].concat(product.incluye || []).join(' '));
        return words.every(function (word) { return text.indexOf(word) !== -1; });
    };

    /* Resultados del buscador del header y del menú móvil. */
    document.querySelectorAll('form.rs-search').forEach(function (form, index) {
        var input = form.querySelector('input[name="q"]');
        if (!input) return;
        var panel = document.createElement('div');
        panel.id = 'rs-search-results-' + index;
        panel.className = 'rs-search-results';
        panel.hidden = true;
        panel.setAttribute('aria-label', 'Resultados de búsqueda');
        form.appendChild(panel);
        input.setAttribute('aria-controls', panel.id);
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('autocomplete', 'off');
        function close() { panel.hidden = true; input.setAttribute('aria-expanded', 'false'); }
        function show() {
            var query = input.value.trim();
            if (!query) { close(); return; }
            var results = DATA.productos.filter(function (product) { return RS.matchesSearch(product, query); });
            panel.innerHTML = '<p class="rs-search-results__count" role="status">' + results.length + (results.length === 1 ? ' producto encontrado' : ' productos encontrados') + '</p>' +
                results.slice(0, 6).map(function (product) {
                    return '<a class="rs-search-result" href="/producto?id=' + encodeURIComponent(product.id) + '"><img src="' + RS.esc(product.imagen) + '" alt=""/><span><strong>' + RS.esc(product.nombre) + '</strong><small>' + (product.precio == null ? 'Consultar precio' : RS.money(product.precio)) + '</small></span></a>';
                }).join('') + (results.length ? '<a class="rs-search-results__all" href="/tienda?q=' + encodeURIComponent(query) + '">Ver todos los resultados <i class="ph-bold ph-arrow-right"></i></a>' : '<p class="rs-search-results__empty">Prueba con otro nombre, categoría o código.</p>');
            panel.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }
        input.addEventListener('input', show);
        input.addEventListener('focus', function () { if (input.value.trim()) show(); });
        form.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { close(); input.focus(); close(); }
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                if (panel.hidden) show();
                var links = Array.from(panel.querySelectorAll('a'));
                if (!links.length) return;
                event.preventDefault();
                var current = links.indexOf(document.activeElement);
                var next = event.key === 'ArrowDown' ? (current + 1) % links.length : (current <= 0 ? links.length - 1 : current - 1);
                links[next].focus();
            }
        });
        form.addEventListener('focusout', function (event) { if (!form.contains(event.relatedTarget)) close(); });
        document.addEventListener('click', function (event) { if (!form.contains(event.target)) close(); });
        form.addEventListener('submit', function () { input.value = input.value.trim(); });
    });

    DATA.productos.forEach(function (p) { RS.byId[p.id] = p; });

    /* Imagen provisional cuando aún no se subió la foto */
    document.addEventListener('error', function (e) {
        var img = e.target;
        if (img.tagName === 'IMG' && img.getAttribute('src') !== PLACEHOLDER && !img.dataset.fallback) {
            img.dataset.fallback = '1';
            img.src = PLACEHOLDER;
        }
    }, true);

    /* Modal de detalle de producto */
    var modal;
    function buildModal() {
        modal = document.createElement('div');
        modal.className = 'rs-modal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.innerHTML = '<div class="rs-modal__overlay js-close-detail"></div><div class="rs-modal__box">' +
            '<button class="rs-modal__close js-close-detail" type="button" aria-label="Cerrar"><i class="ph-bold ph-x"></i></button>' +
            '<div class="rs-modal__media"><img class="js-m-img" alt="" /></div>' +
            '<div class="rs-modal__info"></div></div>';
        document.body.appendChild(modal);
    }
    function openDetail(id) {
        var p = RS.byId[id];
        if (!p) return;
        if (!modal) buildModal();
        var c = RS.cat(p.cat), html = '';
        var img = modal.querySelector('.js-m-img');
        img.removeAttribute('data-fallback');
        img.src = p.imagen;
        img.alt = p.nombre;
        html += '<span class="rs-modal__cat">' + RS.esc(c.name) + ' · ' + RS.esc(p.sub) + '</span>';
        html += '<h2 class="rs-modal__name">' + RS.esc(p.nombre) + '</h2>';
        if (p.modelo) html += '<p class="rs-modal__model">Modelo: ' + RS.esc(p.modelo) + '</p>';
        html += '<div class="rs-modal__price">' + RS.priceHTML(p) + '</div>';
        if (p.medida) html += '<p class="rs-modal__model">Medida: ' + RS.esc(p.medida) + '</p>';
        if (p.incluye) html += '<p class="rs-modal__label">Descripción:</p><ul class="rs-modal__list">' + p.incluye.map(function (i) { return '<li>' + RS.esc(i) + '</li>'; }).join('') + '</ul>';
        if (p.colores) html += '<p class="rs-modal__label">Colores:</p><div class="rs-modal__chips">' + p.colores.map(function (i) { return '<span class="rs-tag">' + RS.esc(i) + '</span>'; }).join('') + '</div>';
        if (p.nota) html += '<p class="rs-modal__note">' + RS.esc(p.nota) + '</p>';
        html += '<div class="rs-modal__cta">' + (p.precio == null ? '' : '<button class="rs-btn rs-btn--primary js-add-cart js-close-detail" type="button" data-id="' + p.id + '"><i class="ph-bold ph-handbag"></i> Agregar al carrito</button>') + '<a class="rs-btn rs-btn--ghost" href="/producto?id=' + p.id + '">Ver página del producto</a><a class="rs-btn rs-btn--wa" target="_blank" rel="noopener" href="' + RS.waLink(RS.productMessage(p)) + '"><i class="ph-fill ph-whatsapp-logo"></i> Pedir por WhatsApp</a>' +
            '<a class="rs-btn rs-btn--ghost" href="/tienda?cat=' + p.cat + '">Ver más de ' + RS.esc(c.name) + '</a></div>';
        modal.querySelector('.rs-modal__info').innerHTML = html;
        modal.classList.add('rs-modal--open');
        document.body.classList.add('rs-no-scroll');
    }
    function closeDetail() {
        if (!modal) return;
        modal.classList.remove('rs-modal--open');
        document.body.classList.remove('rs-no-scroll');
    }
    RS.openDetail = openDetail;

    document.addEventListener('click', function (e) {
        var open = e.target.closest('.js-open-detail');
        if (open) { openDetail(open.dataset.id); return; }
        if (e.target.closest('.js-close-detail')) { closeDetail(); return; }
        var drawerOpen = e.target.closest('.js-drawer-open');
        var drawer = document.querySelector('.rs-drawer');
        if (drawerOpen && drawer) { drawer.classList.add('rs-drawer--open'); document.body.classList.add('rs-no-scroll'); return; }
        if (e.target.closest('.js-drawer-close') && drawer) { drawer.classList.remove('rs-drawer--open'); document.body.classList.remove('rs-no-scroll'); return; }
        if (e.target.closest('.js-top')) window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        closeDetail();
        var drawer = document.querySelector('.rs-drawer--open');
        if (drawer) { drawer.classList.remove('rs-drawer--open'); document.body.classList.remove('rs-no-scroll'); }
    });

    /* Header con sombra y botón "subir" */
    var header = document.querySelector('.rs-header'), topBtn = document.querySelector('.js-top');
    function onScroll() {
        var y = window.scrollY || document.documentElement.scrollTop;
        if (header) header.classList.toggle('rs-header--shadow', y > 8);
        if (topBtn) topBtn.classList.toggle('rs-top-btn--show', y > 500);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* Suscripción: abre WhatsApp con el correo para recibir ofertas */
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('.js-newsletter');
        if (!form) return;
        e.preventDefault();
        window.open(RS.waLink('Hola Detalles Rossety, quiero recibir ofertas y novedades. Mi correo es: ' + form.elements.correo.value.trim()), '_blank', 'noopener');
        form.reset();
    });

    /* Botón flotante de WhatsApp: muestra el mensaje unos segundos al cargar (una vez por sesión) */
    var waFloat = document.querySelector('.js-wa-float');
    if (waFloat) {
        var seen = false;
        try { seen = sessionStorage.getItem('rs-wa-tip') === '1'; } catch (err) { /* sin almacenamiento */ }
        if (!seen) {
            setTimeout(function () { waFloat.classList.add('rs-wa-float--tip'); }, 4000);
            setTimeout(function () {
                waFloat.classList.remove('rs-wa-float--tip');
                try { sessionStorage.setItem('rs-wa-tip', '1'); } catch (err) { /* sin almacenamiento */ }
            }, 9500);
        }
    }

    /* Títulos de sección: aparecen con animación al entrar en pantalla */
    var heads = document.querySelectorAll('.rs-head');
    if ('IntersectionObserver' in window && heads.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('rs-head--in'); io.unobserve(en.target); }
            });
        }, { threshold: 0.35 });
        heads.forEach(function (h) { h.classList.add('rs-head--anim'); io.observe(h); });
    }

    /* Enlaces de WhatsApp genéricos */
    document.querySelectorAll('.js-wa').forEach(function (a) {
        a.href = RS.waLink(a.dataset.msg || 'Hola Detalles Rossety, quisiera información de sus productos.');
        a.target = '_blank';
        a.rel = 'noopener';
    });
})();
