@php
    $items = $maquinariasItems ?? collect();
    $pag = $pagina;
    $footerCorp = isset($footerData) && count($footerData) > 0 ? $footerData[0] : null;
    $waLink = $urlWhatsapp ?? Helpers::whatsappUrl($footerCorp);
@endphp
@if($items->isNotEmpty() || ($pag?->seccion_titulo ?? null))
<section class="jh-maquinarias-galeria default-padding bg-gray">
    <div class="container">
        @if(($pag?->seccion_titulo ?? null) || ($pag?->seccion_descripcion ?? null))
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2">
                    @if($pag?->seccion_titulo ?? null)<h2>{{ $pag->seccion_titulo }}</h2>@endif
                    @if($pag?->seccion_descripcion ?? null)<p>{{ $pag->seccion_descripcion }}</p>@endif
                </div>
            </div>
        </div>
        @endif
        <div class="row jh-maquinarias-grid">
            @foreach($items as $m)
            @php
                $imgFront = Helpers::cmsAssetUrl($m->url_imagen_front);
                $imgBack = $m->url_imagen_back ? Helpers::cmsAssetUrl($m->url_imagen_back) : null;
                $hasFlip = $imgFront && $imgBack;
                $modalSrc = $imgFront ?: $imgBack;
            @endphp
            <div class="col-lg-4 col-md-6 col-sm-6 mb-30">
                <div class="jh-maquinaria-card">
                    <div class="jh-maquinaria-flip {{ $hasFlip ? 'has-dual' : 'single' }}"
                         data-modal-src="{{ $modalSrc }}"
                         data-modal-title="{{ $m->titulo }}">
                        @if($hasFlip)
                        <div class="jh-maquinaria-flip-inner jh-maquinaria-popup-trigger" role="button" tabindex="0" title="Ver imagen ampliada">
                            <div class="jh-maquinaria-flip-face jh-maquinaria-flip-front">
                                <img src="{{ $imgFront }}" alt="{{ $m->titulo }}">
                                <span class="jh-maquinaria-label">{{ $m->titulo }}</span>
                                <div class="jh-product-overlay jh-maquinaria-overlay">
                                    <span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span>
                                </div>
                            </div>
                            <div class="jh-maquinaria-flip-face jh-maquinaria-flip-back">
                                <img src="{{ $imgBack }}" alt="{{ $m->titulo }} — reverso">
                            </div>
                        </div>
                        @else
                        <a href="{{ $modalSrc }}" class="jh-maquinaria-flip-inner jh-maquinaria-popup-trigger"
                           title="{{ $m->titulo }}">
                            <div class="jh-maquinaria-single-wrap">
                                <img src="{{ $modalSrc }}" alt="{{ $m->titulo }}">
                                <span class="jh-maquinaria-label">{{ $m->titulo }}</span>
                                <div class="jh-product-overlay jh-maquinaria-overlay">
                                    <span class="jh-zoom-icon"><i class="fas fa-expand-alt"></i></span>
                                </div>
                            </div>
                        </a>
                        @endif
                    </div>
                    <a href="{{ Helpers::whatsappBtnUrl($m->btn_url ?? null, $footerCorp) }}" class="jh-btn-whatsapp jh-maquinaria-wa" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i> {{ $m->btn_texto ?? 'Me interesa' }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
