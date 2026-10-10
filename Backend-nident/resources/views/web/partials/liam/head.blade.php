@php
    $cssVer = static fn (string $rel) => file_exists(public_path($rel)) ? filemtime(public_path($rel)) : time();
@endphp
<link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
<link rel="stylesheet" href="{{ asset('temp02/css/wow-fix.css') }}?v={{ $cssVer('temp02/css/wow-fix.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/v4-shims.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="{{ asset('temp02/css/nice-select.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/magnific-popup.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/slicknav.min.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/header-custom.css') }}?v={{ $cssVer('temp02/css/header-custom.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/ticker.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="{{ asset('temp02/css/hero-slider.css') }}?v={{ $cssVer('temp02/css/hero-slider.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/topbar.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/header-sticky.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/promo-section.css') }}?v={{ $cssVer('temp02/css/promo-section.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/products-section.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/categories.css') }}?v={{ $cssVer('temp02/css/categories.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/categories-carousel.css') }}?v={{ $cssVer('temp02/css/categories-carousel.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/lookbook-section.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/footer-luxe.css') }}?v={{ $cssVer('temp02/css/footer-luxe.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/whatsapp-float.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/scrolltop.css') }}">
<link rel="stylesheet" href="{{ asset('temp02/css/cart-modal.css') }}">
{{-- fonts.css al final, igual que en la plantilla temp02 --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ $cssVer('temp02/css/fonts.css') }}">
@stack('head_css')
