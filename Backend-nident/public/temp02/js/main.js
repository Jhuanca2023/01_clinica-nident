/*  ---------------------------------------------------
    Template Name: Violet 
    Description: Violet ecommerce Html Template
    Author: Colorlib
    Author URI: https://colorlib.com/
    Version: 1.0
    Created: Colorlib
---------------------------------------------------------  */

'use strict';

(function ($) {

    /*------------------
        Preloader
    --------------------*/
    $(window).on('load', function () {
        $(".loader").fadeOut();
        $("#preloder").delay(200).fadeOut("slow");

        /*------------------
		    Product filter
	    --------------------*/
        if ($('#product-list').length > 0) {
            var containerEl = document.querySelector('#product-list');
            var mixer = mixitup(containerEl);
        }
    });

    /*------------------
        Background Set
    --------------------*/
    $('.set-bg').each(function () {
        var bg = $(this).data('setbg');
        $(this).css('background-image', 'url(' + bg + ')');
    });

    /*------------------
		Navigation (menú móvil)
	--------------------*/
    var $lvHeader = $('#lv-header');
    var $lvBurger = $('#lv-burger');

    function toggleNav(open) {
        $lvHeader.toggleClass('lv-nav-open', open);
        $lvBurger.attr({
            'aria-expanded': open,
            'aria-label': open ? 'Cerrar menú' : 'Abrir menú'
        });
    }

    $lvBurger.on('click', function () {
        toggleNav(!$lvHeader.hasClass('lv-nav-open'));
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $lvHeader.hasClass('lv-nav-open')) toggleNav(false);
    });

    $(window).on('resize', function () {
        if (window.innerWidth > 991 && $lvHeader.hasClass('lv-nav-open')) toggleNav(false);
    });

    /*------------------
		Sticky bar: replica el ítem activo del header principal
	--------------------*/
    var activeHref = $('.lv-header__nav-list > li > a.active').attr('href');
    if (activeHref) {
        $('.lv-sticky-bar__nav a').filter(function () {
            return $(this).attr('href') === activeHref;
        }).addClass('active').attr('aria-current', 'page');
    }

    /*------------------
		Barra informativa: desplazamiento continuo a la izquierda
	--------------------*/
    (function () {
        var bar = document.querySelector('.lv-header-info');
        var inner = bar && bar.querySelector('.lv-header-info__inner');
        if (!inner) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        var items = [].slice.call(inner.querySelectorAll('.lv-header-info__item'));
        if (!items.length) return;

        var track = document.createElement('div');
        var group = document.createElement('div');
        track.className = 'lv-info-track';
        group.className = 'lv-info-group';
        items.forEach(function (item) { group.appendChild(item); });
        track.appendChild(group);
        inner.appendChild(track);
        bar.classList.add('is-marquee');

        // Repite los mensajes hasta cubrir la pantalla y duplica el grupo
        // para que la animación (0 → -50%) empalme sin saltos.
        var base = group.innerHTML;
        var guard = 0;
        while (group.scrollWidth < bar.clientWidth && guard++ < 10) {
            group.insertAdjacentHTML('beforeend', base);
        }
        var clone = group.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
        [].slice.call(group.children).slice(items.length).forEach(function (el) {
            el.setAttribute('aria-hidden', 'true');
        });

        // Velocidad constante (~45 px/s) sin importar el ancho de pantalla
        track.style.setProperty('--lv-marquee-time', Math.round(group.scrollWidth / 45) + 's');
    })();

    /*------------------
		Search model
	--------------------*/
	$('.lv-search-trigger, .search-trigger').on('click', function() {
		$('.search-model').fadeIn(400);
	});

	$('.search-close-switch').on('click', function() {
		$('.search-model').fadeOut(400,function(){
			$('#search-input').val('');
		});
	});

    /*------------------
        Carousel Slider
    --------------------*/
     $(".hero-items").owlCarousel({
        loop: true,
        margin: 0,
        nav: true,
        items: 1,
        dots: true,
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        smartSpeed: 1200,
        autoplayHoverPause: true,
        mouseDrag: false,
        autoplay: true,
        autoplayTimeout: 4000,
    });

    /*------------------
        Carousel Slider
    --------------------*/
    $(".logo-items").owlCarousel({
        loop: true,
		nav: false,
		dots: false,
		margin : 40,
		autoplay: true,
        responsive: {
            0: {
                items: 2
            },
            480: {
                items: 2
            },
            768: {
                items: 3
            },
            992: {
                items: 5
            }
        }
    });


    /*------------------
        Carousel Slider
    --------------------*/
    $(".product-slider").owlCarousel({
        loop: true,
        margin: 0,
        nav: false,
        items: 1,
        dots: true,
        autoplay: true,
    });
    

    /*------------------
        Magnific Popup
    --------------------*/
    $('.pop-up').magnificPopup({
        type: 'image'
    });

    /*-------------------
		Sort Select
	--------------------- */
    if ($.fn.niceSelect) { $('.sort').niceSelect(); }

    /*-------------------
		Cart Select
	--------------------- */
    if ($.fn.niceSelect) { $('.cart-select').niceSelect(); }

    /*-------------------
		Quantity change
	--------------------- */
    var proQty = $('.pro-qty');
    proQty.prepend('<span class="dec qtybtn">-</span>');
    proQty.append('<span class="inc qtybtn">+</span>');
    proQty.on('click', '.qtybtn', function () {
        var $button = $(this);
        var oldValue = $button.parent().find('input').val();
        if ($button.hasClass('inc')) {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            // Don't allow decrementing below zero
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 0;
            }
        }
        $button.parent().find('input').val(newVal);
    });

    /*-------------------
		Radio Btn
	--------------------- */
    $(".shipping-info .cs-item label").on('click', function () {
        $(".shipping-info .cs-item label").removeClass('active');
        $(this).addClass('active');
    });

    $(".checkout-form .diff-addr label").on('click', function () {
        $(this).toggleClass('active');
    });

    $(".payment-method ul li label").on('click', function () {
        $(this).toggleClass('active');
    });

    /*------------------
        WhatsApp Bubble
    --------------------*/
    $(document).ready(function () {
        var $waWrap  = $('.lv-wa-wrap');
        var $waBtn   = $waWrap.find('.lv-wa-btn');
        var $closeBtn = $waWrap.find('.lv-wa-close-btn');

        // Al pasar el mouse sobre el botón → abrir y dejar anclado
        $waBtn.on('mouseenter', function () {
            $waWrap.addClass('lv-wa-open');
        });

        // Botón × → cerrar
        $closeBtn.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $waWrap.removeClass('lv-wa-open');
        });
    });

    /* ===========================
       CART SIDEBAR
    =========================== */
    // Demo estático únicamente: en la tienda Laravel el carrito es del servidor
    // (liam-cart.js + lv-cart-sidebar.js) y este bloque lo pisaría.
    if (window.LIAM_SHOP) return;

    // Cargar carrito desde localStorage (persiste entre páginas)
    var cart = (function() {
        try {
            return JSON.parse(localStorage.getItem('lv_cart')) || [];
        } catch(e) { return []; }
    })();

    function saveCart() {
        try { localStorage.setItem('lv_cart', JSON.stringify(cart)); } catch(e) {}
    }

    function openCart() {
        renderCart();
        $('#lv-cart-sidebar').addClass('active');
        $('#lv-cart-overlay').addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeCart() {
        $('#lv-cart-sidebar').removeClass('active');
        $('#lv-cart-overlay').removeClass('active');
        $('body').css('overflow', '');
    }

    function formatPrice(n) {
        return 'S/' + parseFloat(n).toFixed(2);
    }

    // Exponer globalmente para uso desde otras páginas (shop-detail.js, etc.)
    window.LVCart = {
        cart       : cart,
        openCart   : openCart,
        closeCart  : closeCart,
        renderCart : function() { renderCart(); },
        addItem    : function(item) {
            var found = false;
            for (var i = 0; i < cart.length; i++) {
                if (cart[i].name === item.name && cart[i].talla === item.talla && cart[i].color === item.color) {
                    cart[i].qty += item.qty;
                    found = true;
                    break;
                }
            }
            if (!found) cart.push(item);
            saveCart();
            renderCart();
            openCart();
        },
        saveCart: saveCart
    };

    function renderCart() {
        var $body  = $('#lv-cart-body');
        var $foot  = $('#lv-cart-foot');
        var $count = $('#lv-cart-count');

        if (cart.length === 0) {
            $body.html('<div class="lv-cart-empty"><i class="fa fa-shopping-bag"></i><span>Tu carrito está vacío</span></div>');
            $foot.hide();
            $count.hide();
            $('#lv-cart-count-sticky').hide();
            return;
        }

        var html = '';
        var subtotal   = 0;
        var totalItems = 0;

        $.each(cart, function(i, item) {
            var lineTotal = item.price * item.qty;
            subtotal   += lineTotal;
            totalItems += item.qty;

            html +=
                '<div class="lv-cart-item">' +
                    '<img class="lv-cart-item__img" src="' + item.img + '" alt="' + item.name + '">' +
                    '<div class="lv-cart-item__info">' +
                        '<p class="lv-cart-item__name">' + item.name + '</p>' +
                        '<p class="lv-cart-item__meta">Cantidad: ' + item.qty + ' - S/ ' + parseFloat(lineTotal).toFixed(2) + '</p>' +
                    '</div>' +
                    '<button class="lv-cart-item__delete cart-item-del" data-idx="' + i + '" title="Eliminar">' +
                        '<i class="fa fa-trash-o"></i>' +
                    '</button>' +
                '</div>';
        });

        /* Notas al pie */
        html +=
            '<div class="lv-cart-notes">' +
                '<p>* Envío calculado en la finalización del pedido.</p>' +
                '<p>* Si usted tiene un cupón escríbalo en la finalización de la solicitud.</p>' +
            '</div>';

        $body.html(html);
        $foot.show();
        $('#lv-cart-subtotal').text('S/ ' + parseFloat(subtotal).toFixed(2));
        // Actualizar badges (header principal + sticky bar)
        $count.text(totalItems).show();
        $('#lv-cart-count-sticky').text(totalItems).show();
    }

    // Abrir desde icono del header (principal + sticky)
    $(document).on('click', '#lv-cart-icon, #lv-cart-icon-sticky', function(e) {
        e.preventDefault();
        renderCart();
        openCart();
    });

    // Cerrar
    $(document).on('click', '#lv-cart-close, #lv-cart-overlay', function() {
        closeCart();
    });

    // Agregar producto
    $(document).on('click', '.lv-cart-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var name  = $btn.data('name');
        var price = parseFloat($btn.data('price'));
        var img   = $btn.data('img');
        var talla = $btn.data('talla') || 'M';
        var color = $btn.data('color') || 'Único';

        // Si ya existe, incrementa qty
        var found = false;
        $.each(cart, function(i, item) {
            if (item.name === name && item.talla === talla && item.color === color) {
                item.qty++;
                found = true;
                return false;
            }
        });
        if (!found) {
            cart.push({ name: name, price: price, img: img, talla: talla, color: color, qty: 1 });
        }

        saveCart();
        renderCart();
        openCart();
    });

    // Eliminar ítem
    $(document).on('click', '.cart-item-del', function() {
        var idx = parseInt($(this).data('idx'));
        cart.splice(idx, 1);
        saveCart();
        renderCart();
    });

    // Renderizar carrito de inmediato en la carga inicial de página
    renderCart();

})(jQuery);
