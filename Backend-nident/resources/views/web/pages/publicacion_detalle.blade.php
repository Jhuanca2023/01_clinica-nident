@extends('web.pages.partials.landing_shell')

@section('title', $metaData->titulo_pagina ?? $publicacion->titulo)
@section('meta_description', $metaData->descripcion_pagina ?? '')

@section('page_content')
@include('web.pages.partials.title_banner', [
  'bannerTitulo' => $bannerTitulo ?? $publicacion->titulo,
  'bannerImagenUrl' => $bannerImagenUrl ?? Helpers::cmsAssetUrl('temp02/assets/media/images/title-banner.jpg'),
  'bannerClass' => 'mb-48',
])

<section class="blog-detail-sec mb-96">
  <div class="container-fluid">
    <div class="row row-gap-4">
      <div class="col-lg-8">
        <article class="blog-detail">
          @php $img = Helpers::cmsAssetUrl($publicacion->url_imagen); @endphp
          @if($publicacion->url_imagen)
          <div class="blog-detail-img mb-32">
            <img src="{{ $img }}" alt="{{ $publicacion->titulo }}" class="br-24 w-100">
          </div>
          @endif
          <div class="blog-meta mb-24">
            <span class="color-primary">{{ $publicacion->categoria ?? $publicacion->chip }}</span>
            @if($publicacion->fecha_publicacion)
            <span class="light-black ms-3">{{ \Carbon\Carbon::parse($publicacion->fecha_publicacion)->format('d M, Y') }}</span>
            @endif
            @if($publicacion->autor)
            <span class="light-black ms-3">por {{ $publicacion->autor }}</span>
            @endif
          </div>
          @if($publicacion->resumen)
          <p class="h5 fw-400 mb-32">{{ $publicacion->resumen }}</p>
          @endif
          @if(!empty($publicacion->contenido))
          @include('web.pages.partials.html_doc_iframe', [
            'htmlBody' => $publicacion->contenido,
            'iframeTitle' => $publicacion->titulo,
          ])
          @elseif($publicacion->resumen)
          <p class="light-black">{{ $publicacion->resumen }}</p>
          @endif
        </article>
      </div>
      <div class="col-lg-4">
        <aside class="blog-sidebar">
          @if($recientes->isNotEmpty())
          <div class="sidebar-block mb-32">
            <h5 class="h5 mb-16">Recientes</h5>
            <ul class="recent-posts list-unstyled">
              @foreach($recientes as $r)
              <li class="mb-16">
                <a href="{{ url('/publicaciones/' . $r->slug) }}" class="hover-content">{{ $r->titulo }}</a>
                @if($r->fecha_publicacion)
                <p class="light-black small mb-0">{{ \Carbon\Carbon::parse($r->fecha_publicacion)->format('d M, Y') }}</p>
                @endif
              </li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="sidebar-block">
            <a href="{{ url('/publicaciones') }}" class="cus-btn-2">
              <span>Ver todas</span>
              <span>Ver todas</span>
            </a>
          </div>
        </aside>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
@if(!empty($publicacion->contenido))
@include('web.pages.partials.html_doc_iframe_scripts')
@endif
@endpush
