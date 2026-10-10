@php
    $menuCats = $menuCategorias ?? ['categorias' => [], 'bloques' => []];
@endphp
@if(!empty($menuCats['categorias']) || !empty($menuCats['bloques']))
<div class="lv-catbar" id="lv-catbar">
    <div class="lv-catbar__inner">
        <span class="lv-catbar__title"><i class="fa fa-th-large"></i> Categorías</span>
        <div class="lv-catbar__chips">
            @foreach($menuCats['categorias'] as $cat)
            <div class="lv-chip {{ !empty($cat['subs']) ? 'has-sub' : '' }}">
                <a href="{{ $cat['url'] }}" class="lv-chip__link">
                    <span>{{ ucfirst(strtolower($cat['nombre'])) }}</span>
                    @if(!empty($cat['subs']))<i class="fa fa-angle-down"></i>@endif
                </a>
                @if(!empty($cat['subs']))
                <div class="lv-chip__drop">
                    <a href="{{ $cat['url'] }}" class="lv-chip__drop-all">Ver todo</a>
                    @foreach($cat['subs'] as $sub)
                    <a href="{{ $sub['url'] }}">{{ $sub['nombre'] }}</a>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach

            @foreach($menuCats['bloques'] as $bloque)
            <div class="lv-chip lv-chip--promo {{ !empty($bloque['items']) ? 'has-sub' : '' }}">
                <a href="{{ $bloque['url'] }}" class="lv-chip__link">
                    <span>{{ ucfirst(strtolower($bloque['nombre'])) }}</span>
                    @if(!empty($bloque['items']))<i class="fa fa-angle-down"></i>@endif
                </a>
                @if(!empty($bloque['items']))
                <div class="lv-chip__drop">
                    <a href="{{ $bloque['url'] }}" class="lv-chip__drop-all">Todo el bloque</a>
                    @foreach($bloque['items'] as $it)
                    <a href="{{ $it['url'] }}">{{ $it['nombre'] }}</a>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@once
<style>
/* ── Barra horizontal de categorías (chips) ─────────────────────── */
.lv-catbar {
    background: #ffffff;
    border-bottom: 1px solid #eef2f7;
    box-shadow: 0 3px 10px rgba(15, 23, 42, .04);
    margin-top: -2rem;
    position: relative;
    z-index: 40;
}
.lv-catbar__inner {
    max-width: 1500px;
    margin: 0 auto;
    padding: 11px 30px;
    display: flex;
    align-items: flex-start;
    gap: 18px;
}
.lv-catbar__title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
    padding: 8px 0;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: #0f172a;
    white-space: nowrap;
    border-right: 1px solid #eef2f7;
    padding-right: 18px;
}
.lv-catbar__title i { color: #c0392b; font-size: 15px; }

.lv-catbar__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 9px 10px;
    flex: 1 1 auto;
}

.lv-chip { position: relative; }
.lv-chip__link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    background: #ffe6f1;
    color: #334155;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: border-color .18s ease, color .18s ease, background .18s ease, box-shadow .18s ease;
}
.lv-chip__link i { font-size: 11px; opacity: .55; transition: transform .2s ease, opacity .2s ease; }
.lv-chip:hover > .lv-chip__link,
.lv-chip.is-open > .lv-chip__link {
    border-color: #c0392b;
    color: #c0392b;
    background: #fdf3f2;
    box-shadow: 0 4px 12px rgba(192, 57, 43, .14);
}
.lv-chip:hover > .lv-chip__link i,
.lv-chip.is-open > .lv-chip__link i { transform: rotate(180deg); opacity: 1; }

.lv-chip--promo > .lv-chip__link {
    border-color: #f1c9c3;
    color: #c0392b;
    background: #fdf5f4;
}
.lv-chip--promo > .lv-chip__link::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #c0392b;
    flex: 0 0 auto;
}

/* Dropdown de subcategorías / items */
.lv-chip__drop {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    z-index: 1250;
    min-width: 210px;
    max-width: min(300px, calc(100vw - 24px));
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    padding: 8px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(9px);
    pointer-events: none;
    transition: opacity .16s ease, transform .16s ease, visibility .16s;
}
/* Puente invisible para no perder el hover entre el chip y su dropdown */
.lv-chip.has-sub::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    height: 10px;
}
.lv-chip.has-sub:hover > .lv-chip__drop,
.lv-chip.has-sub.is-open > .lv-chip__drop {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}
.lv-chip__drop a {
    display: block;
    padding: 8px 18px;
    color: #475569;
    font-size: 12.5px;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s ease, color .15s ease;
}
.lv-chip__drop a:hover { background: #fdf3f2; color: #c0392b; }
.lv-chip__drop-all {
    font-weight: 700 !important;
    color: #c0392b !important;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 4px;
}

/* ── Móvil: fila horizontal deslizable + dropdown por tap + sticky ── */
@media (max-width: 991px) {
    .lv-catbar {
        margin-top: -1rem;
        position: sticky;
        top: 0;
        z-index: 9050;
        background: #ffffff;
        transition: top .28s ease, box-shadow .28s ease;
    }
    /* Debajo de la barra sticky del header al scrollear */
    body.body-scrolled .lv-catbar {
        top: 56px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .10);
    }
    .lv-catbar__inner {
        padding: 8px 12px;
        gap: 10px;
        align-items: center;
    }
    .lv-catbar__title {
        font-size: 11px;
        padding: 4px 12px 4px 0;
        border-right: 1px solid #eef2f7;
    }
    .lv-catbar__chips {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 2px;
    }
    .lv-catbar__chips::-webkit-scrollbar { display: none; }
    .lv-chip { flex: 0 0 auto; }
    .lv-chip__link { padding: 7px 13px; font-size: 12px; }
    /* En táctil: sin hover; el JS abre con .is-open */
    .lv-chip.has-sub:hover > .lv-chip__drop {
        opacity: 0;
        visibility: hidden;
        transform: translateY(9px);
        pointer-events: none;
    }
    .lv-chip.has-sub.is-open > .lv-chip__drop {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
        /* Fuera del scroll: no altera overflow ni scrollLeft del slider */
        position: fixed;
        z-index: 9300;
        width: auto;
        max-width: min(300px, calc(100vw - 24px));
        min-width: 210px;
        right: auto;
    }
}

/* Pantallas muy pequeñas: oculta la etiqueta "Categorías" para ganar espacio */
@media (max-width: 560px) {
    .lv-catbar__title { display: none; }
}
</style>
<script>
(function () {
    if (window.__lvCatbarBound) return;
    window.__lvCatbarBound = true;

    function isMobileCatbar() {
        return window.matchMedia('(max-width: 991px)').matches;
    }

    function clearDropPos(drop) {
        if (!drop) return;
        drop.style.top = '';
        drop.style.left = '';
        drop.style.right = '';
        drop.style.width = '';
        drop.style.bottom = '';
    }

    function closeAll(except) {
        document.querySelectorAll('.lv-chip.has-sub.is-open').forEach(function (chip) {
            if (except && chip === except) return;
            chip.classList.remove('is-open');
            var link = chip.querySelector('.lv-chip__link');
            if (link) link.setAttribute('aria-expanded', 'false');
            clearDropPos(chip.querySelector('.lv-chip__drop'));
        });
    }

    function placeDrop(chip) {
        var drop = chip.querySelector('.lv-chip__drop');
        var link = chip.querySelector('.lv-chip__link');
        if (!drop || !link || !isMobileCatbar()) return;

        var rect = link.getBoundingClientRect();
        var pad = 12;
        var maxW = Math.min(300, window.innerWidth - pad * 2);
        var left = Math.round(rect.left);
        if (left + maxW > window.innerWidth - pad) {
            left = Math.round(window.innerWidth - pad - maxW);
        }
        if (left < pad) left = pad;

        drop.style.top = Math.round(rect.bottom + 6) + 'px';
        drop.style.left = left + 'px';
        drop.style.right = 'auto';
        drop.style.width = maxW + 'px';
        drop.style.bottom = 'auto';
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('.lv-chip.has-sub > .lv-chip__link');
        if (link && isMobileCatbar()) {
            e.preventDefault();
            e.stopPropagation();

            var chips = document.querySelector('.lv-catbar__chips');
            var scrollLeft = chips ? chips.scrollLeft : 0;

            var chip = link.parentElement;
            var opening = !chip.classList.contains('is-open');
            closeAll(chip);
            chip.classList.toggle('is-open', opening);
            link.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (opening) placeDrop(chip);

            // Restaura scroll por si el navegador lo movió al enfocar/abrir
            if (chips) chips.scrollLeft = scrollLeft;
            return;
        }

        if (!e.target.closest('.lv-chip.has-sub')) {
            closeAll();
        }
    });

    window.addEventListener('resize', function () { closeAll(); });
    window.addEventListener('scroll', function () {
        if (!isMobileCatbar()) return;
        var open = document.querySelector('.lv-chip.has-sub.is-open');
        if (open) placeDrop(open);
    }, { passive: true });

    var chipsEl = document.querySelector('.lv-catbar__chips');
    if (chipsEl) {
        chipsEl.addEventListener('scroll', function () {
            var open = document.querySelector('.lv-chip.has-sub.is-open');
            if (open) placeDrop(open);
        }, { passive: true });
    }

    document.querySelectorAll('.lv-chip.has-sub > .lv-chip__link').forEach(function (link) {
        link.setAttribute('aria-haspopup', 'true');
        link.setAttribute('aria-expanded', 'false');
    });
})();
</script>
@endonce
@endif
