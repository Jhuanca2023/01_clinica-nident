@php
    $catFallbacks = [
        'temp02/img/inicio/categoria/1.jpg',
        'temp02/img/inicio/categoria/2.jpg',
        'temp02/img/inicio/categoria/3.jpg',
        'temp02/img/inicio/categoria/4.jpg',
    ];
@endphp
<!-- ===== SECCION CATEGORIAS (carrusel) ===== -->
<section id="categoria" class="lv-categories-section lv-anchor-section">
    <div class="container lv-container-wide">
        <div class="lv-categories-head">
            <h2 class="lv-categories-title">Categorías</h2>
            <a href="{{ url('/tienda') }}" class="lv-categories-all">Ver todo el catálogo</a>
        </div>
        <div class="lv-cat-carousel">
            <div class="swiper">
                <div class="swiper-wrapper">
                    @forelse($categorias ?? [] as $cat)
                        @php
                            $catFallback = $catFallbacks[$loop->index % count($catFallbacks)];
                            $catVacia    = (int) ($cat->productos_count ?? 0) === 0;
                        @endphp
                        <div class="swiper-slide">
                            <a href="{{ $catVacia ? '#' : \App\Support\CategoriaTiendaUrl::tienda($cat) }}"
                               class="lv-cat-card{{ $catVacia ? ' lv-cat-card--sin-productos' : '' }}"
                               @if($catVacia) role="button" aria-disabled="true" data-cat-nombre="{{ $cat->nombre }}" @endif>
                                <div class="lv-cat-card__img">
                                    <img src="{{ \App\Support\LiamPageData::img($cat->url_imagen ?? null, $catFallback) }}"
                                         alt="{{ $cat->nombre }}" loading="lazy" decoding="async">
                                </div>
                                <span class="lv-cat-card__name">{{ $cat->nombre }}</span>
                                <span class="lv-cat-card__meta">{{ $catVacia ? 'Próximamente' : 'Ver productos' }}</span>
                            </a>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <a href="{{ url('/tienda') }}" class="lv-cat-card">
                                <div class="lv-cat-card__img">
                                    <img src="{{ asset('temp02/img/inicio/categoria/1.jpg') }}" alt="Ver tienda" loading="lazy" decoding="async">
                                </div>
                                <span class="lv-cat-card__name">Ver tienda</span>
                                <span class="lv-cat-card__meta">Ver productos</span>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
            <button type="button" class="lv-cat-nav lv-cat-nav--prev" aria-label="Categorías anteriores"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
            <button type="button" class="lv-cat-nav lv-cat-nav--next" aria-label="Más categorías"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
        </div>
    </div>
</section>
<!-- ===== FIN CATEGORIAS ===== -->

@once
<style>
.lv-cat-card--sin-productos { cursor: not-allowed; }
</style>
<script>
(function () {
    if (window.__lvCatSinProductosBound) return;
    window.__lvCatSinProductosBound = true;

    document.addEventListener('click', function (e) {
        var item = e.target.closest('.lv-cat-card--sin-productos');
        if (!item) return;
        e.preventDefault();
        e.stopPropagation();

        var nombre = item.getAttribute('data-cat-nombre') || '';
        var msg = nombre
            ? 'La categoría "' + nombre + '" no tiene productos asignados.'
            : 'No hay productos asignados a esta categoría.';

        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: msg,
                showConfirmButton: false,
                timer: 3400,
                timerProgressBar: true
            });
        } else if (window.toastr) {
            window.toastr.info(msg);
        } else {
            alert(msg);
        }
    });
})();
</script>
@endonce
