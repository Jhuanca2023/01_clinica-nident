@extends('web.base')

<!-- Contenido en el Head de la pagina -->
@section('head_page')
<!-- extras here!!-->
@vite(['resources/sass/web_shopDetail_lista.scss'])

@endsection
<!-- COOKIES AND POLICE : HEADER-->
<!-- Contenido en el Body -->
@section('content')
@section('title', $metaData->titulo_pagina ?? 'Valento')
@section('meta_description', $metaData->descripcion_pagina ?? '')
@php
$footerData = Helpers::footer_();
@endphp

<!-- Start All Title Box -->
@include('web.partials.breadcrumb')
<!-- End All Title Box -->
<style>
    /* Estilo del mapa */
    .map-container {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        margin-bottom: 3rem;
    }
        .map-container iframe {
        width: 100% !important;
        height: 450px !important;
        border: 0;
    }
</style>

<div class="container">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 pl-5 text-center">
                <h1 class="title mb-1">Ubicación</h1><!-- End .title mb-2 -->
                <p class="mb-3">“Estamos ubicados en un punto estratégico de la ciudad, cerca de vías principales y de fácil llegada en transporte público y privado.”</p>
            </div><!-- End .col-lg-6 -->
        </div><!-- End .row -->
    </div><!-- End .container -->

    <div class="mb-5">
        @isset($footerData[0]->url_mapa)
        <!-- Mapa en la parte superior -->
        <div class="map-container">
            {!! $footerData[0]->url_mapa!!}
        </div>
        @endisset
    </div><!-- End Google Maps -->
</div><!-- End .page-content -->

@endsection
<!-- COOKIES AND POLICE: FOOTER -->
@section('footer_page')
<script>
</script>
@endsection