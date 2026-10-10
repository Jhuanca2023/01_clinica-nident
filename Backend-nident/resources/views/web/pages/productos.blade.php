@extends('web.pages.partials.landing_shell_jh')

@section('title', $metaData->titulo_pagina ?? 'Productos — Valento')
@section('meta_description', $metaData->descripcion_pagina ?? 'Productos de encuadernación, laminación y papelería — Valento.')

@section('page_content')
@include('web.pages.partials.page_banner_jh', [
    'bannerTitulo' => $pagina->banner_titulo ?? 'Productos',
    'bannerSubtitulo' => 'Valento',
    'bannerImagenUrl' => Helpers::cmsBannerUrl($pagina->banner_url_imagen ?? null),
])
@include('web.pages.productos.partials.productos_intro_jh')
@include('web.pages.productos.partials.productos_galeria_jh')
@include('web.pages.productos.partials.productos_prioridad_jh')
@include('web.pages.productos.partials.productos_frase_jh')
@endsection
