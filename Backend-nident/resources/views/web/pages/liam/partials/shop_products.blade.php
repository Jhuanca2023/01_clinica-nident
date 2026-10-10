<div class="cat-grid" id="product-list">
    @forelse($productosDeGaleria ?? [] as $producto)
        @include('web.partials.liam.product_card', [
            'producto' => $producto,
            'promoBloqueId' => $promoBloqueId ?? null,
        ])
    @empty
        <div class="cat-no-results">
            <i class="fa fa-search"></i>
            No hay productos disponibles.
        </div>
    @endforelse
</div>
