@php
    $items = $productosCatalogo ?? collect();
    $sec = $productoCatalogoSeccion ?? null;
@endphp
@if($items->isNotEmpty())
<div class="popular-courses circle bg-gray carousel-shadow default-padding">
    <div class="container">
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2">
                    <h2>{{ $sec->seccion_titulo ?? 'Nuestros Productos' }}</h2>
                    <p>{{ $sec->seccion_descripcion ?? '' }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="popular-courses-items popular-courses-carousel owl-carousel owl-theme">
                    @foreach($items as $p)
                    @php $img = Helpers::cmsAssetUrl($p->url_imagen); @endphp
                    <div class="item">
                        <div class="thumb">
                            <a href="{{ $img }}" class="popup-link jh-product-popup" title="{{ $p->titulo }}">
                                <img src="{{ $img }}" alt="{{ $p->titulo }}">
                                <div class="jh-product-overlay"><span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span></div>
                            </a>
                            @if($p->badge ?? null)<div class="price">{{ $p->badge }}</div>@endif
                        </div>
                        <div class="info">
                            <h4><a href="#">{{ $p->titulo }}</a></h4>
                            <p>{{ $p->descripcion }}</p>
                            <div class="bottom-info">
                                <a href="{{ Helpers::whatsappBtnUrl($p->btn_url ?? null, isset($footerData[0]) ? $footerData[0] : null) }}" target="_blank" rel="noopener">{{ $p->btn_texto ?? 'Me interesa' }}</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
