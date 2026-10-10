 
<div class="container popular-cats-section">
    <h2 class="title text-center mb-4 popular-cats-heading">
        Categorías más populares
    </h2> 

    <div class="popular-cats-wrapper" style="text-align: -webkit-center;">
        <div class="tab-content tab-content-carousel">

            @if(!empty($resultadoCategoria) && $resultadoCategoria->count() > 0)

            <div class="tab-pane fade show active p-0" id="products-featured-tab" role="tabpanel">
                
                <div class="owl-carousel owl-simple carousel-with-shadow popular-cats-carousel"
                     data-toggle="owl"
                      data-owl-options='{
                                        "nav": false, 
                                        "dots": true,
                                        "loop": false,
                                        "margin": 24,
                                        "autoplay": true,
                                        "autoplayTimeout": 4000, 
                                        "autoplayHoverPause": true,
                                        "autoplaySpeed": 800,
                                        "rewind": true,
                                        "responsive": {
                                            "0": {
                                                "items":2
                                            },
                                            "480": {
                                                "items":2
                                            },
                                            "768": {
                                                "items":3
                                            },
                                            "992": {
                                                "items":5
                                            },
                                            "1200": {
                                                "items":6,
                                                "nav": true
                                            },
                                            "1600": {
                                                "items":6,
                                                "nav": true
                                            }
                                        }
                                    }'>

                    {{-- No usar $data aquí: contamina el scope del padre (index) y rompe el listado de productos --}}
                    @foreach($resultadoCategoria as $categoriaPopular)
                    <div class="popular-cats-item">
                        <a href="{{ $categoriaPopular->url_direccion ?? 'javascript:void(0);' }}" 
                           class="popular-cats-card">

                            <div class="popular-cats-image-container">
                                <div class="popular-cats-image-wrapper">
                                    <img src="{{ $categoriaPopular->url_imagen }}" 
                                         alt="{{ $categoriaPopular->nombre_categoria }}"
                                         class="popular-cats-img">
                                </div>

                                <!-- Overlay con gradiente -->
                                <div class="popular-cats-overlay"></div>
                                
                                <!-- Ícono decorativo -->
                                <div class="popular-cats-icon">
                                    <i class="icon-arrow-right"></i>
                                </div>
                            </div>

                            <div class="popular-cats-content">
                                <h3 class="popular-cats-title">
                                    {{ $categoriaPopular->nombre_categoria }}
                                </h3>
                            </div>
                        </a>
                    </div>
                    @endforeach

                </div><!-- End .owl-carousel -->
            </div>

            @endif
        </div>
    </div>
</div>

<div class="mb-2">
    <h2 class="title text-center mb-3 popular-cats-heading">
        Mejores ofertas para ti
    </h2> 
</div>

<div class="container banner-section">
    <div class="row g-3 justify-content-center">
        @foreach($resultadoBanner as $banner)
        <div class="{{ $banner->estilo_css }}">
            <div class="banner-card banner-overlay">
                <a href="{{ $banner->url_direccion ?? 'javascript:void(0);' }}" class="banner-image-link">
                    <img src="{{ $banner->url_imagen }}" alt="{{ $banner->titulo_principal }}" class="banner-image">
                    <div class="banner-overlay-gradient"></div>
                </a>

                <div class="banner-content-wrapper">
                    @if($banner->titulo_principal)
                    <div class="banner-tag">{{ $banner->titulo_principal }}</div>
                    @endif
                    
                    <h3 class="banner-title">
                        <a href="{{ $banner->url_direccion ?? 'javascript:void(0);' }}">
                            {!! str_replace("\n", "<br>", e($banner->titulo_secundario)) !!}
                        </a>
                    </h3>

                    @if(isset($banner->texto_descuento))
                    <div class="banner-discount">
                        <span class="discount-badge">{{ $banner->texto_descuento }}</span>
                    </div>
                    @endif

                    @if(isset($banner->precio_desde) && $banner->precio_desde > 0)
                    <div class="banner-price">
                        <span class="price-label">Desde</span>
                        <span class="price-value_category">S/ {{ number_format($banner->precio_desde, 2) }}</span>
                    </div>
                    @endif

                    <a href="{{ $banner->url_direccion ?? 'javascript:void(0);' }}" class="banner-cta">
                        <span>{{ $banner->texto_boton ?? 'Comprar ahora' }}</span>
                        <i class="icon-long-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>