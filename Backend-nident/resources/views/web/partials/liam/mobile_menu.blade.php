@php
    $mm = $menuCategorias ?? ['categorias' => [], 'bloques' => []];
    $mmNav = $navItems ?? [];
    $mmActive = $active ?? '';
    $mmHasCats = !empty($mm['categorias']) || !empty($mm['bloques']);
@endphp

{{-- ════════════════════════════════════════════════════════════
     MENÚ OFF-CANVAS — SOLO MÓVIL (drawer multinivel tipo "MENÚ")
     ════════════════════════════════════════════════════════════ --}}
<div class="lv-drawer" id="lv-drawer" aria-hidden="true">
    <div class="lv-drawer__overlay" data-drawer-close></div>
    <aside class="lv-drawer__sheet" role="dialog" aria-label="Menú de navegación">
        <div class="lv-drawer__panels">

            {{-- ── Panel raíz ── --}}
            <section class="lv-drawer__panel is-active" data-panel="dp-root">
                <div class="lv-drawer__head"><span class="lv-drawer__title">Menú</span></div>
                <button type="button" class="lv-drawer__back" data-drawer-close>
                    <i class="fa fa-angle-left"></i> Volver
                </button>
                <ul class="lv-drawer__list">
                    @foreach($mmNav as $i => $item)
                        @php
                            $lbl  = $item['label'] ?? '';
                            $url  = $resolveNavUrl($item);
                            $kids = collect($item['children'] ?? [])
                                ->filter(fn ($c) => is_array($c) && trim((string) ($c['label'] ?? '')) !== '')
                                ->values();
                            $act  = ($mmActive === strtolower($lbl)) || ($mmActive === 'home' && $lbl === 'Inicio');
                        @endphp
                        <li class="lv-drawer__row">
                            <a href="{{ $url }}" class="lv-drawer__link {{ $act ? 'is-active' : '' }}">{{ strtoupper($lbl) }}</a>
                            @if($kids->isNotEmpty())
                            <button type="button" class="lv-drawer__into" data-into="dp-nav-{{ $i }}" aria-label="Ver {{ $lbl }}">
                                <i class="fa fa-angle-right"></i>
                            </button>
                            @endif
                        </li>
                    @endforeach

                    @if($mmHasCats)
                    <li class="lv-drawer__row">
                        <a href="{{ url('/tienda') }}" class="lv-drawer__link">CATEGORÍAS</a>
                        <button type="button" class="lv-drawer__into" data-into="dp-cat" aria-label="Ver categorías">
                            <i class="fa fa-angle-right"></i>
                        </button>
                    </li>
                    @endif
                </ul>
            </section>

            {{-- ── Sub-paneles de ítems del nav con hijos ── --}}
            @foreach($mmNav as $i => $item)
                @php
                    $lbl  = $item['label'] ?? '';
                    $kids = collect($item['children'] ?? [])
                        ->filter(fn ($c) => is_array($c) && trim((string) ($c['label'] ?? '')) !== '')
                        ->values();
                @endphp
                @if($kids->isNotEmpty())
                <section class="lv-drawer__panel" data-panel="dp-nav-{{ $i }}">
                    <div class="lv-drawer__head"><span class="lv-drawer__title">{{ strtoupper($lbl) }}</span></div>
                    <button type="button" class="lv-drawer__back" data-back><i class="fa fa-angle-left"></i> Volver</button>
                    <ul class="lv-drawer__list">
                        @foreach($kids as $c)
                        <li class="lv-drawer__row">
                            <a href="{{ $resolveNavUrl($c, $item) }}" class="lv-drawer__link lv-drawer__link--sub">{{ $c['label'] ?? '' }}</a>
                        </li>
                        @endforeach
                    </ul>
                </section>
                @endif
            @endforeach

            @if($mmHasCats)
            {{-- ── Panel Categorías ── --}}
            <section class="lv-drawer__panel" data-panel="dp-cat">
                <div class="lv-drawer__head"><span class="lv-drawer__title">Categorías</span></div>
                <button type="button" class="lv-drawer__back" data-back><i class="fa fa-angle-left"></i> Volver</button>
                <ul class="lv-drawer__list">
                    @foreach($mm['categorias'] as $ci => $cat)
                    <li class="lv-drawer__row">
                        <a href="{{ $cat['url'] }}" class="lv-drawer__link">{{ strtoupper($cat['nombre']) }}</a>
                        @if(!empty($cat['subs']))
                        <button type="button" class="lv-drawer__into" data-into="dp-cat-{{ $ci }}" aria-label="Ver {{ $cat['nombre'] }}">
                            <i class="fa fa-angle-right"></i>
                        </button>
                        @endif
                    </li>
                    @endforeach
                    @foreach($mm['bloques'] as $bi => $blk)
                    <li class="lv-drawer__row">
                        <a href="{{ $blk['url'] }}" class="lv-drawer__link lv-drawer__link--promo">{{ strtoupper($blk['nombre']) }}</a>
                        @if(!empty($blk['items']))
                        <button type="button" class="lv-drawer__into" data-into="dp-blk-{{ $bi }}" aria-label="Ver {{ $blk['nombre'] }}">
                            <i class="fa fa-angle-right"></i>
                        </button>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </section>

            {{-- Sub-paneles: categorías con subcategorías --}}
            @foreach($mm['categorias'] as $ci => $cat)
                @if(!empty($cat['subs']))
                <section class="lv-drawer__panel" data-panel="dp-cat-{{ $ci }}">
                    <div class="lv-drawer__head"><span class="lv-drawer__title">{{ strtoupper($cat['nombre']) }}</span></div>
                    <button type="button" class="lv-drawer__back" data-back><i class="fa fa-angle-left"></i> Volver</button>
                    <ul class="lv-drawer__list">
                        <li class="lv-drawer__row"><a href="{{ $cat['url'] }}" class="lv-drawer__link lv-drawer__link--all">Ver todo</a></li>
                        @foreach($cat['subs'] as $sub)
                        <li class="lv-drawer__row"><a href="{{ $sub['url'] }}" class="lv-drawer__link lv-drawer__link--sub">{{ $sub['nombre'] }}</a></li>
                        @endforeach
                    </ul>
                </section>
                @endif
            @endforeach

            {{-- Sub-paneles: bloques con submódulos --}}
            @foreach($mm['bloques'] as $bi => $blk)
                @if(!empty($blk['items']))
                <section class="lv-drawer__panel" data-panel="dp-blk-{{ $bi }}">
                    <div class="lv-drawer__head"><span class="lv-drawer__title">{{ strtoupper($blk['nombre']) }}</span></div>
                    <button type="button" class="lv-drawer__back" data-back><i class="fa fa-angle-left"></i> Volver</button>
                    <ul class="lv-drawer__list">
                        <li class="lv-drawer__row"><a href="{{ $blk['url'] }}" class="lv-drawer__link lv-drawer__link--all">Todo el bloque</a></li>
                        @foreach($blk['items'] as $it)
                        <li class="lv-drawer__row"><a href="{{ $it['url'] }}" class="lv-drawer__link lv-drawer__link--sub">{{ $it['nombre'] }}</a></li>
                        @endforeach
                    </ul>
                </section>
                @endif
            @endforeach
            @endif

        </div>
    </aside>
</div>

@once
<script>
(function () {
    var drawer = document.getElementById('lv-drawer');
    if (!drawer) return;

    var panels = drawer.querySelectorAll('.lv-drawer__panel');
    function panelById(id) { return drawer.querySelector('.lv-drawer__panel[data-panel="' + id + '"]'); }

    var stack = ['dp-root'];

    function render() {
        panels.forEach(function (p) { p.classList.remove('is-active', 'is-behind'); });
        stack.forEach(function (id, idx) {
            var p = panelById(id);
            if (!p) return;
            p.classList.add(idx === stack.length - 1 ? 'is-active' : 'is-behind');
        });
        var top = panelById(stack[stack.length - 1]);
        if (top) top.scrollTop = 0;
    }

    function open(startId) {
        stack = ['dp-root'];
        if (startId && startId !== 'dp-root' && panelById(startId)) stack.push(startId);
        render();
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lv-drawer-lock');
    }

    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lv-drawer-lock');
    }

    // Aperturas (barra móvil: categorías / hamburguesa)
    document.querySelectorAll('[data-open-drawer]').forEach(function (b) {
        b.addEventListener('click', function (e) {
            e.preventDefault();
            open(b.getAttribute('data-open-drawer'));
        });
    });

    // Interacciones dentro del drawer
    drawer.addEventListener('click', function (e) {
        var back = e.target.closest('[data-back]');
        if (back) { e.preventDefault(); if (stack.length > 1) { stack.pop(); render(); } else { close(); } return; }

        var closeBtn = e.target.closest('[data-drawer-close]');
        if (closeBtn) { e.preventDefault(); close(); return; }

        // Icono chevron → desplegar submenú
        var into = e.target.closest('[data-into]');
        if (into) { e.preventDefault(); stack.push(into.getAttribute('data-into')); render(); return; }

        // Texto del ítem: si tiene submenú, desplegar (NO navegar); si no, navegación normal
        var link = e.target.closest('.lv-drawer__link');
        if (link) {
            var row = link.closest('.lv-drawer__row');
            var intoBtn = row && row.querySelector('[data-into]');
            if (intoBtn) {
                e.preventDefault();
                stack.push(intoBtn.getAttribute('data-into'));
                render();
            }
        }
    });

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
})();
</script>
@endonce
