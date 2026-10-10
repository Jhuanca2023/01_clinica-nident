@extends('web.pages.partials.landing_shell')

@section('title', $metaData->titulo_pagina ?? 'Publicaciones — Bibliotecas Rodantes')
@section('meta_description', $metaData->descripcion_pagina ?? '')

@section('page_content')
@include('web.pages.partials.title_banner', [
  'bannerTitulo' => $bannerTitulo ?? 'Publicaciones',
  'bannerImagenUrl' => $bannerImagenUrl ?? Helpers::cmsAssetUrl('temp02/assets/media/images/title-banner.jpg'),
  'bannerClass' => 'mb-80',
])

<section class="blog-sec mb-96">
  <div class="container-fluid">
    <div class="heading mb-48">
      <div class="title mb-12">
        <h2 class="h2">{!! $seccionTitulo !!}</h2>
      </div>
      @if($seccionSub)<p>{{ $seccionSub }}</p>@endif
    </div>
    <div class="row row-gap-4" id="publicaciones-grid">
      @include('web.pages.partials.publicaciones_grid', ['publicaciones' => $publicaciones])
    </div>
    @php $totalPages = (int) ceil(max(1, $total) / max(1, $porPagina)); @endphp
    @if($totalPages > 1)
    <nav class="pagination-block mt-48" aria-label="Paginación">
      <ul class="pagination justify-content-center" id="pub-pagination" data-total="{{ $totalPages }}" data-current="{{ $pagina }}">
        @for($p = 1; $p <= $totalPages; $p++)
        <li class="page-item {{ $p == $pagina ? 'active' : '' }}">
          <a class="page-link" href="{{ url('/publicaciones?page=' . $p) }}" data-page="{{ $p }}">{{ $p }}</a>
        </li>
        @endfor
      </ul>
    </nav>
    @endif
  </div>
</section>
@endsection
