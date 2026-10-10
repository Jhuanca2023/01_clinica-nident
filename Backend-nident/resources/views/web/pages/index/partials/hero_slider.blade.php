@php
    use Illuminate\Support\Collection;

    $fallbackImages = [
        1 => asset('temp02/assets/img/experience-1.jpg'),
        2 => asset('temp02/assets/img/brings-leads.jpg'),
        3 => asset('temp02/assets/img/experience-2.jpg'),
    ];

    $slides = ($sliderData ?? collect()) instanceof Collection
        ? $sliderData
        : collect($sliderData ?? []);

    $slides = $slides
        ->where('Activo', 'S')
        ->sortBy('id_slider')
        ->values();

    if ($slides->isEmpty()) {
        $slides = collect([
            (object) ['id_slider' => 1, 'subtitulo' => 'Semplicemente Valento', 'titulo' => 'Ropa laboral para tu empresa', 'descripcion' => 'Uniformes, polos y prendas listas para personalizar, con stock permanente.', 'texto_boton' => 'Ver catálogo', 'url_link' => '/tienda', 'url_imagen' => null],
            (object) ['id_slider' => 2, 'subtitulo' => 'Mayorista', 'titulo' => 'Textil promocional con tu logo', 'descripcion' => 'Camisetas, polos y vestuario para equipos, eventos y colectividades.', 'texto_boton' => 'Conocer Valento', 'url_link' => '/nosotros', 'url_imagen' => null],
            (object) ['id_slider' => 3, 'subtitulo' => 'Desde 1994', 'titulo' => 'Stock permanente, despacho ágil', 'descripcion' => 'Abastecemos profesionales y empresas con la mejor relación calidad-precio.', 'texto_boton' => 'Cotizar ahora', 'url_link' => '/contacto', 'url_imagen' => null],
        ]);
    }

    $btnSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3.97 3.97a.75.75 0 0 1 1.06 0l13.72 13.72V8.25a.75.75 0 0 1 1.5 0V19.5a.75.75 0 0 1-.75.75H8.25a.75.75 0 0 1 0-1.5h9.44L3.97 5.03a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path></svg>';

    if (!isset($footerCorp)) {
        $footerCorp = isset($footerData) && count($footerData) > 0 ? $footerData[0] : null;
    }
    if (!isset($urlWhatsapp)) {
        $urlWhatsapp = Helpers::landingWhatsappUrl($footerCorp);
    }
    $waPhone = '51' . ltrim(preg_replace('/\D/', '', $footerCorp->contacto_telefono ?? '964381275'), '0');
    $telNegocio = preg_replace('/\D/', '', $footerCorp->contacto_telefono ?? '964381275');

    $resolveCtaHref = function ($slide) use ($urlWhatsapp) {
        $href = $slide->url_link ?: '#';
        $label = strtolower($slide->texto_boton ?? '');
        if (
            str_contains($label, 'whatsapp')
            || str_contains($href, 'bit.ly')
            || str_contains($href, 'wa.me')
            || str_contains($href, 'whatsapp.com')
        ) {
            return $urlWhatsapp ?: 'https://wa.me/51964381275';
        }
        if ($href !== '#' && ! str_starts_with($href, '#') && ! preg_match('#^(https?:)?//#i', $href)) {
            return url('/' . ltrim($href, '/'));
        }
        return $href;
    };
@endphp

<section class="hero-section hero-fullscreen" id="contact" data-slider-count="{{ $slides->count() }}">
    @foreach ($slides as $index => $slide)
        @php
            $bgUrl = !empty($slide->url_imagen)
                ? asset($slide->url_imagen)
                : ($fallbackImages[$slide->id_slider] ?? asset('temp02/assets/img/experience-1.jpg'));
            $ctaHref = $resolveCtaHref($slide);
            $ctaTarget = (str_starts_with($ctaHref, '#') || str_starts_with($ctaHref, url('/'))) ? '_self' : '_blank';
            $ctaLabel = $slide->texto_boton ?: 'Saber más';
        @endphp
        <div class="slide-bg {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ $bgUrl }}')" data-slide-index="{{ $index }}"></div>
    @endforeach

    <div class="slide-overlay"></div>

    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-slider">
                    @foreach ($slides as $index => $slide)
                        @php
                            $ctaHref = $resolveCtaHref($slide);
                            $ctaTarget = (str_starts_with($ctaHref, '#') || str_starts_with($ctaHref, url('/'))) ? '_self' : '_blank';
                            $ctaLabel = $slide->texto_boton ?: 'Saber más';
                        @endphp
                        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-slide-index="{{ $index }}">
                            <div class="hero-text">
                                @if (!empty($slide->subtitulo))
                                    <span>{{ $slide->subtitulo }}</span>
                                @endif
                                @if (!empty($slide->titulo))
                                    <h1>{{ $slide->titulo }}</h1>
                                @endif
                                @if (!empty($slide->descripcion))
                                    <p>{{ $slide->descripcion }}</p>
                                @endif
                                <a href="{{ $ctaHref }}" target="{{ $ctaTarget }}" class="btn" @if($ctaTarget === '_blank') rel="noopener noreferrer" @endif>
                                    <span>{{ $ctaLabel }}</span>
                                    {!! $btnSvg !!}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="review">
                    <ul>
                        <li><img src="{{ asset('temp02/assets/img/clients-1.jpg') }}" alt="Cliente"></li>
                        <li><img src="{{ asset('temp02/assets/img/clients-2.jpg') }}" alt="Cliente"></li>
                        <li><img src="{{ asset('temp02/assets/img/clients-3.jpg') }}" alt="Cliente"></li>
                        <li><a href="#">+</a></li>
                    </ul>
                    <div>
                        <ul class="star">
                            <li><i class="fa-solid fa-star"></i></li>
                            <li><i class="fa-solid fa-star"></i></li>
                            <li><i class="fa-solid fa-star"></i></li>
                            <li><i class="fa-solid fa-star"></i></li>
                            <li><i class="fa-solid fa-star"></i></li>
                        </ul>
                        <span>94% de clientes satisfechos</span>
                    </div>
                    <img src="{{ asset('temp02/assets/img/arror.png') }}" alt="" class="arror">
                </div>
            </div>

            <div class="col-lg-5 offset-lg-1">
                <form role="form" class="get-a-quote" id="contact-form" method="post" action="#"
                      data-whatsapp-phone="{{ $waPhone }}"
                      data-business-phone="{{ $telNegocio }}">
                    @csrf
                    <img src="{{ asset('temp02/assets/img/lins.png') }}" alt="" class="lins">
                    <img src="{{ asset('temp02/assets/img/fom-img.png') }}" alt="">
                    <h3>Solicita tu Cotización</h3>
                    <h6>Te contactamos en menos de 24 horas</h6>
                    <input type="text" name="nombre" placeholder="Tu nombre completo" required>
                    <input type="email" name="email" placeholder="Tu correo electrónico" required>
                    <input type="number" name="telefono" placeholder="Tu WhatsApp / Celular" required>
                    <p>¿Cómo prefieres que te contactemos?</p>
                    <div class="d-flex align-items-center">
                        <div class="radio-button">
                            <input type="radio" id="whatsapp" name="contacto" value="WhatsApp" checked>
                            <label for="whatsapp">WhatsApp</label>
                        </div>
                        <div class="radio-button">
                            <input type="radio" id="llamada" name="contacto" value="Llamada">
                            <label for="llamada">Llamada</label>
                        </div>
                    </div>
                    <button type="submit" class="btn">
                        <span>Solicitar Informes Ahora</span>
                        {!! $btnSvg !!}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <button type="button" class="slider-btn slider-prev edge-arrow" aria-label="Anterior">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M11.03 3.97a.75.75 0 0 1 0 1.06l-6.22 6.22H21a.75.75 0 0 1 0 1.5H4.81l6.22 6.22a.75.75 0 1 1-1.06 1.06l-7.5-7.5a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    </button>
    <button type="button" class="slider-btn slider-next edge-arrow" aria-label="Siguiente">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
    </button>

    <div class="hero-dots">
        @foreach ($slides as $index => $slide)
            <span class="dot {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}" role="button" tabindex="0" aria-label="Ir al slide {{ $index + 1 }}"></span>
        @endforeach
    </div>
</section>
