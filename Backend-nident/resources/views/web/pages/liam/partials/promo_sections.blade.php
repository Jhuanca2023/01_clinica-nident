@php
    $dominio = rtrim(config('app.url'), '/') . '/';
    // Closure (no función global) para evitar "Cannot redeclare" si la vista se renderiza más de una vez
    $promoImg = function ($path, $fallback = '') {
        if (!$path) return $fallback ? asset($fallback) : '';
        if (str_starts_with($path, 'http') || str_starts_with($path, '//')) return $path;
        return asset(ltrim($path, '/'));
    };
    // Precio como en la plantilla: S/ 45 .00 (enteros grandes, decimales pequeños)
    $promoPrecio = function ($precio) {
        [$entero, $decimal] = explode('.', number_format((float) $precio, 2, '.', ''));
        return '<sup>S/</sup>' . $entero . '<sub>.' . $decimal . '</sub>';
    };
    // Título del hero: saltos de línea y *palabra* resaltada en dorado
    $promoTitulo = function ($texto) {
        $html = nl2br(e($texto));
        return preg_replace('/\*([^*]+)\*/', '<span>$1</span>', $html);
    };
@endphp

@forelse($promoBloques ?? [] as $bloque)
<section id="bloque{{ $loop->iteration }}" class="promo-section lv-anchor-section">
    <div class="lv-section-title wow fadeInUp" data-wow-duration="0.7s" data-wow-delay="0.1s">
        <span class="lv-subtitle">{{ $bloque->titulo_seccion ?? 'Ofertas Destacadas' }}</span>
        @php
            $parts = explode('&', $bloque->subtitulo_seccion ?? 'Ofertas & Promociones', 2);
        @endphp
        <h2>
            <span class="lv-accent">{{ trim($parts[0]) }}{{ isset($parts[1]) ? ' &' : '' }}</span>
            {{ isset($parts[1]) ? trim($parts[1]) : '' }}
        </h2>
    </div>
    <div class="promo-section__row">

        {{-- Hero izquierda --}}
        <div class="promo-hero wow fadeInLeft" data-wow-duration="0.75s" data-wow-delay="0.2s">
            <img class="promo-hero__img"
                 src="{{ $promoImg($bloque->imagen_hero, 'temp02/img/slider-1.jpg') }}"
                 alt="{{ $bloque->hero_etiqueta ?? '' }}">
            <div class="promo-hero__overlay">
                <p class="promo-hero__tag">{{ $bloque->hero_etiqueta ?? 'Nueva Colección' }}</p>
                <h2 class="promo-hero__title">
                    {!! $promoTitulo($bloque->hero_titulo ?? 'Tu imagen en nuestras manos') !!}
                </h2>
                <div class="promo-hero__brands">
                    @foreach(explode('·', $bloque->hero_subtitulo ?? 'Valento · Excelencia · Premium') as $brand)
                        <span>{{ trim($brand) }}</span>
                        @if(!$loop->last)<span>&bull;</span>@endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Grid 2x2 items --}}
        <div class="promo-grid">
            @php
                // Productos del bloque + productos por submódulo
                $idsBloque = ($bloque->productosVinculados ?? collect())
                    ->pluck('id_producto')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                $itemProductMap = [];
                if (\Illuminate\Support\Facades\Schema::hasTable('web_promo_item_producto')) {
                    $itemIdsAll = ($bloque->items ?? collect())->pluck('id_promo_item')->filter()->all();
                    if (! empty($itemIdsAll)) {
                        $rows = \Illuminate\Support\Facades\DB::table('web_promo_item_producto')
                            ->whereIn('id_promo_item', $itemIdsAll)
                            ->orderBy('orden')
                            ->get(['id_promo_item', 'id_producto']);
                        foreach ($rows as $r) {
                            $iid = (int) $r->id_promo_item;
                            $pid = (int) $r->id_producto;
                            $itemProductMap[$iid] = $itemProductMap[$iid] ?? [];
                            $itemProductMap[$iid][] = $pid;
                            $idsBloque[] = $pid;
                        }
                    }
                }

                if (count($idsBloque) === 0) {
                    $idsBloque = ($bloque->items ?? collect())
                        ->where('Activo', 'S')
                        ->pluck('id_producto')
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();
                }

                $idsBloque = array_values(array_unique(array_filter(array_map('intval', $idsBloque))));

                // Solo productos activos reales (+ precio / imagen de referencia por producto)
                $productoPrecioMap = [];
                if (count($idsBloque) > 0) {
                    $productosActivos = \App\Models\Producto::whereIn('id_producto', $idsBloque)
                        ->where('Activo', 'S')
                        ->get(['id_producto', 'precio', 'precio_old', 'url_imagen', 'descuento_porcentaje']);
                    $idsBloque = $productosActivos
                        ->pluck('id_producto')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all();
                    foreach ($productosActivos as $pa) {
                        $productoPrecioMap[(int) $pa->id_producto] = [
                            'precio'     => (float) str_replace(',', '', (string) ($pa->precio ?? 0)),
                            'precio_old' => (float) str_replace(',', '', (string) ($pa->precio_old ?? 0)),
                            'url_imagen' => $pa->url_imagen ?? null,
                            'descuento_porcentaje' => isset($pa->descuento_porcentaje) ? (float) $pa->descuento_porcentaje : null,
                        ];
                    }
                }
            @endphp
            @foreach(($bloque->items ?? collect())->where('Activo', 'S')->take(4) as $item)
            @php
                $cardDelay = number_format(0.25 + ($loop->index * 0.12), 2) . 's';
                $idsItem = $itemProductMap[(int) $item->id_promo_item] ?? [];
                if (empty($idsItem) && !empty($item->id_producto)) {
                    $idsItem = [(int) $item->id_producto];
                }
                $idsItem = array_values(array_intersect($idsItem, $idsBloque));
                $tieneProductosItem = count($idsItem) > 0;
                $compraUrl = null;
                $precioRef = null;
                $precioListaRef = 0;
                $imgCard = $item->imagen;
                $mostrarPrecioPortada = strtoupper(trim((string) ($item->mostrar_precio_portada ?? 'N'))) === 'S';
                $idPortada = (int) ($item->id_producto_portada ?? 0);
                if ($idPortada > 0 && ! in_array($idPortada, $idsItem, true)) {
                    $idPortada = 0;
                }
                if ($tieneProductosItem) {
                    $focusId = $idPortada > 0 ? $idPortada : $idsItem[0];
                    $compraUrl = \App\Support\PromoTiendaUrl::tiendaOfertas($bloque, null, $focusId);
                    // Imagen: producto Portada tiene prioridad sobre la imagen por defecto de la tarjeta
                    if ($idPortada > 0) {
                        $imgProd = $productoPrecioMap[$idPortada]['url_imagen'] ?? null;
                        if (! empty($imgProd)) {
                            $imgCard = $imgProd;
                        }
                    }
                    if ($mostrarPrecioPortada && $idPortada > 0) {
                        $precioListaRef = (float) ($productoPrecioMap[$idPortada]['precio'] ?? 0);
                        $precioOldMap = (float) ($productoPrecioMap[$idPortada]['precio_old'] ?? 0);
                        if ($precioOldMap > $precioListaRef && $precioListaRef > 0) {
                            $precioListaRef = $precioOldMap;
                        }
                        $precioRef = $precioListaRef;
                        $pctProd = (float) ($productoPrecioMap[$idPortada]['descuento_porcentaje'] ?? 0);
                        if ($pctProd <= 0 && strtolower(trim((string) ($item->tipo_oferta ?? ''))) === 'porcentaje') {
                            $pctProd = (float) ($item->descuento_porcentaje ?? 0);
                        }
                        if ($pctProd > 0 && $precioListaRef > 0) {
                            $precioRef = round($precioListaRef * (1 - ($pctProd / 100)), 2);
                        } else {
                            $precioListaRef = 0;
                        }
                    } else {
                        $precioListaRef = 0;
                    }
                }
                // Sin productos en el submódulo → sin enlace (no usar productos del bloque)
            @endphp
            @if($compraUrl)
            <a href="{{ $compraUrl }}"
               class="promo-card wow fadeInUp"
               data-wow-duration="0.6s"
               data-wow-delay="{{ $cardDelay }}">
                <div class="promo-card__info">
                    <div>
                        <p class="promo-card__category">{{ $item->categoria ?? 'Polos' }}</p>
                        <h3 class="promo-card__name">{!! nl2br(e($item->nombre ?? 'Producto')) !!}</h3>
                        @php
                            $esPctCard = strtolower(trim((string) ($item->tipo_oferta ?? 'pack'))) === 'porcentaje'
                                && (float) ($item->descuento_porcentaje ?? 0) > 0;
                            $pctCard = $esPctCard ? (float) $item->descuento_porcentaje : 0;
                        @endphp
                        @if($esPctCard)
                        <p class="promo-card__offer promo-card__offer--percent">{{ rtrim(rtrim(number_format($pctCard, 2, '.', ''), '0'), '.') }}%<span> DE DESCUENTO</span></p>
                        @else
                        <p class="promo-card__offer">{{ $item->multiplicador ?? '' }}</p>
                        <p class="promo-card__price">
                            {!! $promoPrecio($item->precio ?? 0) !!}
                        </p>
                        @endif
                    </div>
                    <div>
                        <span class="promo-card__btn">Compra Ahora</span>
                        <p class="promo-card__disclaimer">{{ $item->descripcion ?? '' }}</p>
                    </div>
                </div>
                <div class="promo-card__img-wrap">
                    @if($mostrarPrecioPortada && $precioRef && $precioRef > 0)
                    <span class="promo-card__img-price">
                        <small>Precio</small>
                        @if($precioListaRef > $precioRef)
                        <span class="promo-card__img-price-old">S/ {{ number_format($precioListaRef, 2) }}</span>
                        @endif
                        <span class="promo-card__img-price-new">S/ {{ number_format($precioRef, 2) }}</span>
                    </span>
                    @endif
                    <img src="{{ $promoImg($imgCard, 'temp02/img/inicio/categoria/1.jpg') }}"
                         alt="{{ $item->nombre ?? '' }}">
                </div>
            </a>
            @else
            <div class="promo-card promo-card--sin-productos wow fadeInUp"
                 data-wow-duration="0.6s"
                 data-wow-delay="{{ $cardDelay }}"
                 aria-disabled="true"
                 title="Sin productos vinculados">
                <div class="promo-card__info">
                    <div>
                        <p class="promo-card__category">{{ $item->categoria ?? 'Polos' }}</p>
                        <h3 class="promo-card__name">{!! nl2br(e($item->nombre ?? 'Producto')) !!}</h3>
                        @php
                            $esPctCard = strtolower(trim((string) ($item->tipo_oferta ?? 'pack'))) === 'porcentaje'
                                && (float) ($item->descuento_porcentaje ?? 0) > 0;
                            $pctCard = $esPctCard ? (float) $item->descuento_porcentaje : 0;
                        @endphp
                        @if($esPctCard)
                        <p class="promo-card__offer promo-card__offer--percent">{{ rtrim(rtrim(number_format($pctCard, 2, '.', ''), '0'), '.') }}%<span> DE DESCUENTO</span></p>
                        @else
                        <p class="promo-card__offer">{{ $item->multiplicador ?? '' }}</p>
                        <p class="promo-card__price">
                            {!! $promoPrecio($item->precio ?? 0) !!}
                        </p>
                        @endif
                    </div>
                    <div>
                        <span class="promo-card__btn promo-card__btn--sin-productos">Compra Ahora</span>
                        <p class="promo-card__disclaimer">{{ $item->descripcion ?? '' }}</p>
                    </div>
                </div>
                <div class="promo-card__img-wrap">
                    @if($mostrarPrecioPortada && $precioRef && $precioRef > 0)
                    <span class="promo-card__img-price">
                        <small>Precio</small>
                        @if($precioListaRef > $precioRef)
                        <span class="promo-card__img-price-old">S/ {{ number_format($precioListaRef, 2) }}</span>
                        @endif
                        <span class="promo-card__img-price-new">S/ {{ number_format($precioRef, 2) }}</span>
                    </span>
                    @endif
                    <img src="{{ $promoImg($imgCard, 'temp02/img/inicio/categoria/1.jpg') }}"
                         alt="{{ $item->nombre ?? '' }}">
                </div>
            </div>
            @endif
            @endforeach
        </div>

    </div>
</section>
@empty
{{-- Fallback estático --}}
<section class="promo-section">
    <div class="lv-section-title wow fadeInUp" data-wow-duration="0.7s" data-wow-delay="0.1s">
        <span class="lv-subtitle">Ofertas Destacadas</span>
        <h2><span class="lv-accent">Ofertas &</span> Promociones</h2>
    </div>
    <div class="promo-section__row">
        <div class="promo-hero wow fadeInLeft" data-wow-duration="0.75s" data-wow-delay="0.2s">
            <img class="promo-hero__img" src="{{ asset('temp02/img/slider-1.jpg') }}" alt="">
            <div class="promo-hero__overlay">
                <p class="promo-hero__tag">Nueva Colección</p>
                <h2 class="promo-hero__title">Tu imagen<br><span>en</span><br>nuestras manos</h2>
            </div>
        </div>
        <div class="promo-grid">
            <div class="promo-card wow fadeInUp" data-wow-duration="0.6s" data-wow-delay="0.3s">
                <div class="promo-card__info"><div>
                    <p class="promo-card__category">Polos</p>
                    <h3 class="promo-card__name">Básico<br>Clásico</h3>
                    <p class="promo-card__offer">Hasta</p>
                    <p class="promo-card__price"><sup>S/</sup>45<sub>.00</sub></p>
                </div><div>
                    <a href="#" class="promo-card__btn promo-card__btn--sin-productos" role="button" aria-disabled="true">Compra Ahora</a>
                </div></div>
                <div class="promo-card__img-wrap"><img src="{{ asset('temp02/img/inicio/categoria/1.jpg') }}" alt=""></div>
            </div>
        </div>
    </div>
</section>
@endforelse

@once
<script>
(function () {
    if (window.__lvPromoSinProductosBound) return;
    window.__lvPromoSinProductosBound = true;

    document.addEventListener('click', function (e) {
        var card = e.target.closest('.promo-card--sin-productos, .promo-card__btn--sin-productos');
        if (!card) return;
        e.preventDefault();
        e.stopPropagation();

        var msg = 'No hay productos asignados a esta oferta.';
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
