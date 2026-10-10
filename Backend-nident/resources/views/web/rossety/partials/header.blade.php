@php
    // Header editable desde el admin (Inicio & Header ▸ web_header). Mismo diseño que el lienzo del admin.
    $rsH = $rsHeader ?? [];
    $rsNav = $rsH['nav'] ?? [];
    $rsCats = $rsCatalog['categorias'] ?? [];
    $rsIconoRed = [
        'facebook' => 'ph-facebook-logo', 'instagram' => 'ph-instagram-logo', 'tiktok' => 'ph-tiktok-logo',
        'youtube' => 'ph-youtube-logo', 'whatsapp' => 'ph-whatsapp-logo', 'twitter' => 'ph-x-logo',
        'linkedin' => 'ph-linkedin-logo',
    ];
    $rsPath = '/'.trim(request()->path(), '/');
    $rsActivo = static function (array $item) use ($rsPath) {
        $p = '/'.trim((string) parse_url((string) ($item['href'] ?? $item['url'] ?? '/'), PHP_URL_PATH), '/');
        return $p === '/' ? $rsPath === '/' : ($rsPath === $p || str_starts_with($rsPath, $p.'/'));
    };
    $rsHref = static fn (array $item) => url((string) ($item['href'] ?? $item['url'] ?? '/'));
@endphp
    <div class="rs-topbar">
        <div class="rs-container rs-topbar__inner">
            <div class="rs-topbar__social">
                @foreach(($rsH['redes'] ?? []) as $red)
                    @if($red['tipo'] === 'whatsapp')
                        <a class="js-wa" href="#" aria-label="WhatsApp"><i class="ph-fill ph-whatsapp-logo"></i></a>
                    @else
                        <a href="{{ $red['url'] }}" target="_blank" rel="noopener" aria-label="{{ $red['label'] }}"><i class="ph-fill {{ $rsIconoRed[$red['tipo']] ?? 'ph-link' }}"></i></a>
                    @endif
                @endforeach
            </div>
            <p class="rs-topbar__promo">
                <i class="ph-fill ph-heart"></i>
                <span>{{ $rsH['mensaje'] ?? '' }}</span>
                <strong class="rs-topbar__code">{{ $rsTelefono }}</strong>
            </p>
            <div class="rs-topbar__lang">
                <span>PEN <i class="ph-bold ph-caret-down"></i></span>
                <span class="rs-topbar__sep"></span>
                <span>ES <i class="ph-bold ph-caret-down"></i></span>
            </div>
        </div>
    </div>

    <header class="rs-header">
        <div class="rs-container rs-header__main">
            <div class="rs-header__left">
                <button class="rs-burger js-drawer-open" type="button" aria-label="Abrir menú"><i class="ph-bold ph-list"></i></button>
                <div class="rs-location">
                    <i class="ph ph-map-pin"></i>
                    <div class="rs-location__text"><span>Enviar a</span><strong>{{ $rsCiudad }}</strong></div>
                    <a class="rs-location__btn" href="{{ route('contacto.pagina') }}">Cambiar</a>
                </div>
            </div>
            <a class="rs-logo" href="{{ route('home') }}"><img src="{{ $rsLogo }}" alt="Detalles Rossety" /></a>
            <div class="rs-header__right">
                <form class="rs-search" action="{{ route('tienda') }}" method="get" role="search">
                    <input class="rs-search__input" type="search" name="q" value="{{ request('q', '') }}" placeholder="{{ $rsH['buscador'] ?? '' }}" aria-label="Buscar productos" />
                    <button class="rs-search__btn" type="submit" aria-label="Buscar"><i class="ph-bold ph-magnifying-glass"></i></button>
                </form>
                <div class="rs-header__actions">
                    <a class="rs-icon-btn" href="{{ route('contacto.pagina') }}" aria-label="Mi contacto"><i class="ph-bold ph-user"></i></a>
                    <a class="rs-icon-btn rs-icon-btn--hide" href="/tienda?tag=destacado" aria-label="Más pedidos"><i class="ph-bold ph-heart"></i><span class="rs-icon-btn__count">0</span></a>
                    <button class="rs-icon-btn js-cart-open" type="button" aria-label="Abrir carrito"><i class="ph-bold ph-handbag"></i><span class="rs-icon-btn__count js-cart-count">0</span></button>
                </div>
            </div>
        </div>
        <nav class="rs-nav" aria-label="Principal">
            <div class="rs-container rs-nav__inner">
                <div class="rs-nav__left">
                    <div class="rs-catmenu">
                        <button class="rs-catmenu__btn" type="button"><i class="ph ph-list"></i>Categorías<i class="ph-bold ph-caret-down"></i></button>
                        <div class="rs-catmenu__panel">
                            @foreach($rsCats as $cat)
                                <a class="rs-catmenu__item" href="/tienda?cat={{ $cat['slug'] }}"><i class="ph {{ $cat['icon'] ?? 'ph-tag' }}"></i><span>{{ $cat['name'] }}</span></a>
                            @endforeach
                        </div>
                    </div>
                    <ul class="rs-nav__list">
                        @foreach($rsNav as $item)
                            <li><a class="rs-nav__link{{ $rsActivo($item) ? ' rs-nav__link--active' : '' }}" href="{{ $rsHref($item) }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <a class="rs-nav__promo" href="{{ url($rsH['promoUrl'] ?? '/tienda') }}">{{ $rsH['promoTexto'] ?? '' }}</a>
            </div>
        </nav>
    </header>

    <div class="rs-drawer">
        <div class="rs-drawer__overlay js-drawer-close"></div>
        <aside class="rs-drawer__panel">
            <div class="rs-drawer__head">
                <img src="{{ $rsLogo }}" alt="Detalles Rossety" />
                <button class="rs-drawer__close js-drawer-close" type="button" aria-label="Cerrar menú"><i class="ph-bold ph-x"></i></button>
            </div>
            <form class="rs-search" action="{{ route('tienda') }}" method="get">
                <input class="rs-search__input" type="search" name="q" value="{{ request('q', '') }}" placeholder="¿Qué detalle estás buscando?" aria-label="Buscar productos" />
                <button class="rs-search__btn" type="submit" aria-label="Buscar"><i class="ph-bold ph-magnifying-glass"></i></button>
            </form>
            <p class="rs-drawer__title">Menú</p>
            @foreach($rsNav as $item)
                <a class="rs-drawer__link" href="{{ $rsHref($item) }}"><i class="ph ph-caret-right"></i>{{ $item['label'] }}</a>
            @endforeach
            <p class="rs-drawer__title">Categorías</p>
            @foreach($rsCats as $cat)
                <a class="rs-drawer__link" href="/tienda?cat={{ $cat['slug'] }}"><i class="ph {{ $cat['icon'] ?? 'ph-tag' }}"></i>{{ $cat['name'] }}</a>
            @endforeach
        </aside>
    </div>
