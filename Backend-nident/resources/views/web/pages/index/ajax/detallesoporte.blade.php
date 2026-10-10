<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
<style>
/* ========================================
   SECCIÓN DE BENEFICIOS - MINIMARKET
   ======================================== */
.benefits-section {
    background: linear-gradient(135deg, #922b21 0%, #a82024 100%);
    padding: 0;
    position: relative;
    overflow: hidden;
}

/* Patrón decorativo de fondo */
.benefits-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 10% 50%, rgba(255,255,255,0.05) 0%, transparent 50%),
        radial-gradient(circle at 90% 50%, rgba(255,255,255,0.03) 0%, transparent 50%);
    pointer-events: none;
}

/* Iconos decorativos flotantes */
.benefits-bg-icons {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
}

.benefits-bg-icons i {
    position: absolute;
    color: rgba(255, 255, 255, 0.04);
    font-size: 80px;
}

.benefits-bg-icons i:nth-child(1) { top: 10%; left: 5%; transform: rotate(-15deg); }
.benefits-bg-icons i:nth-child(2) { top: 60%; left: 15%; transform: rotate(10deg); font-size: 60px; }
.benefits-bg-icons i:nth-child(3) { top: 20%; right: 10%; transform: rotate(20deg); font-size: 70px; }
.benefits-bg-icons i:nth-child(4) { top: 70%; right: 5%; transform: rotate(-10deg); }

.benefits-container {
    position: relative;
    z-index: 1;
    padding: 30px 0;
}

.benefits-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0;
}

/* Tarjeta de beneficio */
.benefit-card {
     width: 100%;
    min-width: 250px;
    max-width: 300px;
    padding: 28px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    position: relative;
    transition: all 0.3s ease;
}

.benefit-card::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 1px;
    height: 50%;
    background: rgba(255, 255, 255, 0.15);
}

.benefit-card:last-child::after {
    display: none;
}

.benefit-card:hover {
    background: rgba(255, 255, 255, 0.08);
}

/* Icono del beneficio */
.benefit-icon {
    width: 60px;
    height: 60px;
    min-width: 60px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.benefit-icon i {
    font-size: 26px;
    color: #fff;
    transition: transform 0.3s ease;
}

.benefit-card:hover .benefit-icon {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.08);
    border-color: rgba(255, 255, 255, 0.3);
}

.benefit-card:hover .benefit-icon i {
    transform: scale(1.1);
}

/* Contenido del beneficio */
.benefit-content {
    flex: 1;
}

.benefit-title {
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 4px 0;
    line-height: 1.3;
    letter-spacing: 0.3px;
}

.benefit-text {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    line-height: 1.4;
}

/* Badge especial */
.benefit-badge {
    position: absolute;
    top: 8px;
    right: 20px;
    background: #ffc107;
    color: #333;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
/* ========== RESPONSIVE ========== */
@media (max-width: 1199px) {
    .benefit-card {
        min-width: 220px;
        padding: 24px 20px;
    }

    .benefit-icon {
        width: 54px;
        height: 54px;
        min-width: 54px;
    }

    .benefit-icon i {
        font-size: 24px;
    }

    .benefit-title {
        font-size: 16px;
    }
}

@media (max-width: 991px) {
    .benefits-container {
        padding: 20px 0;
    }

    .benefits-row {
        gap: 0;
    }

    .benefit-card {
        flex: 0 0 50%;
        max-width: 50%;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .benefits-section .benefits-row.swiper {
        overflow: hidden !important;
    }

    .benefit-card:nth-child(odd)::after {
        display: block;
    }

    .benefit-card:nth-child(even)::after {
        display: none;
    }

    .benefit-card:nth-last-child(-n+2) {
        border-bottom: none;
    }
}

@media (max-width: 767px) {
    .benefits-container {
        padding: 15px 0;
    }

    .benefits-row {
        padding: 0 10px;
    }
    /* Para activar el modo carrusel en móviles */
    .benefits-row.swiper { 
        display: block !important;
        overflow: hidden; 
    }

    .benefit-card {
        flex: none !important; 
        min-width: 0 !important; 
        max-width: none !important; 
        width: auto !important;
    }
    .benefits-section {
        overflow: hidden; /* Contiene el desbordamiento a nivel de sección */
    }
    .benefits-row .swiper-slide {
        width: 50% !important;
    }
    /* Separador vertical entre columnas izquierda */
    .benefit-card:nth-child(odd)::after {
        display: block !important;
    }

    .benefit-card:nth-child(even)::after {
        display: none !important;
    }

    /* Sin borde inferior en la última fila */
    .benefit-card:nth-last-child(-n+2) {
        border-bottom: none;
    }

    .benefit-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 12px;
    }

    .benefit-icon i {
        font-size: 20px;
    }

    .benefit-title {
        font-size: 13px;
        letter-spacing: 0;
    }

    .benefit-text {
        font-size: 11px;
    }

    .benefit-badge {
        top: 6px;
        right: 8px;
        transform: none;
        font-size: 9px;
        padding: 2px 6px;
    }

    .benefits-bg-icons {
        display: none;
    }
}

@media (max-width: 480px) {
    .benefit-card {
        padding: 14px 12px;
        gap: 10px;
    }

    .benefit-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 10px;
    }

    .benefit-icon i {
        font-size: 18px;
    }

    .benefit-title {
        font-size: 12px;
    }

    .benefit-text {
        font-size: 11px;
    }
}
</style>

<!-- Sección de Beneficios Minimarket -->
<section class="benefits-section">
    <!-- Iconos decorativos de fondo -->
    <div class="benefits-bg-icons">
        <i class="fas fa-shopping-basket"></i>
        <i class="fas fa-truck"></i>
        <i class="fas fa-tags"></i>
        <i class="fas fa-headset"></i>
    </div>

    <div class="container benefits-container">
        <div class="benefits-row swiper"> 
            <div class="swiper-wrapper"> 
                <!-- Envío Gratis --> 
                @foreach($beneficios as $beneficio) 
                <div class="benefit-card swiper-slide">
                    @if($beneficio->badge) 
                        <span class="benefit-badge">{{ $beneficio->badge }}</span>
                    @endif 
                    <div class="benefit-icon"> 
                        <i class="{{ str_replace( ['fa-solid', 'fa-regular', 'fa-brands', 'fa-light'], 
                            ['fas', 'far', 'fab', 'fal'], $beneficio->icono ) }}">
                        </i> 
                    </div> 
                    <div class="benefit-content"> 
                        <h3 class="benefit-title">{{ $beneficio->titulo }}</h3>
                        <p class="benefit-text">{{ $beneficio->descripcion }}</p>
                    </div> 
                </div> 
                @endforeach 
                <!-- Devoluciones -->
            </div> 
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script> 
<script>
    let swiper;

let benefitsSwiper = null;

    function destroyBenefitsSwiper() {
        if (benefitsSwiper) {
            benefitsSwiper.destroy(true, true);
            benefitsSwiper = null;
        }
        const wrapper = document.querySelector('.benefits-row .swiper-wrapper');
        if (wrapper) wrapper.style.cssText = '';
        document.querySelectorAll('.benefits-row .benefit-card').forEach(function(card) {
            card.style.cssText = '';
        });
    }

    function initBenefitsSwiper() {
        destroyBenefitsSwiper();

        if (window.innerWidth < 992) {
            benefitsSwiper = new Swiper('.benefits-row', {
                slidesPerView: 2,
                slidesPerGroup: 1,
                spaceBetween: 0,
                loop: true,
                grabCursor: true,
                speed: 500,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: false,
                },  
            });
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        initBenefitsSwiper();
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initBenefitsSwiper, 250);
        });
    });
</script>