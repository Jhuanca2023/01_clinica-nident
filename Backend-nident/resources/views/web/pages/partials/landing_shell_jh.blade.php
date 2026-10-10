@extends('web.base_landing')

@section('content')
<div class="se-pre-con"></div>

@include('web.pages.index.partials.header_jh', ['paginaActiva' => $paginaActiva ?? ''])

@yield('page_content')

@include('web.pages.index.partials.footer_jh')
@include('web.pages.index.partials.floating_jh')
@include('web.pages.index.partials.scripts_jh')
@endsection
