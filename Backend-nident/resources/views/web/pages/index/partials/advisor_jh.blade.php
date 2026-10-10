@php $srv = $servicios ?? collect(); $secTitulo = $srv->first()->seccion_titulo ?? 'Fabricamos Espirales'; @endphp
@if($srv->isNotEmpty())
<section id="maquinarias" class="advisor-area jh-advisor-section bg-gray carousel-shadow default-padding bottom-less">
    <div class="container">
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2"><h2>{!! $secTitulo !!}</h2></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="advisor-items advisor-carousel-solid owl-carousel owl-theme text-center jh-advisor-carousel">
                    @foreach($srv as $s)
                    @php $img = Helpers::cmsAssetUrl($s->url_foto ?? $s->url_icono); @endphp
                    <div class="advisor-item">
                        <div class="info-box">
                            <img src="{{ $img }}" alt="{{ $s->titulo }}">
                            <div class="info-title">
                                <h4>{{ $s->titulo }}</h4>
                                <span>{{ $s->subtitulo ?? '' }}</span>
                                @if($s->btn_texto ?? null)
                                <a href="{{ Helpers::whatsappBtnUrl($s->btn_url ?? null, isset($footerData[0]) ? $footerData[0] : null) }}" class="btn btn-theme effect btn-sm" target="_blank" rel="noopener" style="margin-top:8px;">{{ $s->btn_texto }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif
