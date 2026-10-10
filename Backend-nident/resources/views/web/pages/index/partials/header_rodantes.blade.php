@php
    $wh = $webHeader;
    $logoUrl = Helpers::cmsAssetUrl($wh->url_logo ?? 'temp02/assets/image/logos/bibliotecas-rodante-logo-menu.png');
    $navItems = $wh->nav_items ?? [];
@endphp
<header class="header main-menu" id="inicio">
  <div class="container-fluid">
    <nav class="navigation">
      <a href="{{ url('/') }}" class="d-flex align-items-center">
        <img src="{{ $logoUrl }}" alt="Bibliotecas Rodantes" class="header-logo">
      </a>
      <div class="menu-button-right">
        <div class="main-menu__nav">
          <ul class="main-menu__list cf" id="menu-main-menu">
            @foreach($navItems as $item)
            <li class="{{ !empty($item['children']) ? 'dropdown' : '' }}">
              <a href="{{ $item['href'] ?? '#' }}">{{ $item['label'] ?? '' }}</a>
              @if(!empty($item['children']))
              <ul>
                @foreach($item['children'] as $child)
                <li><a href="{{ $child['href'] ?? '#' }}">{{ $child['label'] ?? '' }}</a></li>
                @endforeach
              </ul>
              @endif
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="main-menu__right">
        <div class="search-heart-icon d-sm-flex d-none">
          <div class="d-flex align-items-center gap-24">
            @if($wh->btn_visitanos_texto ?? null)
            <a href="{{ $wh->btn_visitanos_url ?? '#contacto' }}" class="cus-btn">
              <span class="text">{{ $wh->btn_visitanos_texto }}</span>
            </a>
            @endif
            @if($wh->btn_eventos_texto ?? null)
            <a href="{{ $wh->btn_eventos_url ?? '#eventos' }}" class="cus-btn-2">
              <span>{{ $wh->btn_eventos_texto }}</span>
              <span>{{ $wh->btn_eventos_texto }}</span>
            </a>
            @endif
          </div>
        </div>
        <a href="#" class="d-xl-none d-flex main-menu__toggler mobile-nav__toggler">
          <img src="{{ asset('temp02/assets/media/vector/menu-2.png') }}" alt="">
        </a>
      </div>
    </nav>
  </div>
</header>
<div class="stricky-header stricked-menu"><div class="sticky-header__content"></div></div>
