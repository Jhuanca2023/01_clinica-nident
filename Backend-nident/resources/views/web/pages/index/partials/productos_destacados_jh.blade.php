@php
    $items = $productosDestacados ?? collect();
    $sec = $productoDestacadoSeccion ?? null;
@endphp
@if($items->isNotEmpty())
<div id="productos" class="featured-courses-area left-details default-padding">
    <div class="container">
        <div class="row">
            <div class="featured-courses-carousel owl-carousel owl-theme">
                @foreach($items as $p)
                @php $img = Helpers::cmsAssetUrl($p->url_imagen); @endphp
                <div class="item">
                    <div class="row align-items-center">
                        <div class="col-lg-5">
                            <div class="thumb">
                                <a href="{{ $img }}" class="jh-product-popup jh-slider-popup" title="{{ $p->titulo }}">
                                    <img src="{{ $img }}" alt="{{ $p->titulo }}">
                                    <div class="jh-product-overlay"><span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span></div>
                                </a>
                                @if($p->url_video_youtube ?? null)
                                <div class="live-view">
                                    <a class="popup-youtube" href="{{ $p->url_video_youtube }}"><i class="fa fa-video"></i></a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-7 info">
                            <h2><a href="#">{{ $p->titulo }}</a></h2>
                            <h4>{{ $sec->seccion_subtitulo ?? 'Productos destacados' }}</h4>
                            <p>{{ $p->descripcion }}</p>
                            <a href="{{ Helpers::whatsappBtnUrl($p->btn_url ?? null, isset($footerData[0]) ? $footerData[0] : null) }}" class="btn btn-theme effect btn-md" target="_blank" rel="noopener">{{ $p->btn_texto ?? 'Me interesa' }}</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
