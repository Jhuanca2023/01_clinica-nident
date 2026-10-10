/* Detalles Rossety · Tienda: filtros (categoría, subcategoría, búsqueda, precio, etiqueta), orden y paginación */
(function () {
    var RS = window.RS, root = document.querySelector('.js-shop');
    if (!RS || !root) return;

    var PAGE = 24;
    var el = {
        grid: root.querySelector('.js-grid'), count: root.querySelector('.js-count'),
        q: root.querySelector('.js-q'), cats: root.querySelector('.js-cats'), subs: root.querySelector('.js-subs'),
        min: root.querySelector('.js-min'), max: root.querySelector('.js-max'), tags: root.querySelector('.js-tags'),
        sort: root.querySelector('.js-sort'), more: root.querySelector('.js-more'), empty: root.querySelector('.js-empty'),
        active: root.querySelector('.js-active'), panel: root.querySelector('.rs-filters'), overlay: root.querySelector('.rs-filters-overlay'),
        title: document.querySelector('.js-shop-title')
    };
    var TAGS = [['', 'Todos'], ['destacado', 'Más pedidos'], ['nuevo', 'Nuevos'], ['temporada', 'Temporada'], ['girasol', 'Girasol']];
    var state = { cat: '', sub: '', q: '', min: '', max: '', tag: '', sort: 'relevancia', view: '3', shown: PAGE };

    var params = new URLSearchParams(window.location.search);
    ['cat', 'sub', 'q', 'tag', 'min', 'max', 'sort'].forEach(function (k) { if (params.get(k)) state[k] = params.get(k); });
    try { var savedView = localStorage.getItem('rs-shop-view'); if (['list','2','3','4'].indexOf(savedView) !== -1) state.view = savedView; } catch (err) { /* Vista predeterminada sin almacenamiento. */ }
    if (['relevancia','menor','mayor','nombre'].indexOf(state.sort) === -1) state.sort = 'relevancia';

    function matches(p, skipCat) {
        if (!skipCat && state.cat && p.cat !== state.cat) return false;
        if (!skipCat && state.sub && p.sub !== state.sub) return false;
        if (state.tag && p.tag !== state.tag) return false;
        if (state.q && !RS.matchesSearch(p, state.q)) return false;
        var price = p.precio;
        if (state.min !== '' && (price == null || price < Number(state.min))) return false;
        if (state.max !== '' && (price == null || price > Number(state.max))) return false;
        return true;
    }
    function sorted(list) {
        var l = list.slice();
        if (state.sort === 'menor') l.sort(function (a, b) { return (a.precio == null) - (b.precio == null) || a.precio - b.precio; });
        if (state.sort === 'mayor') l.sort(function (a, b) { return (a.precio == null) - (b.precio == null) || b.precio - a.precio; });
        if (state.sort === 'nombre') l.sort(function (a, b) { return a.nombre.localeCompare(b.nombre, 'es'); });
        return l;
    }

    function opt(label, count, active, attrs) {
        return '<li><button type="button" class="rs-filter__opt' + (active ? ' rs-filter__opt--active' : '') + '" ' + attrs + '><span>' + RS.esc(label) + '</span><span class="rs-filter__count">' + count + '</span></button></li>';
    }
    function renderFilters() {
        var totalAll = RS.data.productos.filter(function (p) { return matches(p, true); }).length;
        var html = opt('Todas las categorías', totalAll, !state.cat, 'data-cat=""');
        RS.data.categorias.forEach(function (c) {
            var n = RS.data.productos.filter(function (p) { return p.cat === c.slug && matches(p, true); }).length;
            html += opt(c.name, n, state.cat === c.slug, 'data-cat="' + c.slug + '"');
        });
        el.cats.innerHTML = html;

        var sub = '';
        if (state.cat) {
            var c = RS.cat(state.cat);
            sub = opt('Todas', RS.data.productos.filter(function (p) { return p.cat === c.slug && matches(p, true); }).length, !state.sub, 'data-sub=""');
            c.subs.forEach(function (s) {
                var n = RS.data.productos.filter(function (p) { return p.cat === c.slug && p.sub === s && matches(p, true); }).length;
                sub += opt(s, n, state.sub === s, 'data-sub="' + RS.esc(s) + '"');
            });
        }
        el.subs.innerHTML = sub;
        el.subs.parentElement.classList.toggle('rs-hidden', !state.cat);
        el.tags.innerHTML = TAGS.map(function (t) {
            return '<button type="button" class="rs-chip' + (state.tag === t[0] ? ' rs-chip--active' : '') + '" data-tag="' + t[0] + '">' + t[1] + '</button>';
        }).join('');
    }
    function renderActive() {
        var chips = [];
        if (state.cat) chips.push(['cat', RS.cat(state.cat).name]);
        if (state.sub) chips.push(['sub', state.sub]);
        if (state.q) chips.push(['q', '“' + state.q + '”']);
        if (state.tag) chips.push(['tag', (TAGS.filter(function (t) { return t[0] === state.tag; })[0] || ['', state.tag])[1]]);
        if (state.min !== '') chips.push(['min', 'Desde S/ ' + state.min]);
        if (state.max !== '') chips.push(['max', 'Hasta S/ ' + state.max]);
        el.active.innerHTML = chips.map(function (c) {
            return '<button type="button" class="rs-chip rs-chip--active" data-clear="' + c[0] + '">' + RS.esc(c[1]) + ' <i class="ph-bold ph-x"></i></button>';
        }).join('');
    }
    function render() {
        var list = sorted(RS.data.productos.filter(function (p) { return matches(p); }));
        var cat = state.cat ? RS.cat(state.cat) : null;
        if (el.title) el.title.textContent = cat ? cat.name : 'Toda la tienda';
        el.count.innerHTML = '<i class="ph-fill ph-package" aria-hidden="true"></i><strong>' + list.length + '</strong> ' + (list.length === 1 ? 'producto' : 'productos');
        el.grid.dataset.view = state.view;
        root.querySelectorAll('[data-shop-view]').forEach(function (button) {
            var active = button.dataset.shopView === state.view;
            button.classList.toggle('rs-shop__view--active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        RS.renderCards(el.grid, list.slice(0, state.shown));
        el.more.classList.toggle('rs-hidden', list.length <= state.shown);
        el.empty.classList.toggle('rs-hidden', list.length > 0);
        renderFilters();
        renderActive();
        var qs = new URLSearchParams();
        ['cat', 'sub', 'q', 'tag', 'min', 'max'].forEach(function (k) { if (state[k] !== '') qs.set(k, state[k]); });
        if (state.sort !== 'relevancia') qs.set('sort', state.sort);
        history.replaceState(null, '', window.location.pathname + (qs.toString() ? '?' + qs : ''));
    }
    function update(patch) { Object.assign(state, patch, { shown: PAGE }); render(); }
    function togglePanel(open) {
        el.panel.classList.toggle('rs-filters--open', open);
        el.overlay.classList.toggle('rs-filters-overlay--open', open);
    }

    el.q.value = state.q;
    el.min.value = state.min;
    el.max.value = state.max;
    el.sort.value = state.sort;
    var timer;
    el.q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { update({ q: el.q.value.trim() }); }, 200); });
    el.min.addEventListener('input', function () { update({ min: el.min.value }); });
    el.max.addEventListener('input', function () { update({ max: el.max.value }); });
    el.sort.addEventListener('change', function () { update({ sort: el.sort.value }); });
    el.more.addEventListener('click', function () { state.shown += PAGE; render(); });

    root.addEventListener('click', function (e) {
        var viewButton = e.target.closest('[data-shop-view]');
        if (viewButton) {
            state.view = viewButton.dataset.shopView;
            try { localStorage.setItem('rs-shop-view', state.view); } catch (err) { /* Vista utilizable sin almacenamiento. */ }
            render(); return;
        }
        var b;
        if ((b = e.target.closest('[data-cat]')) && el.cats.contains(b)) return update({ cat: b.dataset.cat, sub: '' });
        if ((b = e.target.closest('[data-sub]')) && el.subs.contains(b)) return update({ sub: b.dataset.sub });
        if ((b = e.target.closest('[data-tag]'))) return update({ tag: b.dataset.tag });
        if ((b = e.target.closest('[data-clear]'))) {
            var k = b.dataset.clear, patch = {};
            patch[k] = '';
            if (k === 'cat') patch.sub = '';
            if (k === 'q') el.q.value = '';
            if (k === 'min') el.min.value = '';
            if (k === 'max') el.max.value = '';
            return update(patch);
        }
        if (e.target.closest('.js-reset')) {
            el.q.value = ''; el.min.value = ''; el.max.value = '';
            el.sort.value = 'relevancia';
            return update({ cat: '', sub: '', q: '', min: '', max: '', tag: '', sort: 'relevancia' });
        }
        if (e.target.closest('.js-filters-open')) return togglePanel(true);
        if (e.target.closest('.js-filters-close') || e.target.closest('.rs-filters-overlay')) togglePanel(false);
    });

    render();
})();
