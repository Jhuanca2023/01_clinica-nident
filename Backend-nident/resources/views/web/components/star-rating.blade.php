@php
    // Conversión segura del rating a número
    $ratingValue = isset($rating) ? $rating : 0;
    $interactive = $interactive ?? false;
    $id = $id ?? 'star-rating-' . uniqid();
    $productId = $productId ?? null;
    
    // Limpieza y conversión numérica
    if (is_string($ratingValue)) {
        $ratingValue = str_replace(',', '.', $ratingValue);
        $ratingValue = preg_replace('/[^0-9.]/', '', $ratingValue);
    }
    
    $ratingNumber = (float)$ratingValue;
    $ratingNumber = max(0, min(5, $ratingNumber));
    
    // Cálculo de estrellas para modo estático
    $fullStars = floor($ratingNumber);
    $halfStar = ($ratingNumber - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
@endphp

<div class="product-rating {{ $interactive ? 'interactive-stars' : '' }}" id="{{ $id }}" data-rating="{{ $ratingNumber }}" @if($productId) data-product-id="{{ $productId }}" @endif>
    @if($interactive)
        {{-- Estrellas interactivas: Sistema Ultra-Compatible --}}
        <div class="interactive-rating-wrapper" style="position: relative !important; z-index: 2147483647 !important; display: block !important; padding: 10px; pointer-events: auto !important;">
            <div style="display: flex !important; gap: 5px !important;">
                @for($i = 1; $i <= 5; $i++)
                    <div class="star-click-zone" 
                         onclick="window.handleStarRatingClick({{ $i }}, {{ $productId ? (int)$productId : 'null' }}, '{{ $id }}');"
                         style="cursor: pointer !important; font-size: 40px !important; color: {{ $i <= $ratingNumber ? '#ffa41c' : '#ccc' }} !important; padding: 5px !important; display: inline-block !important; position: relative !important; z-index: 2147483647 !important; pointer-events: auto !important;">
                        ★
                    </div>
                @endfor
            </div>
            <p style="font-size: 12px; color: #666; margin-top: 5px;">Presiona una estrella para calificar</p>
        </div>
    @else
        {{-- Estrellas estáticas --}}
        @for($i = 0; $i < $fullStars; $i++)
            <i class="fa fa-star" style="color:#ffa41c;"></i>
        @endfor

        @if($halfStar)
            <i class="fa fa-star-half-o" style="color:#ffa41c;"></i>
        @endif

        @for($i = 0; $i < $emptyStars; $i++)
            <i class="fa fa-star-o" style="color:#ccc;"></i>
        @endfor
    @endif

    @if(isset($label) || isset($slot))
        <span class="product-rating-label">
            {{ $label ?? '' }}
            {{ $slot }}
        </span>
    @endif
</div>