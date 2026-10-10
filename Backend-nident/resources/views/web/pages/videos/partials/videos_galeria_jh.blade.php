@php
    $titulo = $pagina->seccion_titulo ?? 'Creando Espirales';
    $descripcion = $pagina->seccion_descripcion ?? '';
@endphp
<section class="event-area default-padding jh-videos-section jh-videos-page">
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
                @forelse($videos as $v)
                    @include('web.pages.partials.video_card_jh', ['v' => $v])
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No hay videos publicados por el momento.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
