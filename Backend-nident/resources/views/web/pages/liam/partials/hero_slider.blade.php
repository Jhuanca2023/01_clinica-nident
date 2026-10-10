@php
    use Illuminate\Support\Collection;

    // Imágenes del hero de la plantilla (descargadas a storage_/slider) si el slide no tiene imagen propia
    $heroFallbackImgs = [
        asset('storage_/slider/valento-1523381210434-271e8be1f52b.jpg'),
        asset('storage_/slider/valento-1562157873-818bc0726f68.jpg'),
        asset('storage_/slider/valento-1489987707025-afc232f7ea0f.jpg'),
    ];

    $heroHref = static function ($raw) {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '#') {
            return url('/tienda');
        }
        if (preg_match('#^(https?:)?//#i', $raw) || str_starts_with($raw, '#') || str_starts_with($raw, 'mailto:') || str_starts_with($raw, 'tel:')) {
            return $raw;
        }

        return url('/' . ltrim($raw, '/'));
    };

    $heroRaw = ($sliderData ?? collect());
    $heroRaw = $heroRaw instanceof Collection ? $heroRaw : collect($heroRaw);

    $heroSlides = $heroRaw
        ->filter(fn ($s) => strtoupper(trim((string) ($s->Activo ?? 'S'))) === 'S')
        ->sortBy(fn ($s) => (int) ($s->id_slider ?? 0))
        ->values()
        ->map(function ($s, $i) use ($heroFallbackImgs, $heroHref) {
            $btnTexto = trim((string) ($s->texto_boton ?? $s->btn_texto ?? ''));
            $btnUrl   = $s->url_link ?? $s->btn_url ?? '';

            return [
                'img'       => trim((string) ($s->url_imagen ?? '')) !== ''
                    ? \App\Support\LiamPageData::img($s->url_imagen)
                    : $heroFallbackImgs[$i % count($heroFallbackImgs)],
                'eyebrow'   => trim((string) ($s->subtitulo ?? '')),
                'titulo'    => trim((string) ($s->titulo ?? '')),
                'texto'     => trim((string) ($s->descripcion ?? $s->text ?? '')),
                'btn_texto' => $btnTexto,
                'btn_url'   => $heroHref($btnUrl),
            ];
        })
        ->filter(fn ($s) => $s['titulo'] !== '' || $s['eyebrow'] !== '' || $s['texto'] !== '')
        ->values();

    // Sin data en BD: copy corporativo Valento (mismo texto del template temp02).
    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([
            [
                'img'       => $heroFallbackImgs[0],
                'eyebrow'   => 'Semplicemente Valento',
                'titulo'    => 'Ropa laboral para tu empresa',
                'texto'     => 'Uniformes, polos y prendas listas para personalizar, con stock permanente.',
                'btn_texto' => 'Ver catálogo',
                'btn_url'   => url('/tienda'),
            ],
            [
                'img'       => $heroFallbackImgs[1],
                'eyebrow'   => 'Mayorista',
                'titulo'    => 'Textil promocional con tu logo',
                'texto'     => 'Camisetas, polos y vestuario para equipos, eventos y colectividades.',
                'btn_texto' => 'Conocer Valento',
                'btn_url'   => url('/nosotros'),
            ],
            [
                'img'       => $heroFallbackImgs[2],
                'eyebrow'   => 'Desde 1994',
                'titulo'    => 'Stock permanente, despacho ágil',
                'texto'     => 'Abastecemos profesionales y empresas con la mejor relación calidad-precio.',
                'btn_texto' => 'Cotizar ahora',
                'btn_url'   => url('/contacto'),
            ],
        ]);
    }

    $heroTotal = str_pad((string) $heroSlides->count(), 2, '0', STR_PAD_LEFT);
@endphp

<!-- Hero Slider Begin (Swiper) -->
<section class="vl-hero" aria-label="Destacados Valento">
    <div class="swiper vl-hero__swiper">
        <div class="swiper-wrapper">
            @foreach($heroSlides as $slide)
            <div class="swiper-slide vl-hero__slide">
                <img class="vl-hero__img"
                     src="{{ $slide['img'] }}"
                     alt="{{ $slide['titulo'] ?: 'Valento' }}"
                     @if($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                     decoding="async">
                <div class="vl-hero__overlay" aria-hidden="true"></div>
                <div class="vl-hero__content">
                    @if($slide['eyebrow'] !== '')
                        <span class="vl-hero__eyebrow">{{ $slide['eyebrow'] }}</span>
                    @endif
                    @if($slide['titulo'] !== '')
                        @if($loop->first)
                            <h1 class="vl-hero__title">{{ $slide['titulo'] }}</h1>
                        @else
                            <h2 class="vl-hero__title">{{ $slide['titulo'] }}</h2>
                        @endif
                    @endif
                    @if($slide['texto'] !== '')
                        <p class="vl-hero__text">{{ $slide['texto'] }}</p>
                    @endif
                    @if($slide['btn_texto'] !== '')
                        <a href="{{ $slide['btn_url'] }}" class="vl-hero__btn">{{ $slide['btn_texto'] }} <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <button type="button" class="vl-hero__nav vl-hero__nav--prev" aria-label="Slide anterior"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
    <button type="button" class="vl-hero__nav vl-hero__nav--next" aria-label="Slide siguiente"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
    <div class="vl-hero__pagination"></div>
    <div class="vl-hero__counter" aria-hidden="true"><strong class="vl-hero__current">01</strong> / {{ $heroTotal }}</div>
</section>
<!-- Hero Slider End -->
