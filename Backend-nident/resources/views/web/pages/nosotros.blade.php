@extends('web.pages.partials.landing_shell_jh')

@section('title', $metaData->titulo_pagina ?? 'Nosotros — Valento')
@section('meta_description', $metaData->descripcion_pagina ?? 'Conoce Valento — fabricantes y distribuidores de espirales PVC, micas y enmicados.')

@section('page_content')
@include('web.pages.partials.page_banner_jh', [
    'bannerTitulo' => $pagina->banner_titulo ?? 'Nosotros',
    'bannerSubtitulo' => $pagina->banner_subtitulo ?? $pagina->banner_texto ?? 'Valento',
    'bannerImagenUrl' => $bannerImagenUrl ?? Helpers::cmsBannerUrl(
        $pagina->banner_url_imagen ?? null,
        array_filter([
            $pagina->catalogo_url_imagen ?? null,
            $pagina->intro_url_logo ?? null,
        ])
    ),
])
@include('web.pages.nosotros.partials.nosotros_intro_jh')
@include('web.pages.nosotros.partials.nosotros_quienes_jh')
@include('web.pages.nosotros.partials.nosotros_catalogo_jh')
@include('web.pages.nosotros.partials.nosotros_productos_jh')
@include('web.pages.nosotros.partials.nosotros_estadisticas_jh')
@include('web.pages.nosotros.partials.nosotros_frase_jh')
@endsection
