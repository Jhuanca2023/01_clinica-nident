<!-- Cabecera de página con imagen de fondo - Diseño Minimarket -->
@php
    $sliderData = Helpers::slider_();
    $headerLink = $sliderData[0]->url_link ?? '';
    $headerImage = $sliderData[0]->url_imagen ?? null;

// Imagen por defecto de minimarket/supermercado (frutas y verduras frescas)
//$defaultImage = 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1920&q=80';
//$backgroundImage = $headerImage ? URL::asset($headerImage) : $defaultImage;

$backgroundImage = $headerImage ? URL::asset($headerImage) : null;
@endphp

<style>
    /* ========== BREADCRUMB MINIMARKET STYLES ========== */
    .minimarket-breadcrumb-section {
        position: relative;
        background: linear-gradient(135deg, #a82024 0%, #922b21 50%, #e04347 100%);
        overflow: hidden;
        min-height: 180px;
    }

    .minimarket-breadcrumb-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('{{ $backgroundImage }}') center center / cover no-repeat;
        opacity: 0.35;
        transition: transform 0.5s ease, opacity 0.3s ease;
    }

    .minimarket-breadcrumb-section:hover::before {
        transform: scale(1.05);
        opacity: 0.45;
    }

    .minimarket-breadcrumb-section::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg,
                rgba(168, 32, 36, 0.75) 0%,
                rgba(204, 41, 46, 0.65) 50%,
                rgba(168, 32, 36, 0.8) 100%);
    }

    .breadcrumb-content-wrapper {
        position: relative;
        z-index: 2;
        padding: 40px 0 30px;
    }

    /* Icono decorativo */
    .breadcrumb-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        margin-right: 15px;
        margin-bottom: 15px;
        vertical-align: middle;
        backdrop-filter: blur(5px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        animation: pulse-icon 2s ease-in-out infinite;
    }

    .breadcrumb-icon i {
        font-size: 24px;
        color: #fff;
    }

    @keyframes pulse-icon {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    /* Subtítulo */
    .breadcrumb-subtitle {
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
        color: #ffd700;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 15px;
        vertical-align: middle;
        padding: 6px 16px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        backdrop-filter: blur(5px);
    }

    /* Etiqueta naranja "PRODUCTO" en detalle (como en referencia) */
    .breadcrumb-subtitle-producto {
        color: #fff;
        background: rgba(230, 120, 30, 0.95);
        border-radius: 6px;
        letter-spacing: 2px;
    }

    /* Título principal */
    .breadcrumb-main-title {
        font-size: 36px;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        line-height: 1.3;
    }

    .breadcrumb-main-title span {
        display: block;
        font-weight: 400;
        font-size: 18px;
        color: rgba(255, 255, 255, 0.9);
        margin-top: 8px;
    }

    /* Indicador de link clickeable */
    .header-link-indicator {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 3;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 193, 7, 0.9);
        color: #333;
        font-size: 12px;
        font-weight: 600;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .header-link-indicator:hover {
        background: #ffc107;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    .header-link-indicator i {
        font-size: 14px;
    }

    /* Elementos decorativos flotantes */
    .floating-elements {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
        overflow: hidden;
        pointer-events: none;
    }

    .floating-element {
        position: absolute;
        opacity: 0.12;
        color: #fff;
        font-size: 40px;
    }

    /* Animaciones individuales más dinámicas */
    .floating-element:nth-child(1) {
        top: 15%;
        left: 8%;
        font-size: 45px;
        animation: float1 4s ease-in-out infinite;
    }

    .floating-element:nth-child(2) {
        top: 55%;
        left: 20%;
        font-size: 32px;
        animation: float2 5s ease-in-out infinite;
    }

    .floating-element:nth-child(3) {
        top: 25%;
        right: 12%;
        font-size: 38px;
        animation: float3 4.5s ease-in-out infinite;
    }

    .floating-element:nth-child(4) {
        top: 65%;
        right: 18%;
        font-size: 42px;
        animation: float4 5.5s ease-in-out infinite;
    }

    .floating-element:nth-child(5) {
        top: 8%;
        left: 45%;
        font-size: 28px;
        animation: float5 4s ease-in-out infinite;
    }

    .floating-element:nth-child(6) {
        top: 75%;
        left: 55%;
        font-size: 35px;
        animation: float6 5s ease-in-out infinite;
    }

    .floating-element:nth-child(7) {
        top: 40%;
        left: 75%;
        font-size: 30px;
        animation: float7 4.2s ease-in-out infinite;
    }

    .floating-element:nth-child(8) {
        top: 85%;
        left: 5%;
        font-size: 26px;
        animation: float8 5.2s ease-in-out infinite;
    }

    /* Animación 1: Diagonal arriba-derecha */
    @keyframes float1 {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg) scale(1);
        }

        25% {
            transform: translate(15px, -20px) rotate(10deg) scale(1.1);
        }

        50% {
            transform: translate(25px, -8px) rotate(-5deg) scale(1);
        }

        75% {
            transform: translate(10px, -25px) rotate(15deg) scale(1.05);
        }
    }

    /* Animación 2: Movimiento circular */
    @keyframes float2 {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        25% {
            transform: translate(20px, -15px) rotate(-15deg);
        }

        50% {
            transform: translate(30px, 10px) rotate(10deg);
        }

        75% {
            transform: translate(10px, 20px) rotate(-10deg);
        }
    }

    /* Animación 3: Rebote suave */
    @keyframes float3 {

        0%,
        100% {
            transform: translateY(0) rotate(0deg) scale(1);
        }

        33% {
            transform: translateY(-30px) rotate(20deg) scale(1.15);
        }

        66% {
            transform: translateY(-15px) rotate(-10deg) scale(0.95);
        }
    }

    /* Animación 4: Zigzag */
    @keyframes float4 {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        20% {
            transform: translate(-15px, -20px) rotate(-12deg);
        }

        40% {
            transform: translate(10px, -30px) rotate(8deg);
        }

        60% {
            transform: translate(-10px, -15px) rotate(-8deg);
        }

        80% {
            transform: translate(15px, -25px) rotate(12deg);
        }
    }

    /* Animación 5: Pulso con movimiento */
    @keyframes float5 {

        0%,
        100% {
            transform: translate(0, 0) scale(1) rotate(0deg);
        }

        50% {
            transform: translate(20px, -20px) scale(1.2) rotate(180deg);
        }
    }

    /* Animación 6: Ondulación */
    @keyframes float6 {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        25% {
            transform: translate(-20px, -10px) rotate(-20deg);
        }

        50% {
            transform: translate(0, -25px) rotate(0deg);
        }

        75% {
            transform: translate(20px, -10px) rotate(20deg);
        }
    }

    /* Animación 7: Espiral suave */
    @keyframes float7 {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg) scale(1);
        }

        25% {
            transform: translate(10px, -15px) rotate(90deg) scale(1.1);
        }

        50% {
            transform: translate(0, -30px) rotate(180deg) scale(1);
        }

        75% {
            transform: translate(-10px, -15px) rotate(270deg) scale(1.1);
        }
    }

    /* Animación 8: Flotación lenta */
    @keyframes float8 {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        33% {
            transform: translate(25px, -20px) rotate(15deg);
        }

        66% {
            transform: translate(-10px, -35px) rotate(-15deg);
        }
    }

    /* ========== NAVEGACIÓN BREADCRUMB ========== */
    .minimarket-breadcrumb-nav {
        background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);
        border-bottom: 3px solid #922b21;
        padding: 12px 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .minimarket-breadcrumb-nav .breadcrumb {
        margin: 0;
        padding: 0;
        background: transparent;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .minimarket-breadcrumb-nav .breadcrumb-item {
        display: flex;
        align-items: center;
        font-size: 15px;
    }

    .minimarket-breadcrumb-nav .breadcrumb-item+.breadcrumb-item::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #922b21;
        border-radius: 50%;
        margin: 0 12px;
    }

    .minimarket-breadcrumb-nav .breadcrumb-item a {
        color: #922b21;
        text-decoration: none;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .minimarket-breadcrumb-nav .breadcrumb-item a:hover {
        background: #922b21;
        color: #fff;
    }

    .minimarket-breadcrumb-nav .breadcrumb-item a i {
        font-size: 13px;
    }

    .minimarket-breadcrumb-nav .breadcrumb-item.active {
        color: #555;
        font-weight: 600;
        padding: 5px 12px;
        background: rgba(204, 41, 46, 0.1);
        border-radius: 20px;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 991px) {
        .minimarket-breadcrumb-section {
            min-height: 160px;
        }

        .breadcrumb-content-wrapper {
            padding: 30px 0 25px;
        }

        .breadcrumb-main-title {
            font-size: 30px;
        }

        .breadcrumb-main-title span {
            font-size: 16px;
        }

        .breadcrumb-icon {
            width: 55px;
            height: 55px;
        }

        .breadcrumb-icon i {
            font-size: 22px;
        }
    }

    @media (max-width: 767px) {
        .minimarket-breadcrumb-section {
            min-height: 140px;
        }

        .breadcrumb-content-wrapper {
            padding: 25px 15px 20px;
        }

        .breadcrumb-main-title {
            font-size: 26px;
        }

        .breadcrumb-main-title span {
            font-size: 15px;
        }

        .breadcrumb-subtitle {
            font-size: 12px;
            letter-spacing: 2px;
            padding: 5px 12px;
        }

        .breadcrumb-icon {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .breadcrumb-icon i {
            font-size: 20px;
        }

        .header-link-indicator {
            padding: 6px 12px;
            font-size: 11px;
            top: 10px;
            right: 10px;
        }

        .floating-elements {
            opacity: 0.5;
        }

        .floating-element {
            font-size: 20px !important;
        }

        .minimarket-breadcrumb-nav {
            padding: 10px 0;
        }

        .minimarket-breadcrumb-nav .breadcrumb-item {
            font-size: 14px;
        }

        .minimarket-breadcrumb-nav .breadcrumb-item+.breadcrumb-item::before {
            margin: 0 8px;
            width: 5px;
            height: 5px;
        }
    }

    @media (max-width: 480px) {
        .minimarket-breadcrumb-section {
            min-height: 120px;
        }

        .breadcrumb-main-title {
            font-size: 22px;
        }

        .breadcrumb-main-title span {
            font-size: 13px;
        }

        .breadcrumb-subtitle {
            font-size: 11px;
            letter-spacing: 1px;
        }

        .breadcrumb-icon {
            width: 45px;
            height: 45px;
        }

        .breadcrumb-icon i {
            font-size: 18px;
        }

        .minimarket-breadcrumb-nav .breadcrumb {
            gap: 3px;
        }

        .minimarket-breadcrumb-nav .breadcrumb-item {
            font-size: 13px;
        }

        .minimarket-breadcrumb-nav .breadcrumb-item a,
        .minimarket-breadcrumb-nav .breadcrumb-item.active {
            padding: 4px 10px;
        }
    }

    /* Cursor clickeable cuando hay link */
    .clickable-header {
        cursor: pointer;
    }
</style>

<!-- Sección principal del breadcrumb -->
<section class="minimarket-breadcrumb-section {{ !empty($headerLink) ? 'clickable-header' : '' }}"
    data-header-link="{{ $headerLink }}">

    <!-- Elementos decorativos flotantes -->
    <div class="floating-elements">
        <i class="floating-element fas fa-shopping-basket"></i>
        <i class="floating-element fas fa-apple-alt"></i>
        <i class="floating-element fas fa-carrot"></i>
        <i class="floating-element fas fa-shopping-cart"></i>
        <i class="floating-element fas fa-lemon"></i>
        <i class="floating-element fas fa-pepper-hot"></i>
        <i class="floating-element fas fa-leaf"></i>
        <i class="floating-element fas fa-seedling"></i>
    </div>

    @if(!empty($headerLink))
        <div class="header-link-indicator" onclick="window.open('{{ $headerLink }}', '_blank')">
            <i class="fas fa-external-link-alt"></i>
            <span>Ver promoción</span>
        </div>
    @endif

    <div class="container">
        <div class="breadcrumb-content-wrapper text-center">

            @if(request()->is('web_about') || request()->is('about'))
                <div class="breadcrumb-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="breadcrumb-subtitle">{{ __('Conócenos') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('¿Quiénes Somos?') }}
                    <span>Tu minimarket de confianza</span>
                </h1>

            @elseif(request()->is('web_gallery*') || request()->is('gallery*'))
                <div class="breadcrumb-icon">
                    <i class="fas fa-images"></i>
                </div>
                <div class="breadcrumb-subtitle">{{ __('Galería') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('Nuestros Productos') }}
                    <span>Calidad y frescura garantizada</span>
                </h1>

            @elseif(request()->is('web_contact') || request()->is('contact'))
                <div class="breadcrumb-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="breadcrumb-subtitle">{{ __('Contáctenos') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('Atención al Cliente') }}
                    <span>Estamos para ayudarte</span>
                </h1>

            @elseif(request()->is('web_checkout/index'))
                <div class="breadcrumb-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="breadcrumb-subtitle">{{ __('Proceso de Compra') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('Pago Seguro') }}
                    <span>Compra sin preocupaciones</span>
                </h1>

            @elseif(request()->is('web_shopDetail_lista') || request()->is('shop-detail-lista'))
                <div class="breadcrumb-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <div class="breadcrumb-subtitle">{{ __('Catálogo') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('Listado de Productos') }}
                    <span>Todo lo que necesitas en un solo lugar</span>
                </h1>

            @elseif(request()->is('web_shopDetail/*'))
                <div class="breadcrumb-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="breadcrumb-subtitle breadcrumb-subtitle-producto">{{ __('PRODUCTO') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('Detalle del Producto') }}
                    <span>Información completa</span>
                </h1>

            @else
                <div class="breadcrumb-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="breadcrumb-subtitle">{{ __('Minimarket') }}</div>
                <h1 class="breadcrumb-main-title">
                    {{ __('Bienvenido') }}
                    <span>Los mejores productos al mejor precio</span>
                </h1>
            @endif

        </div>
    </div>
</section>

<!-- Navegación breadcrumb -->
<nav aria-label="breadcrumb" class="minimarket-breadcrumb-nav">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/"><i class="fas fa-home"></i> Inicio</a>
            </li>

            @if(request()->is('web_about') || request()->is('about'))
                <li class="breadcrumb-item active" aria-current="page">{{ __('Nosotros') }}</li>

            @elseif(request()->is('web_gallery*') || request()->is('gallery*'))
                <li class="breadcrumb-item active" aria-current="page">{{ __('Productos') }}</li>

            @elseif(request()->is('web_contact') || request()->is('contact'))
                <li class="breadcrumb-item active" aria-current="page">{{ __('Contáctenos') }}</li>

            @elseif(request()->is('web_checkout/index'))
                @if(session('id_producto_temp'))
                    <li class="breadcrumb-item"><a
                            href="/web_shopDetail/{{ session('id_producto_temp') }}">{{ __('Detalle') }}</a></li>
                    <li class="breadcrumb-item"><a href="/web_shopDetail_lista">{{ __('Productos') }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ __('Comprando') }}</li>

            @elseif(request()->is('web_shopDetail/*'))
                @if(session('id_producto_temp'))
                    <li class="breadcrumb-item"><a href="/web_shopDetail_lista">{{ __('Productos') }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ __('Detalle') }}</li>

            @elseif(request()->is('web_shopDetail_lista'))
                @if(session('id_producto_temp'))
                    <li class="breadcrumb-item"><a
                            href="/web_shopDetail/{{ session('id_producto_temp') }}">{{ __('Detalle') }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ __('Productos') }}</li>
            @endif
        </ol>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const header = document.querySelector('.minimarket-breadcrumb-section.clickable-header');

        if (header) {
            const link = header.getAttribute('data-header-link');

            if (link && link !== '') {
                header.addEventListener('click', function (e) {
                    // Evitar abrir si se hizo clic en el indicador
                    if (!e.target.closest('.header-link-indicator')) {
                        window.open(link, '_blank');
                    }
                });
            }
        }
    });
</script>