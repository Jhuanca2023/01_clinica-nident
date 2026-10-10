@php
    $items = $productosGaleria ?? collect();
    $sec = $productosGaleriaSeccion ?? null;
@endphp
@if($items->isNotEmpty() || ($sec?->seccion_titulo ?? null))
<section class="jh-productos-galeria popular-courses circle bg-gray default-padding">
    <div class="container">
        @if(($sec?->seccion_titulo ?? null) || ($sec?->seccion_descripcion ?? null))
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2">
                    @if($sec?->seccion_titulo ?? null)<h2>{{ $sec->seccion_titulo }}</h2>@endif
                    @if($sec?->seccion_descripcion ?? null)<p>{{ $sec->seccion_descripcion }}</p>@endif
                </div>
            </div>
        </div>
        @endif
        <div class="row jh-productos-galeria-grid">
            @foreach($items as $p)
            @php $img = Helpers::cmsAssetUrl($p->url_imagen); @endphp
            <div class="col-lg-3 col-md-6 col-sm-6 mb-30">
                <div class="item jh-productos-galeria-card">
                    <div class="thumb">
                        <a href="{{ $img }}" class="popup-link jh-product-popup" title="{{ $p->titulo }}">
                            <img src="{{ $img }}" alt="{{ $p->titulo }}">
                            <div class="jh-product-overlay"><span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span></div>
                        </a>
                    </div>
                    <div class="info">
                        <h4><a href="#">{{ $p->titulo }}</a></h4>
                        @if($p->descripcion ?? null)<p>{{ $p->descripcion }}</p>@endif
                        <div class="bottom-info">
                            <a href="{{ Helpers::whatsappBtnUrl($p->btn_url ?? null, isset($footerData[0]) ? $footerData[0] : null) }}" class="jh-btn-whatsapp" target="_blank" rel="noopener">
                                <i class="fab fa-whatsapp"></i> {{ $p->btn_texto ?? 'Me interesa' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
