@php $fb = $footerBanner ?? null; @endphp
@if($fb && ($fb->Activo ?? 'S') === 'S')
<div class="lv-footer-banner">
    <img src="{{ \App\Support\LiamPageData::img($fb->url_imagen, 'temp02/img/inicio/2.jpg') }}" alt="Valento" class="lv-footer-banner__img">
    <div class="lv-footer-banner__overlay">
        <div class="lv-footer-banner__content">
            <span class="lv-footer-banner__sub">{{ $fb->subtitulo }}</span>
            <h3 class="lv-footer-banner__title">{!! preg_replace('/\*([^*]+)\*/', '<span>$1</span>', nl2br(e($fb->titulo ?? ''))) !!}</h3>
            @if($fb->btn_texto)
                <a href="{{ $fb->btn_url ?? url('/tienda') }}" class="lv-footer-banner__btn">{{ $fb->btn_texto }}</a>
            @endif
        </div>
    </div>
</div>
@endif
