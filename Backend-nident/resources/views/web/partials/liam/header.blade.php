@php
    $h = $webHeader ?? null;
    $logo = $h && $h->url_logo
        ? \App\Support\LiamPageData::img($h->url_logo, 'temp02/img/inicio/logo-valento.svg')
        : asset('temp02/img/inicio/logo-valento.svg');

    $navItems = $h->nav_items ?? [
        ['label' => 'Inicio', 'url' => url('/')],
        ['label' => 'Tienda', 'url' => url('/tienda')],
        ['label' => 'Nosotros', 'url' => url('/nosotros')],
        ['label' => 'Contacto', 'url' => url('/contacto')],
    ];
    $navItems = \App\Support\LiamPageData::syncHeaderNavItems(
        is_array($navItems) ? $navItems : []
    );

    // Franja de avisos: editable en el admin (web_header.ticker_items)
    $tickerItems = collect(is_array($h->ticker_items ?? null) ? $h->ticker_items : [])
        ->filter(fn ($t) => is_array($t) && trim((string) ($t['texto'] ?? '')) !== '')
        ->values()
        ->all();
    if (empty($tickerItems)) {
        $tickerItems = [
            ['texto' => 'Venta mayorista para profesionales', 'icon' => 'temp02/img/icons/delivery.png'],
            ['texto' => 'Stock permanente y despacho ágil', 'icon' => 'temp02/img/icons/voucher.png'],
            ['texto' => 'Personaliza con el logo de tu empresa', 'icon' => 'temp02/img/icons/sales.png'],
        ];
    }

    $iconMapTop = [
        'facebook'  => 'fa fa-facebook',
        'instagram' => 'fa fa-instagram',
        'tiktok'    => 'fa fa-music',
        'whatsapp'  => 'fa fa-whatsapp',
        'youtube'   => 'fa fa-youtube-play',
        'linkedin'  => 'fa fa-linkedin',
        'twitter'   => 'fa fa-twitter',
    ];
    // Redes: misma fuente que footer y contacto (web_footer.footer_redes). WhatsApp va en el botón dorado.
    $redesSitio = \App\Support\LiamPageData::redesSociales($footerCorp ?? null);
    $topbarRedes = collect($redesSitio ?? [])
        ->reject(fn ($r) => $r['tipo'] === 'whatsapp')
        ->map(fn ($r) => ['icon' => $iconMapTop[$r['tipo']] ?? 'fa fa-link', 'url' => $r['url'], 'label' => $r['label']])
        ->values()
        ->all();
    if ($redesSitio === null) {
        $topbarRedes = [
            ['icon' => 'fa fa-facebook', 'url' => 'https://facebook.com', 'label' => 'Facebook'],
            ['icon' => 'fa fa-instagram', 'url' => 'https://instagram.com', 'label' => 'Instagram'],
            ['icon' => 'fa fa-music', 'url' => 'https://tiktok.com', 'label' => 'TikTok'],
            ['icon' => 'fa fa-youtube-play', 'url' => 'https://youtube.com', 'label' => 'YouTube'],
        ];
    }

    // Barra superior: teléfono, correo, horario y botón (editables en el admin del header)
    $topPhone = trim((string) ($h->top_telefonos ?? ''));
    if ($topPhone === '') {
        $topPhone = \App\Support\ContactoValento::telefonoDisplay();
    }
    $topPhoneTel = preg_replace('/\D+/', '', $topPhone);
    if ($topPhoneTel !== '' && ! str_starts_with($topPhoneTel, '51')) {
        $topPhoneTel = '51' . ltrim($topPhoneTel, '0');
    }

    $topEmail = trim((string) ($h->top_email ?? ''));
    if ($topEmail === '') {
        $topEmail = \App\Support\ContactoValento::ventasEmail();
    }

    $topHours = trim((string) ($h->topbar_promo ?? ''));
    if ($topHours === '') {
        $topHours = 'Lun – Sáb: 9:00 – 18:00';
    }

    $topCtaTexto = trim((string) ($h->topbar_cta_texto ?? ''));
    if ($topCtaTexto === '') {
        $topCtaTexto = 'Cotiza por WhatsApp';
    }

    $urlWaTop = $urlWhatsapp ?? '';
    if ($urlWaTop === '') {
        $urlWaTop = \App\Support\ContactoValento::whatsappUrl();
    }

    $resolveNavUrl = static function ($item, $parentItem = null) {
        $url = trim((string) ($item['url'] ?? $item['href'] ?? '#'));
        if ($url === '' || $url === '#') {
            if (is_array($parentItem)) {
                $parentPath = trim((string) ($parentItem['url'] ?? $parentItem['href'] ?? ''));
                $parentPath = preg_replace('/#.*$/', '', $parentPath) ?: '';
                if ($parentPath !== '' && $parentPath !== '#' && ! str_starts_with($parentPath, '#')) {
                    return preg_match('#^https?://#i', $parentPath) ? $parentPath : url($parentPath);
                }
            }
            return '#';
        }
        if (preg_match('#^https?://#i', $url)) { return $url; }
        if (str_starts_with($url, '#')) {
            $parentPath = '';
            if (is_array($parentItem)) {
                $parentPath = trim((string) ($parentItem['url'] ?? $parentItem['href'] ?? ''));
                $parentPath = preg_replace('/#.*$/', '', $parentPath) ?: '';
            }
            if ($parentPath === '' || $parentPath === '#' || str_starts_with($parentPath, '#')) {
                $hash = strtolower($url);
                if (in_array($hash, ['#direcciones', '#formulario', '#mapa'], true)) {
                    $parentPath = '/contacto';
                } elseif (in_array($hash, ['#nosotros', '#estadisticas', '#valores', '#trayectoria'], true)) {
                    $parentPath = '/nosotros';
                } elseif (in_array($hash, ['#categoria', '#lookbook', '#nuestros_clientes'], true) || preg_match('/^#bloque\d+$/', $hash)) {
                    $parentPath = '/';
                }
            }
            if ($parentPath !== '' && $parentPath !== '#' && ! str_starts_with($parentPath, '#')) {
                if (preg_match('#^https?://#i', $parentPath)) {
                    return rtrim($parentPath, '/') . $url;
                }
                return url($parentPath) . $url;
            }
            return $url;
        }
        if (str_contains($url, '#')) {
            [$path, $hash] = explode('#', $url, 2);
            $path = trim($path);
            if ($path === '') { return url('/contacto') . '#' . $hash; }
            return url($path) . '#' . $hash;
        }
        return url($url);
    };

    $cartCount = $cartCount ?? 0;
    $active = $paginaActiva ?? '';
    $cuentaUrl = url('/ope_pedidos/listar_pedidos');
    $userFirstName = Auth::check()
        ? explode(' ', trim(Auth::user()->name ?? 'Cliente'))[0]
        : '';

    // Menú editable en el admin: todos los ítems principales con nombre
    $navMain = collect($navItems)
        ->filter(fn ($item) => is_array($item) && trim((string) ($item['label'] ?? '')) !== '')
        ->values();

    // Página activa por ruta (así funciona aunque se renombre el ítem en el admin)
    $rutaActiva = ['home' => '/', 'inicio' => '/', 'tienda' => '/tienda', 'nosotros' => '/nosotros', 'contacto' => '/contacto'][$paginaActiva ?? ''] ?? null;
    $esActivo = static function ($item) use ($rutaActiva) {
        $ruta = parse_url(trim((string) ($item['url'] ?? $item['href'] ?? '')), PHP_URL_PATH) ?: '/';
        return $rutaActiva !== null && rtrim($ruta, '/') === rtrim($rutaActiva, '/');
    };
    if ($navMain->isEmpty()) {
        $navMain = collect([
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Tienda', 'url' => '/tienda'],
            ['label' => 'Nosotros', 'url' => '/nosotros'],
            ['label' => 'Contacto', 'url' => '/contacto'],
        ]);
    }
@endphp

<div class="vl-topbar">
    <div class="vl-topbar__inner">
        <ul class="vl-topbar__info">
            <li>
                <i class="fa fa-phone" aria-hidden="true"></i>
                <a href="tel:+{{ $topPhoneTel }}">{{ $topPhone }}</a>
            </li>
            <li class="vl-topbar__mail">
                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                <a href="mailto:{{ $topEmail }}">{{ $topEmail }}</a>
            </li>
            <li class="vl-topbar__hours">
                <i class="fa fa-clock-o" aria-hidden="true"></i>{{ $topHours }}
            </li>
        </ul>
        <div class="vl-topbar__right">
            @if(!empty($topbarRedes))
            <span class="vl-topbar__label">Síguenos</span>
            <ul class="vl-topbar__social">
                @foreach($topbarRedes as $red)
                <li>
                    <a href="{{ $red['url'] }}" target="_blank" rel="noopener" aria-label="{{ $red['label'] }}">
                        <i class="{{ $red['icon'] }}" aria-hidden="true"></i>
                    </a>
                </li>
                @endforeach
            </ul>
            @endif
            <a class="vl-topbar__cta" href="{{ $urlWaTop }}" target="_blank" rel="noopener">
                <i class="fa fa-whatsapp" aria-hidden="true"></i>
                <span>{{ $topCtaTexto }}</span>
            </a>
        </div>
    </div>
</div>

<header class="lv-header" id="lv-header">
    <div class="lv-header__inner">
        <a href="{{ url('/') }}" class="lv-header__logo" aria-label="Valento - Inicio">
            <img src="{{ $logo }}" alt="Valento - Semplicemente Valento">
        </a>
        <nav class="lv-header__nav" id="lv-main-nav" aria-label="Navegacion principal">
            <ul class="lv-header__nav-list">
                @foreach($navMain as $item)
                    @php
                        $url = $resolveNavUrl($item);
                        $label = $item['label'] ?? '';
                        $isActive = $esActivo($item);
                        $children = collect($item['children'] ?? [])
                            ->filter(static fn ($c) => is_array($c) && trim((string) ($c['label'] ?? '')) !== '')
                            ->values();
                        $hasSub = $children->isNotEmpty();
                    @endphp
                    <li class="{{ $hasSub ? 'has-sub' : '' }}">
                        <a href="{{ $url }}" class="{{ $isActive ? 'active' : '' }}"@if($isActive) aria-current="page"@endif>{{ $label }}</a>
                        @if($hasSub)
                        <ul class="lv-sub-menu">
                            @foreach($children as $child)
                            <li><a href="{{ $resolveNavUrl($child, $item) }}">{{ $child['label'] ?? '' }}</a></li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
        <div class="lv-header__access">
            @guest
                <a href="{{ url('/registration') }}">Registrarse</a><span>/</span><a href="{{ url('/login') }}" class="in">Iniciar sesión</a>
            @else
                <a href="{{ $cuentaUrl }}">Hola, {{ strtolower($userFirstName) }}</a><span>/</span><a href="{{ route('logout') }}" class="in">Salir</a>
            @endguest
        </div>
        <div class="lv-header__icons">
            {{-- Buscador en vivo (pls-live-search): la lupa abre el panel bajo el header --}}
            <div class="vl-search" id="vl-search">
                <button type="button" class="lv-icon-btn vl-search-toggle" aria-label="Buscar productos" aria-expanded="false" aria-controls="vl-search-panel">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
                <div class="vl-search__panel lv-header__search" id="vl-search-panel" role="search">
                    <form action="{{ url('/tienda') }}" method="get" class="lv-header__search-form">
                        <input type="text" name="q" id="lv-search-input" placeholder="Buscar un producto" value="{{ request('q') }}" aria-label="Buscar un producto">
                        <button type="submit" class="lv-search-trigger" aria-label="Buscar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            <a href="{{ Auth::check() ? $cuentaUrl : url('/login') }}" class="lv-icon-btn lv-account-btn" aria-label="Mi cuenta">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <a href="#" class="lv-cart-link" id="lv-cart-icon" aria-label="Carrito de compras">
                <span class="lv-cart-badge" id="lv-cart-count" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">{{ $cartCount }}</span>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </a>
            <button type="button" class="lv-burger" id="lv-burger" aria-label="Abrir menu" aria-expanded="false" aria-controls="lv-main-nav"><span></span></button>
        </div>
    </div>
</header>

<div class="lv-header-info">
    <div class="lv-header-info__inner">
        @foreach($tickerItems as $t)
        <div class="lv-header-info__item">
            @if(!empty($t['icon']))
            <img src="{{ \App\Support\LiamPageData::img($t['icon']) }}" alt="">
            @endif
            <p>{{ $t['texto'] ?? '' }}</p>
        </div>
        @endforeach
    </div>
</div>

<div class="lv-sticky-bar" id="lv-sticky-bar">
    <div class="lv-sticky-bar__inner">
        <a href="{{ url('/') }}" class="lv-sticky-bar__logo" aria-label="Valento">
            <img src="{{ $logo }}" alt="Valento">
        </a>
        <nav class="lv-sticky-bar__nav">
            <ul>
                @foreach($navMain as $item)
                    @php
                        $stickyLabel = $item['label'] ?? '';
                        $stickyActive = $esActivo($item);
                    @endphp
                    <li>
                        <a href="{{ $resolveNavUrl($item) }}" class="{{ $stickyActive ? 'active' : '' }}">{{ $stickyLabel }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
        <div class="lv-sticky-bar__icons">
            <button type="button" class="lv-icon-btn vl-search-toggle" data-vl-search-top aria-label="Buscar productos">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <a href="{{ Auth::check() ? $cuentaUrl : url('/login') }}" class="lv-icon-btn lv-account-btn" aria-label="Mi cuenta">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <a href="#" class="lv-cart-link" id="lv-cart-icon-sticky" aria-label="Carrito de compras">
                <span class="lv-cart-badge lv-cart-badge-sticky" id="lv-cart-count-sticky" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">{{ $cartCount }}</span>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </a>
        </div>
    </div>
</div>