@php
    $items = $nosotrosContadores ?? collect();
@endphp
@if($items->isNotEmpty())
<section class="jh-nosotros-stats default-padding mb-5">
    <div class="container">
        <div class="jh-nosotros-stats-grid">
            {{-- Efecto lluvia / meteoros de fondo --}}
            <div class="jh-nosotros-rain" aria-hidden="true">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
            @foreach($items as $c)
            <div class="jh-nosotros-stat-card">
                <div class="jh-nosotros-stat-head">
                    <span class="jh-nosotros-stat-num">{{ $c->valor }}{{ $c->sufijo ?? '' }}</span>
                    <span class="jh-nosotros-stat-label text-white">{{ $c->etiqueta }}</span>
                </div>
                @if($c->descripcion ?? null)
                <p class="jh-nosotros-stat-desc text-white">{{ $c->descripcion }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
