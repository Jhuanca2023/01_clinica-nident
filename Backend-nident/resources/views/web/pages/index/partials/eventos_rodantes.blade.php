@php
    $evFirst = $eventos->isNotEmpty() ? $eventos->first() : null;
    $evTitulo = $evFirst->seccion_titulo ?? 'Nuestros <span class="color-primary">Eventos</span>';
    $evSub = $evFirst->seccion_subtitulo ?? '';
@endphp
<section class="gallery-sec" id="eventos" aria-labelledby="eventos-titulo">
  <div class="container-fluid">
    <header class="gallery-sec__header heading mb-48">
      <div class="title mb-12">
        <h2 id="eventos-titulo" class="h2 wow fadeIn animated" data-wow-delay="330ms">{!! $evTitulo !!}</h2>
      </div>
      @if($evSub)<p class="gallery-sec__subtitle">{{ $evSub }}</p>@endif
    </header>
    <div class="gallery-sec__list row row-gap-4 mb-48" data-component="eventos-grid">
      @foreach($eventos as $i => $evento)
      @php
        $delay = 350 + ($i * 50);
        $imgEv = Helpers::cmsAssetUrl($evento->url_imagen);
      @endphp
      <article class="col-lg-4 col-md-6 gallery-sec__item wow fadeInUp animated"
        data-wow-delay="{{ $delay }}ms"
        data-evento-id="{{ $evento->id_evento }}"
        data-evento-slug="{{ $evento->slug }}">
        <a href="javascript:void(0)" class="galler-img gallery-sec__link" role="button"
          aria-label="Ver imagen: {{ $evento->titulo }}" title="{{ $evento->titulo }}">
          <img src="{{ $imgEv }}" alt="{{ $evento->alt_imagen ?? $evento->titulo }}" loading="lazy">
        </a>
      </article>
      @endforeach
    </div>
    <footer class="gallery-sec__footer text-center">
      <a href="#eventos" class="cus-btn-2">
        <span>Ver más eventos</span>
        <span>Ver más eventos</span>
      </a>
    </footer>
  </div>
</section>
