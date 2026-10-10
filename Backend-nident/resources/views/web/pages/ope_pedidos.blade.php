@extends('web.base_shop')

@section('title', 'Mi Cuenta | Valento')

@section('head_page')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection


@section('content')
@include('web.partials.liam.header')
<style>
    /* ============================================
   SIDEBAR NAVIGATION
   ============================================ */
    .sidebar-navigation {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 15px 0;
        position: sticky;
        top: 20px;
    }

    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-menu-item {
        margin: 0;
        padding: 0;
    }

    .nav-menu-link {
        display: flex;
        align-items: center;
        padding: 14px 20px;
        color: #5a5a5a;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        cursor: pointer;
    }

    .nav-menu-link:hover {
        background: #f8f9fa;
        color: #922b21;
        text-decoration: none;
        border-left-color: #922b21;
    }

    .nav-menu-item.active .nav-menu-link {
        background: linear-gradient(90deg, #e8f2ff 0%, #fff 100%);
        color: #922b21;
        border-left-color: #922b21;
        font-weight: 500;
    }

    .nav-menu-link i {
        font-size: 18px;
        width: 24px;
        margin-right: 12px;
        text-align: center;
    }

    .nav-menu-link span {
        font-size: 14px;
    }

    /* ============================================
   ORDERS CONTAINER
   ============================================ */
    .orders-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .profile-header-card {
    background: #1e3a5f;
    color: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    /* Sombra oscura neutral y elegante */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.profile-header-card h4 {
    margin-bottom: 5px;
    color: #fff;
}

.profile-header-card p {
    color: rgba(255, 255, 255, 0.8);
}

    /* ============================================
   ORDER CARDS
   ============================================ */
    .order-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .order-header {
        padding: 15px 20px;
        background: #fff;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }

    .order-header:hover {
        background: #f8f9fa;
    }

    .order-number {
        font-size: 14px;
        margin-bottom: 5px;
        display: flex;
        gap: 6px;
    }

    .order-date {
        font-size: 12px;
        color: #6c757d;
    }

    .order-summary-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-total-amount {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
    }

    .toggle-icon {
        font-size: 1.5rem;
        transition: transform 0.3s ease;
        color: #667eea;
    }

    .toggle-icon.rotated {
        transform: rotate(180deg);
    }

    /* ============================================
   PRODUCT ITEMS
   ============================================ */
    .product-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }

    .product-item:hover {
        background: #f8f9fa;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-image {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 10px;
        background: #fff;
        text-align: center;
    }

    .product-image img {
        max-height: 80px;
        width: auto;
    }

    .product-name {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .product-details {
        font-size: 14px;
        color: #6c757d;
    }

    /* ============================================
   TRACKING SECTION
   ============================================ */
    .tracking-section {
        padding: 20px;
        background: #fff;
        border-top: 2px solid #e9ecef;
    }

    .tracking-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
    }

    .tracking-timeline {
        position: relative;
        padding-left: 30px;
    }

    .tracking-step {
        position: relative;
        padding-bottom: 30px;
    }

    .tracking-step:last-child {
        padding-bottom: 0;
    }

    .tracking-step::before {
        content: '';
        position: absolute;
        left: -23px;
        top: 8px;
        bottom: -8px;
        width: 2px;
        background: #e0e0e0;
    }

    .tracking-step:last-child::before {
        display: none;
    }

    .tracking-icon {
        position: absolute;
        left: -30px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #e0e0e0;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e0e0;
    }

    .tracking-step.active .tracking-icon {
        background: #28a745;
        box-shadow: 0 0 0 2px #28a745;
    }

    .tracking-step.current .tracking-icon {
        background: #922b21;
        box-shadow: 0 0 0 2px #922b21;
        width: 20px;
        height: 20px;
        left: -32px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 0 2px #922b21, 0 0 0 4px rgba(253, 5, 5, 0.4);
        }

        50% {
            box-shadow: 0 0 0 2px #922b21, 0 0 0 10px rgba(253, 5, 5, 0.15);
        }
    }

    .tracking-content h6 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .tracking-content p {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 3px;
    }

    .tracking-content small {
        font-size: 12px;
        color: #999;
    }

    /* ============================================
   PAYMENT STATUS
   ============================================ */
    .payment-status-section {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 2px solid #e9ecef;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: 500;
        font-size: 12px;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-review {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .btn-upload-proof {
        background: #922b21;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-upload-proof:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(253, 5, 5, 0.3);
        color: #fff;
    }

    /* ============================================
   COMPROBANTE INLINE BADGE
   ============================================ */
    .comprobante-inline {
        display: inline-flex;
        align-items: center;
    }

    .comprobante-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
    }

    .comprobante-badge:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .comprobante-badge i {
        font-size: 18px;
    }

    .comprobante-badge span {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .gap-3 {
        gap: 15px;
    }

    /* ============================================
   MODAL — fix z-index (header=9000, topbar=10000)
   ============================================ */
    .modal { z-index: 10500 !important; }
    .modal-backdrop { z-index: 10400 !important; }
    .swal2-container { z-index: 100050 !important; }
    .swal2-container { z-index: 20000 !important; }

    #uploadPaymentModal .modal-content {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border-radius: 8px;
    }

    #uploadPaymentModal .custom-file-label::after {
        content: "Buscar";
        border-radius: 0 6px 6px 0;
        background-color: #6c757d;
        color: #fff;
    }

    /* ============================================
   SECCIÓN DIRECCIONES — diseño profesional
   ============================================ */
    .dir-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 22px 28px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        border-radius: 12px 12px 0 0;
    }
    .dir-header__title {
        font-family: 'Montserrat', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        letter-spacing: 0.3px;
    }
    .dir-header__title i { color: #c0392b; font-size: 16px; }

    .dir-btn-add {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        background: #c0392b;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: background .2s, box-shadow .2s, transform .15s;
        text-transform: uppercase;
        box-shadow: 0 4px 12px rgba(192,57,43,.25);
    }
    .dir-btn-add:hover {
        background: #a93226;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(192,57,43,.35);
        color: #fff;
    }

    /* Grid de tarjetas */
    #direcciones-lista { gap: 0; }

    .dir-card {
        background: #fff;
        border: 1.5px solid #ebebeb;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        transition: border-color .25s, box-shadow .25s, transform .2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .dir-card:hover {
        border-color: #c0392b;
        box-shadow: 0 8px 28px rgba(192,57,43,.12);
        transform: translateY(-2px);
    }
    .dir-card--principal {
        border-color: #c0392b;
        box-shadow: 0 4px 20px rgba(192,57,43,.12);
    }

    /* Franja superior coloreada */
    .dir-card__stripe {
        height: 4px;
        background: #ebebeb;
    }
    .dir-card--principal .dir-card__stripe {
        background: linear-gradient(90deg, #c0392b, #e74c3c);
    }
    .dir-card:hover .dir-card__stripe {
        background: linear-gradient(90deg, #c0392b, #e74c3c);
    }

    .dir-card__body {
        padding: 18px 20px 14px;
        flex: 1;
    }

    .dir-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .dir-badge-principal {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        background: #c0392b;
        color: #fff;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .dir-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(192,57,43,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .dir-icon-wrap i { color: #c0392b; font-size: 18px; }
    .dir-card--principal .dir-icon-wrap { background: rgba(192,57,43,.14); }

    .dir-alias {
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 800;
        color: #1a1a1a;
        margin-bottom: 2px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .dir-info-row {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #666;
        margin-bottom: 4px;
        font-family: 'Montserrat', sans-serif;
    }
    .dir-info-row i {
        color: #bbb;
        font-size: 11px;
        width: 14px;
        text-align: center;
        flex-shrink: 0;
    }
    .dir-info-row--name { color: #333; font-weight: 600; font-size: 13px; }
    .dir-info-row--addr { color: #555; font-size: 12px; }
    .dir-info-row--ref { color: #999; font-style: italic; }

    .dir-divider {
        height: 1px;
        background: #f3f3f3;
        margin: 12px 0;
    }

    .dir-actions {
        display: flex;
        gap: 6px;
        padding: 12px 20px;
        background: #fafafa;
        border-top: 1px solid #f3f3f3;
    }
    .dir-btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 13px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
        border: 1.5px solid;
        cursor: pointer;
        transition: all .15s;
        letter-spacing: 0.3px;
        line-height: 1;
    }
    .dir-btn-edit {
        border-color: #e0e0e0;
        color: #555;
        background: #fff;
    }
    .dir-btn-edit:hover { background: #f0f0f0; border-color: #ccc; }
    .dir-btn-delete {
        border-color: rgba(192,57,43,.3);
        color: #c0392b;
        background: rgba(192,57,43,.04);
    }
    .dir-btn-delete:hover { background: rgba(192,57,43,.12); border-color: #c0392b; }
    .dir-btn-principal {
        border-color: rgba(192,57,43,.3);
        color: #c0392b;
        background: transparent;
    }
    .dir-btn-principal:hover { background: rgba(192,57,43,.08); border-color: #c0392b; }

    /* ============================================
   MODAL DIRECCIÓN — diseño mejorado
   ============================================ */
    #modalDireccion .modal-content {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
    }
    #modalDireccion .modal-header {
        background: #1a1a1a !important;
        padding: 18px 24px;
        border-bottom: none;
    }
    #modalDireccion .modal-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #fff !important;
    }
    #modalDireccion .modal-body {
        padding: 24px 28px;
        background: #f9f9f9;
    }
    #modalDireccion .form-group label,
    #modalDireccion label {
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        font-weight: 700;
        color: #c0392b;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 5px;
    }
    #modalDireccion .form-control {
        border: 1.5px solid #e0e0e0;
        border-radius: 7px;
        padding: 9px 12px;
        font-size: 13px;
        font-family: 'Montserrat', sans-serif;
        color: #333;
        background: #fff;
        transition: border-color .2s;
    }
    #modalDireccion .form-control:focus {
        border-color: #c0392b;
        box-shadow: 0 0 0 3px rgba(192,57,43,.1);
        outline: none;
    }
    #modalDireccion .modal-footer {
        background: #fff !important;
        border-top: 1px solid #f0f0f0;
        padding: 14px 24px;
    }
    #modalDireccion .btn-secondary {
        background: #f0f0f0;
        border: none;
        color: #555;
        border-radius: 8px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 700;
        padding: 9px 20px;
        letter-spacing: 0.5px;
    }
    #modalDireccion .btn-secondary:hover { background: #e0e0e0; }
    #modalDireccion .btn-primary {
        background: #c0392b !important;
        border-color: #c0392b !important;
        border-radius: 8px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 700;
        padding: 9px 24px;
        letter-spacing: 0.5px;
        transition: background .2s;
    }
    #modalDireccion .btn-primary:hover { background: #a93226 !important; border-color: #a93226 !important; }

    /* ============================================
   PAGINATION
   ============================================ */
    .orders-pagination .page-link {
        color: #667eea;
        border-radius: 6px;
        margin: 0 3px;
        border: 1px solid #dee2e6;
    }

    .orders-pagination .page-item.active .page-link {
        background: #922b21;
        border-color: #922b21;
        color: #fff;
    }

    /* ============================================
   TAB SYSTEM
   ============================================ */
    .tab-content>.tab-pane {
        display: none;
    }

    .tab-content>.tab-pane.active {
        display: block;
    }

    /* ============================================
   RESPONSIVE
   ============================================ */
    @media (max-width: 768px) {
        .sidebar-navigation {
            margin-bottom: 20px;
        }

        .order-header {
            padding: 12px 15px;
        }

        .order-summary-info {
            margin-top: 10px;
            width: 100%;
            justify-content: space-between;
        }

        .product-item {
            padding: 12px 15px;
        }

        .tracking-timeline {
            padding-left: 25px;
        }
    }










    /* ===================START: ESTILOS DE ESTADOS ================== */
    .tracking-step-cancelled {
        background: #fff3cd;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #dc3545;
    }

    .tracking-step-cancelled .tracking-icon {
        background: #dc3545 !important;
        box-shadow: 0 0 0 2px #dc3545 !important;
    }

    .tracking-step-cancelled .tracking-icon i {
        color: white;
        font-size: 12px;
        line-height: 16px;
    }

    .tracking-step-return {
        background: #d1ecf1;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }

    .tracking-step-return .tracking-icon {
        background: #17a2b8 !important;
        box-shadow: 0 0 0 2px #17a2b8 !important;
    }

    .tracking-step-return .tracking-icon i {
        color: white;
        font-size: 12px;
        line-height: 16px;
    }

    /* ===================END: ESTILOS DE ESTADOS ================== */

    /* ===================START: MENSAJE DEL ADMINISTRADOR ================== */
    .admin-message-light {
        margin-top: 15px;
    }

    .admin-message-light-content {
        background: #fff;
        border-left: 3px solid #922b21;
        border-radius: 6px;
        padding: 12px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        box-shadow: 0 2px 6px rgba(253, 5, 5, 0.1);
    }

    .admin-message-light-icon {
        color: #922b21;
        font-size: 18px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .admin-message-light-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .admin-message-light-label {
        color: #922b21;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .admin-message-light-message {
        color: #495057;
        font-size: 14px;
        line-height: 1.5;
    }

    .admin-message-light-date {
        color: #6c757d;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }

    .admin-message-light-date i {
        font-size: 12px;
    }

    /* ===================END: MENSAJE DEL ADMINISTRADOR ================== */
</style>

<div class="container pt-4">
    @php
        $tabParam = request('tab');
        $activeTab = match (true) {
            request()->has('page'), $tabParam === 'link-misPedidos' => 'misPedidos',
            $tabParam === 'link-miPerfil' => 'miPerfil',
            $tabParam === 'tab-direccion' => 'direccion',
            default => 'bienvenidos',
        };
    @endphp
    
    <div class="row">
        <!-- SIDEBAR -->
        <aside class="col-md-2 col-lg-2">
            <div class="sidebar-navigation">
                <ul class="nav-menu">
                    <li class="nav-menu-item {{ $activeTab === 'bienvenidos' ? 'active' : '' }}">
                        <a class="nav-menu-link" data-tab="tab-bienvenidos">
                            <i class="fa fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li class="nav-menu-item {{ $activeTab === 'misPedidos' ? 'active' : '' }}">
                        <a class="nav-menu-link" data-tab="link-misPedidos">
                            <i class="fa fa-shopping-cart"></i>
                            <span>Mis Compras</span>
                        </a>
                    </li>
                    <li class="nav-menu-item {{ $activeTab === 'miPerfil' ? 'active' : '' }}">
                        <a class="nav-menu-link" data-tab="link-miPerfil">
                            <i class="fa fa-user"></i>
                            <span>Mi perfil</span>
                        </a>
                    </li>
                    <li class="nav-menu-item {{ $activeTab === 'direccion' ? 'active' : '' }}">
                        <a class="nav-menu-link" data-tab="tab-direccion">
                            <i class="fa fa-map-marker"></i>
                            <span>Direcciones</span>
                        </a>
                    </li>
                    <li class="nav-menu-item">
                        <a href="javascript:void(0)" class="nav-menu-link" onclick="confirmarCerrarSesion()">
                            <i class="fa fa-power-off"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- CONTENT -->
        <div class="col-md-10 col-lg-10">
            <div class="tab-content">
                <!-- MIS COMPRAS TAB -->
                <div class="tab-pane {{ $activeTab === 'misPedidos' ? 'active' : '' }}" id="link-misPedidos">
                    @if(count($ProductsComprasArrays) == 0)
                    <div class="text-center py-5">
                        <i class="mdi mdi-cart-off" style="font-size: 80px; color: #e0e0e0;"></i>
                        <h3 class="mt-3">No tienes pedidos registrados</h3>
                        <a href="/" class="btn btn-primary mt-3">
                            <i class="mdi mdi-shopping mr-2"></i>Comenzar a comprar
                        </a>
                    </div>
                    @else
                    <div class="orders-container">
                        <div class="profile-header-card">
                            <h4><i class="mdi mdi-shopping mr-2"></i>Mis Compras</h4>
                            <p class="mb-0 small">Gestiona tus pedidos y comprobantes</p>
                        </div>

                        @foreach($ProductsComprasArrays as $key => $data1)
                        @php
                        $orderData = json_decode(array_keys($data1)[0]);
                        $orderSummary = Helpers::cantidadProducto($orderData->codigo_pedido);
                        $isFirst = $key === 0;
                        @endphp
                        <div class="order-card">
                            <div class="order-header" data-toggle="collapse" data-target="#orderCollapse{{$key}}">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <div class="order-number">
                                            <i class="mdi mdi-package-variant mr-2"></i>
                                            <strong>Pedido:</strong>
                                            <span class="text-primary">{{$orderData->codigo_pedido}}</span>
                                        </div>
                                        <div class="order-date">
                                            <i class="mdi mdi-calendar-clock mr-1"></i>
                                            {{$orderData->created_at}}
                                        </div>
                                    </div>
                                    <div class="order-summary-info">
                                        <span class="badge badge-info">{{$orderSummary["cantidad"]}} {{$orderSummary["cantidad"] == 1 ? 'artículo' : 'artículos'}}</span>
                                        @if($orderData->estado_en_camino == 6)
                                        <span class="badge" style="background-color: #4caf50; color: #fff; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; text-transform: none; line-height: 1;">
                                            <i class="mdi mdi-check-circle mr-1" style="font-size: 12px;"></i>Entregado
                                        </span>
                                        @endif
                                        <span class="order-total-amount">S/ {{number_format($orderSummary["total"], 2)}}</span>
                                        <i class="mdi mdi-chevron-down toggle-icon {{$isFirst ? 'rotated' : ''}}"></i>
                                    </div>
                                </div>
                            </div>

                            <div id="orderCollapse{{$key}}" class="collapse {{$isFirst ? 'show' : ''}}">
                                @foreach($data1 as $key2 => $data2)
                                @foreach($data2 as $key3 => $data3)
                                <div class="product-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 col-3">
                                            <div class="product-image">
                                                @php
                                                    $imgPedido = trim((string) ($data3->pro_url_imagen ?? ''));
                                                    if ($imgPedido === '') {
                                                        $imgPedido = asset('temp02/img/product/product-1.jpg');
                                                    } elseif (! preg_match('#^https?://#i', $imgPedido)) {
                                                        $imgPedido = \App\Support\LiamPageData::img($imgPedido, 'temp02/img/product/product-1.jpg');
                                                    }
                                                @endphp
                                                <img src="{{ $imgPedido }}" alt="{{ $data3->pro_nombre }}" onerror="this.src='{{ asset('temp02/img/product/product-1.jpg') }}'">
                                            </div>
                                        </div>
                                        <div class="col-md-7 col-9">
                                            <h6 class="product-name">{{$data3->pro_nombre}}</h6>
                                            <div class="product-details">
                                                @if(!empty($data3->codigo_producto))
                                                <span class="d-block mb-1"><i class="mdi mdi-barcode mr-1"></i>Código: <strong>{{ $data3->codigo_producto }}</strong></span>
                                                @endif
                                                <span><i class="mdi mdi-cart mr-1"></i>Cantidad: <strong>{{$data3->pro_cantidad}}</strong></span>
                                                <span class="ml-3"><i class="mdi mdi-cash mr-1"></i>Precio: <strong>S/ {{number_format($data3->pro_precio, 2)}}</strong></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-12 mt-3 mt-md-0 text-md-right">
                                            <button onclick="obtenerDetalleProducto('{{$data3->id_pedido}}','{{$data3->id_producto}}')" class="btn btn-outline-primary btn-sm">
                                                <i class="mdi mdi-eye mr-1"></i>Ver detalles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @endforeach


                                <!-- TRACKING SECTION -->
                                <div class="tracking-section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="tracking-title">
                                                <i class="mdi mdi-truck-delivery mr-2"></i>Entrega y seguimiento de pedido
                                            </h6>
                                            <div class="tracking-timeline">

                                                @if($orderData->pasarela_estado_pago == 'completado' || $orderData->comprobante_conforme == 1)
                                                <div class="tracking-step active">
                                                    <div class="tracking-icon"></div>
                                                    <div class="tracking-content">
                                                        <h6>Pago aprobado</h6>
                                                        <p>Tu pago ha sido verificado correctamente</p>
                                                        <small>{{$orderData->created_at}}</small>
                                                    </div>
                                                </div>
                                                @else

                                                <div class="tracking-step current">
                                                    <div class="tracking-icon"></div>
                                                    <div class="tracking-content">
                                                        <h6>Pendiente de pago</h6>
                                                        <p>Estamos esperando la confirmación de tu pago</p>
                                                        <small>{{$orderData->created_at}}</small>
                                                    </div>
                                                </div>
                                                @endif


                                                <!-- ============== START: ESTADOS DEL PRODUCTO =======================-->
                                                @php
                                                // Estados normales del flujo de entrega
                                                $estadosNormales = [
                                                1 => [
                                                ['titulo' => 'Pedido recibido', 'descripcion' => 'Tu pedido ha sido recibido', 'estado' => 'Pendiente']
                                                ],
                                                2 => [
                                                ['titulo' => 'Pedido recibido', 'descripcion' => 'Tu pedido ha sido recibido', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido confirmado / Pago confirmado', 'descripcion' => 'Tu pedido ha sido confirmado y el pago verificado', 'estado' => 'En proceso']
                                                ],
                                                3 => [
                                                ['titulo' => 'Pedido recibido', 'descripcion' => 'Tu pedido ha sido recibido', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido confirmado / Pago confirmado', 'descripcion' => 'Tu pedido ha sido confirmado y el pago verificado', 'estado' => 'Completado'],
                                                ['titulo' => 'Preparando pedido', 'descripcion' => 'Tu pedido está siendo preparado', 'estado' => 'En proceso']
                                                ],
                                                4 => [
                                                ['titulo' => 'Pedido recibido', 'descripcion' => 'Tu pedido ha sido recibido', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido confirmado / Pago confirmado', 'descripcion' => 'Tu pedido ha sido confirmado y el pago verificado', 'estado' => 'Completado'],
                                                ['titulo' => 'Preparando pedido', 'descripcion' => 'Tu pedido está siendo preparado', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido listo', 'descripcion' => 'Tu pedido está listo para ser enviado', 'estado' => 'En proceso']
                                                ],
                                                5 => [
                                                ['titulo' => 'Pedido recibido', 'descripcion' => 'Tu pedido ha sido recibido', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido confirmado / Pago confirmado', 'descripcion' => 'Tu pedido ha sido confirmado y el pago verificado', 'estado' => 'Completado'],
                                                ['titulo' => 'Preparando pedido', 'descripcion' => 'Tu pedido está siendo preparado', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido listo', 'descripcion' => 'Tu pedido está listo para ser enviado', 'estado' => 'Completado'],
                                                ['titulo' => 'En camino', 'descripcion' => 'Tu pedido ya está en recorrido', 'estado' => 'En proceso']
                                                ],
                                                6 => [
                                                ['titulo' => 'Pedido recibido', 'descripcion' => 'Tu pedido ha sido recibido', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido confirmado / Pago confirmado', 'descripcion' => 'Tu pedido ha sido confirmado y el pago verificado', 'estado' => 'Completado'],
                                                ['titulo' => 'Preparando pedido', 'descripcion' => 'Tu pedido está siendo preparado', 'estado' => 'Completado'],
                                                ['titulo' => 'Pedido listo', 'descripcion' => 'Tu pedido está listo para ser enviado', 'estado' => 'Completado'],
                                                ['titulo' => 'En camino', 'descripcion' => 'Tu pedido ya está en recorrido', 'estado' => 'Completado'],
                                                ['titulo' => 'Entregado', 'descripcion' => 'Pedido completado exitosamente', 'estado' => 'Completado']
                                                ]
                                                ];

                                                // Estados especiales (cancelado)
                                                $estadosEspeciales = [
                                                7 => [
                                                'titulo' => 'Cancelado o rechazado',
                                                'descripcion' => 'El pedido ha sido cancelado o rechazado',
                                                'icono' => 'mdi-close-circle',
                                                'clase' => 'tracking-step-cancelled'
                                                ]
                                                ];

                                                $esEstadoEspecial = in_array($orderData->estado_en_camino, [7]);
                                                @endphp

                                                @if($esEstadoEspecial)
                                                {{-- ESTADOS ESPECIALES: CANCELADO O DEVOLUCIÓN --}}
                                                @php
                                                $estadoEspecial = $estadosEspeciales[$orderData->estado_en_camino];
                                                @endphp

                                                <div class="tracking-step {{ $estadoEspecial['clase'] }}">
                                                    <div class="tracking-icon">
                                                        <i class="mdi {{ $estadoEspecial['icono'] }}"></i>
                                                    </div>
                                                    <div class="tracking-content">
                                                        <h6>{{ $estadoEspecial['titulo'] }}</h6>
                                                        <p>{{ $estadoEspecial['descripcion'] }}</p>
                                                        <small>{{ $orderData->created_at }}</small>
                                                    </div>
                                                </div>

                                                @if($orderData->estado_en_camino == 6)
                                                <div class="alert alert-warning mt-3">
                                                    <i class="mdi mdi-information-outline mr-2"></i>
                                                    <strong>Pedido cancelado.</strong> Si tienes alguna duda, contáctanos.
                                                </div>
                                                @elseif($orderData->estado_en_camino == 7)
                                                <div class="alert alert-info mt-3">
                                                    <i class="mdi mdi-information-outline mr-2"></i>
                                                    <strong>Proceso de devolución iniciado.</strong> Te contactaremos pronto.
                                                </div>
                                                @endif
                                                @else
                                                {{-- ESTADOS NORMALES DEL FLUJO --}}
                                                @php
                                                $estadosActuales = $estadosNormales[$orderData->estado_en_camino] ?? [];
                                                @endphp

                                                @foreach($estadosActuales as $index => $paso)
                                                @php
                                                $esUltimo = $index === count($estadosActuales) - 1;
                                                $claseEstado = in_array($orderData->estado_en_camino, [5, 6])
                                                ? 'active'
                                                : ($esUltimo ? 'current' : 'active');
                                                @endphp

                                                <div class="tracking-step {{ $claseEstado }}">
                                                    <div class="tracking-icon"></div>
                                                    <div class="tracking-content">
                                                        <h6>{{ $paso['titulo'] }}</h6>
                                                        <p>{{ $paso['descripcion'] }}</p>
                                                        <small>{{ $paso['estado'] }}</small>
                                                    </div>
                                                </div>
                                                @endforeach

                                                @if($orderData->estado_en_camino < 5)
                                                    <div class="tracking-step">
                                                    <div class="tracking-icon"></div>
                                                    <div class="tracking-content">
                                                        <h6>Entregado</h6>
                                                        <p>Pedido completado exitosamente</p>
                                                        <small>Pendiente</small>
                                                    </div>
                                            </div>
                                            @endif
                                            @endif
                                            <!-- ============== END: ESTADOS DEL PRODUCTO =======================-->

                                            @if(isset($orderData->ultima_observacion) && $orderData->ultima_observacion)
                                            <div class="admin-message-light mt-3">
                                                <div class="admin-message-light-content">
                                                    <i class="mdi mdi-message-text-outline admin-message-light-icon"></i>
                                                    <div class="admin-message-light-text">
                                                        <span class="admin-message-light-label">Mensaje del administrador:</span>
                                                        <span class="admin-message-light-message">{{ $orderData->ultima_observacion }}</span>
                                                        @if($orderData->fecha_observacion)
                                                        <small class="admin-message-light-date">
                                                            <i class="mdi mdi-clock-outline"></i> {{ date('d/m/Y H:i', strtotime($orderData->fecha_observacion)) }}
                                                        </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                        </div>

                                        <div class="mt-3 p-3 bg-light rounded">
                                            <h6 class="mb-2"><i class="mdi mdi-map-marker mr-2"></i>Entrega a domicilio</h6>
                                            <p class="mb-1 small"><strong>{{$orderData->direccion_envio_ubicacion ?? 'Dirección no especificada'}}</strong></p>
                                            <p class="mb-0 small text-muted">Destinatario: {{$orderData->nombre_destinatario ?? Auth::user()->name}}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mt-4 mt-md-0">
                                        <h6 class="tracking-title">
                                            <i class="mdi mdi-credit-card mr-2"></i>Medio de pago y resumen
                                        </h6>
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h6 class="mb-2">
                                                @if(($orderData->radio_tipo_pago ?? '') === 'whatsapp' || strcasecmp((string)($orderData->metodo_pago ?? ''), 'WhatsApp') === 0)
                                                <i class="mdi mdi-whatsapp mr-2 text-success"></i>Medio de pago
                                                @else
                                                <i class="mdi mdi-wallet mr-2"></i>Medio de pago
                                                @endif
                                            </h6>
                                            @php
                                                $etiquetaPago = $orderData->medio_pago_etiqueta
                                                    ?? \App\Http\Controllers\Web\PedidosController::etiquetaMedioPago(
                                                        (string) ($orderData->radio_tipo_pago ?? ''),
                                                        (string) ($orderData->metodo_pago ?? '')
                                                    );
                                            @endphp
                                            <p class="mb-0 small">
                                                <strong>{{ $etiquetaPago }}</strong>
                                                @if(($orderData->radio_tipo_pago ?? '') === 'whatsapp' || strcasecmp((string)($orderData->metodo_pago ?? ''), 'WhatsApp') === 0)
                                                    <span class="badge badge-success ml-1" style="font-size:10px;vertical-align:middle;">Pedido por WhatsApp</span>
                                                @elseif(!empty($orderData->metodo_pago) || in_array(strtolower((string)($orderData->radio_tipo_pago ?? '')), ['normal','pasarela','online'], true))
                                                    <span class="badge badge-primary ml-1" style="font-size:10px;vertical-align:middle;">Pasarela / web</span>
                                                @endif
                                            </p>
                                            <p class="mb-0 small text-muted">Total: S/ {{number_format($orderSummary["total"], 2)}}</p>
                                        </div>

                                        <div class="p-3 bg-light rounded">
                                            <div class="d-flex justify-content-between">
                                                <span class="font-weight-bold">Total:</span>
                                                <span class="font-weight-bold text-primary">S/ {{number_format($orderSummary["total"], 2)}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($orderData->pasarela_estado_pago !== 'completado')
                            <!-- PAYMENT STATUS SECTION -->
                            <div class="payment-status-section">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        @if($orderData->comprobante_conforme == 1)
                                        <span class="status-badge status-approved">
                                            <i class="mdi mdi-check-circle mr-2"></i>Comprobante aprobado
                                        </span>
                                        @elseif($orderData->subido_comprobante_url)
                                        <span class="status-badge status-review">
                                            <i class="mdi mdi-clock-outline mr-2"></i>En revisión
                                        </span>
                                        @else
                                        <span class="status-badge status-pending">
                                            <i class="mdi mdi-alert-circle-outline mr-2"></i>Pendiente de pago
                                        </span>
                                        @endif

                                        @if($orderData->subido_comprobante_url)
                                        @php
                                        $extension = pathinfo($orderData->subido_comprobante_url, PATHINFO_EXTENSION);
                                        $isPdf = strtolower($extension) === 'pdf';
                                        $comprobanteUrl = route('comprobante.show', $orderData->subido_comprobante_url);
                                        @endphp
                                        <div class="comprobante-inline" id="preview_wrapper_{{$orderData->id_pedido}}">
                                            @if($isPdf)
                                            <div class="comprobante-badge" onclick="openPdfModal('{{$comprobanteUrl}}')">
                                                <i class="mdi mdi-file-pdf-box text-danger mr-2"></i>
                                                <span>{{basename($orderData->subido_comprobante_url)}}</span>
                                            </div>
                                            @else
                                            <div class="comprobante-badge" onclick="openImageModal('{{$comprobanteUrl}}')">
                                                <i class="mdi mdi-image text-primary mr-2"></i>
                                                <span>Comprobante.{{$extension}}</span>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mt-2 mt-md-0">
                                        @if($orderData->comprobante_conforme != 1)
                                        <button class="btn btn-upload-proof btn-sm" onclick="openUploadModal({{$orderData->id_pedido}}, '{{$orderData->codigo_pedido}}')">
                                            <i class="mdi mdi-cloud-upload mr-2"></i>
                                            {{$orderData->subido_comprobante_url ? 'Cambiar' : 'Subir'}} comprobante
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                    @endforeach

                    @if($totalPages > 1)
                    <nav class="orders-pagination mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item {{$page <= 1 ? 'disabled' : ''}}">
                                <a class="page-link" href="{{url()->current().'?page='.($page - 1)}}">
                                    <i class="mdi mdi-chevron-left"></i>
                                </a>
                            </li>
                            @for($i = 1; $i <= $totalPages; $i++)
                                <li class="page-item {{$i == $page ? 'active' : ''}}">
                                <a class="page-link" href="{{url()->current().'?page='.$i}}">{{$i}}</a>
                                </li>
                                @endfor
                                <li class="page-item {{$page >= $totalPages ? 'disabled' : ''}}">
                                    <a class="page-link" href="{{url()->current().'?page='.($page + 1)}}">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </a>
                                </li>
                        </ul>
                    </nav>
                    @endif
                </div>

                <!-- MODAL SUBIR COMPROBANTE -->
                <div class="modal fade" id="uploadPaymentModal" tabindex="-1" data-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="modal-title text-white">
                                    <i class="mdi mdi-cloud-upload mr-2"></i>Subir comprobante
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <form id="uploadProofForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body" style="padding: 1.5rem;">
                                    <div class="alert alert-info" style="border-radius: 8px; border-left: 4px solid #17a2b8;">
                                        <i class="mdi mdi-information mr-2"></i>
                                        <strong>Pedido:</strong> <span id="modal_order_code"></span>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Seleccione el archivo *</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="comprobante_file" name="subido_comprobante_url" accept="image/jpeg,image/jpg,image/png,application/pdf" required>
                                            <label class="custom-file-label" for="comprobante_file">Elegir archivo...</label>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            <i class="mdi mdi-information-outline mr-1"></i>
                                            Formatos: JPG, PNG, PDF (Máx. 2MB)
                                        </small>
                                    </div>
                                    <div id="file_preview_container" style="display: none;">
                                        <label class="font-weight-semibold">
                                            <i class="mdi mdi-eye mr-1"></i>Vista previa:
                                        </label>
                                        <div id="image_preview_wrapper" style="display: none;">
                                            <div class="text-center p-3 bg-light" style="border: 2px dashed #dee2e6; border-radius: 8px;">
                                                <img id="preview_image" src="" alt="Vista previa" class="img-fluid" style="max-height: 300px; border-radius: 6px;">
                                            </div>
                                        </div>
                                        <div id="pdf_preview_wrapper" style="display: none;">
                                            <div class="text-center p-4 bg-light" style="border: 2px dashed #dee2e6; border-radius: 8px;">
                                                <i class="mdi mdi-file-pdf-box text-danger" style="font-size: 64px;"></i>
                                                <p class="mb-0 mt-2 font-weight-medium" id="pdf_filename"></p>
                                                <small class="text-muted">Archivo PDF seleccionado</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="btn_clear_file">
                                            <i class="mdi mdi-close mr-1"></i>Cambiar archivo
                                        </button>
                                    </div>
                                    <div id="upload_message" class="mt-3" style="display: none;"></div>
                                </div>
                                <div class="modal-footer" style="background: #f8f9fa;">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="mdi mdi-close mr-1"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="btn_submit_proof">
                                        <i class="mdi mdi-cloud-upload mr-1"></i>Subir
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- MI PERFIL TAB -->
            <div class="tab-pane {{ $activeTab === 'miPerfil' ? 'active' : '' }}" id="link-miPerfil">
                <div class="card">
                    <div class="card-body">
                        <h5>Mi Perfil</h5>
                        <p id="perfil_social_aviso" class="small text-muted" style="display:none;">
                            Tu cuenta está vinculada a Google. Los datos de nombre, apellidos y correo no se pueden editar aquí (solo el teléfono).
                        </p>
                        <form>
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nombres *</label>
                                    <input type="text" name="txt_perfil_nombre" id="txt_perfil_nombre" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Apellidos *</label>
                                    <input type="text" name="txt_perfil_apellido" id="txt_perfil_apellido" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Email *</label>
                                    <input type="email" name="txt_perfil_email" id="txt_perfil_email" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Teléfono *</label>
                                    <input type="text" name="txt_perfil_telefono" id="txt_perfil_telefono" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DIRECCIONES TAB -->
            <div class="tab-pane {{ $activeTab === 'direccion' ? 'active' : '' }}" id="tab-direccion">
                <div style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                    <div class="dir-header">
                        <h5 class="dir-header__title">
                            <i class="fa fa-map-marker"></i> Mis Direcciones
                        </h5>
                        <button class="dir-btn-add" onclick="abrirModalDireccion()">
                            <i class="fa fa-plus"></i> Nueva Dirección
                        </button>
                    </div>
                    <div style="padding:20px 24px;">
                        <div id="direcciones-lista" class="row">
                            <div class="col-12 text-center py-5">
                                <i class="fa fa-spinner fa-spin fa-2x" style="color:#c0392b;"></i>
                                <p style="margin-top:10px;color:#888;font-size:13px;">Cargando direcciones...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL DIRECCIÓN -->
            <div class="modal fade" id="modalDireccion" tabindex="-1" data-backdrop="static">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa fa-map-marker" style="color:#c0392b;margin-right:8px;"></i>
                                <span id="modal-title-text">Nueva Dirección</span>
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8;">
                                <span>&times;</span>
                            </button>
                        </div>
                        <form id="formDireccion">
                            @csrf
                            <input type="hidden" id="direccion_id" name="direccion_id">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Alias <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="alias" name="alias"
                                               placeholder="Ej: Casa, Oficina, Casa de mamá" required>
                                        <small style="font-size:11px;color:#999;margin-top:3px;display:block;">Un nombre para identificar esta dirección</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Nombre del destinatario <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="nombre_destinatario" name="nombre_destinatario" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Teléfono <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Departamento <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="departamento" name="departamento" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Provincia <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="provincia" name="provincia" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Distrito <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="distrito" name="distrito" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>Dirección completa <span style="color:#c0392b;">*</span></label>
                                        <input type="text" class="form-control" id="direccion" name="direccion"
                                               placeholder="Calle, número, departamento, etc." required>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label>Referencia</label>
                                        <input type="text" class="form-control" id="referencia" name="referencia"
                                               placeholder="Cerca de..., frente a...">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Código Postal</label>
                                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
                                    </div>
                                    <div class="col-12 mt-1">
                                        <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(192,57,43,.05);border:1.5px solid rgba(192,57,43,.2);border-radius:8px;">
                                            <input type="checkbox" id="es_principal" name="es_principal"
                                                   style="width:16px;height:16px;accent-color:#c0392b;flex-shrink:0;">
                                            <label for="es_principal" style="margin:0;font-size:13px;color:#333;font-weight:600;cursor:pointer;text-transform:none;letter-spacing:0;">
                                                <i class="fa fa-star" style="color:#c0392b;margin-right:4px;"></i>
                                                Establecer como dirección principal
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fa fa-times" style="margin-right:5px;"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary" id="btnGuardarDireccion">
                                    <i class="fa fa-save" style="margin-right:5px;"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL DETALLE PRODUCTO -->
            <div class="modal fade" id="modalDetalleProducto" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                        <div class="modal-header bg-light border-0 py-3">
                            <h5 class="modal-title font-weight-bold text-dark">
                                <i class="mdi mdi-information-outline mr-2 text-primary"></i>Detalles del producto
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; transition: all 0.2s;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0 idBodyGaleria">
                            <!-- El contenido se carga vía AJAX -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 8px;">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INICIO TAB -->
            <div class="tab-pane {{ $activeTab === 'bienvenidos' ? 'active' : '' }}" id="tab-bienvenidos">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h3>¡Bienvenido!</h3>
                        <p class="text-muted">Gestiona tus compras y perfil desde este panel</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@include('web.partials.liam.footer')
@endsection


@section('footer_page')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let currentOrderId = null;
    let formSubmitting = false;

    // ============================================
    // FUNCIONES DE NAVEGACIÓN
    // ============================================
    function obtenerDetalleProducto(id_pedido, id_producto) {
        const _token = $("input[name=_token]").val();
        
        // Limpiar y mostrar modal con loader
        $('.idBodyGaleria').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div></div>');
        $('#modalDetalleProducto').modal('show');

        $.ajax({
            url: "/ope_pedidos/listar_pedidos_obtener",
            method: "POST",
            data: {
                _token: _token,
                id_pedido: id_pedido,
                id_producto: id_producto
            },
            success: function(response) {
                $('.idBodyGaleria').html(response);
            },
            error: function() {
                $('.idBodyGaleria').html('<div class="text-center py-5 text-danger"><i class="mdi mdi-alert-circle mr-2"></i>Error al cargar el detalle del producto.</div>');
            }
        });
    }

    // ============================================
    // MODAL FUNCTIONS
    // ============================================
    function openUploadModal(orderId, orderCode) {
        currentOrderId = orderId;
        formSubmitting = false;
        $('#modal_order_code').text(orderCode);
        $('#uploadProofForm').attr('action', '/ope_pedidos/' + orderId + '/subir_comprobante_pedido');
        $('#upload_message, #file_preview_container, #image_preview_wrapper, #pdf_preview_wrapper').hide();
        $('#uploadPaymentModal').modal('show');
    }

    function openImageModal(url) {
        Swal.fire({
            imageUrl: url,
            showConfirmButton: false,
            width: '80%'
        });
    }

    function openPdfModal(url) {
        Swal.fire({
            html: `<iframe src="${url}" width="100%" height="600px" style="border: none;"></iframe>`,
            width: '90%',
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cerrar'
        });
    }

    // ============================================
    // ACTUALIZAR VISTA COMPROBANTE
    // ============================================
    function actualizarVistaComprobante(filename, orderId) {
        const ext = filename.split('.').pop().toLowerCase();
        const isPdf = ext === 'pdf';
        const url = `/comprobantes/${filename}`;
        const wrapper = $(`#preview_wrapper_${orderId}`);

        if (wrapper.length === 0) {
            const section = $(`[onclick*="openUploadModal(${orderId}"]`).closest('.payment-status-section');
            if (section.length) {
                section.find('.d-flex:first').append(crearPreview(filename, url, isPdf, orderId));
            }
        } else {
            wrapper.find('.comprobante-badge').remove();
            wrapper.append(crearPreview(filename, url, isPdf, orderId, false));
            wrapper.hide().fadeIn(500);
        }

        $(`[onclick*="openUploadModal(${orderId}"]`)
            .removeClass('btn-upload-proof')
            .addClass('btn-success')
            .html('<i class="mdi mdi-check-circle mr-1"></i>Comprobante subido');
    }

    function crearPreview(filename, url, isPdf, orderId, full = true) {
        const ext = filename.split('.').pop();

        if (full) {
            return isPdf ?
                `<div class="comprobante-inline" id="preview_wrapper_${orderId}">
                <div class="comprobante-badge" onclick="openPdfModal('${url}')">
                    <i class="mdi mdi-file-pdf-box text-danger mr-2"></i>
                    <span>${filename}</span>
                </div>
            </div>` :
                `<div class="comprobante-inline" id="preview_wrapper_${orderId}">
                <div class="comprobante-badge" onclick="openImageModal('${url}')">
                    <i class="mdi mdi-image text-primary mr-2"></i>
                    <span>Comprobante.${ext}</span>
                </div>
            </div>`;
        } else {
            return isPdf ?
                `<div class="comprobante-badge" onclick="openPdfModal('${url}')">
                <i class="mdi mdi-file-pdf-box text-danger mr-2"></i>
                <span>${filename}</span>
            </div>` :
                `<div class="comprobante-badge" onclick="openImageModal('${url}')">
                <i class="mdi mdi-image text-primary mr-2"></i>
                <span>Comprobante.${ext}</span>
            </div>`;
        }
    }

    function showMessage(msg, type) {
        const icon = type === 'success' ? 'check-circle' : 'alert-circle';
        $('#upload_message').html(`
        <div class="alert alert-${type}">
            <i class="mdi mdi-${icon} mr-2"></i>${msg}
        </div>
    `).fadeIn();
    }

    // ============================================
    // DOCUMENT READY
    // ============================================
    $(document).ready(function() {

        // Sistema de tabs sin hashes
        $(document).on('click', '.nav-menu-link[data-tab]', function(e) {
            e.preventDefault();
            const targetTab = $(this).data('tab');

            // Actualizar menú activo
            $('.nav-menu-item').removeClass('active');
            $(this).closest('.nav-menu-item').addClass('active');

            // Mostrar tab correspondiente
            $('.tab-pane').removeClass('active');
            $('#' + targetTab).addClass('active');

            // Scroll suave al inicio
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        });

        // Preview de archivo
        $(document).on('change', '#comprobante_file', function() {
            const file = this.files[0];
            const label = $(this).next('.custom-file-label');

            if (file) {
                label.text(file.name);
                $('#file_preview_container').show();

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview_image').attr('src', e.target.result);
                        $('#image_preview_wrapper').show();
                        $('#pdf_preview_wrapper').hide();
                    }
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    $('#pdf_filename').text(file.name);
                    $('#pdf_preview_wrapper').show();
                    $('#image_preview_wrapper').hide();
                }
            } else {
                label.text('Elegir archivo...');
                $('#file_preview_container').hide();
            }
        });

        // Limpiar archivo
        $(document).on('click', '#btn_clear_file', function() {
            $('#comprobante_file').val('');
            $('.custom-file-label').text('Elegir archivo...');
            $('#file_preview_container, #image_preview_wrapper, #pdf_preview_wrapper').hide();
        });

        // Submit formulario con protección
        $(document).on('submit', '#uploadProofForm', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (formSubmitting) {
                return false;
            }

            const submitBtn = $('#btn_submit_proof');
            const originalText = submitBtn.html();

            if (!$('#comprobante_file')[0].files.length) {
                showMessage('Seleccione un archivo', 'danger');
                return false;
            }

            formSubmitting = true;
            submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-1"></i>Subiendo...');
            $('#upload_message').hide();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response && response.success) {
                        showMessage(response.message || 'Comprobante subido correctamente', 'success');
                        setTimeout(function() {
                            $('#uploadPaymentModal').modal('hide');
                            actualizarVistaComprobante(response.file, currentOrderId);
                            formSubmitting = false;
                        }, 1000);
                    } else {
                        showMessage(response.message || 'Error desconocido', 'danger');
                        formSubmitting = false;
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    let msg = 'Error al subir el archivo';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }

                    showMessage(msg, 'danger');
                    formSubmitting = false;
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });

            return false;
        });

        // Limpiar modal al cerrar
        $('#uploadPaymentModal').on('hidden.bs.modal', function() {
            formSubmitting = false;
            $('#uploadProofForm')[0].reset();
            $('#comprobante_file').next('.custom-file-label').text('Elegir archivo...');
            $('#file_preview_container, #upload_message').hide();
            $('#btn_submit_proof').prop('disabled', false).html('<i class="mdi mdi-cloud-upload mr-1"></i>Subir');
        });

        // Animación del acordeón
        $(document).on('shown.bs.collapse', '.collapse', function() {
            $(this).prev('.order-header').find('.toggle-icon').addClass('rotated');
        });

        $(document).on('hidden.bs.collapse', '.collapse', function() {
            $(this).prev('.order-header').find('.toggle-icon').removeClass('rotated');
        });
    });



    // ======================= START: PERFIL TAB =========================
    document.addEventListener('DOMContentLoaded', function() {

        // ============================================
        // DETECTAR CLIC EN EL TAB DE MI PERFIL
        // ============================================
        const tabPerfilLink = document.querySelector('.nav-menu-link[data-tab="link-miPerfil"]');

        if (tabPerfilLink) {
            tabPerfilLink.addEventListener('click', function() {
                // Cargar datos cada vez que se hace clic
                setTimeout(cargarDatosPerfil, 100);
            });
        }

        // Si el tab de perfil está activo al cargar la página
        const tabActivo = document.querySelector('.tab-pane#link-miPerfil.active');
        if (tabActivo) {
            cargarDatosPerfil();
        }

        // ============================================
        // FUNCIÓN PARA CARGAR DATOS
        // ============================================
        async function cargarDatosPerfil() {
            try {
                const response = await axios.get('/ope_pedidos/perfil_obtener');
                console.log('Datos recibidos:', response.data);

                if (response.data.success && response.data.data) {
                    const data = response.data.data;
                    document.getElementById('txt_perfil_nombre').value = data.nombre || '';
                    document.getElementById('txt_perfil_apellido').value = data.apellido || '';
                    document.getElementById('txt_perfil_email').value = data.email || '';
                    document.getElementById('txt_perfil_telefono').value = data.telefono || '';
                    const bloquear = !!data.campos_identidad_bloqueados;
                    ['txt_perfil_nombre', 'txt_perfil_apellido', 'txt_perfil_email'].forEach(function (id) {
                        const el = document.getElementById(id);
                        if (el) {
                            el.readOnly = bloquear;
                            el.classList.toggle('bg-light', bloquear);
                        }
                    });
                    const aviso = document.getElementById('perfil_social_aviso');
                    if (aviso) aviso.style.display = bloquear ? 'block' : 'none';
                }
            } catch (error) {
                console.error('Error al cargar datos del perfil:', error);
            }
        }

        // ============================================
        // ENVIAR FORMULARIO DE PERFIL
        // ============================================
        const formPerfil = document.querySelector('#link-miPerfil form');

        if (formPerfil) {
            formPerfil.addEventListener('submit', async function(e) {
                e.preventDefault();

                const btnSubmit = this.querySelector('button[type="submit"]');
                const btnText = btnSubmit.innerHTML;

                try {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

                    const formData = {
                        nombre: document.getElementById('txt_perfil_nombre').value.trim(),
                        apellido: document.getElementById('txt_perfil_apellido').value.trim(),
                        email: document.getElementById('txt_perfil_email').value.trim(),
                        telefono: document.getElementById('txt_perfil_telefono').value.trim()
                    };

                    const response = await axios.post('/ope_pedidos/perfil_actualizar', formData, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.data.message || 'Perfil actualizado correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }

                } catch (error) {
                    console.error('Error:', error);

                    let errorMessage = 'Ocurrió un error al actualizar el perfil';

                    if (error.response && error.response.data) {
                        if (error.response.data.errors) {
                            const errors = Object.values(error.response.data.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (error.response.data.message) {
                            errorMessage = error.response.data.message;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: errorMessage
                    });

                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = btnText;
                }
            });
        }
    });
    // ======================= END: PERFIL TAB =========================


    //======================= START: DIRECCIONES TAB =========================

    // ============================================
    // GESTIÓN DE DIRECCIONES
    // ============================================
    let direccionActual = null;

    // Cargar direcciones cuando se hace clic en el tab
    document.addEventListener('DOMContentLoaded', function() {
        const tabDireccionLink = document.querySelector('.nav-menu-link[data-tab="tab-direccion"]');

        if (tabDireccionLink) {
            tabDireccionLink.addEventListener('click', function() {
                setTimeout(cargarDirecciones, 100);
            });
        }

        // Si el tab está activo al cargar
        const tabActivo = document.querySelector('.tab-pane#tab-direccion.active');
        if (tabActivo) {
            cargarDirecciones();
        }
    });

    async function cargarDirecciones() {
        try {
            const response = await axios.get('/ope_pedidos/direcciones_listar');

            if (response.data.success) {
                mostrarDirecciones(response.data.data);
            }
        } catch (error) {
            console.error('Error al cargar direcciones:', error);
            document.getElementById('direcciones-lista').innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fa fa-exclamation-circle" style="font-size:40px;color:#dc3545;"></i>
                <p class="mt-3" style="color:#888;">Error al cargar las direcciones. Recarga la página.</p>
            </div>`;
        }
    }

    function mostrarDirecciones(direcciones) {
        const container = document.getElementById('direcciones-lista');

        if (direcciones.length === 0) {
            container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fa fa-map-marker" style="font-size:48px;color:#e0e0e0;"></i>
                <h5 class="mt-3" style="color:#555;">No tienes direcciones registradas</h5>
                <p style="color:#999;font-size:13px;">Agrega tu primera dirección de envío</p>
                <button class="dir-btn-add mt-3" onclick="abrirModalDireccion()">
                    <i class="fa fa-plus"></i> Agregar dirección
                </button>
            </div>`;
            return;
        }

        container.innerHTML = direcciones.map(dir => `
        <div class="col-md-6 mb-4">
            <div class="dir-card ${dir.es_principal ? 'dir-card--principal' : ''}">
                <div class="dir-card__stripe"></div>
                <div class="dir-card__body">
                    <div class="dir-card__top">
                        <div style="flex:1;min-width:0;">
                            ${dir.es_principal ? `<span class="dir-badge-principal"><i class="fa fa-star"></i>&nbsp;Principal</span><br>` : ''}
                            <p class="dir-alias" style="margin-top:${dir.es_principal?'6px':'0'};">${dir.alias}</p>
                        </div>
                        <div class="dir-icon-wrap">
                            <i class="fa fa-map-marker"></i>
                        </div>
                    </div>

                    <div class="dir-info-row dir-info-row--name">
                        <i class="fa fa-user"></i>
                        <span>${dir.nombre_destinatario}</span>
                    </div>
                    <div class="dir-info-row">
                        <i class="fa fa-phone"></i>
                        <span>${dir.telefono}</span>
                    </div>
                    <div class="dir-divider"></div>
                    <div class="dir-info-row dir-info-row--addr">
                        <i class="fa fa-home"></i>
                        <span>${dir.direccion}</span>
                    </div>
                    <div class="dir-info-row">
                        <i class="fa fa-building-o"></i>
                        <span>${dir.distrito}, ${dir.provincia}, ${dir.departamento}</span>
                    </div>
                    ${dir.referencia ? `
                    <div class="dir-info-row dir-info-row--ref">
                        <i class="fa fa-info-circle"></i>
                        <span>${dir.referencia}</span>
                    </div>` : ''}
                </div>
                <div class="dir-actions">
                    <button class="dir-btn-action dir-btn-edit" onclick="editarDireccion(${dir.id})">
                        <i class="fa fa-pencil"></i> Editar
                    </button>
                    <button class="dir-btn-action dir-btn-delete" onclick="eliminarDireccion(${dir.id})">
                        <i class="fa fa-trash-o"></i> Eliminar
                    </button>
                    ${!dir.es_principal ? `
                    <button class="dir-btn-action dir-btn-principal" onclick="establecerPrincipal(${dir.id})">
                        <i class="fa fa-star-o"></i> Principal
                    </button>` : ''}
                </div>
            </div>
        </div>`).join('');
    }

    function abrirModalDireccion(direccionId = null) {
        direccionActual = direccionId;
        const form = document.getElementById('formDireccion');
        form.reset();

        if (direccionId) {
            document.getElementById('modal-title-text').textContent = 'Editar Dirección';
            cargarDatosDireccion(direccionId);
        } else {
            document.getElementById('modal-title-text').textContent = 'Nueva Direccion';
            document.getElementById('direccion_id').value = '';
        }

        $('#modalDireccion').modal('show');
    }

    async function cargarDatosDireccion(id) {
        try {
            const response = await axios.get('/ope_pedidos/direcciones_listar');
            const direccion = response.data.data.find(d => d.id == id);

            if (direccion) {
                document.getElementById('direccion_id').value = direccion.id;
                document.getElementById('alias').value = direccion.alias;
                document.getElementById('nombre_destinatario').value = direccion.nombre_destinatario;
                document.getElementById('telefono').value = direccion.telefono;
                document.getElementById('departamento').value = direccion.departamento;
                document.getElementById('provincia').value = direccion.provincia;
                document.getElementById('distrito').value = direccion.distrito;
                document.getElementById('direccion').value = direccion.direccion;
                document.getElementById('referencia').value = direccion.referencia || '';
                document.getElementById('codigo_postal').value = direccion.codigo_postal || '';
                document.getElementById('es_principal').checked = direccion.es_principal;
            }
        } catch (error) {
            console.error('Error al cargar dirección:', error);
        }
    }

    async function editarDireccion(id) {
        abrirModalDireccion(id);
    }

    async function confirmarCerrarSesion() {
        const result = await Swal.fire({
            title: '¿Cerrar sesión?',
            text: 'Tendrás que iniciar sesión nuevamente para continuar',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            window.location.href = "{{ route('logout') }}";
        }
    }

    async function eliminarDireccion(id) {
        const result = await Swal.fire({
            title: '¿Eliminar dirección?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                const response = await axios.delete(`/ope_pedidos/direcciones_eliminar/${id}`);

                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: response.data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    cargarDirecciones();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo eliminar la dirección'
                });
            }
        }
    }

    async function establecerPrincipal(id) {
        try {
            const response = await axios.post(`/ope_pedidos/direcciones_principal/${id}`);

            if (response.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                cargarDirecciones();
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo actualizar la dirección principal'
            });
        }
    }

    // Submit formulario de dirección
    document.getElementById('formDireccion').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btnGuardar = document.getElementById('btnGuardarDireccion');
        const btnText = btnGuardar.innerHTML;

        try {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Guardando...';

            const formData = {
                alias: document.getElementById('alias').value.trim(),
                nombre_destinatario: document.getElementById('nombre_destinatario').value.trim(),
                telefono: document.getElementById('telefono').value.trim(),
                departamento: document.getElementById('departamento').value.trim(),
                provincia: document.getElementById('provincia').value.trim(),
                distrito: document.getElementById('distrito').value.trim(),
                direccion: document.getElementById('direccion').value.trim(),
                referencia: document.getElementById('referencia').value.trim(),
                codigo_postal: document.getElementById('codigo_postal').value.trim(),
                es_principal: document.getElementById('es_principal').checked
            };

            const direccionId = document.getElementById('direccion_id').value;
            let response;

            if (direccionId) {
                // Actualizar
                response = await axios.put(`/ope_pedidos/direcciones_actualizar/${direccionId}`, formData, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json'
                    }
                });
            } else {
                // Crear
                response = await axios.post('/ope_pedidos/direcciones_crear', formData, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json'
                    }
                });
            }

            if (response.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#modalDireccion').modal('hide');
                cargarDirecciones();
            }

        } catch (error) {
            let errorMessage = 'Ocurrió un error al guardar la dirección';

            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    errorMessage = errors.join('<br>');
                } else if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMessage
            });

        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = btnText;
        }
    });

    // Limpiar modal al cerrar
    $('#modalDireccion').on('hidden.bs.modal', function() {
        document.getElementById('formDireccion').reset();
        document.getElementById('direccion_id').value = '';
        direccionActual = null;
    });
    //======================= END: DIRECCIONES TAB =========================
</script>
@endsection