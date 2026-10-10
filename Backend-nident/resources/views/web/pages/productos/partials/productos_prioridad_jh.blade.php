@php
    $p = $pagina;
    $imgUrl = Helpers::cmsAssetUrl($p->prioridad_url_imagen ?? 'temp02/assets/img/jh_importaciones/inicio/seccion%201.jpg');
@endphp
@if($p->prioridad_titulo ?? $p->prioridad_descripcion ?? null)
<section class="jh-productos-prioridad default-padding bg-gray">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="jh-productos-prioridad-visual">
                    <span class="jh-productos-prioridad-bg"></span>
                    <div class="jh-productos-prioridad-thumb">
                        <a href="{{ $imgUrl }}" class="popup-link" title="{{ strip_tags($p->prioridad_titulo ?? '') }}">
                            <img src="{{ $imgUrl }}" alt="{{ strip_tags($p->prioridad_titulo ?? '') }}">
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                @if($p->prioridad_etiqueta ?? null)<span class="jh-productos-prioridad-tag">{{ $p->prioridad_etiqueta }}</span>@endif
                @if($p->prioridad_titulo ?? null)<h2 class="jh-productos-prioridad-title">{{ $p->prioridad_titulo }}</h2>@endif
                @if($p->prioridad_descripcion ?? null)<p class="jh-productos-prioridad-desc">{{ $p->prioridad_descripcion }}</p>@endif
                @if($p->prioridad_btn_texto ?? null)
                <a href="{{ $p->prioridad_btn_url ?? route('contacto.pagina') }}" class="btn btn-theme effect btn-md jh-productos-prioridad-btn">
                    {{ $p->prioridad_btn_texto }} <i class="fas fa-arrow-up-right"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
