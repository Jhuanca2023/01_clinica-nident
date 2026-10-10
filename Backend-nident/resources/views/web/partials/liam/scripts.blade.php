@php
    $jsVer = static fn (string $rel) => file_exists(public_path($rel)) ? filemtime(public_path($rel)) : time();
@endphp
<script src="{{ asset('temp02/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('temp02/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('temp02/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('temp02/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('temp02/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('temp02/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('temp02/js/mixitup.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
window.LIAM_SHOP = {
    csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    baseUrl: @json(url('/')),
    userId: @json(Auth::check() ? Auth::id() : null),
    routes: {
        agregar:      @json(url('/web_shopDetail_agregar')),
        eliminar:     @json(url('/web_shopDetail_eliminar')),
        actualizar:   @json(url('/web_shopDetail_actualizar')),
        totalCarrito: @json(url('/web_shopDetail_total_carrito')),
        cartItems:    @json(url('/web_cart_items')),
        tienda:       @json(url('/tienda')),
        carrito:      @json(url('/shop-detail-lista')),
        checkout:     @json(url('/web_checkout/index')),
        wishlistAdd:  @json(url('/web_wishlist_agregar')),
        wishlistDel:  @json(url('/web_wishlist_eliminar')),
    }
};
</script>
<script src="{{ asset('temp02/js/main.js') }}?v={{ $jsVer('temp02/js/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="{{ asset('temp02/js/hero-slider.js') }}?v={{ $jsVer('temp02/js/hero-slider.js') }}"></script>
<script src="{{ asset('temp02/js/categories-carousel.js') }}?v={{ $jsVer('temp02/js/categories-carousel.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script src="{{ asset('temp02/js/liam-wow.js') }}?v={{ $jsVer('temp02/js/liam-wow.js') }}"></script>
<script src="{{ asset('temp02/js/liam-wishlist.js') }}?v={{ $jsVer('temp02/js/liam-wishlist.js') }}"></script>
<script src="{{ asset('temp02/js/liam-cart.js') }}?v={{ $jsVer('temp02/js/liam-cart.js') }}"></script>
<script src="{{ asset('temp02/js/lv-cart-sidebar.js') }}?v={{ $jsVer('temp02/js/lv-cart-sidebar.js') }}"></script>
<script src="{{ asset('temp02/js/liam-whatsapp.js') }}?v={{ $jsVer('temp02/js/liam-whatsapp.js') }}"></script>
<script src="{{ asset('temp02/js/liam-header-sticky.js') }}?v={{ $jsVer('temp02/js/liam-header-sticky.js') }}"></script>
<script src="{{ asset('temp02/js/vl-search.js') }}?v={{ $jsVer('temp02/js/vl-search.js') }}" defer></script>
@include('web.partials.components.pls_live_search')
