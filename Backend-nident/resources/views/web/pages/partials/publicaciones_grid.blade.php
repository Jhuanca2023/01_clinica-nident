@foreach($publicaciones as $pub)
@php
  $imgPub = Helpers::cmsAssetUrl($pub->url_imagen);
  $url = $pub->slug ? url('/publicaciones/' . $pub->slug) : ($pub->url_enlace ?: '#');
  $fecha = $pub->fecha_publicacion ? \Carbon\Carbon::parse($pub->fecha_publicacion)->format('d M, Y') : '';
@endphp
<div class="col-lg-3 col-md-6">
  <div class="blog-card">
    <a href="{{ $url }}" class="card-img">
      <img src="{{ $imgPub }}" alt="{{ $pub->titulo }}">
      <span class="pub-chip">{{ $pub->chip ?? ($pub->categoria ?? 'Publicación') }}</span>
    </a>
    <div class="card-content">
      @if($fecha)<p class="blog-date light-black mb-8">{{ $fecha }}</p>@endif
      <a href="{{ $url }}" class="fw-400 h6 hover-content">{{ $pub->titulo }}</a>
      <p class="light-black">{{ $pub->resumen }}</p>
      <div class="card-meta">
        <a href="{{ $url }}" class="read-link">Leer más</a>
      </div>
    </div>
  </div>
</div>
@endforeach
