@php
    $p = $pagina;
    $logoUrl = Helpers::cmsAssetUrl($p->intro_url_logo ?? 'temp02/assets/img/jh_importaciones/inicio/logo_jh.png');
@endphp
@if($p->intro_titulo ?? $p->intro_descripcion ?? null)
<section class="jh-nosotros-intro default-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 jh-nosotros-intro-text">
                @if($p->intro_titulo ?? null)
                <h2 class="jh-nosotros-intro-title">{{ $p->intro_titulo }}</h2>
                @endif
                @if($p->intro_descripcion ?? null)
                <p class="jh-nosotros-intro-desc">{{ $p->intro_descripcion }}</p>
                @endif
                @if($p->intro_btn_texto ?? null)
                <a href="{{ $p->intro_btn_url ?? route('contacto.pagina') }}" class="btn btn-theme effect btn-md jh-nosotros-intro-btn">
                    {{ $p->intro_btn_texto }} <i class="fas fa-arrow-up-right"></i>
                </a>
                @endif
            </div>
            <div class="col-lg-6 text-center">
                <div class="jh-nosotros-intro-logo">
                    <img src="{{ $logoUrl }}" alt="{{ strip_tags($p->intro_titulo ?? 'Valento') }}">
                </div>
            </div>
        </div>
    </div>
</section>
@endif
