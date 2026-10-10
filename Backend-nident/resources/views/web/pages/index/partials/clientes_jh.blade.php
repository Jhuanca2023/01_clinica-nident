@php
    $sec = $clienteSeccion ?? null;
    $stats = $clienteEstadisticas ?? collect();
    $logos = $clienteLogos ?? collect();
@endphp
@if($sec || $stats->isNotEmpty() || $logos->isNotEmpty())
<section id="clientes" class="jh-clients-area default-padding bottom-less">
    <div class="container">
        @if($sec)
        <div class="row">
            <div class="col-12 text-center jh-clients-header">
                @if($sec->tag ?? null)<span class="jh-clients-tag">{{ $sec->tag }}</span>@endif
                <h2>{{ $sec->titulo }}</h2>
                <p>{{ $sec->descripcion }}</p>
            </div>
        </div>
        @endif
        @if($stats->isNotEmpty())
        <div class="row jh-trust-row">
            @foreach($stats as $st)
            <div class="col-6 col-md-3 jh-trust-item">
                <div class="jh-trust-card">
                    @if($st->icono_clase ?? null)<i class="{{ $st->icono_clase }}"></i>@endif
                    <h3>{{ $st->valor }}</h3>
                    <span>{{ $st->etiqueta }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @if($logos->isNotEmpty())
        <div class="row jh-clients-carousel-row">
            <div class="col-12">
                <div class="clients-items owl-carousel owl-theme text-center jh-clients-carousel">
                    @foreach($logos as $lg)
                    @php $img = Helpers::cmsAssetUrl($lg->url_imagen); @endphp
                    <div class="single-item">
                        <a href="{{ $lg->url_enlace ?? ($urlWhatsapp ?? '#') }}" target="_blank" rel="noopener" title="{{ $lg->nombre ?? 'Cliente' }}">
                            <img src="{{ $img }}" alt="{{ $lg->nombre ?? 'Cliente' }}">
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if($sec && ($sec->cta_texto ?? null))
        <div class="row">
            <div class="col-12 text-center jh-clients-cta">
                <p>{{ $sec->cta_texto }}</p>
                @php $ctaWa = Helpers::landingWhatsappUrl($footerCorp ?? null); @endphp
                <a href="{{ $ctaWa }}" target="_blank" rel="noopener" class="jh-clients-btn"><i class="fab fa-whatsapp"></i> Contáctanos por WhatsApp</a>
            </div>
        </div>
        @endif
    </div>
</section>
@endif
