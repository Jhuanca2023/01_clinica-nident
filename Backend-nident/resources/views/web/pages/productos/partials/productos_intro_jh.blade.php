@php $p = $pagina; $imgUrl = Helpers::cmsAssetUrl($p->intro_url_imagen ?? 'temp02/assets/img/jh_importaciones/inicio/jh_importaciones_img_5.jpg'); @endphp
@if($p->intro_titulo ?? $p->intro_descripcion ?? null)
<section class="jh-productos-intro default-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 jh-productos-intro-text">
                @if($p->intro_titulo ?? null)<h2 class="jh-productos-intro-title">{{ $p->intro_titulo }}</h2>@endif
                @if($p->intro_descripcion ?? null)<div class="jh-productos-intro-desc">{!! $p->intro_descripcion !!}</div>@endif
                @if($p->intro_btn_texto ?? null)
                <a href="{{ $p->intro_btn_url ?? route('contacto.pagina') }}" class="btn btn-theme effect btn-md jh-productos-intro-btn">
                    {{ $p->intro_btn_texto }} <i class="fas fa-arrow-up-right"></i>
                </a>
                @endif
            </div>
            <div class="col-lg-6 text-center">
                <div class="jh-productos-intro-img">
                    <img src="{{ $imgUrl }}" alt="{{ strip_tags($p->intro_titulo ?? 'Nuestros Productos') }}">
                </div>
            </div>
        </div>
    </div>
</section>
@endif
