@php
    $items = $lineasProducto ?? collect();
    $sec = $lineaProductoSeccion ?? null;
@endphp
@if($items->isNotEmpty())
<div id="top-categories" class="top-cat-area default-padding bottom-less">
    <div class="container">
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2">
                    <h2>{{ $sec->seccion_titulo ?? 'Líneas de Producto' }}</h2>
                    <p>{{ $sec->seccion_descripcion ?? '' }}</p>
                </div>
            </div>
        </div>
        <div class="top-cat-items">
            <div class="row">
                @foreach($items as $l)
                @php $img = Helpers::cmsAssetUrl($l->url_imagen); @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 equal-height">
                    <div class="item jh-cat-card" style="background-image: url('{{ $img }}');">
                        <img class="jh-card-img" src="{{ $img }}" alt="{{ $l->titulo }}">
                        @if($l->badge ?? null)<span class="cat-badge">{{ $l->badge }}</span>@endif
                        <a href="{{ $l->url_enlace ?? '#productos' }}">
                            @if($l->icono_clase ?? null)<i class="{{ $l->icono_clase }}"></i>@endif
                            <div class="info"><h4>{{ $l->titulo }}</h4><span>Ver más</span></div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
