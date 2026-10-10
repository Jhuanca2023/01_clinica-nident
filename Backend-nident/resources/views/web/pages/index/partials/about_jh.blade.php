@php
    $a = $webAbout;
    $imgMain = Helpers::cmsAssetUrl($a->url_imagen ?? 'temp02/assets/img/jh_importaciones/inicio/seccion%201.jpg');
    $chars = $aboutCaracteristicas ?? collect();
@endphp
<div id="nosotros" class="about-area default-padding mb-5">
    <div class="container">
        <div class="about-info">
            <div class="row">
                <div class="col-lg-6 thumb">
                    <div class="jh-about-thumb-wrap">
                        <a href="{{ $imgMain }}" class="popup-link jh-about-popup" title="{{ strip_tags($a->titulo ?? '') }}">
                            <img src="{{ $imgMain }}" alt="{{ strip_tags($a->titulo ?? '') }}">
                            <div class="jh-about-overlay"><span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span></div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 info">
                    @if($a->subtitulo ?? null)<h5>{{ $a->subtitulo }}</h5>@endif
                    @if($a->titulo ?? null)<h2>{!! $a->titulo !!}</h2>@endif
                    {!! $a->descripcion ?? '' !!}
                    @if($a->texto_extendido ?? null){!! $a->texto_extendido !!}@endif
                    @if($a->btn_texto ?? null)
                    <a href="{{ $a->btn_url ?? '#' }}" class="btn btn-dark border btn-md" target="_blank" rel="noopener">{{ $a->btn_texto }}</a>
                    @endif
                </div>
            </div>
            @if($chars->isNotEmpty())
            <div class="our-features jh-our-features">
                <div class="row">
                    @foreach($chars as $c)
                    @php $imgC = Helpers::cmsAssetUrl($c->url_imagen); @endphp
                    <div class="col-lg-4 col-md-4">
                        <div class="item jh-feature-card">
                            <div class="jh-feature-img">
                                <a href="{{ $imgC }}" class="jh-product-popup jh-feature-popup" title="{{ $c->titulo }}">
                                    <img src="{{ $imgC }}" alt="{{ $c->titulo }}">
                                    <div class="jh-product-overlay"><span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span></div>
                                </a>
                            </div>
                            <div class="info">
                                <h4>{{ $c->titulo }}</h4>
                                <p>{{ $c->descripcion }}</p>
                                <a href="{{ $c->url_enlace ?? '#productos' }}">{{ $c->texto_enlace ?? 'Ver más' }}</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
