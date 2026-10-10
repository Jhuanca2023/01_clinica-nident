@php
    $grupos = $estructuraGaleriaCategoria ?? [];
    if ($grupos instanceof \Illuminate\Support\Collection) {
        $grupos = $grupos->all();
    }
    $gruposPromo = $estructuraPromoGaleria ?? [];
    if ($gruposPromo instanceof \Illuminate\Support\Collection) {
        $gruposPromo = $gruposPromo->all();
    }

    $seleccionados = array_values(array_filter(array_map('strval', $filtrosSeleccionados ?? [])));
    $esFiltroLibres = in_array('libres', $seleccionados, true);
    // "libres" no es un ítem del menú; no marcar categorías/promos como activas
    $filtroActivo = $esFiltroLibres
        ? ''
        : ($seleccionados[0] ?? '');

    // Menú vertical unificado (una sola selección)
    $menuItems = [];

    foreach ($grupos as $grupo) {
        $cat = $grupo['categoria'] ?? [];
        $subs = collect($grupo['subcategorias'] ?? []);
        $catId = $cat['id'] ?? null;
        $catNombre = $cat['nombre'] ?? 'Categoría';
        $hasSubs = $subs->isNotEmpty() && ($subs->first()['nombre'] ?? '') !== '';

        if ($hasSubs) {
            $menuItems[] = [
                'type' => 'section',
                'label' => $catNombre,
                'count' => (int) ($cat['total_productos'] ?? 0),
            ];
            foreach ($subs as $sub) {
                $subId = $sub['id'] ?? '';
                $filterVal = $catId . '-' . $subId;
                $menuItems[] = [
                    'type' => 'item',
                    'label' => $sub['nombre'] ?? '',
                    'value' => $filterVal,
                    'count' => (int) ($sub['total_productos'] ?? 0),
                    'indent' => true,
                    'active' => $filtroActivo !== '' && $filtroActivo === (string) $filterVal,
                ];
            }
        } else {
            $filterVal = ($catId ?? '') . '-null';
            $menuItems[] = [
                'type' => 'item',
                'label' => $catNombre,
                'value' => $filterVal,
                'count' => (int) ($cat['total_productos'] ?? 0),
                'indent' => false,
                'active' => $filtroActivo !== '' && $filtroActivo === (string) $filterVal,
            ];
        }
    }

    foreach ($gruposPromo as $grupoPromo) {
        $catP = $grupoPromo['categoria'] ?? [];
        $subsP = collect($grupoPromo['subcategorias'] ?? []);
        $bloqueId = $catP['id'] ?? 0;
        $bloqueVal = $catP['filter_value'] ?? ('promo-' . $bloqueId);
        $bloqueNombre = $catP['nombre'] ?? 'Ofertas';

        $menuItems[] = [
            'type' => 'section',
            'label' => $bloqueNombre,
            'count' => (int) ($catP['total_productos'] ?? 0),
            'promo' => true,
        ];

        $menuItems[] = [
            'type' => 'item',
            'label' => 'Todo el bloque',
            'value' => $bloqueVal,
            'count' => (int) ($catP['total_productos'] ?? 0),
            'indent' => true,
            'active' => $filtroActivo !== '' && $filtroActivo === (string) $bloqueVal,
        ];

        foreach ($subsP as $subP) {
            $itemId = $subP['id'] ?? '';
            $filterValP = $subP['filter_value'] ?? ($bloqueVal . '-' . $itemId);
            $menuItems[] = [
                'type' => 'item',
                'label' => $subP['nombre'] ?? '',
                'value' => $filterValP,
                'count' => (int) ($subP['total_productos'] ?? 0),
                'indent' => true,
                'active' => $filtroActivo !== '' && $filtroActivo === (string) $filterValP,
            ];
        }
    }

    $hayMenu = collect($menuItems)->contains(fn ($i) => ($i['type'] ?? '') === 'item');
    // En modo libres no marcar "Todos" (el listado es un subconjunto especial)
    $todosActivo = ! $esFiltroLibres && $filtroActivo === '';
@endphp

<form id="filtros-form" @if($esFiltroLibres) data-filtro-libres="1" @endif>
    @csrf

    {{-- Precio / Color / Talla (temp02) — Facetas desde BD --}}
    @php
        $pMin = (int) ($precioMin ?? 0);
        $pMax = max($pMin, (int) ($precioMax ?? 500));
        $pSelMax = (int) ($precioSelMax ?? $pMax);
        $pSelMin = (int) ($precioSelMin ?? $pMin);
        $tallasSel = array_map('strval', $tallasSeleccionadas ?? []);
        $coloresSel = array_map(static fn ($c) => mb_strtolower((string) $c), $coloresSeleccionados ?? []);
        $listaTallas = $filtrosTalla ?? [];
        $listaColores = $filtrosColor ?? [];
    @endphp

    <div class="cat-filter open">
        <div class="cat-filter__head">
            <span class="cat-filter__head-title">Precio</span>
            <i class="fa fa-chevron-down cat-filter__arrow"></i>
        </div>
        <div class="cat-filter__body">
            <div class="cat-price-range">
                <input type="range" id="cat-price-range" min="{{ $pMin }}" max="{{ $pMax }}" value="{{ $pSelMax }}" step="1"
                       data-precio-min="{{ $pMin }}" data-precio-max="{{ $pMax }}">
                <div class="cat-price-labels">
                    <span>S/ {{ $pMin }}</span>
                    <span id="cat-price-max">S/ {{ $pSelMax }}</span>
                </div>
            </div>
            <input type="hidden" id="cat-precio-min" value="{{ $pSelMin }}">
            <input type="hidden" id="cat-precio-max" value="{{ $pSelMax }}">
        </div>
    </div>

    {{-- Marca (temp02): todos los productos del catálogo son Valento --}}
    @php $totalMarca = \App\Models\Producto::where('Activo', 'S')->count(); @endphp
    <div class="cat-filter open">
        <div class="cat-filter__head">
            <span class="cat-filter__head-title">Marca</span>
            <i class="fa fa-chevron-down cat-filter__arrow"></i>
        </div>
        <div class="cat-filter__body">
            <div class="cat-check-item">
                <input type="checkbox" id="m-valento" class="cat-chk-marca" value="Valento">
                <label for="m-valento">rossety <span class="count">({{ $totalMarca }})</span></label>
            </div>
        </div>
    </div>

    @if(!empty($listaColores))
    <div class="cat-filter open">
        <div class="cat-filter__head">
            <span class="cat-filter__head-title">Color</span>
            <i class="fa fa-chevron-down cat-filter__arrow"></i>
        </div>
        <div class="cat-filter__body">
            @foreach($listaColores as $i => $col)
                @php
                    $nombre = is_array($col) ? (string) ($col['nombre'] ?? '') : (string) $col;
                    $count = is_array($col) ? (int) ($col['count'] ?? 0) : 0;
                    $id = 'c-' . \Illuminate\Support\Str::slug($nombre) . '-' . $i;
                    $checked = in_array(mb_strtolower($nombre), $coloresSel, true);
                @endphp
                @if($nombre !== '')
                <div class="cat-check-item">
                    <input type="checkbox" id="{{ $id }}" class="cat-chk-color" value="{{ $nombre }}" {{ $checked ? 'checked' : '' }}>
                    <label for="{{ $id }}">{{ mb_strtoupper($nombre) }} @if($count)<span class="count">({{ $count }})</span>@endif</label>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($listaTallas))
    <div class="cat-filter open">
        <div class="cat-filter__head">
            <span class="cat-filter__head-title">Tallas</span>
            <i class="fa fa-chevron-down cat-filter__arrow"></i>
        </div>
        <div class="cat-filter__body">
            @foreach($listaTallas as $i => $tal)
                @php
                    $nombre = is_array($tal) ? (string) ($tal['nombre'] ?? '') : (string) $tal;
                    $count = is_array($tal) ? (int) ($tal['count'] ?? 0) : 0;
                    $id = 't-' . \Illuminate\Support\Str::slug($nombre) . '-' . $i;
                    $checked = in_array($nombre, $tallasSel, true)
                        || in_array(mb_strtoupper($nombre), array_map('mb_strtoupper', $tallasSel), true);
                @endphp
                @if($nombre !== '')
                <div class="cat-check-item">
                    <input type="checkbox" id="{{ $id }}" class="cat-chk-talla" value="{{ $nombre }}" {{ $checked ? 'checked' : '' }}>
                    <label for="{{ $id }}">{{ $nombre }} @if($count)<span class="count">({{ $count }})</span>@endif</label>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="cat-filter open">
        <div class="cat-filter__head">
            <span class="cat-filter__head-title">Categorías</span>
            <i class="fa fa-chevron-down cat-filter__arrow"></i>
        </div>
        <div class="cat-filter__body">
            <nav class="cat-nav-menu" aria-label="Navegación de categorías">
                <button type="button"
                        class="cat-nav-item filtro-nav-item {{ $todosActivo ? 'is-active' : '' }}"
                        data-value="">
                    <span class="cat-nav-item__label">Todos</span>
                </button>

                @forelse($menuItems as $item)
                    @if(($item['type'] ?? '') === 'section')
                        <div class="cat-nav-section {{ !empty($item['promo']) ? 'cat-nav-section--promo' : '' }}">
                            <span class="cat-nav-section__label">{{ $item['label'] }}</span>
                        </div>
                    @else
                        <button type="button"
                                class="cat-nav-item filtro-nav-item {{ !empty($item['indent']) ? 'cat-nav-item--indent' : '' }} {{ !empty($item['active']) ? 'is-active' : '' }}"
                                data-value="{{ $item['value'] }}">
                            <span class="cat-nav-item__label">{{ $item['label'] }}</span>
                        </button>
                    @endif
                @empty
                    <p class="cat-filter-empty">No hay categorías disponibles.</p>
                @endforelse

                @if(!$hayMenu && count($menuItems) === 0)
                    {{-- ya cubierto por @empty --}}
                @endif
            </nav>
        </div>
    </div>

    <button type="button" class="cat-clear-btn" id="cat-clear-filtros">Limpiar filtros</button>
</form>

<style>
.cat-nav-menu {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.cat-nav-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 4px 4px;
    margin-top: 6px;
    border-top: 1px solid #eee;
}
.cat-nav-section:first-of-type { border-top: none; margin-top: 2px; }
.cat-nav-section__label {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #1a1a1a;
}
.cat-nav-section--promo .cat-nav-section__label::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #c0392b;
    margin-right: 6px;
    vertical-align: middle;
}
.cat-nav-section__count {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    color: #999;
}
.cat-nav-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 8px 10px;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    border-radius: 4px;
    transition: background 0.15s ease, color 0.15s ease;
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    color: #444;
}
.cat-nav-item--indent { padding-left: 18px; }
.cat-nav-item:hover {
    background: #f8f8f8;
    color: #c0392b;
}
.cat-nav-item.is-active {
    background: rgba(192, 57, 43, 0.08);
    color: #c0392b;
    font-weight: 700;
}
.cat-nav-item__label {
    flex: 1;
    line-height: 1.35;
}
.cat-nav-item__count {
    font-size: 11px;
    color: #999;
    margin-left: 8px;
    flex-shrink: 0;
}
.cat-nav-item.is-active .cat-nav-item__count { color: #c0392b; }
.cat-filter-empty {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: #999;
    margin: 0;
}
</style>
