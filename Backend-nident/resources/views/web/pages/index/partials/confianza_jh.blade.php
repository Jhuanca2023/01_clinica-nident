@php
    $p = $porqueElejirnos;
    $bg = Helpers::cmsAssetUrl($p->url_imagen_fondo ?? 'temp02/assets/img/jh_importaciones/inicio/seccion%202.jpg');
    $items = $confianzaItems ?? collect();
@endphp
@if($p || $items->isNotEmpty())
<div class="wcs-area bg-dark text-light">
    <div class="container-full">
        <div class="row">
            <div class="col-lg-6 thumb bg-cover" style="background-image: url('{{ $bg }}');"></div>
            <div class="col-lg-6 content">
                <div class="site-heading text-left">
                    @if($p->titulo ?? null)<h2>{{ $p->titulo }}</h2>@endif
                    @if($p->descripcion ?? null)<p>{{ $p->descripcion }}</p>@endif
                </div>
                @foreach($items as $it)
                @php $thumb = Helpers::cmsAssetUrl($it->url_imagen); @endphp
                <div class="item">
                    <div class="icon"><img class="jh-wcs-thumb" src="{{ $thumb }}" alt="{{ $it->titulo }}"></div>
                    <div class="info">
                        <h4><a href="{{ $it->url_enlace ?? '#productos' }}">{{ $it->titulo }}</a></h4>
                        <p>{{ $it->descripcion }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
