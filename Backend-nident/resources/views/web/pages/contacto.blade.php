@extends('web.pages.partials.landing_shell_jh')

@section('title', $metaData->titulo_pagina ?? 'Contacto — Valento')
@section('meta_description', $metaData->descripcion_pagina ?? 'Contáctanos — Valento.')

@section('page_content')
@include('web.pages.partials.page_banner_jh', [
    'bannerTitulo' => $paginaContacto->banner_titulo ?? 'Contáctanos',
    'bannerSubtitulo' => 'Estamos para ayudarte',
    'bannerImagenUrl' => Helpers::cmsBannerUrl(
        $paginaContacto->banner_url_imagen ?? null,
        array_filter([$paginaContacto->url_imagen_form ?? null])
    ),
])
@include('web.pages.partials.contacto_pagina_jh')
@include('web.pages.partials.cta_frase_jh', ['fraseTexto' => $paginaContacto->frase_texto ?? null])
@endsection
