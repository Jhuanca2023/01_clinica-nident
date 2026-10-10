@php
    $pubFirst = $publicaciones->isNotEmpty() ? $publicaciones->first() : null;
    $pubTitulo = $pubFirst->seccion_titulo ?? 'Publicaciones <span class="color-primary">Recientes</span>';
    $pubSub = $pubFirst->seccion_subtitulo ?? '';
@endphp
<section class="blog-sec mb-48" id="publicaciones">
  <div class="container-fluid">
    <div class="heading mb-48">
      <div class="title mb-12">
        <h2 class="h2">{!! $pubTitulo !!}</h2>
      </div>
      @if($pubSub)<p>{{ $pubSub }}</p>@endif
    </div>
    <div class="row row-gap-4 mb-48">
      @foreach($publicaciones as $pub)
      @php $imgPub = Helpers::cmsAssetUrl($pub->url_imagen); @endphp
      <div class="col-lg-3 col-md-6">
        <div class="blog-card">
          <a href="javascript:void(0)" class="card-img" role="button" aria-label="Ver imagen: {{ $pub->titulo }}">
            <img src="{{ $imgPub }}" alt="{{ $pub->titulo }}">
            <span class="pub-chip">{{ $pub->chip ?? 'Publicación' }}</span>
          </a>
          <div class="card-content">
            @php $pubUrl = $pub->slug ? url('/publicaciones/' . $pub->slug) : ($pub->url_enlace ?: url('/publicaciones')); @endphp
            <a href="{{ $pubUrl }}" class="fw-400 h6 hover-content">{{ $pub->titulo }}</a>
            <p class="light-black">{{ $pub->resumen }}</p>
            <div class="card-meta">
              <span class="read-link">Leer más</span>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="text-center pb-4">
      <a href="{{ url('/publicaciones') }}" class="cus-btn-2">
        <span>Ver más publicaciones</span>
        <span>Ver más publicaciones</span>
      </a>
    </div>
  </div>
</section>
