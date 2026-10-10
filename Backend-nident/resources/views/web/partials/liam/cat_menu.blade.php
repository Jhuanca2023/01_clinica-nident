@php
    $menuCats = $menuCategorias ?? ['categorias' => [], 'bloques' => []];
    $menuId = $menuId ?? 'lv-cat-menu';
    $menuExtraClass = $menuExtraClass ?? '';
@endphp
@if(!empty($menuCats['categorias']) || !empty($menuCats['bloques']))
<div class="lv-cat-menu {{ $menuExtraClass }}" id="{{ $menuId }}">
    <button type="button" class="lv-cat-menu__trigger" aria-haspopup="true" aria-expanded="false">
        Categorías <i class="fa fa-angle-down lv-cat-menu__caret"></i>
    </button>
    <div class="lv-cat-menu__panel">
        @foreach($menuCats['categorias'] as $cat)
        <div class="lv-cat-menu__row {{ !empty($cat['subs']) ? 'has-sub' : '' }}">
            <a href="{{ $cat['url'] }}" class="lv-cat-menu__link">
                <span>{{ ucfirst(strtolower($cat['nombre'])) }}</span>
                @if(!empty($cat['subs']))<i class="fa fa-angle-right"></i>@endif
            </a>
            @if(!empty($cat['subs']))
            <div class="lv-cat-menu__flyout">
                <a href="{{ $cat['url'] }}" class="lv-cat-menu__flyout-all">Ver todo</a>
                @foreach($cat['subs'] as $sub)
                <a href="{{ $sub['url'] }}">{{ $sub['nombre'] }}</a>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach

        @if(!empty($menuCats['bloques']))
        <div class="lv-cat-menu__divider"></div>
        @foreach($menuCats['bloques'] as $bloque)
        <div class="lv-cat-menu__row lv-cat-menu__row--promo {{ !empty($bloque['items']) ? 'has-sub' : '' }}">
            <a href="{{ $bloque['url'] }}" class="lv-cat-menu__link">
                <span>{{ ucfirst(strtolower($bloque['nombre'])) }}</span>
                @if(!empty($bloque['items']))<i class="fa fa-angle-right"></i>@endif
            </a>
            @if(!empty($bloque['items']))
            <div class="lv-cat-menu__flyout">
                <a href="{{ $bloque['url'] }}" class="lv-cat-menu__flyout-all">Todo el bloque</a>
                @foreach($bloque['items'] as $it)
                <a href="{{ $it['url'] }}">{{ $it['nombre'] }}</a>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
        @endif
    </div>
</div>
@endif
