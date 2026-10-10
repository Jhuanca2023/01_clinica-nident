@extends('web.base_shop')

@section('title', $metaData->titulo_pagina ?? 'Valento | Ropa laboral y textil')
@section('meta_description', $metaData->descripcion_pagina ?? '')

@section('content')
@include('web.partials.liam.header')
@include('web.pages.liam.partials.hero_slider')
@include('web.pages.liam.partials.categories_home')
@include('web.pages.liam.partials.products_home')
@include('web.pages.liam.partials.promo_sections')
@include('web.pages.liam.partials.lookbook')
@include('web.pages.liam.partials.footer_banner')
@include('web.partials.liam.footer')
@endsection

{{-- Cursor "onda" (punto + aro) solo en el home --}}
@section('head_page')
<link rel="stylesheet" href="{{ asset('temp02/css/cursor-gotas.css') }}?v={{ filemtime(public_path('temp02/css/cursor-gotas.css')) }}">
@endsection

@push('scripts')
<script src="{{ asset('temp02/js/cursor-gotas.js') }}?v={{ filemtime(public_path('temp02/js/cursor-gotas.js')) }}" defer></script>
@endpush
