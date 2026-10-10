<div class="lv-cart-overlay" id="lv-cart-overlay"></div>
<div class="lv-cart-sidebar" id="lv-cart-sidebar">
    <div class="lv-cart-sidebar__head">
        <i class="fa fa-shopping-bag"></i>
        <h3>Carrito</h3>
        <button type="button" class="lv-cart-sidebar__close" id="lv-cart-close">&times;</button>
    </div>
    <div class="lv-cart-sidebar__body" id="lv-cart-body">
        <div class="lv-cart-empty">
            <i class="fa fa-shopping-bag"></i>
            <span>Tu carrito está vacío</span>
        </div>
    </div>
    <div class="lv-cart-sidebar__foot" id="lv-cart-foot" style="display:none;">
        <div class="lv-cart-subtotal-row">
            <span class="lv-subtotal-label">Subtotal</span>
            <span class="lv-subtotal-value" id="lv-cart-total-val">S/ 0.00</span>
        </div>
        <a href="{{ url('/shop-detail-lista') }}" class="lv-cart-btn-primary">Ir Al Carrito</a>
    </div>
</div>
