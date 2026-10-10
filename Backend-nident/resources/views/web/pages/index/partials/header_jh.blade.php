@php
    $wh = $webHeader;
    $logoUrl = Helpers::cmsAssetUrl($wh->url_logo ?? 'temp02/assets/img/jh_importaciones/inicio/logo_jh.png');
    $navItems = is_array($wh->nav_items ?? null) ? $wh->nav_items : (json_decode($wh->nav_items ?? '[]', true) ?: []);
    $sideLinks = is_array($wh->side_menu_enlaces ?? null) ? $wh->side_menu_enlaces : (json_decode($wh->side_menu_enlaces ?? '[]', true) ?: []);
    $redesSide = is_array($wh->redes_side ?? null) ? $wh->redes_side : (json_decode($wh->redes_side ?? '[]', true) ?: []);
    $footerCorp = isset($footerData) && count($footerData) > 0 ? $footerData[0] : null;
    $waTop = $urlWhatsapp ?? \Helpers::whatsappUrl($footerCorp);
    if (!empty($wh->url_whatsapp_top) && !preg_match('#(wa\.me|api\.whatsapp)#i', $wh->url_whatsapp_top)) {
        $waTop = $wh->url_whatsapp_top;
    }
@endphp
<div class="top-bar-area address-two-lines bg-dark text-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 address-info">
                <div class="info box">
                    <ul>
                        @if($wh->top_ubicacion ?? null)
                        <li><span><i class="fas fa-map"></i> Ubicación</span>{{ $wh->top_ubicacion }}</li>
                        @endif
                        @if($wh->top_email ?? null)
                        <li><span><i class="fas fa-envelope-open"></i> Correo</span>{{ $wh->top_email }}</li>
                        @endif
                        @if($wh->top_telefonos ?? null)
                        <li><span><i class="fas fa-phone"></i> Celular</span>{{ $wh->top_telefonos }}</li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="user-login text-end col-lg-4">
                <a href="{{ $waTop }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                <a href="{{ $wh->url_contacto_top ?? route('contacto.pagina') }}"><i class="fas fa-envelope"></i> Contáctanos</a>
            </div>
        </div>
    </div>
</div>
<header id="inicio">
    <nav class="navbar mobile-sidenav inc-shape navbar-sticky navbar-default validnavs dark">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="navbar-brand-left">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu"><i class="fa fa-bars"></i></button>
                    <a class="navbar-brand" href="{{ url('/') }}#inicio"><img src="{{ $logoUrl }}" class="logo" alt="Logo"></a>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <img src="{{ $logoUrl }}" alt="Logo">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu"><i class="fa fa-times"></i></button>
                <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
                    @foreach($navItems as $item)
                    @php
                        $href = $item['href'] ?? '#';
                        $label = $item['label'] ?? '';
                        $isActive = false;
                        if (!empty($paginaActiva)) {
                            if ($paginaActiva === 'nosotros' && str_contains($href, '/nosotros')) {
                                $isActive = true;
                            } elseif ($paginaActiva === 'contacto' && str_contains($href, '/contacto')) {
                                $isActive = true;
                            } elseif ($paginaActiva === 'productos' && (str_contains($href, '/productos') || str_contains($href, '#productos'))) {
                                $isActive = true;
                            } elseif ($paginaActiva === 'maquinarias' && (str_contains($href, '/maquinarias') || str_contains($href, '#maquinarias'))) {
                                $isActive = true;
                            } elseif ($paginaActiva === 'inicio' && ($href === '/' || $href === url('/') || str_contains($href, '#inicio'))) {
                                $isActive = true;
                            }
                        }
                    @endphp
                    <li class="dropdown{{ $isActive ? ' active' : '' }}"><a href="{{ $href }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="attr-right">
                <div class="attr-nav attr-box"><ul><li class="side-menu"><a href="#"><span class="bar-1"></span><span class="bar-2"></span><span class="bar-3"></span></a></li></ul></div>
            </div>
            <div class="side">
                <a href="#" class="close-side"><i class="fas fa-times"></i></a>
                <div class="widget"><div class="logo"><img src="{{ $logoUrl }}" alt="Logo"></div></div>
                <div class="widget">
                    <h4 class="title">Enlaces</h4>
                    <ul>
                        @foreach($sideLinks as $lnk)
                        <li><a href="{{ $lnk['href'] ?? '#' }}">{{ $lnk['label'] ?? '' }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="widget social">
                    <h4 class="title">Nuestras Redes</h4>
                    <ul class="link">
                        @foreach($redesSide as $red)
                        @php
                            $icon = match($red['red'] ?? '') {
                                'facebook' => 'fab fa-facebook-f',
                                'whatsapp' => 'fab fa-whatsapp',
                                'tiktok' => 'fab fa-tiktok',
                                default => 'fas fa-link',
                            };
                        @endphp
                        <li><a href="{{ $red['url'] ?? '#' }}" target="_blank" rel="noopener"><i class="{{ $icon }}"></i></a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="overlay-screen"></div>
    </nav>
</header>
