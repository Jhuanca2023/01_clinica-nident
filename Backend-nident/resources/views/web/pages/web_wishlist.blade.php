@extends('web.base_shop')

@section('head_page')
<style>
/* ========================================
   WISHLIST - DISEÑO MINIMARKET PROFESIONAL
   ======================================== */

:root {
    --wl-primary: #1a1a1a;
    --wl-primary-dark: #000000;
    --wl-primary-light: #3a3a3a;
    --wl-accent: #c0392b;
    --wl-success: #28a745;
    --wl-text-dark: #1a1a1a;
    --wl-text-medium: #555555;
    --wl-text-light: #888888;
    --wl-bg-light: #f5f5f5;
    --wl-bg-white: #ffffff;
    --wl-border-color: #e8e8e8;
}

/* === Header de la sección === */
.wishlist-hero {
    background: linear-gradient(135deg, #111 0%, #1a1a1a 60%, #2a2a2a 100%);
    padding: 40px 0;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}

.wishlist-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 50%, rgba(255,255,255,0.05) 0%, transparent 50%);
    pointer-events: none;
}

.wishlist-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.wishlist-hero-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.wishlist-hero-icon {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.15);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
}

.wishlist-hero-icon i {
    font-size: 28px;
    color: white;
}

.wishlist-hero-text h1 {
    font-size: 26px;
    font-weight: 700;
    color: white;
    margin: 0 0 4px 0;
}

.wishlist-hero-text p {
    font-size: 15px;
    color: rgba(255,255,255,0.8);
    margin: 0;
}

.wishlist-counter {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 14px;
    padding: 14px 24px;
    text-align: center;
}

.wishlist-counter .count-number {
    font-size: 28px;
    font-weight: 800;
    color: white;
    display: block;
    line-height: 1;
}

.wishlist-counter .count-label {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    margin-top: 4px;
    display: block;
}

/* === Grid de productos === */
.wishlist-grid {
    padding: 0 0 50px;
}

/* === Tarjeta de producto mejorada === */
.wl-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;

    background: var(--wl-bg-white);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid var(--wl-border-color);
    transition: all 0.3s ease;
    margin-bottom: 24px;
    position: relative;
}

.wl-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.10), 0 4px 12px rgba(0,0,0,0.06);
}
.wl-card-body {
    flex-grow: 1; /* ocupa espacio disponible */
}

.wl-card-image {
    position: relative;
    background: #fafafa;
    overflow: hidden;
}

.wl-card-image a {
    display: block;
}

.wl-card-image img {
    width: 100%;
    height: 220px;
    object-fit: contain;
    padding: 16px;
    transition: transform 0.4s ease;
}

.wl-card:hover .wl-card-image img {
    transform: scale(1.05);
}

/* Botón eliminar favorito */
.wl-remove-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 38px;
    height: 38px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    z-index: 2;
    text-decoration: none;
}

.wl-remove-btn i {
    font-size: 16px;
    color: #c0392b;
    transition: all 0.3s ease;
}

.wl-remove-btn:hover {
    background: var(--wl-primary);
    transform: scale(1.1);
    box-shadow: 0 4px 16px rgba(0,0,0,0.20);
}

.wl-remove-btn:hover i {
    color: white;
}

/* Badge de favorito */
.wl-fav-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #c0392b;
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
    z-index: 2;
}

.wl-fav-badge i {
    font-size: 10px;
}

/* Contenido de la tarjeta */
.wl-card-body {
    padding: 18px 18px 14px;
}

.wl-card-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--wl-text-dark);
    margin: 0 0 10px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.wl-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.wl-card-title a:hover {
    color: var(--wl-primary);
}

.wl-card-price {
    font-size: 22px;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0;
}

/* Footer de la tarjeta */
.wl-card-footer {
    padding: 0 18px 18px;
}

.wl-btn-cart {
    width: 100%;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 700;
    color: white;
    background: #1a1a1a;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.wl-btn-cart::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.wl-btn-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.20);
    color: white;
    text-decoration: none;
}

.wl-btn-cart:hover::before {
    left: 100%;
}

/* === Estado vacío mejorado === */
.empty-wishlist-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 500px;
    padding: 60px 20px;
    text-align: center;
}

.empty-wishlist-icon {
    width: 140px;
    height: 140px;
    background: linear-gradient(135deg, #1a1a1a 0%, #444 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 30px;
    animation: pulse-icon 2s infinite;
}

.empty-wishlist-icon i {
    font-size: 60px;
    color: white;
}

@keyframes pulse-icon {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(0,0,0,0.20);
    }
    50% {
        box-shadow: 0 0 0 20px rgba(0,0,0,0);
    }
}

.empty-wishlist-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--wl-text-dark);
    margin-bottom: 12px;
}

.empty-wishlist-description {
    font-size: 16px;
    color: var(--wl-text-light);
    max-width: 480px;
    margin: 0 auto 32px;
    line-height: 1.6;
}

.btn-explore-products {
    background: #1a1a1a;
    color: white;
    padding: 16px 36px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 16px;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.20);
}

.btn-explore-products:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    color: white;
    text-decoration: none;
}

.wishlist-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    max-width: 800px;
    margin: 50px auto 0;
}

.wishlist-feature {
    background: var(--wl-bg-white);
    padding: 28px 22px;
    border-radius: 16px;
    transition: all 0.3s ease;
    border: 1px solid var(--wl-border-color);
    text-align: center;
}

.wishlist-feature:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    border-color: rgba(0,0,0,0.08);
}

.wishlist-feature-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, rgba(0,0,0,0.06) 0%, rgba(0,0,0,0.04) 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.wishlist-feature-icon i {
    font-size: 24px;
    color: #1a1a1a;
}

.wishlist-feature h6 {
    font-size: 16px;
    font-weight: 700;
    color: var(--wl-text-dark);
    margin-bottom: 8px;
}

.wishlist-feature p {
    font-size: 14px;
    color: var(--wl-text-light);
    margin: 0;
    line-height: 1.5;
}

/*Agregar para la lista de deseo vacio alinear */
.empty-wishlist-container{
    display:flex;
    justify-content:center;
    align-items:center;
    width: 100%;
    min-height:200px;
}
.btn-explore-products{
    background: #1a1a1a;
}
/* === Responsive === */
@media (max-width: 991px) {
    .wl-card-image img {
        height: 180px;
    }
}

@media (max-width: 768px) {
    .wishlist-hero {
        padding: 30px 0;
        padding-top: 70px;
        margin-bottom: 24px;
    }

    .wishlist-hero-text h1 {
        font-size: 22px;
    }

    .wishlist-hero-icon {
        width: 52px;
        height: 52px;
    }

    .wishlist-hero-icon i {
        font-size: 22px;
    }

    .wishlist-counter {
        display: none;
    }

    /* Reducir tamaño de tarjeta para 2 columnas */
    .wl-card {
        margin-bottom: 16px;
        border-radius: 12px;
    }

    .wl-card-image img {
        height: 140px;
        padding: 10px;
    }

    /* Ajustar badge y botón eliminar */
    .wl-fav-badge {
        font-size: 10px;
        padding: 3px 8px;
        top: 8px;
        left: 8px;
    }
    
    .wl-fav-badge span {
        display: none; /* Ocultar texto "Favorito" si es necesario, pero mantengamos el icono */
    }

    .wl-remove-btn {
        width: 30px;
        height: 30px;
        top: 8px;
        right: 8px;
    }

    .wl-remove-btn i {
        font-size: 14px;
    }

    .wl-card-body {
        padding: 12px 10px 8px;
    }

    .wl-card-title {
        font-size: 12.5px;
        margin-bottom: 6px;
        height: 35px; /* Altura fija para alinear productos */
    }

    .wl-card-price {
        font-size: 16px;
        margin-bottom: 4px;
    }

    .wl-card-footer {
        padding: 0 10px 12px;
    }

    .wl-btn-cart {
        padding: 8px 10px;
        font-size: 12px;
        border-radius: 8px;
    }
    
    .wl-btn-cart span {
        display: none; /* En móvil muy pequeño ocultamos texto */
    }
    
    .wl-btn-cart i {
        font-size: 16px;
        margin: 0;
    }
    
    /* Si la pantalla no es tan pequeña, mostramos texto corto */
    @media (min-width: 400px) {
        .wl-btn-cart span {
            display: inline;
            content: 'Añadir';
        }
        .wl-btn-cart span::after {
            content: '';
        }
    }

    .empty-wishlist-icon {
        width: 110px;
        height: 110px;
    }

    .empty-wishlist-icon i {
        font-size: 45px;
    }

    .empty-wishlist-title {
        font-size: 22px;
    }

    .empty-wishlist-description {
        font-size: 14px;
    }

    .wishlist-features {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}
</style>
@endsection


@section('content')
@include('web.partials.liam.header')

@if(!empty($listProductIndex) && is_iterable($listProductIndex) && count($listProductIndex) > 0)
<!-- Hero de la wishlist -->
<div class="wishlist-hero">
    <div class="container">
        <div class="wishlist-hero-content">
            <div class="wishlist-hero-left">
                <div class="wishlist-hero-icon">
                    <i class="fa fa-heart"></i>
                </div>
                <div class="wishlist-hero-text">
                    <h1>Mi Lista de Deseos</h1>
                    <p>Tus productos favoritos guardados en un solo lugar</p>
                </div>
            </div>
            <div class="wishlist-counter">
                <span class="count-number">{{ count($listProductIndex) }}</span>
                <span class="count-label">{{ count($listProductIndex) == 1 ? 'producto' : 'productos' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Grid de productos -->
<div class="container">
    <div class="row wishlist-grid">
        @foreach($listProductIndex as $data)
        @php
            $slugDetalle = !empty($data->slug) ? $data->slug : ($data->id_producto ?? 0);
            $urlDetalle  = url('/web_shopDetail/' . $slugDetalle);
            $tituloProd  = $data->titulo ?? $data->nombre ?? 'Producto';
        @endphp
        <div class="col-6 col-md-4 col-lg-3" id="wl-item-{{$data->id_producto}}">
            <div class="wl-card">
                <div class="wl-card-image">
                    <span class="wl-fav-badge">
                        <i class="fa fa-heart"></i> <span>Favorito</span>
                    </span>

                    @if(Auth::check())
                    <div id="idActualizarDivFavoritoDiv{{$data->id_producto}}"></div>
                    <a href="javascript:void(0)"
                       id="idActualizarDivFavorito{{$data->id_producto}}"
                       type="button"
                       class="wl-remove-btn clsBotonEliminarFavorito clsActionDelete"
                       style="display: flex; align-items: center; justify-content: center;"
                       onclick="clsActionDelete('ESTADO_GALLERY_PRODUCT','{{$data->id_producto}}','{{ Auth::id() }}')"
                       title="Eliminar de favoritos">
                        <i class="fa fa-times" style="margin: 0; line-height: 1;"></i>
                    </a>
                    @else
                    <div id="idActualizarDivFavoritoDiv{{$data->id_producto}}"></div>
                    <a href="javascript:void(0)"
                       id="idActualizarDivFavorito{{$data->id_producto}}"
                       type="button"
                       class="wl-remove-btn clsBotonAgragarFavorito clsActionAdd"
                       style="display: flex; align-items: center; justify-content: center;"
                       onclick="clsActionAdd('ESTADO_GALLERY_PRODUCT','{{$data->id_producto}}')"
                       title="Agregar a favoritos">
                        <i class="fa fa-heart-o" style="margin: 0; line-height: 1;"></i>
                    </a>
                    @endif

                    <a href="{{ $urlDetalle }}">
                        <img src="{{ URL::asset($data->url_imagen) }}" alt="{{ $tituloProd }}" loading="lazy">
                    </a>
                </div>

                <div class="wl-card-body">
                    <h6 class="wl-card-title">
                        <a href="{{ $urlDetalle }}">
                            {{ Str::limit($tituloProd, 45) }}
                        </a>
                    </h6>
                    <p class="wl-card-price">S/ {{ number_format($data->precio, 2) }}</p>
                </div>

                <div class="wl-card-footer">
                    <a href="javascript:void(0)" 
                       class="wl-btn-cart clsBtnAgregarCarritoGlobal" 
                       data-codigo="{{ $data->codigo_producto }}" 
                       data-id="{{ $data->id_producto }}">
                        <i class="fa fa-cart-plus"></i>
                        <span class="d-none d-sm-inline">Añadir al carrito</span>
                        <span class="d-inline d-sm-none">Añadir</span>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@else
<!-- Estado vacío -->
<div class="container">
    <div class="empty-wishlist-container">
        <div class="empty-wishlist-icon">
            <i class="fa fa-heart-o"></i>
        </div>

        <h2 class="empty-wishlist-title">Tu lista de deseos está vacía</h2>

        <p class="empty-wishlist-description">
            Aún no has agregado productos a tu lista de deseos. Explora nuestra tienda y guarda tus productos favoritos para comprarlos más tarde.
        </p>

        <a href="{{ url('/') }}" class="btn-explore-products">
            <i class="fa fa-shopping-bag"></i>
            Explorar productos
        </a>

        <div class="wishlist-features">
            <div class="wishlist-feature">
                <div class="wishlist-feature-icon">
                    <i class="fa fa-heart"></i>
                </div>
                <h6>Guarda tus favoritos</h6>
                <p>Agrega productos que te gusten para encontrarlos fácilmente después</p>
            </div>

            <div class="wishlist-feature">
                <div class="wishlist-feature-icon">
                    <i class="fa fa-bell"></i>
                </div>
                <h6>Recibe notificaciones</h6>
                <p>Te avisaremos cuando haya ofertas en tus productos guardados</p>
            </div>

            <div class="wishlist-feature">
                <div class="wishlist-feature-icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <h6>Compra más rápido</h6>
                <p>Accede rápidamente a tus productos favoritos cuando estés listo</p>
            </div>
        </div>
    </div>
</div>
@endif
@include('web.partials.liam.footer')
@endsection


@section('footer_page')
<script type="text/javascript" src="{{ URL::asset('assets/js/index/index.js') }}"></script>
@endsection
