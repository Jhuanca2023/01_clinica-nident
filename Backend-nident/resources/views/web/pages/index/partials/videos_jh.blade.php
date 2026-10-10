@php
    $paginaVideos = $paginaVideos ?? $videoSeccion ?? null;
    $videos = $videos ?? collect();
    $titulo = $paginaVideos->seccion_titulo ?? 'Creando Espirales';
    $descripcion = $paginaVideos->seccion_descripcion ?? '';
@endphp
@if($paginaVideos || $videos->isNotEmpty())
<section id="videos" class="event-area default-padding jh-videos-section">
    <div class="container">
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2">
                    <h2 class="jh-section-title-line">{{ $titulo }}</h2>
                    @if($descripcion)<p>{{ $descripcion }}</p>@endif
                </div>
            </div>
        </div>
        <div class="event-items">
            <div class="row jh-videos-grid">
                @foreach($videos as $v)
                    @include('web.pages.partials.video_card_jh', ['v' => $v])
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
