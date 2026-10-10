@extends('web.base')

@section('head_page')
@vite(['resources/sass/web_about.scss'])
@endsection

@section('title', $metaData->titulo_pagina ?? 'Valento')
@section('meta_description', $metaData->descripcion_pagina ?? '')
@section('content')

@php
    $aboutVision   = $AboutData->get(0) ?? (object)['titulo' => '', 'descripcion' => '', 'url_imagen' => ''];
    $aboutMission  = $AboutData->get(1) ?? (object)['titulo' => '', 'descripcion' => '', 'url_imagen' => ''];
    $aboutWhoWeAre = $AboutData->get(2) ?? (object)['titulo' => '', 'descripcion' => '', 'url_imagen' => ''];
@endphp

@include('web.partials.breadcrumb')

<section class="about-section-modern">
    <div class="container">

        <!-- Quiénes Somos -->
        <div class="about-block">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 order-lg-1 order-2">
                    <div class="about-content">
                        <div class="badge-wrapper scroll-reveal reveal-left">
                            <span class="badge-number">01</span>
                            <span class="badge-line"></span>
                        </div>
                        <h2 class="about-title scroll-reveal reveal-left" style="transition-delay:0.1s">
                            {{ $aboutWhoWeAre->titulo }}
                        </h2>
                        <div class="about-divider scroll-reveal reveal-left" style="transition-delay:0.2s"></div>
                        <div class="about-text scroll-reveal reveal-left" style="transition-delay:0.3s">
                            {!! str_replace("\n", "<br />", $aboutWhoWeAre->descripcion) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 order-lg-2 order-1">
                    <div class="about-image-wrapper scroll-reveal reveal-zoom" style="transition-delay:0.1s">
                        <div class="image-decoration"></div>
                        <img src="{{ $aboutWhoWeAre->url_imagen }}"
                             alt="{{ $aboutWhoWeAre->titulo }}"
                             class="about-image">
                        <div class="image-overlay"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visión -->
        <div class="about-block reverse">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 order-lg-1 order-1">
                    <div class="about-image-wrapper scroll-reveal reveal-zoom" style="transition-delay:0.1s">
                        <div class="image-decoration"></div>
                        <img src="{{ $aboutVision->url_imagen }}"
                             alt="{{ $aboutVision->titulo }}"
                             class="about-image">
                        <div class="image-overlay"></div>
                    </div>
                </div>
                <div class="col-lg-7 order-lg-2 order-2">
                    <div class="about-content">
                        <div class="badge-wrapper scroll-reveal reveal-right">
                            <span class="badge-number">02</span>
                            <span class="badge-line"></span>
                        </div>
                        <h2 class="about-title scroll-reveal reveal-right" style="transition-delay:0.1s">
                            {{ $aboutVision->titulo }}
                        </h2>
                        <div class="about-divider scroll-reveal reveal-right" style="transition-delay:0.2s"></div>
                        <div class="about-text scroll-reveal reveal-right" style="transition-delay:0.3s">
                            {!! str_replace("\n", "<br />", $aboutVision->descripcion) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Misión -->
        <div class="about-block">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 order-lg-1 order-2">
                    <div class="about-content">
                        <div class="badge-wrapper scroll-reveal reveal-left">
                            <span class="badge-number">03</span>
                            <span class="badge-line"></span>
                        </div>
                        <h2 class="about-title scroll-reveal reveal-left" style="transition-delay:0.1s">
                            {{ $aboutMission->titulo }}
                        </h2>
                        <div class="about-divider scroll-reveal reveal-left" style="transition-delay:0.2s"></div>
                        <div class="about-text scroll-reveal reveal-left" style="transition-delay:0.3s">
                            {!! str_replace("\n", "<br />", $aboutMission->descripcion) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 order-lg-2 order-1">
                    <div class="about-image-wrapper scroll-reveal reveal-zoom" style="transition-delay:0.1s">
                        <div class="image-decoration"></div>
                        <img src="{{ $aboutMission->url_imagen }}"
                             alt="{{ $aboutMission->titulo }}"
                             class="about-image">
                        <div class="image-overlay"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection


@section('footer_page')
<script>
(function () {
    var els = document.querySelectorAll('.scroll-reveal');

    function reveal(entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }

    var observer = new IntersectionObserver(reveal, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    els.forEach(function (el) {
        observer.observe(el);
    });
})();
</script>
@endsection