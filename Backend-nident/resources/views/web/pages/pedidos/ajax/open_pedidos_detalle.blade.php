<div class="product-detail-container">
    @php
        $prod = $obtenerProducto[0] ?? null;
        $imgSrc = $prod && !empty($prod->pro_url_imagen)
            ? (preg_match('#^https?://#i', $prod->pro_url_imagen) ? $prod->pro_url_imagen : asset($prod->pro_url_imagen))
            : asset('temp02/img/product/product-1.jpg');
    @endphp
    @if(!$prod)
        <div class="text-center py-5 text-muted">Producto no disponible.</div>
    @else
    <!-- Product Gallery & Basic Info -->
    <div class="product-gallery-section">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-5 col-md-6">
                <div class="product-image-container">
                    <div class="main-image mb-3">
                        <img src="{{ $imgSrc }}" alt="{{ $prod->pro_nombre }}"
                             class="img-fluid rounded-lg shadow-sm border" id="mainProductImage">
                    </div>
                    <div class="thumbnail-gallery d-flex">
                        <div class="thumbnail-item active mr-2">
                            <img src="{{ $imgSrc }}" alt="Thumbnail"
                                 class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-7 col-md-6">
                <div class="product-info-container p-4 bg-white rounded-lg shadow-sm">
                    <h1 class="product-title ">{{ $prod->pro_nombre }}</h1>
                    @if(!empty($prod->codigo_producto))
                    <p class="mb-2 text-muted">
                        <i class="mdi mdi-barcode mr-1"></i>Código:
                        <strong class="text-dark">{{ $prod->codigo_producto }}</strong>
                    </p>
                    @endif
                    @php 
                        // Según requerimiento: Si 'ratings_enabled' es true (calificación por el usuario habilitada), NO DEBE haber estrellas.
                        // Solo se mostrarán estrellas por defecto cuando sea valoración 100% manual por el administrador.
                        $ratingValue = 0;
                        if(empty($prod->ratings_enabled)) {
                            if(isset($prod->admin_rating) && floatval($prod->admin_rating) > 0) {
                                $ratingValue = (float)$prod->admin_rating;
                            } elseif(isset($prod->numero_estrellas) && floatval($prod->numero_estrellas) > 0) {
                                $ratingValue = (float)$prod->numero_estrellas;
                            }
                        }
                    @endphp
                    
                    @if($ratingValue > 0)
                    <div class="product-rating mb-3">
                        <div class="stars">
                             @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($ratingValue))
                                    <i class="fas fa-star text-warning"></i>
                                @elseif ($i - 0.5 <= $ratingValue)
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                            <span class="ml-2 text-muted">({{ number_format($ratingValue, 1) }})</span>
                        </div>
                    </div>
                    @endif
                    
                    <div class="price-section ">
                        <div class="current-price d-flex align-items-center">
                            <span class="price-amount h3 text-primary font-weight-bold">S/ {{ number_format((float) $prod->pro_precio, 2) }}</span>
                            @if(isset($prod->pro_precio_old) && $prod->pro_precio_old > $prod->pro_precio)
                                @php
                                    $descuento = round((1 - $prod->pro_precio / $prod->pro_precio_old) * 100);
                                @endphp
                                <span class="discount-badge ml-3 badge badge-danger">{{ $descuento }}% OFF</span>
                            @endif
                        </div>
                        @if(isset($prod->pro_precio_old) && $prod->pro_precio_old > $prod->pro_precio)
                        <div class="original-price">
                            <del class="text-muted">S/ {{ number_format((float) $prod->pro_precio_old, 2) }}</del>
                            <span class="text-success ml-2 small">Ahorras S/ {{ number_format((float) ($prod->pro_precio_old - $prod->pro_precio), 2) }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Product Description -->
                    <div class="product-description ">
                        <h5 class="section-title">Descripción</h5>
                        <div class="description-text">
                            {!! !empty($prod->pro_descripcion) ? $prod->pro_descripcion : '<p class="text-muted mb-0">Sin descripción adicional.</p>' !!}
                        </div>
                    </div>
                    
                    <!-- Quantity Selector -->
                    <div class="quantity-selector ">
                        <h5 class="section-title">Cantidad</h5>
                        <div class="input-group quantity-control" style="width: 150px;">
                            <input type="text" class="form-control text-center" value="{{ $prod->pro_cantidad }}"
                                   id="txt_cantidad" name="txt_cantidad" disabled>
                        </div>
                    </div>
                    
                    <!-- Product Policies -->
                    <div class="product-policies">
                        <h5 class="section-title">Políticas del producto</h5>
                        <ul class="policy-list list-unstyled">
                            <li class="">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                <span>Protección del comprador garantizada</span>
                            </li>
                            <li class="">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                <span>Reembolso completo si no recibe su pedido</span>
                            </li>
                            <li class="">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                <span>Devoluciones aceptadas si el producto no es como se describe</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                <span>Envío rápido y seguro</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs Section -->
    <div class="product-tabs-section mt-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <ul class="nav nav-tabs product-detail-tabs" id="productTabs" role="tablist">
                    @foreach($obtenerFichaTecnica as $keys_ => $item_)
                    <li class="nav-item">
                        <a class="nav-link {{$item_->orden ==1 ? 'active':'' }}" 
                           id="tab-{{$item_->orden}}" data-toggle="tab" href="#fichaTecnica{{$item_->orden}}" 
                           role="tab" aria-controls="fichaTecnica{{$item_->orden}}" 
                           aria-selected="{{$item_->orden ==1 ? 'true' : 'false'}}">
                           {{$item_->titulo}}
                        </a>
                    </li>
                    @endforeach
                </ul>
                
                <div class="tab-content p-4" id="productTabsContent">
                    @foreach($obtenerFichaTecnica as $keys_ => $item_)
                    <div class="tab-pane fade {{$item_->orden ==1 ? 'show active':'' }}" 
                         id="fichaTecnica{{$item_->orden}}" role="tabpanel" 
                         aria-labelledby="tab-{{$item_->orden}}">
                        <div class="tab-content-inner">
                            {!! (count($obtenerFichaTecnica)!=0?str_replace("\n", "<br />", $item_->descripcion):'')!!}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @endif

</div>

<style>
    .product-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 15px;
    }
    
    .product-image-container {
        position: relative;
    }
    
    .main-image {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        text-align: center;
        padding: 20px;
    }
    
    .main-image img {
        max-height: 400px;
        width: auto;
        object-fit: contain;
    }
    
    .thumbnail-item {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .thumbnail-item:hover {
        opacity: 0.8;
    }
    
    .thumbnail-item.active {
        border: 2px solid #922b21;
    }
    
    .product-info-container {
        height: 100%;
    }
    
    .product-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #333;
    }
    
    .price-section {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 6px;
    }
    
    .price-amount {
        color: #d32f2f;
    }
    
    .discount-badge {
        font-size: 0.9rem;
        padding: 5px 10px;
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #555;
    }
    
    .description-text {
        line-height: 1.6;
        color: #555;
    }
    
    .quantity-control .btn {
        width: 40px;
    }
    
    .quantity-control input {
        font-weight: bold;
    }
    
    .action-buttons .btn {
        padding: 12px;
        font-size: 1.1rem;
    }
    
    .policy-list i {
        width: 20px;
    }
    
    .product-detail-tabs {
        border-bottom: 1px solid #dee2e6;
    }
    
    .product-detail-tabs .nav-link {
        color: #555;
        font-weight: 500;
        padding: 12px 20px;
        border: none;
    }
    
    .product-detail-tabs .nav-link.active {
        color: #922b21;
        background-color: transparent;
        border-bottom: 3px solid #922b21;
    }
    
    .product-detail-tabs .nav-link:hover:not(.active) {
        color: #922b21;
        border-bottom: 3px solid #f8f9fa;
    }
    
    .tab-content-inner {
        line-height: 1.8;
    }
    
    @media (max-width: 767.98px) {
        .product-title {
            font-size: 1.5rem;
        }
        
        .main-image img {
            max-height: 300px;
        }
        
        .product-detail-tabs .nav-link {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    }
</style>

<script>
    // Funcionalidad para cambiar imágenes al hacer clic en miniaturas
    $(document).ready(function() {
        $('.thumbnail-item').click(function() {
            $('.thumbnail-item').removeClass('active');
            $(this).addClass('active');
            var newSrc = $(this).find('img').attr('src');
            $('#mainProductImage').attr('src', newSrc);
        });
        
        // Control de cantidad
        $('#increment-qty').click(function() {
            var currentVal = parseInt($('#txt_cantidad').val());
            if (currentVal < 1000) {
                $('#txt_cantidad').val(currentVal + 1);
            }
        });
        
        $('#decrement-qty').click(function() {
            var currentVal = parseInt($('#txt_cantidad').val());
            if (currentVal > 1) {
                $('#txt_cantidad').val(currentVal - 1);
            }
        });
    });
</script>