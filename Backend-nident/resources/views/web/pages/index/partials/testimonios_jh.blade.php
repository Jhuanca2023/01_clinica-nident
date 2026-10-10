@php $items = $testimonios ?? collect(); $ref = $items->first(); @endphp
@if($items->isNotEmpty())
<div class="testimonials-area carousel-shadow default-padding bg-dark text-light">
    <div class="container">
        <div class="row">
            <div class="site-heading text-center">
                <div class="col-lg-8 offset-lg-2">
                    <h2>{{ $ref->seccion_titulo ?? 'Confianza y calidad' }}</h2>
                    @if($ref->badge_seccion ?? null)<p>{{ $ref->badge_seccion }}</p>@endif
                </div>
            </div>
        </div>
        <div class="clients-review-carousel owl-carousel owl-theme">
            @foreach($items as $t)
            @php $thumb = Helpers::cmsAssetUrl($t->url_avatar); @endphp
            <div class="item">
                <div class="row align-items-center">
                    <div class="col-lg-5 thumb"><img src="{{ $thumb }}" alt="{{ $t->nombre }}"></div>
                    <div class="col-lg-7 info">
                        <p>{{ $t->testimonio }}</p>
                        <h4>{{ $t->nombre }}</h4>
                        <span>{{ $t->subtitulo }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
