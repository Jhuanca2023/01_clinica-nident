<div class="products mb-3">
    <div class="row gal-cards-row">
        @php $isMayorista = Auth::check() ? \App\Helpers\Helper::validateIsMayorista(Auth::id()) : false; @endphp
        @foreach($productosDeGaleria as $data)
        <div class="col-6 col-md-4 @if($productosDeGaleria->count() == 1) col-lg-12 @elseif($productosDeGaleria->count() == 2) col-lg-6 @else col-lg-4 @endif gal-cards-col">
            @include('web.pages.galeria.ajax.productos_grids')
        </div>
        @endforeach
    </div>
</div>

@if($productosDeGaleria->isEmpty())
<div class="col-12">
    <div class="gal-no-results">
        <i class="fas fa-search"></i>
        <p>No se encontraron productos con los filtros seleccionados.</p>
    </div>
</div>
@endif
