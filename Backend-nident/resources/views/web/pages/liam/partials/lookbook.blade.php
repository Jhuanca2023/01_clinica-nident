@php $lb = $lookbook ?? null; @endphp
@if($lb && ($lb->Activo ?? 'S') === 'S')
<section id="lookbook" class="lookbok-section lv-anchor-section">
    <div class="lv-section-title wow fadeInUp" data-wow-duration="0.7s" data-wow-delay="0.1s">
        <span class="lv-subtitle">{{ $lb->subtitulo ?? 'Desde Zaragoza, 1994' }}</span>
        <h2><span class="lv-accent">{{ $lb->titulo ?? '#Valento' }}</span> {{ $lb->titulo_accent ?? 'Stile' }}</h2>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 offset-lg-1">
                <div class="lookbok-left wow fadeInLeft" data-wow-duration="0.75s" data-wow-delay="0.2s">
                    <div class="section-title">
                        <h2>1994 <br />#Valento</h2>
                    </div>
                    <p>{{ $lb->descripcion }}</p>
                    @if($lb->btn_texto)
                        <a href="{{ $lb->btn_url ?? url('/tienda') }}" class="primary-btn look-btn lv-look-btn">{{ $lb->btn_texto }}</a>
                    @endif
                </div>
            </div>
            <div class="col-lg-5 offset-lg-1">
                @php
                    $latTexto = trim((string) ($lb->imagen_lateral_texto ?? '')) ?: 'Valento';
                    $latCta   = trim((string) ($lb->imagen_lateral_cta ?? '')) ?: 'Ver Colección';
                    $latUrlRaw = trim((string) ($lb->imagen_lateral_url ?? ''));
                    $latHref = '';
                    if ($latUrlRaw !== '') {
                        $latHref = preg_match('#^https?://#i', $latUrlRaw)
                            ? $latUrlRaw
                            : url('/' . ltrim($latUrlRaw, '/'));
                    }
                    $latTag = $latHref !== '' ? 'a' : 'div';
                @endphp
                <{{ $latTag }}
                    @if($latHref !== '') href="{{ $latHref }}" @endif
                    class="lookbok-pic lv-lookbok-pic wow fadeInRight{{ $latHref === '' ? ' lv-lookbok-pic--no-link' : '' }}"
                    data-wow-duration="0.8s"
                    data-wow-delay="0.25s"
                    @if($latHref === '')
                        role="button"
                        tabindex="0"
                        data-lv-lookbook-empty="1"
                        aria-label="{{ $latCta }}"
                    @endif
                >
                    <img src="{{ \App\Support\LiamPageData::img($lb->url_imagen_lateral ?? $lb->url_imagen, 'temp02/img/inicio/4.jpg') }}" alt="{{ $latTexto }}">
                    <div class="pic-text">{{ $latTexto }}</div>
                </{{ $latTag }}>
            </div>
        </div>
    </div>
</section>
@once
<style>
/* El enlace de la imagen se comporta como el div .lookbok-pic de la plantilla */
.lv-lookbok-pic { display: block; }
</style>
<script>
(function () {
    if (window.__lvLookbookEmptyBound) return;
    window.__lvLookbookEmptyBound = true;
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-lv-lookbook-empty="1"]');
        if (!el) return;
        e.preventDefault();
        var msg = 'No hay enlace configurado para esta imagen.';
        if (window.Swal) {
            window.Swal.fire({
                toast: true, position: 'top-end', icon: 'info', title: msg,
                showConfirmButton: false, timer: 3200, timerProgressBar: true
            });
        } else if (window.toastr) {
            window.toastr.info(msg);
        }
    });
})();
</script>
@endonce
@endif
@if(!($lb && ($lb->Activo ?? 'S') === 'S'))
<div id="lookbook" class="lv-anchor-section" aria-hidden="true"></div>
@endif
