@extends('web.base_shop')

@section('title', $metaData->titulo_pagina ?? 'Nosotros | Valento')

@section('head_page')

<link rel="stylesheet" href="{{ asset('temp02/css/about.css') }}?v={{ filemtime(public_path('temp02/css/about.css')) }}">
{{-- Mismo orden que temp02/about.html: fonts.css después de about.css para que los títulos usen Cinzel --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ filemtime(public_path('temp02/css/fonts.css')) }}">

@endsection

@section('content')

@include('web.partials.liam.header')

@php

    $p = $pagina ?? null;

    $heroImg = \App\Support\LiamPageData::img($p->url_imagen_hero ?? null, 'temp02/img/inicio/2.jpg');

@endphp



<section class="ab-hero">

    <div class="ab-hero__bg" style="background-image:url('{{ $heroImg }}')"></div>

    <div class="ab-hero__overlay"></div>

    <div class="ab-hero__content">

        <span class="ab-hero__tag">Valento Stile S.A.C. — Lima, Perú</span>

        <h1 class="ab-hero__title text-white">{!! $p->titulo_hero ?? 'Ropa laboral,<br>con <span>stock real</span>' !!}</h1>

        <p class="ab-hero__subtitle">{{ $p->subtitulo_hero ?? 'Polos de excelencia' }}</p>

    </div>

</section>



{{-- Ancla menú: /nosotros#nosotros --}}
<section id="nosotros" class="ab-section ab-anchor-section">

    <div class="container">

        <div class="ab-story">

            <div class="ab-story__img-wrap wow fadeInLeft" data-wow-duration="0.8s" data-wow-delay="0.1s">

                <img src="{{ \App\Support\LiamPageData::img($p->url_imagen_seccion ?? null, 'temp02/img/inicio/3.jpg') }}" alt="Nuestra historia">

                @if(!empty($p->historia_badge))

                    <span class="ab-story__img-badge">{{ $p->historia_badge }}</span>

                @endif

            </div>

            <div class="wow fadeInRight" data-wow-duration="0.8s" data-wow-delay="0.2s">

                <span class="ab-section__label">Nuestra Historia</span>

                <h2 class="ab-section__title">{{ $p->titulo_seccion ?? 'Pasión por la excelencia' }}</h2>

                <div class="ab-section__text">{!! $p->descripcion_seccion ?? $p->descripcion ?? '<p>Valento nace de la pasión por la moda y la calidad.</p>' !!}</div>

                @if(($historiaItems ?? collect())->isNotEmpty())

                <ul class="ab-story__list">

                    @foreach($historiaItems as $item)

                    <li class="wow fadeInUp" data-wow-duration="0.5s" data-wow-delay="{{ number_format(0.1 + $loop->index * 0.1, 1) }}s"><i class="fa fa-check-circle"></i> {{ $item->texto }}</li>

                    @endforeach

                </ul>

                @endif

            </div>

        </div>

    </div>

</section>



{{-- Ancla menú: /nosotros#estadisticas --}}
@if(($contadores ?? collect())->isNotEmpty())

<section id="estadisticas" class="ab-section ab-section--dark ab-anchor-section" style="padding:0;">

    <div class="ab-stats">

        @foreach($contadores as $c)

        <div class="ab-stat wow zoomIn" data-wow-duration="0.6s" data-wow-delay="{{ number_format($loop->index * 0.12, 2) }}s">

            <span class="ab-stat__num" data-target="{{ preg_replace('/\D/', '', $c->valor) }}" data-suffix="{{ $c->sufijo ?? '' }}">0</span>

            <span class="ab-stat__label">{{ $c->etiqueta }}</span>

        </div>

        @endforeach

    </div>

</section>

@else
<div id="estadisticas" class="ab-anchor-section" aria-hidden="true"></div>
@endif



{{-- Ancla menú: /nosotros#valores --}}
@if(($valores ?? collect())->isNotEmpty())

<section id="valores" class="ab-section ab-section--gray ab-anchor-section">

    <div class="container">

        <div class="text-center wow fadeInDown" data-wow-duration="0.7s" data-wow-delay="0.1s">

            <span class="ab-section__label">Lo que nos mueve</span>

            <h2 class="ab-section__title" style="max-width:500px;margin:0 auto 6px;">Nuestros valores</h2>

        </div>

        @php $valoresEffects = ['fadeInUp','zoomIn','flipInX','bounceInUp','fadeInLeft','fadeInRight']; @endphp
        <div class="ab-values">

            @foreach($valores as $v)

            @php $vEf = $valoresEffects[$loop->index % count($valoresEffects)]; @endphp
            <div class="ab-value-card wow {{ $vEf }}" data-wow-duration="0.6s" data-wow-delay="{{ number_format(0.1 + $loop->index * 0.12, 2) }}s">

                {{-- icon: SVG en línea (como el template) o clase Font Awesome --}}
                @php $vIcon = $v['icon'] ?? 'fa-star'; @endphp
                <div class="ab-value-card__icon">
                    @if(str_starts_with(trim($vIcon), '<svg'))
                        {!! $vIcon !!}
                    @else
                        <i class="fa {{ $vIcon }}"></i>
                    @endif
                </div>

                <p class="ab-value-card__title">{{ $v['titulo'] ?? '' }}</p>

                <p class="ab-value-card__text">{{ $v['texto'] ?? '' }}</p>

            </div>

            @endforeach

        </div>

    </div>

</section>

@else
<div id="valores" class="ab-anchor-section" aria-hidden="true"></div>
@endif



{{-- Ancla menú: /nosotros#trayectoria --}}
@if(($timeline ?? collect())->isNotEmpty())

<section id="trayectoria" class="ab-section ab-section--gray ab-anchor-section">

    <div class="container">

        <div class="text-center">

            <span class="ab-section__label">Nuestra trayectoria</span>

            <h2 class="ab-section__title" style="margin:0 auto 6px;">Historia de crecimiento</h2>

        </div>

        <div class="ab-timeline">

            @foreach($timeline as $t)

            @php $tlEf = $loop->even ? 'fadeInLeft' : 'fadeInRight'; @endphp
            <div class="ab-timeline__item wow {{ $tlEf }}" data-wow-duration="0.65s" data-wow-delay="{{ number_format(0.1 + $loop->index * 0.15, 2) }}s" style="position:relative;">

                <div class="ab-timeline__dot"></div>

                <div class="ab-timeline__card">

                    <p class="ab-timeline__year">{{ $t['year'] ?? '' }}</p>

                    <p class="ab-timeline__title">{{ $t['titulo'] ?? '' }}</p>

                    <p class="ab-timeline__text">{{ $t['texto'] ?? '' }}</p>

                </div>

            </div>

            @endforeach

        </div>

    </div>

</section>

@else
<div id="trayectoria" class="ab-anchor-section" aria-hidden="true"></div>
@endif



@php
    $ctaWaUrl = $urlWhatsapp ?? '';
    if ($ctaWaUrl === '') {
        $ctaWaUrl = 'https://wa.me/' . \App\Support\ContactoValento::whatsappNumero();
    }
@endphp
<section class="ab-cta">
    <div class="container wow zoomIn" data-wow-duration="0.7s" data-wow-delay="0.1s">
        <div class="ab-cta__frame">
            <span class="ab-cta__eyebrow">{{ $p->cta_eyebrow ?? 'Pedidos corporativos' }}</span>
            <h2 class="ab-cta__title">{!! $p->cta_titulo ?? '¿Cotizamos el uniforme <em>de tu equipo</em>?' !!}</h2>
            <p class="ab-cta__text">{{ $p->cta_texto ?? 'Revisa el catálogo o escríbenos: ropa laboral, promocional y deportiva Valento.' }}</p>
            <div class="ab-cta__actions">
                <a href="{{ url($p->cta_btn_url ?? '/tienda') }}" class="ab-cta__btn">{{ $p->cta_btn_texto ?? 'Ver colección' }}</a>
                <a href="{{ $ctaWaUrl }}" target="_blank" rel="noopener" class="ab-cta__btn ab-cta__btn--ghost">{{ $p->cta_btn2_texto ?? 'Cotizar por WhatsApp' }}</a>
            </div>
        </div>
    </div>
</section>



@include('web.partials.liam.footer')

@endsection

@push('scripts')

<script src="{{ asset('temp02/js/about.js') }}"></script>

@endpush


