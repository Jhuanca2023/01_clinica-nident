/* =============================================
   Categories Page — Valento
   Filtros, ordenamiento y grid de productos
   ============================================= */
'use strict';

(function ($) {

    /* ── Datos de productos ──────────────── */
    var products = [
        { id:1, name:'Polo Laboral Atlas', cat:'Laboral', price:33, oldPrice:59, img:'img/productos/polo/polo-azul-marino.jpg', marca:'Valento', color:'Azul', talla:['S','M','L','XL'], discount:44 },
        { id:2, name:'Camiseta Promocional Norte', cat:'Laboral', price:33, oldPrice:59, img:'img/productos/polo/camiseta-blanca-logo.jpg', marca:'Valento', color:'Blanco', talla:['M','L','XL'], discount:44 },
        { id:3, name:'Polo Técnico Dry Sport', cat:'Laboral', price:20, oldPrice:36, img:'img/productos/polo/polo-celeste.jpg', marca:'Valento', color:'Azul', talla:['S','M','L'], discount:44 },
        { id:4, name:'Camisa Oxford Uniforme', cat:'Laboral', price:33, oldPrice:59, img:'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80', marca:'Valento', color:'Beige', talla:['S','M','L','XL'], discount:44 },
        { id:5, name:'Softshell Alta Visibilidad', cat:'Laboral', price:33, oldPrice:59, img:'https://images.unsplash.com/photo-1594938291221-94f18cbb5660?auto=format&fit=crop&w=800&q=80', marca:'Valento', color:'Negro', talla:['M','L','XL'], discount:44 },
        { id:6, name:'Pantalón Cargo Industrial', cat:'Laboral', price:19, oldPrice:34, img:'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=800&q=80', marca:'Valento', color:'Gris', talla:['S','M'], discount:44 },
        { id:7, name:'Polo Manga Larga Classic', cat:'Laboral', price:30, oldPrice:54, img:'img/productos/polo/polo-gris-carbon.jpg', marca:'Valento', color:'Gris', talla:['S','M','L','XL'], discount:44 },
        { id:8, name:'Chaqueta Polar Work', cat:'Laboral', price:30, oldPrice:54, img:'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=800&q=80', marca:'Valento', color:'Negro', talla:['M','L','XL'], discount:44 },
        { id:9, name:'Polo Piqué Basic Azul', cat:'Promocional', price:25, oldPrice:45, img:'img/productos/polo/polo-celeste.jpg', marca:'Valento', color:'Azul', talla:['S','M','L','XL','2XL'], discount:44 },
        { id:10, name:'Polo Piqué Basic Blanco', cat:'Promocional', price:22, oldPrice:40, img:'img/productos/polo/polo-blanco.jpg', marca:'Valento', color:'Blanco', talla:['S','M','L'], discount:45 },
        { id:11, name:'Camiseta Deportiva Roja', cat:'Deportivo', price:28, oldPrice:50, img:'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=800&q=80', marca:'Valento', color:'Rojo', talla:['M','L','XL'], discount:44 },
        { id:12, name:'Polo Técnico Premium', cat:'Protección', price:45, oldPrice:80, img:'img/productos/polo/polo-burdeos.jpg', marca:'Valento', color:'Rojo', talla:['S','M','L','XL'], discount:44 },
    ];

    var filtered  = products.slice();
    var activeFilters = { marcas:[], colores:[], tallas:[], maxPrice:100 };

    /* ── Renderizar grid ─────────────────── */
    function renderGrid() {
        var $grid = $('#cat-grid');
        $grid.empty();

        if (filtered.length === 0) {
            $grid.html('<div class="cat-no-results"><i class="fa fa-search"></i>No se encontraron productos</div>');
            $('#cat-count').text('0');
            return;
        }

        $.each(filtered, function (i, p) {
            var html =
                '<div class="cat-card" data-id="' + p.id + '">' +
                    '<div class="cat-card__img-wrap">' +
                        '<img src="' + p.img + '" alt="' + p.name + '">' +
                        '<span class="cat-card__badge">' + p.discount + '% DSCTO</span>' +
                        '<button class="cat-card__wish" data-id="' + p.id + '" title="Favorito"><i class="fa fa-heart-o"></i></button>' +
                    '</div>' +
                    '<p class="cat-card__category">' + p.cat + '</p>' +
                    '<p class="cat-card__name">' + p.name + '</p>' +
                    '<div class="cat-card__footer">' +
                        '<div>' +
                            '<span class="cat-card__price-old">S/ ' + p.oldPrice.toFixed(2) + '</span>' +
                            '<span class="cat-card__price">S/ ' + p.price.toFixed(2) + '</span>' +
                        '</div>' +
                        '<button class="cat-card__add lv-cat-add-btn" ' +
                            'data-name="' + p.name + '" ' +
                            'data-price="' + p.price + '" ' +
                            'data-img="' + p.img + '" ' +
                            'data-talla="' + (p.talla[1] || p.talla[0]) + '" ' +
                            'data-color="' + p.color + '">' +
                            '<i class="fa fa-shopping-bag"></i> Agregar' +
                        '</button>' +
                    '</div>' +
                '</div>';
            $grid.append(html);
        });

        $('#cat-count').text(filtered.length);
    }

    /* ── Aplicar filtros ─────────────────── */
    function applyFilters() {
        filtered = products.filter(function (p) {
            var okMarca  = activeFilters.marcas.length  === 0 || activeFilters.marcas.indexOf(p.marca)  > -1;
            var okColor  = activeFilters.colores.length === 0 || activeFilters.colores.indexOf(p.color) > -1;
            var okTalla  = activeFilters.tallas.length  === 0 || p.talla.some(function(t){ return activeFilters.tallas.indexOf(t) > -1; });
            var okPrice  = p.price <= activeFilters.maxPrice;
            return okMarca && okColor && okTalla && okPrice;
        });
        sortProducts();
        renderGrid();
    }

    /* ── Ordenar ─────────────────────────── */
    function sortProducts() {
        var val = $('#cat-sort').val();
        if (val === 'menor')    filtered.sort(function(a,b){ return a.price - b.price; });
        if (val === 'mayor')    filtered.sort(function(a,b){ return b.price - a.price; });
        if (val === 'az')       filtered.sort(function(a,b){ return a.name.localeCompare(b.name); });
        if (val === 'za')       filtered.sort(function(a,b){ return b.name.localeCompare(a.name); });
        if (val === 'descuento')filtered.sort(function(a,b){ return b.discount - a.discount; });
    }

    /* ── Eventos checkboxes ──────────────── */
    $(document).on('change', '.cat-chk-marca', function () {
        activeFilters.marcas = [];
        $('.cat-chk-marca:checked').each(function(){ activeFilters.marcas.push($(this).val()); });
        applyFilters();
    });
    $(document).on('change', '.cat-chk-color', function () {
        activeFilters.colores = [];
        $('.cat-chk-color:checked').each(function(){ activeFilters.colores.push($(this).val()); });
        applyFilters();
    });
    $(document).on('change', '.cat-chk-talla', function () {
        activeFilters.tallas = [];
        $('.cat-chk-talla:checked').each(function(){ activeFilters.tallas.push($(this).val()); });
        applyFilters();
    });
    $(document).on('input', '#cat-price-range', function () {
        activeFilters.maxPrice = parseInt($(this).val());
        $('#cat-price-max').text('S/ ' + $(this).val());
        applyFilters();
    });

    /* ── Ordenar ─────────────────────────── */
    $(document).on('change', '#cat-sort', function () {
        sortProducts();
        renderGrid();
    });

    /* ── Toggle filtros (accordion) ─────── */
    $(document).on('click', '.cat-filter__head', function () {
        $(this).closest('.cat-filter').toggleClass('open');
    });

    /* ── Limpiar filtros ─────────────────── */
    $(document).on('click', '#cat-clear', function () {
        activeFilters = { marcas:[], colores:[], tallas:[], maxPrice:100 };
        $('input[type="checkbox"]').prop('checked', false);
        $('#cat-price-range').val(100);
        $('#cat-price-max').text('S/ 100');
        applyFilters();
    });

    /* ── Wishlist toggle ─────────────────── */
    $(document).on('click', '.cat-card__wish', function (e) {
        e.stopPropagation();
        var $icon = $(this).find('i');
        $(this).toggleClass('active');
        $icon.toggleClass('fa-heart-o fa-heart');
    });

    /* ── Click card → ir a detalle ──────── */
    $(document).on('click', '.cat-card', function (e) {
        if (!$(e.target).closest('.cat-card__add, .cat-card__wish').length) {
            window.location.href = 'shop-detail.html';
        }
    });

    /* ── Agregar al carrito desde grid ──── */
    $(document).on('click', '.lv-cat-add-btn', function (e) {
        e.stopPropagation();
        var $btn  = $(this);
        var item  = {
            name  : $btn.data('name'),
            price : parseFloat($btn.data('price')),
            img   : $btn.data('img'),
            talla : $btn.data('talla') || 'M',
            color : $btn.data('color') || 'Unico',
            qty   : 1
        };
        if (window.LVCart) {
            window.LVCart.addItem(item);
        }
        // Feedback visual
        $btn.html('<i class="fa fa-check"></i> Agregado').css('background','#27ae60');
        setTimeout(function(){ $btn.html('<i class="fa fa-shopping-bag"></i> Agregar').css('background',''); }, 1500);
    });

    /* ── Init ────────────────────────────── */
    $(document).ready(function () {
        renderGrid();
    });

})(jQuery);
