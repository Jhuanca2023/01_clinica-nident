@php
    $imgUrl = Helpers::cmsAssetUrl($pagina->catalogo_url_imagen ?? null);
@endphp
@if($pagina->catalogo_url_imagen ?? null)
<section class="jh-nosotros-catalogo default-padding-bottom">
    <div class="container">
        <div class="jh-nosotros-catalogo-header">
            <h3 class="jh-nosotros-catalogo-titulo">
                <i class="fas fa-book-open"></i> Nuestro Catálogo
            </h3>
            <p class="jh-nosotros-catalogo-subtitulo">Haz clic en la imagen para verla en detalle</p>
        </div>
        <div class="jh-nosotros-catalogo-wrap">
            <a href="{{ $imgUrl }}" class="popup-link jh-nosotros-catalogo-link" title="Catálogo Valento">
                <img src="{{ $imgUrl }}" alt="Catálogo Valento" class="jh-nosotros-catalogo-img">
                <div class="jh-nosotros-catalogo-overlay">
                    <span class="jh-nosotros-catalogo-zoom">
                        <i class="fas fa-search-plus"></i>
                        <span>Ver completo</span>
                    </span>
                </div>
            </a>
        </div>
    </div>
</section>
@endif
