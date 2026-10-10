@php
    $seccion = $clientesSeccion ?? null;
    $logos = ($clientesLogos ?? collect())->filter(fn ($l) => !empty($l->url_imagen) || !empty($l->nombre));
    $fallbackImg = 'temp02/img/logos/logo-1.png';
    $cacheVer = $seccion?->updated_at
        ? strtotime((string) $seccion->updated_at)
        : ($logos->max(fn ($l) => strtotime((string) ($l->updated_at ?? ''))) ?: time());
@endphp

@if($seccion || $logos->isNotEmpty())
<section id="nuestros_clientes" class="lv-clientes-section spad lv-anchor-section" aria-label="Nuestros clientes" data-cms-version="{{ $cacheVer }}">
    <div class="container">
        @if($seccion && ($seccion->titulo || $seccion->tag || $seccion->descripcion))
        <div class="lv-clientes-section__head">
            @if(!empty($seccion->tag))
                <span class="lv-clientes-section__tag">{{ $seccion->tag }}</span>
            @endif
            @if(!empty($seccion->titulo))
                <h2 class="lv-clientes-section__title">{{ $seccion->titulo }}</h2>
            @endif
            @if(!empty($seccion->descripcion))
                <p class="lv-clientes-section__desc">{{ $seccion->descripcion }}</p>
            @endif
        </div>
        @endif

        @if($logos->isNotEmpty())
        <div class="lv-clientes-carousel logo-items owl-carousel">
            @foreach($logos as $logo)
                @php
                    $href = trim((string) ($logo->url_enlace ?? ''));
                    $imgSrc = \App\Support\LiamPageData::img($logo->url_imagen ?? null, $fallbackImg);
                    $imgVer = !empty($logo->updated_at) ? strtotime((string) $logo->updated_at) : $cacheVer;
                    $imgSrcWithVer = $imgSrc . (str_contains($imgSrc, '?') ? '&' : '?') . 'v=' . $imgVer;
                @endphp
                <div class="lv-clientes-logo">
                    @if($href !== '' && $href !== '#')
                        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" title="{{ $logo->nombre ?? '' }}">
                            <span class="lv-clientes-logo__ring">
                                <img src="{{ $imgSrcWithVer }}" alt="{{ $logo->nombre ?? 'Cliente' }}">
                            </span>
                        </a>
                    @else
                        <span class="lv-clientes-logo__ring">
                            <img src="{{ $imgSrcWithVer }}" alt="{{ $logo->nombre ?? 'Cliente' }}">
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@else
<div id="nuestros_clientes" class="lv-anchor-section" aria-hidden="true"></div>
@endif
