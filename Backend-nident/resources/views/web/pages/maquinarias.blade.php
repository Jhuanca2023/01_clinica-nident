@extends('web.pages.partials.landing_shell_jh')

@section('title', $metaData->titulo_pagina ?? 'Máquinarias — Valento')
@section('meta_description', $metaData->descripcion_pagina ?? 'Máquinarias de encuadernación y laminado — Valento.')

@section('page_content')
@include('web.pages.partials.page_banner_jh', [
    'bannerTitulo' => $pagina->banner_titulo ?? 'Máquinarias',
    'bannerSubtitulo' => 'Valento',
    'bannerImagenUrl' => Helpers::cmsBannerUrl($pagina->banner_url_imagen ?? null),
])
@include('web.pages.maquinarias.partials.maquinarias_galeria_jh')
@include('web.pages.partials.cta_frase_jh', ['fraseTexto' => $pagina->frase_texto ?? null])
@endsection
