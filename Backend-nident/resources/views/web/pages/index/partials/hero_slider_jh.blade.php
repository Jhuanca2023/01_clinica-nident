@php
    $slides = collect($sliderData ?? [])->where('Activo', 'S')->sortBy('id_slider')->values();
    if ($slides->isEmpty()) {
        $slides = collect([(object)['subtitulo'=>'','titulo'=>'','descripcion'=>'','texto_boton'=>'','url_link'=>'#','url_imagen'=>null]]);
    }
    $cfg = $sliderConfig ?? (object) ['autoplay' => 'S', 'interval_ms' => 5000, 'pause_on_hover' => 'S'];
    $autoplayOn = ($cfg->autoplay ?? 'S') === 'S';
    $intervalMs = max(2000, min(30000, (int) ($cfg->interval_ms ?? 5000)));
    $pauseHover = ($cfg->pause_on_hover ?? 'S') === 'S' ? 'hover' : 'false';
    $multiSlide = $slides->count() > 1;
@endphp
<div class="banner-area banner-style-one content-top-heading less-paragraph text-normal">
    <div id="bootcarousel"
         class="carousel slide jh-hero-carousel carousel-fade"
         data-jh-autoplay="{{ $autoplayOn ? 'S' : 'N' }}"
         data-jh-interval="{{ $intervalMs }}"
         data-jh-pause-hover="{{ $cfg->pause_on_hover ?? 'S' }}"
         @if($multiSlide && $autoplayOn)
         data-bs-ride="carousel"
         data-bs-interval="{{ $intervalMs }}"
         @endif
         data-bs-pause="{{ $pauseHover }}"
         data-bs-wrap="true"
         data-bs-touch="true">
        <div class="carousel-inner text-light carousel-zoom">
            @foreach($slides as $i => $slide)
            @php $bg = Helpers::cmsAssetUrl($slide->url_imagen ?? ''); @endphp
            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                <div class="slider-thumb bg-fixed" style="background-image: url('{{ $bg }}');"></div>
                <div class="box-table shadow dark">
                    <div class="box-cell">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-7">
                                    <div class="content">
                                        @if(!empty($slide->subtitulo) || !empty($slide->slide_tag))
                                        <h3 data-animation="animated slideInLeft">{!! $slide->subtitulo ?? $slide->slide_tag !!}</h3>
                                        @endif
                                        @if(!empty($slide->titulo))
                                        <h1 data-animation="animated slideInUp">{!! $slide->titulo !!}</h1>
                                        @endif
                                        @if(!empty($slide->descripcion))
                                        <p data-animation="animated slideInUp">{!! $slide->descripcion !!}</p>
                                        @endif
                                        @if(!empty($slide->texto_boton))
                                        <a data-animation="animated slideInUp" class="btn btn-theme effect btn-md" href="{{ $slide->url_link ?: '#' }}" target="_blank" rel="noopener">{{ $slide->texto_boton }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($multiSlide)
        <button class="carousel-control-prev left carousel-control" type="button" data-bs-target="#bootcarousel" data-bs-slide="prev">
            <i class="fa fa-angle-left"></i><span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next right carousel-control" type="button" data-bs-target="#bootcarousel" data-bs-slide="next">
            <i class="fa fa-angle-right"></i><span class="visually-hidden">Next</span>
        </button>
        @endif
    </div>
</div>
