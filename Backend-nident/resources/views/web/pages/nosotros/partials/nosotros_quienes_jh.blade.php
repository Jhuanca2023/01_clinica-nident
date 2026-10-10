@php
    $q = $nosotrosQuienes;
    $beneficios = $nosotrosQuienesBeneficios ?? collect();
    $imgUrl = Helpers::cmsAssetUrl($q->url_imagen ?? 'temp02/assets/img/jh_importaciones/inicio/advisor_espirales.jpg');
@endphp
@if($q)
<section class="jh-nosotros-quienes default-padding bg-gray">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="jh-nosotros-quienes-thumb">
                    <a href="{{ $imgUrl }}" class="popup-link" title="{{ strip_tags($q->titulo ?? '') }}">
                        <img src="{{ $imgUrl }}" alt="{{ strip_tags($q->titulo ?? '') }}">
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                @if($q->titulo ?? null)<h2 class="jh-nosotros-quienes-title">{{ $q->titulo }}</h2>@endif
                @if($q->descripcion ?? null)<div class="jh-nosotros-quienes-desc">{!! $q->descripcion !!}</div>@endif
                @if($beneficios->isNotEmpty())
                <ul class="jh-nosotros-beneficios list-unstyled">
                    @foreach($beneficios as $b)
                    <li><i class="fas fa-check-circle"></i> {{ $b->texto }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
