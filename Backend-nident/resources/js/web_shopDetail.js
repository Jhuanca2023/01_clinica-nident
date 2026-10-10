$(document).ready(function () {
    console.log('--- Script web_shopDetail.js cargado correctamente - CALIFICAR V3 ---');
    $('body').attr('data-rating-js-version', '3');

    // ============================================
    // SISTEMA DE CALIFICACIÓN POR USUARIO (Amazon Style)
    // ============================================
    $(document).on('click', '.star-input', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const _this = $(this);
        const rating = parseInt(_this.data('value')) || 0;
        const container = _this.closest('.product-rating');

        // Intentar obtener ID de producto de varios lugares por si acaso
        let id_producto = $('input[name="txt_id_producto"]').val() ||
            $('#codigo_producto').closest('form').find('input[name="txt_id_producto"]').val() ||
            window.location.pathname.split('/').pop();

        console.log("LOG: Star Clicked ->", rating, "Product ID ->", id_producto);

        // Feedback Visual Inmediato (Pintar las estrellas)
        container.find('.star-input').each(function () {
            const val = parseInt($(this).data('value'));
            if (val <= rating) {
                $(this).removeClass('far').addClass('fas').attr('style', 'color: #ffa41c !important');
            } else {
                $(this).removeClass('fas').addClass('far').attr('style', 'color: #ccc !important');
            }
        });

        if (!id_producto || isNaN(id_producto)) {
            console.error("ERROR: No se pudo encontrar el ID del producto.");
            $('#rating-message').text('Error: ID no encontrado').css('color', 'red').show();
            return;
        }

        $('#rating-message').text('Guardando calificación...').css('color', '#666').show();

        axios.post('/producto/calificar', {
            id_producto: id_producto,
            rating: rating,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
            .then(function (response) {
                console.log("Respuesta servidor:", response.data);
                if (response.data.status === 'success' || response.data.rating) {
                    $('#rating-message').text('¡Gracias por calificar!').css('color', '#28a745').fadeOut(3000);
                    if (response.data.summary) {
                        updateRatingSummary(response.data.summary);
                    }
                }
            })
            .catch(function (error) {
                console.error('Error al calificar:', error);
                let msg = 'Error al calificar';
                if (error.response && error.response.data && error.response.data.message) {
                    msg = error.response.data.message;
                }
                $('#rating-message').text(msg).css('color', '#dc3545');
            });
    });

    // Evento para abrir el popover de calificaciones (Amazon Style) en Móvil
    $(document).on('click', '.rating-trigger', function (e) {
        if (window.innerWidth <= 991) {
            e.preventDefault();
            e.stopPropagation();
            const $popover = $(this).next('.rating-popover');
            $('.rating-popover').not($popover).fadeOut(150);
            $popover.fadeToggle(200);
        } else {
            // COMPORTAMIENTO DESKTOP: El hover de CSS maneja la visibilidad, 
            // pero el click puede servir para anclarlo si se desea.
            // Por ahora, evitamos que un click accidental cause problemas.
        }
    });

    // Evento para cerrar el popover con la X
    $(document).on('click', '.popover-close', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.rating-popover').fadeOut(150);
    });

    // Cerrar al hacer clic fuera (Solo si está visible)
    $(document).on('click', function (e) {
        if (window.innerWidth <= 991 && $('.rating-popover').is(':visible')) {
            if (!$(e.target).closest('.rating-popover').length && !$(e.target).closest('.rating-trigger').length) {
                $('.rating-popover').fadeOut(150);
            }
        }
    });

    // VARIABLES GLOBALES
    let totalCarrito = 0;
    let cantidadInicialCargada = 1;
    let countdownInterval = null;
    let horaCorteData = null;
    let productosAgregadosModal = []; // Track de productos agregados en la sesion del modal

    // ============================================
    // BOTONES AUMENTAR/DISMINUIR CANTIDAD
    // ============================================
    function aplicarPrecioVolumenDetalle() {
        const baseEl = document.getElementById('precio_unitario_base');
        const umbralEl = document.getElementById('oferta_maxima_cantidad');
        const volPrecioEl = document.getElementById('oferta_maxima_cantidad_por_precio');
        const qtyEl = document.getElementById('txt_cantidad');
        const precioTxt = document.getElementById('txtPrecio');
        const subtotalEl = document.getElementById('detalle_subtotal_linea');
        if (!baseEl || !qtyEl) return;

        const base = parseFloat(baseEl.value) || 0;
        const packQty = umbralEl && umbralEl.value !== '' ? parseInt(umbralEl.value, 10) : NaN;
        const packPrice = volPrecioEl && volPrecioEl.value !== '' ? parseFloat(volPrecioEl.value) : NaN;

        let rawQty = parseInt(qtyEl.value, 10);
        if (isNaN(rawQty) || rawQty < 1) rawQty = 1;
        if (rawQty > 1000) rawQty = 1000;
        const qty = rawQty;

        // Pack xN: packs completos a packPrice + unidades sueltas a precio base
        // Ej. 2x70 c/u 40 → 1=40, 2=70, 3=110, 4=140, 5=180
        let lineTotal = base * qty;
        let unitMostrar = base;
        let hasPack = false;
        if (!isNaN(packQty) && !isNaN(packPrice) && packQty >= 1 && packPrice > 0 && base > 0) {
            const packs = Math.floor(qty / packQty);
            const resto = qty % packQty;
            lineTotal = (packs * packPrice) + (resto * base);
            unitMostrar = qty > 0 ? (lineTotal / qty) : base;
            hasPack = packs > 0;
        }

        if (precioTxt) {
            const t = (precioTxt.textContent || '').trim();
            const label = hasPack
                ? ('S/ ' + unitMostrar.toFixed(2) + ' c/u')
                : ('S/ ' + unitMostrar.toFixed(2));
            if (t.indexOf('S/') === 0) {
                precioTxt.textContent = label;
            } else {
                precioTxt.textContent = unitMostrar.toFixed(2);
            }
        }
        if (subtotalEl) {
            subtotalEl.textContent = 'S/ ' + lineTotal.toFixed(2);
        }

        let hint = document.getElementById('sd-pack-hint');
        if (!hint && (packQty >= 1 && packPrice > 0)) {
            hint = document.createElement('p');
            hint.id = 'sd-pack-hint';
            hint.style.cssText = "font-family:'Montserrat',sans-serif;font-size:11px;color:#c0392b;margin:4px 0 0;font-weight:600;";
            if (subtotalEl && subtotalEl.parentNode) {
                subtotalEl.parentNode.appendChild(hint);
            }
        }
        if (hint) {
            if (hasPack && !isNaN(packQty) && !isNaN(packPrice)) {
                const packs = Math.floor(qty / packQty);
                const resto = qty % packQty;
                let txt = 'Oferta ' + packQty + 'x aplicada (' + packs + ' pack' + (packs > 1 ? 's' : '') + ')';
                if (resto > 0) {
                    txt += ' + ' + resto + ' unidad' + (resto > 1 ? 'es' : '') + ' a precio normal';
                }
                hint.textContent = txt;
                hint.style.display = '';
            } else if (!isNaN(packQty) && !isNaN(packPrice) && packQty >= 1) {
                hint.textContent = 'Lleva ' + packQty + ' por S/ ' + packPrice.toFixed(2);
                hint.style.display = '';
            } else {
                hint.style.display = 'none';
            }
        }
    }

    $(document).on('click', '.btn-plus', function () {
        const input = $('#txt_cantidad');
        let valor = parseInt(input.val()) || 1;
        if (valor < 1000) {
            input.val(valor + 1).trigger('change');
            validarEnvioGratis();
            aplicarPrecioVolumenDetalle();
        }
    });

    $(document).on('click', '.btn-minus', function () {
        const input = $('#txt_cantidad');
        let valor = parseInt(input.val()) || 1;
        if (valor > 1) {
            input.val(valor - 1).trigger('change');
            validarEnvioGratis();
            aplicarPrecioVolumenDetalle();
        }
    });

    aplicarPrecioVolumenDetalle();
    window.aplicarPrecioVolumenDetalle = aplicarPrecioVolumenDetalle;

    // ============================================
    // RESPONSIVE - SIDEBAR
    // ============================================
    function aplicarResponsive() {
        if ($(window).width() > 768) {
            $(".clsShopDetallProductoSider").addClass("slide-track");
        } else {
            $(".clsShopDetallProductoSider").removeClass("slide-track");
            $('.clsShopDetallProductoSider').removeAttr('style');
        }
    }
    aplicarResponsive();
    $(window).resize(function () {
        aplicarResponsive();
    });

    // ============================================
    // SCROLL SIDEBAR (solo layout legacy)
    // ============================================
    var $siderEl = $('.sider');
    var $footerEl = $('footer');
    var $slideTrack = $('.slide-track');
    if ($slideTrack.length && $siderEl.length && $siderEl.offset() && $footerEl.length && $footerEl.offset()) {
        var $cart = $slideTrack,
            $cartOffset = $siderEl.offset().top,
            $cartHeight = $cart.outerHeight(),
            $win = $(window),
            $footerOffset = $footerEl.offset().top;

        $win.scroll(function () {
            var $scrollTop = $win.scrollTop();
            if ($scrollTop >= $cartOffset) {
                $cart.css({
                    position: 'fixed',
                    top: '5rem',
                    marginRight: '5rem',
                    heigth: '35rem'
                });
            } else if ($cart.css('position') === 'fixed') {
                $cart.css({
                    position: '',
                    marginRight: '0',
                });
            }
            if ($scrollTop >= $footerOffset - $cartHeight) {
                $cart.css({
                    position: '',
                    bottom: '0',
                    top: '',
                    marginRight: '5rem',
                });
            }
        });
    }

    // ============================================
    // CAMBIO DE VARIACIÓN DE PRODUCTO
    // ============================================
    let btnImagenPrecio = document.querySelectorAll('.btnImagenPrecio');
    btnImagenPrecio.forEach(element => {
        element.addEventListener('click', function () {
            let txtCodigo = document.querySelector('#txtCodigo');
            let txtPrecio = document.querySelector('#txtPrecio');
            let codigo_producto = document.querySelector('#codigo_producto');
            let txtDescripcion = document.querySelector('#txtDescripcion');
            let txtTitulo = document.querySelector('#txtTitulo');

            if (txtPrecio) txtPrecio.innerHTML = element.getAttribute('isPrecio');
            if (codigo_producto) codigo_producto.value = element.getAttribute('isCodigoProducto');
            if (txtCodigo) txtCodigo.innerHTML = element.getAttribute('isCodigoProducto');
            if (txtDescripcion) txtDescripcion.innerHTML = element.getAttribute('isDescripcion');
            if (txtTitulo) txtTitulo.innerHTML = element.getAttribute('isTitulo');

            const baseP = element.getAttribute('data-precio-base');
            const volC = element.getAttribute('data-volumen-cantidad');
            const volP = element.getAttribute('data-volumen-precio');
            const pb = document.getElementById('precio_unitario_base');
            const oc = document.getElementById('oferta_maxima_cantidad');
            const op = document.getElementById('oferta_maxima_cantidad_por_precio');
            if (pb && baseP) pb.value = baseP;
            if (oc) oc.value = volC !== null && volC !== undefined ? volC : '';
            if (op) op.value = volP !== null && volP !== undefined ? volP : '';

            const rating = element.getAttribute('isCantidadEstrellas') || 0;
            const label = `${parseFloat(rating).toFixed(1)} Vendido por Valento. <span style="margin-left:10px;color:#1976d2;cursor:pointer;" title="¡Disponible en stock!"><i class="fas fa-info-circle"></i></span>`;
            renderStarRating(rating, label, '#txtEstrellas');

            setTimeout(function () {
                validarEnvioGratis();
                aplicarPrecioVolumenDetalle();
            }, 200);
        });
    });

    // ============================================
    // RENDER ESTRELLAS
    // ============================================
    function renderStarRating(rating, label, containerSelector) {
        rating = parseFloat(rating) || 0;
        const fullStars = Math.floor(rating);
        const halfStar = (rating - fullStars) >= 0.5 ? 1 : 0;
        const emptyStars = 5 - fullStars - halfStar;

        let html = '<div class="product-rating">';
        for (let i = 0; i < fullStars; i++) {
            html += '<i class="fas fa-star text-warning"></i>';
        }
        if (halfStar) {
            html += '<i class="fas fa-star-half-alt text-warning"></i>';
        }
        for (let i = 0; i < emptyStars; i++) {
            html += '<i class="far fa-star"></i>';
        }
        if (label) {
            html += `<span class="icon-tooltip">${label}</span>`;
        }
        html += '</div>';
        document.querySelector(containerSelector).innerHTML = html;
    }

    // ============================================
    // FUNCIÓN PARA EXTRAER PRECIO
    // ============================================
    function extraerPrecio(selectorOElemento) {
        const elemento = typeof selectorOElemento === 'string'
            ? document.querySelector(selectorOElemento)
            : selectorOElemento;

        if (!elemento) return 0;

        const texto = elemento.textContent || elemento.innerText;
        const precio = texto.replace(/[^\d.]/g, '');

        return parseFloat(precio) || 0;
    }

    // ============================================
    // OBTENER TOTAL DEL CARRITO DESDE EL SERVIDOR
    // ============================================
    function obtenerTotalCarrito() {
        return fetch('/web_shopDetail_total_carrito', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                totalCarrito = parseFloat(data.total) || 0;
                return totalCarrito;
            })
            .catch(error => {
                console.error('Error al obtener total del carrito:', error);
                return 0;
            });
    }

    // ============================================
    // HINT DE DELIVERY (reemplaza lógica S/199 antigua)
    // ============================================
    function validarEnvioGratis() {
        // No sobrescribir si el usuario ya calculó el costo de envío
        if (window.DEL_DELIVERY_CALCULADO) return;
        if (window.DEL_HINT_DELIVERY) {
            $('.mensajeLlevaloDesde').html(window.DEL_HINT_DELIVERY);
        }
    }
    window.validarEnvioGratis = validarEnvioGratis;

    $('#txt_cantidad').on('input', function () {
        let val = this.value;
        // Permitir vacío temporalmente mientras el usuario escribe
        if (val === '' || val === '-') return;
        let num = parseInt(val);
        // Si el valor es 0 o inválido, limpiar el campo para que el usuario escriba
        if (isNaN(num) || num < 1) {
            this.value = '';
            return;
        }
        if (num > 1000) this.value = 1000;
        validarEnvioGratis();
        aplicarPrecioVolumenDetalle();
    });

    $('#txt_cantidad').on('blur', function () {
        let num = parseInt(this.value);
        if (isNaN(num) || num < 1) this.value = 1;
        if (num > 1000) this.value = 1000;
        validarEnvioGratis();
        aplicarPrecioVolumenDetalle();
    });

    $('#txt_cantidad').on('change', function () {
        validarEnvioGratis();
        aplicarPrecioVolumenDetalle();
    });

    $('.btnImagenPrecio').on('click', function () {
        setTimeout(() => {
            const cantidadInput = document.querySelector('#txt_cantidad');
            if (cantidadInput) {
                cantidadInicialCargada = parseInt(cantidadInput.value) || 1;
            }
            validarEnvioGratis();
        }, 200);
    });

    obtenerTotalCarrito().then(() => {
        const cantidadInput = document.querySelector('#txt_cantidad');
        if (cantidadInput) {
            cantidadInicialCargada = parseInt(cantidadInput.value) || 1;
        }
        validarEnvioGratis();
        aplicarPrecioVolumenDetalle();
    });

    // ============================================
    // PRODUCTOS SUGERIDOS - FUNCIONES
    // ============================================
    function cargarProductosSugeridos(idProducto) {
        const section = $('#sugeridosSection');
        const carousel = $('#sugeridosCarousel');
        carousel.empty();
        section.hide();

        console.log('Cargando sugeridos para producto:', idProducto);

        fetch('/web_shopDetail_sugeridos/' + idProducto, {
            headers: { 'Accept': 'application/json' }
        })
            .then(response => {
                console.log('Respuesta sugeridos status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Datos sugeridos recibidos:', data);
                if (!data.sugeridos || data.sugeridos.length === 0) {
                    console.log('No hay productos sugeridos');
                    return;
                }

                data.sugeridos.forEach(function (prod, index) {
                    const nombreProducto = prod.nombre || 'Producto';
                    const precioOld = prod.precio_old && parseFloat(prod.precio_old) > parseFloat(prod.precio)
                        ? `<span class="sugerido-precio-old">S/ ${parseFloat(prod.precio_old).toFixed(2)}</span>` : '';
                    const descuento = prod.precio_old && parseFloat(prod.precio_old) > parseFloat(prod.precio)
                        ? `<span class="sugerido-descuento">-${Math.round((1 - prod.precio / prod.precio_old) * 100)}%</span>` : '';

                    const estrellas = generarEstrellasSugerido(prod.display_rating || prod.numero_estrellas || 0);

                    const card = `
                    <div class="sugerido-card" style="animation-delay: ${index * 0.08}s">
                        <a href="/web_shopDetail/${prod.slug || prod.id_producto}" class="sugerido-img-link">
                            <img src="/${prod.url_imagen}" alt="${nombreProducto}" class="sugerido-img" loading="lazy">
                        </a>
                        ${descuento}
                        <div class="sugerido-info">
                            <div class="sugerido-estrellas">${estrellas}</div>
                            <a href="/web_shopDetail/${prod.slug || prod.id_producto}" class="sugerido-nombre" title="${nombreProducto}">
                                ${nombreProducto.length > 40 ? nombreProducto.substring(0, 40) + '...' : nombreProducto}
                            </a>
                            <div class="sugerido-precios">
                                ${precioOld}
                                <span class="sugerido-precio">S/ ${parseFloat(prod.precio).toFixed(2)}</span>
                            </div>
                            <button type="button" class="sugerido-btn-agregar"
                                    data-codigo="${prod.codigo_producto}"
                                    data-id="${prod.id_producto}"
                                    data-nombre="${nombreProducto}"
                                    data-precio="${prod.precio}">
                                <i class="fas fa-cart-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                `;
                    carousel.append(card);
                });

                section.fadeIn(300);
                inicializarNavSugeridos();
            })
            .catch(function (error) {
                console.error('Error al cargar sugeridos:', error);
            });
    }
    window.cargarProductosSugeridos = cargarProductosSugeridos;

    function generarEstrellasSugerido(rating) {
        rating = parseFloat(rating) || 0;
        const full = Math.floor(rating);
        const half = (rating - full) >= 0.5 ? 1 : 0;
        const empty = 5 - full - half;
        let html = '';
        for (let i = 0; i < full; i++) html += '<i class="fas fa-star"></i>';
        if (half) html += '<i class="fas fa-star-half-alt"></i>';
        for (let i = 0; i < empty; i++) html += '<i class="far fa-star"></i>';
        return html;
    }

    function inicializarNavSugeridos() {
        const carousel = document.getElementById('sugeridosCarousel');
        if (!carousel) return;
        const scrollAmount = 220;

        $('#sugeridosPrev').off('click').on('click', function () {
            carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        $('#sugeridosNext').off('click').on('click', function () {
            carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }

    // Agregar producto sugerido al carrito
    $(document).on('click', '.sugerido-btn-agregar', function () {
        const btn = $(this);
        const codigoProducto = btn.data('codigo');
        const nombreProducto = btn.data('nombre');
        const precioProducto = btn.data('precio');

        if (btn.hasClass('sugerido-agregado')) return;

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Agregando...');

        let formData = new FormData();
        formData.append('codigo_producto', codigoProducto);
        formData.append('cantidad', 1);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        axios.post('/web_shopDetail_agregar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
            .then(function (response) {
                if (response.data.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock',
                        text: response.data.message || 'No se pudo añadir el producto.',
                    });
                    btn.prop('disabled', false);
                    btn.html('<i class="fas fa-cart-plus"></i> Agregar');
                    return;
                }
                if (response.data.status === 'success') {
                    btn.addClass('sugerido-agregado');
                    btn.html('<i class="fas fa-check"></i> Agregado');

                    if (response.data.result && response.data.result.total_carrito) {
                        totalCarrito = parseFloat(response.data.result.total_carrito);
                    }

                    // Agregar producto sugerido a la lista del modal
                    const imgSugerido = btn.closest('.sugerido-card').find('.sugerido-img').attr('src') || '';
                    productosAgregadosModal.push({
                        nombre: nombreProducto,
                        precio: parseFloat(precioProducto) || 0,
                        cantidad: 1,
                        imagen: imgSugerido,
                        subtotal: parseFloat(precioProducto) || 0
                    });

                    // Cambiar a vista multiple
                    renderizarVistaMultiple();

                    $('.clsDetalle_CountCart').text(response.data.result["detalle_cantidad"]);
                    $('.clsDescripcion_Igv').text('S/' + response.data.result["descripcion_t_igv"]);
                    $('.clsDetalle_Total').text('S/' + response.data.result["descripcion_t_total"]);

                    // Actualizar header del carrito si existe
                    if (typeof actualizarContadorCarritoHeader === 'function') {
                        actualizarContadorCarritoHeader();
                    }
                }
            })
            .catch(function (error) {
                console.error('Error al agregar sugerido:', error);
                var serverMsg = error.response && error.response.data && error.response.data.message
                    ? error.response.data.message
                    : null;
                if (serverMsg) {
                    Swal.fire({ icon: 'error', title: 'Error', text: serverMsg });
                }
                btn.prop('disabled', false);
                btn.html('<i class="fas fa-cart-plus"></i> Agregar');
            });
    });

    // ============================================
    // RENDERIZAR VISTA MULTIPLE EN MODAL
    // ============================================
    function renderizarVistaMultiple() {
        if (productosAgregadosModal.length <= 1) return;

        // Ocultar vista individual, mostrar multiple
        $('#modalVistaSingle').hide();
        $('#modalVistaMultiple').show();
        $('#modalCantidadProductos').text(productosAgregadosModal.length);

        let html = '';
        productosAgregadosModal.forEach(function (prod, index) {
            html += `
                <div class="modal-producto-item ${index === productosAgregadosModal.length - 1 ? 'modal-producto-nuevo' : ''}">
                    <div class="modal-producto-img">
                        <img src="${prod.imagen}" alt="${prod.nombre}">
                    </div>
                    <div class="modal-producto-info">
                        <span class="modal-producto-nombre">${prod.nombre}</span>
                        <span class="modal-producto-qty">Cant: ${prod.cantidad}</span>
                    </div>
                    <div class="modal-producto-precio">
                        <span>S/ ${prod.precio.toFixed(2)}</span>
                    </div>
                </div>
            `;
        });

        $('#modalProductosLista').html(html);
    }

    // ============================================
    // AGREGAR AL CARRITO — manejado en liam-cart.js (servidor)
    // ============================================

    // ============================================
    // GALERÍA DE IMÁGENES
    // ============================================
    const $thumbWrapper = $('.modern-thumbs-wrapper');
    const $thumbItems = $('.modern-thumb');
    const $thumbLinks = $('.modern-thumbs-wrapper .modern-thumb-link');
    let currentPosition = 0;

    // Recopilar imágenes del producto para lightbox
    const galleryImages = [];
    $('.modern-thumbs-wrapper a[data-src]').each(function () {
        galleryImages.push($(this).data('src'));
    });
    let lbCurrentIndex = 0;

    // Click en miniatura → actualizar imagen principal
    $('.modern-thumbs-wrapper a[data-src]').on('click', function (e) {
        e.preventDefault();
        const src = $(this).data('src');
        const idx = parseInt($(this).data('index')) || 0;
        $('#product-zoom').attr('src', src);
        $thumbLinks.removeClass('active-thumb');
        $(this).addClass('active-thumb');
        
        // elevateZoom (main.js) añade clase "active" al <a> del gallery; dejar solo uno
        $(this).closest('.modern-thumbs-wrapper').find('a').removeClass('active');
        $(this).addClass('active');
        lbCurrentIndex = idx;
    });

    // Desactivamos navegación vertical antigua

    // ============================================
    // LIGHTBOX (overlay puro, sin Bootstrap modal)
    // ============================================
    function lbOpen(index) {
        if (!galleryImages.length) return;
        lbCurrentIndex = Math.max(0, Math.min(index, galleryImages.length - 1));
        lbRender();
        $('#productLightboxModal').addClass('lb-active');
        $('body').addClass('lb-lightbox-open').css('overflow', 'hidden');
        $('.zoomContainer, .zoomWindowContainer').hide();
    }

    function lbClose() {
        $('#productLightboxModal').removeClass('lb-active');
        $('body').removeClass('lb-lightbox-open').css('overflow', '');
    }

    function lbRender() {
        const src = galleryImages[lbCurrentIndex];
        $('#lightboxMainImg').css('opacity', 0).attr('src', src).animate({ opacity: 1 }, 180);
        $('#lbCounter').text((lbCurrentIndex + 1) + ' / ' + galleryImages.length);

        let thumbsHtml = '';
        galleryImages.forEach(function (imgSrc, i) {
            thumbsHtml += `<div class="lightbox-thumb-item${i === lbCurrentIndex ? ' active' : ''}" data-lb-index="${i}">
                <img src="${imgSrc}" alt="Miniatura ${i + 1}">
            </div>`;
        });
        $('#lbThumbsStrip').html(thumbsHtml);
    }

    // Abrir lightbox — en detalle Liam (.sd-main-img-wrap) lo maneja el script de la vista
    if (!$('.sd-main-img-wrap').length) {
        $('#product-zoom, #btn-product-gallery').on('click', function (e) {
            e.preventDefault();
            lbOpen(lbCurrentIndex);
        });
    }

    // Navegar lightbox
    $('#lbPrev').on('click', function (e) {
        e.stopPropagation();
        lbOpen(lbCurrentIndex > 0 ? lbCurrentIndex - 1 : galleryImages.length - 1);
    });
    $('#lbNext').on('click', function (e) {
        e.stopPropagation();
        lbOpen(lbCurrentIndex < galleryImages.length - 1 ? lbCurrentIndex + 1 : 0);
    });

    // Cerrar con botón X
    $('#lbCloseBtn').on('click', function (e) {
        e.stopPropagation();
        lbClose();
    });

    // Cerrar al hacer clic en el fondo oscuro
    $('#productLightboxModal').on('click', function (e) {
        if ($(e.target).is('#productLightboxModal')) {
            lbClose();
        }
    });

    // Click en miniaturas del lightbox
    $(document).on('click', '.lightbox-thumb-item', function (e) {
        e.stopPropagation();
        lbOpen(parseInt($(this).data('lb-index')));
    });

    // Navegación con teclado
    $(document).on('keydown', function (e) {
        if (!$('#productLightboxModal').hasClass('lb-active')) return;
        if (e.key === 'ArrowLeft') lbOpen(lbCurrentIndex > 0 ? lbCurrentIndex - 1 : galleryImages.length - 1);
        if (e.key === 'ArrowRight') lbOpen(lbCurrentIndex < galleryImages.length - 1 ? lbCurrentIndex + 1 : 0);
        if (e.key === 'Escape') lbClose();
    });

    // ============================================
    // CÁLCULO DE ENVÍO
    // ============================================
    $('#txt_listar_distritos, #txt_listar_departamentos').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    function cargarDistritos(idDepartamento) {
        const $districtSelect = $('#txt_listar_distritos');

        if (!idDepartamento) {
            $districtSelect.prop('disabled', true).val('').trigger('change');
            return;
        }

        if (idDepartamento === 'Lima Metropolitana') {
            $('#idTiempoEntrega').show();
        } else {
            $('#idTiempoEntrega').hide();
        }

        $districtSelect.prop('disabled', true);
        $districtSelect.html('<option value="">Cargando distritos...</option>');

        $.ajax({
            url: `/web_calcular_envio/${encodeURIComponent(idDepartamento)}/lista_distritos`,
            method: 'GET',
            dataType: 'json',
            success: function (result) {
                const arrays = Array.isArray(result) ? result : (typeof result === 'string' ? JSON.parse(result) : []);
                let options = '<option value="" selected disabled>Seleccione...</option>';
                arrays.forEach(dataRow => {
                    options += `<option value="${dataRow.address_distrito}">${dataRow.address_distrito}</option>`;
                });
                $districtSelect.html(options).prop('disabled', false);
            },
            error: function () {
                showError('No se pudieron cargar los distritos');
                $districtSelect.html('<option value="" selected disabled>Error al cargar</option>');
            }
        });
    }

    $('#txt_listar_departamentos').on('change', function () {
        cargarDistritos($(this).val());
    });

    const initialDepto = $('#txt_listar_departamentos').val();
    if (initialDepto) {
        cargarDistritos(initialDepto);
    }

    function toggleLoading(show) {
        if (show) {
            $('#calculateBtn').prop('disabled', true);
            $('#calculateBtn span').removeClass('d-none');
        } else {
            $('#calculateBtn').prop('disabled', false);
            $('#calculateBtn span').addClass('d-none');
        }
    }

    $('#formCalcularEnvio').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        toggleLoading(true);
        $('#shippingResults').addClass('d-none');
        $('#shippingResultsPlaceholder').removeClass('d-none');

        let formData = new FormData(this);
        formData.append('address_departamento', $('#txt_listar_departamentos').val() || null);
        formData.append('address_distrito', $('#txt_listar_distritos').val() || null);
        var _pb  = parseFloat($('#precio_unitario_base').val()) || 0;
        var _qty = parseInt($('#txt_cantidad').val()) || 1;
        formData.append('subtotal_carrito', (_pb * _qty).toFixed(2));
        formData.append('lat_cliente', $('#del_lat_cliente').val() || '');
        formData.append('lng_cliente', $('#del_lng_cliente').val() || '');

        axios.post('/web_calcular_envio/calcular_envio', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        })
            .then(function (response) {
                console.log('Respuesta del servidor:', response.data);
                updateShippingResults(response.data);
                if (typeof window.procesarResultadoDelivery === 'function') {
                    window.procesarResultadoDelivery(response.data);
                }
                toggleLoading(false);
            })
            .catch(function (error) {
                console.error('Error:', error.response ? error.response.data : error);
                showError('Ocurrió un error al calcular el envío');
                toggleLoading(false);
            });
    });
    // ============================================
    // ACTUALIZAR RESULTADOS DE ENVÍO (CORREGIDO)
    // ============================================
    function updateShippingResults(data) {
        const txt_dia_entrega = $('#txt_dia_entrega').val() || '';
        // ✅ LIMPIAR CONTENIDO PREVIO PARA EVITAR DUPLICACIÓN
        $('.costoEnvio').empty();
        $('.lugarEnvio').empty();

        let html = '';
        let html_Lugar = '';
        let html_Lugar_agencia = '';

        let html_domicilio_recibelo_hoy = '';
        let html_domicilio_empresa = '';
        let contacto_direccion = data.contacto_direccion;
        let distrito = data.distrito || '';
        let precio_envio = parseFloat(data.precio_envio) || 0;
        let pago_contra_entrega = data.pago_contra_entrega || '';
        let address_departamento = data.address_departamento || '';

        // ============================================
        // ✅ HTML BASE CON SECCIÓN DE ENVÍO A DOMICILIO
        // ============================================
        html += `<div class="shipping-section">
                    <div class="shipping-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13" />
                            <polygon points="16,8 20,8 23,11 23,16 16,16 16,8" />
                            <circle cx="5.5" cy="18.5" r="2.5" />
                            <circle cx="18.5" cy="18.5" r="2.5" />
                        </svg>
                    </div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Envío a domicilio <span class="location ">a ${distrito} por S/${precio_envio.toFixed(2)}</span>
                        </h3>
                    </div>
                </div>`;

        // ============================================
        // ✅ MENSAJE DE PAGO CONTRAENTREGA
        // ============================================
        html += `${pago_contra_entrega === '1' || pago_contra_entrega === 1 ? `<p class="clsMensajePageAlEntrega mb-1">Puedes pagar al momento de la entrega</p>` : ""}`;

        // ============================================
        // ✅ HTML PARA UBICACIÓN - VALIDACIÓN DE PROVINCIA
        // ============================================
        // Opción 1: Si es Lima Metropolitana
        if (address_departamento === 'Lima Metropolitana') {
            html_Lugar = `<div class="shipping-section">
                    <div class="shipping-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 0 24 24" id="map-marker-question"><path fill="#6563FF" d="M12.44,13.11,12.27,13a1,1,0,0,0-1.09.22.87.87,0,0,0-.22.32,1,1,0,0,0-.08.39,1,1,0,0,0,.08.38,1.07,1.07,0,0,0,.54.54,1,1,0,0,0,.38.08,1.09,1.09,0,0,0,.39-.08,1,1,0,0,0,.32-.22,1,1,0,0,0,0-1.41ZM11.88,6A2.75,2.75,0,0,0,9.5,7.32a1,1,0,1,0,1.73,1A.77.77,0,0,1,11.88,8a.75.75,0,1,1,0,1.5,1,1,0,1,0,0,2,2.75,2.75,0,1,0,0-5.5Zm8.58,3.68A8.5,8.5,0,0,0,7.3,3.36,8.56,8.56,0,0,0,3.54,9.63,8.46,8.46,0,0,0,6,16.46l5.3,5.31a1,1,0,0,0,1.42,0L18,16.46A8.46,8.46,0,0,0,20.46,9.63ZM16.6,15.05,12,19.65l-4.6-4.6A6.49,6.49,0,0,1,5.53,9.83,6.57,6.57,0,0,1,8.42,5a6.47,6.47,0,0,1,7.16,0,6.57,6.57,0,0,1,2.89,4.81A6.49,6.49,0,0,1,16.6,15.05Z"></path></svg>
                    </div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">${contacto_direccion}</h3>
                    </div>
                </div>`;
        }
        // Opción 2: Si es FUERA de Lima Metropolitana (Provincia)
        else {
            // ✅ PROVINCIA: Solo mostrar agencias (SIN dirección de Lima)
            html_Lugar = `<div class="pickup-section">
                        <div class="pickup-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                        </div>
                        <div class="pickup-content">
                            <h3>Recoge y paga el envío en la agencia <strong>Shalom</strong> <span class="location">${address_departamento}</span></h3>
                            <a href="https://agencias.shalom.pe/" target="_blank" class="location-link">Ver ubicaciones y horarios</a>
                        </div>
                    </div>`;

            html_Lugar += `<div class="pickup-section">
                        <div class="pickup-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                        </div>
                        <div class="pickup-content">
                            <h3>Recoge y paga el envío en la agencia <strong>Marvisur</strong> <span class="location">${address_departamento}</span></h3>
                            <a href="https://www.expresomarvisur.com/sucursales" target="_blank" class="location-link">Ver ubicaciones y horarios</a>
                        </div>
                    </div>`;
        }

        // ============================================
        // ✅ LÓGICA DE ENVÍO A DOMICILIO (CON VALIDACIÓN)
        // ============================================
        // if (data.hora_corte_procesada) {
        // const tipoEntrega = data.hora_corte_procesada.tipo;
        const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg"  height="25px" viewBox="0 0 24 24" id="rocket"><path fill="#3d007eff" d="M22.601 2.062a1 1 0 0 0-.713-.713A11.252 11.252 0 0 0 10.47 4.972L9.354 6.296 6.75 5.668a2.777 2.777 0 0 0-3.387 1.357l-2.2 3.9a1 1 0 0 0 .661 1.469l3.073.659a13.42 13.42 0 0 0-.555 2.434 1 1 0 0 0 .284.836l3.1 3.1a1 1 0 0 0 .708.293c.028 0 .057-.001.086-.004a12.169 12.169 0 0 0 2.492-.49l.644 3.004a1 1 0 0 0 1.469.661l3.905-2.202a3.035 3.035 0 0 0 1.375-3.304l-.668-2.76 1.237-1.137A11.204 11.204 0 0 0 22.6 2.062ZM3.572 10.723l1.556-2.76a.826.826 0 0 1 1.07-.375l1.718.416-.65.772a13.095 13.095 0 0 0-1.59 2.398Zm12.47 8.222-2.715 1.532-.43-2.005a11.34 11.34 0 0 0 2.414-1.62l.743-.683.404 1.664a1.041 1.041 0 0 1-.416 1.112Zm1.615-6.965-3.685 3.386a9.773 9.773 0 0 1-5.17 2.304l-2.405-2.404a10.932 10.932 0 0 1 2.401-5.206l1.679-1.993a.964.964 0 0 0 .078-.092L11.99 6.27a9.278 9.278 0 0 1 8.81-3.12 9.218 9.218 0 0 1-3.143 8.829Zm-.923-6.164a1.5 1.5 0 1 0 1.5 1.5 1.5 1.5 0 0 0-1.5-1.5Z"></path></svg>`;

        if (txt_dia_entrega === 'hoy') {
            // Caso 1: Recíbelo HOY
            html_domicilio_recibelo_hoy = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Recíbelo Hoy por S/${precio_envio.toFixed(2)}</h3>
                    </div>
                </div>`;
        }
        else if (txt_dia_entrega === 'lunes') {
            // Caso 2: Llega MAÑANA
            html_domicilio_recibelo_hoy = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Llega el Lunes por S/${precio_envio.toFixed(2)}</h3>
                    </div>
                </div>`;
        }
        else if (txt_dia_entrega === 'mañana') {
            // Caso 2: Llega MAÑANA
            html_domicilio_recibelo_hoy = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Llega mañana por S/${precio_envio.toFixed(2)}</h3>
                    </div>
                </div>`;
        }

        // }

        // ============================================
        // ✅ LÓGICA DE RETIRO EN TIENDA (CON VALIDACIÓN)
        // ============================================
        if (data.hora_corte_procesada) {
            const tipoRetiro = data.hora_corte_procesada.tipo;
            const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 0 24 24" id="map-marker-question"><path fill="#6563FF" d="M12.44,13.11,12.27,13a1,1,0,0,0-1.09.22.87.87,0,0,0-.22.32,1,1,0,0,0-.08.39,1,1,0,0,0,.08.38,1.07,1.07,0,0,0,.54.54,1,1,0,0,0,.38.08,1.09,1.09,0,0,0,.39-.08,1,1,0,0,0,.32-.22,1,1,0,0,0,0-1.41ZM11.88,6A2.75,2.75,0,0,0,9.5,7.32a1,1,0,1,0,1.73,1A.77.77,0,0,1,11.88,8a.75.75,0,1,1,0,1.5,1,1,0,1,0,0,2,2.75,2.75,0,1,0,0-5.5Zm8.58,3.68A8.5,8.5,0,0,0,7.3,3.36,8.56,8.56,0,0,0,3.54,9.63,8.46,8.46,0,0,0,6,16.46l5.3,5.31a1,1,0,0,0,1.42,0L18,16.46A8.46,8.46,0,0,0,20.46,9.63ZM16.6,15.05,12,19.65l-4.6-4.6A6.49,6.49,0,0,1,5.53,9.83,6.57,6.57,0,0,1,8.42,5a6.47,6.47,0,0,1,7.16,0,6.57,6.57,0,0,1,2.89,4.81A6.49,6.49,0,0,1,16.6,15.05Z"></path></svg>`;

            if (tipoRetiro === 'hoy') {
                // Caso 1: Retíralo INMEDIATAMENTE (hoy)
                html_domicilio_empresa = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Retíralo inmediatamente en ${contacto_direccion}</h3>
                    </div>
                </div>`;
            } else if (tipoRetiro === 'manana') {
                // Caso 2: Retíralo MAÑANA
                html_domicilio_empresa = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Retíralo mañana en ${contacto_direccion}</h3>
                    </div>
                </div>`;
            } else if (data.hora_corte_procesada.dia_nombre) {
                // Caso 3: Retíralo el [DÍA ESPECÍFICO] (ej: Miércoles)
                html_domicilio_empresa = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Retíralo el ${data.hora_corte_procesada.dia_nombre} en ${contacto_direccion}</h3>
                    </div>
                </div>`;
            } else {
                // Fallback: Retiro sin fecha específica
                html_domicilio_empresa = `<div class="shipping-section">
                    <div class="shipping-icon">${svgIcon}</div>
                    <div class="shipping-content">
                        <h3 style="margin-bottom: -5px;">Retíralo en ${contacto_direccion}</h3>
                    </div>
                </div>`;
            }
        }

        // ============================================
        // ✅ AGREGAR HTML AL DOM (SIN DUPLICACIÓN)
        // ============================================
        $('.costoEnvio').html(html);

        // ============================================
        // ✅ LÓGICA MEJORADA: Mostrar opciones según departamento
        // ============================================
        if (address_departamento === 'Lima Metropolitana') {
            // LIMA METROPOLITANA: Mostrar retiro en tienda según hora_regresiva_descripcion
            if (data.hora_regresiva_descripcion !== 'Retíralo por agencia shalom o marvisur') {
                // Lima + hora de corte activa: Mostrar "Recíbelo Hoy" + "Retíralo"
                $('.lugarEnvio').html(html_domicilio_recibelo_hoy + html_domicilio_empresa);

            } else {

                if (data.hora_regresiva_descripcion === 'Retíralo por agencia shalom o marvisur') {

                    // ✅ PROVINCIA: Solo mostrar agencias (SIN dirección de Lima)
                    html_Lugar_agencia = `<div class="pickup-section">
                        <div class="pickup-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                        </div>
                        <div class="pickup-content">
                            <h3>Recoge y paga el envío en la agencia <strong>Shalom</strong> <span class="location">${address_departamento}</span></h3>
                            <a href="https://agencias.shalom.pe/" target="_blank" class="location-link">Ver ubicaciones y horarios</a>
                        </div>
                    </div>`;

                    html_Lugar_agencia += `<div class="pickup-section">
                        <div class="pickup-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                        </div>
                        <div class="pickup-content">
                            <h3>Recoge y paga el envío en la agencia <strong>Marvisur</strong> <span class="location">${address_departamento}</span></h3>
                            <a href="https://www.expresomarvisur.com/sucursales" target="_blank" class="location-link">Ver ubicaciones y horarios</a>
                        </div>
                    </div>`;
                } else {
                    html_Lugar_agencia = "";
                }

                // Lima + sin hora de corte: Solo ubicación genérica
                $('.lugarEnvio').html(html_domicilio_recibelo_hoy + html_Lugar_agencia);//Esta variable se utiliza siempre entodo:html_domicilio_recibelo_hoy

            }

        } else {
            // PROVINCIA: SIEMPRE mostrar agencias (Shalom + Marvisur)
            // También incluir las opciones de envío si existen
            if (data.hora_regresiva_descripcion !== 'Retíralo por agencia shalom o marvisur') {

                // Provincia con hora de corte: Mostrar "Recíbelo" + "Retíralo" + Agencias
                $('.lugarEnvio').html(html_domicilio_recibelo_hoy + html_domicilio_empresa + html_Lugar);
            } else {

                // Provincia sin hora de corte: Solo agencias
                $('.lugarEnvio').html(html_Lugar);
            }
        }

        // ============================================
        // ✅ PROCESAR HORA DE CORTE (CONTADOR)
        // ============================================
        if (data.hora_corte_procesada) {
            console.log('Datos de hora de corte:', data.hora_corte_procesada);
            inicializarCountdown(data.hora_corte_procesada, data.hora_regresiva_descripcion);
        }
    }

    // ============================================
    // SISTEMA DE COUNTDOWN CON VALIDACIÓN DE DÍAS HÁBILES
    // ============================================
    function inicializarCountdown(datosCorte, descripcion) {
        console.log('Inicializando countdown:', datosCorte, descripcion);

        if (!datosCorte) {
            console.log('No hay datos de corte');
            return;
        }

        horaCorteData = datosCorte;

        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        const container = $('.costoEnvio');
        let html = '';

        // ✅ CASO 1: Contador activo (dentro de ventana de 12 horas)
        if (datosCorte.tipo === 'hoy') {
            console.log('Mostrando contador para HOY');
            html = `
            <div class="clsComprandoLasProximasHoras">
                <p>Pídelo en las próximas</p>
                <span id="corte_tiempo_promocion" style="padding-top: 15px; font-weight: bold; color: #FF5722;">
                    <span id="countdown-horas">00</span>h <span id="countdown-minutos">00</span>m
                </span>
            </div>
        `;
            container.prepend(html);
            actualizarContador();
            countdownInterval = setInterval(actualizarContador, 1000);
        }
        // ✅ CASO 2: Llega MAÑANA (Lunes-Jueves después de 3pm)
        else if (datosCorte.tipo === 'manana') {
            html = `
            <label class="clsMensajeRetireAgencia mb-1" style="font-weight: bold; color: #4CAF50;">
                Llega Mañana ${datosCorte.dia} de ${datosCorte.mes}
            </label>
        `;
            container.prepend(html);
        }
        // ✅ CASO 3: Llega el LUNES (Viernes después de 3pm, Sábado o Domingo)
        else if (datosCorte.tipo === 'lunes') {
            html = `
            <label class="clsMensajeRetireAgencia mb-1" style="font-weight: bold; color: #4CAF50;">
                Llega el Lunes ${datosCorte.dia} de ${datosCorte.mes}
            </label>
        `;
            container.prepend(html);
        }
    }


    function actualizarContador() {
        const ahora = Math.floor(Date.now() / 1000);

        if (!horaCorteData) return;

        if (horaCorteData.tipo === 'hoy') {
            const tiempoRestante = horaCorteData.timestamp_corte - ahora;

            if (tiempoRestante <= 0) {
                // ✅ Hora de corte alcanzada, recargar desde el servidor
                console.log('Contador terminado, recargando datos del servidor...');
                recargarDatosEnvio();
                return;
            }

            const horas = Math.floor(tiempoRestante / 3600);
            const minutos = Math.floor((tiempoRestante % 3600) / 60);

            const horasElement = document.getElementById('countdown-horas');
            const minutosElement = document.getElementById('countdown-minutos');

            if (horasElement && minutosElement) {
                horasElement.textContent = horas.toString().padStart(2, '0');
                minutosElement.textContent = minutos.toString().padStart(2, '0');
            }
        }
    }

    // ✅ FUNCIÓN MEJORADA: Ya no necesita calcular días hábiles localmente
    // El servidor se encarga de calcular la fecha correcta considerando fin de semana
    function cambiarAManana() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        console.log('Cambiando a modo mañana, recargando desde servidor...');

        // ✅ IMPORTANTE: Recargar desde el servidor para obtener la fecha correcta
        // El servidor calculará automáticamente si cae en fin de semana
        recargarDatosEnvio();
    }

    function recargarDatosEnvio() {
        const departamento = $('#txt_listar_departamentos').val();
        const distrito = $('#txt_listar_distritos').val();
        const peso = $('input[name="txt_peso_kilogramo"]').val();
        const medidas = $('input[name="txt_paqueta_medidas"]').val();
        const dimension = $('input[name="txt_paquete_dimencion"]').val();

        if (departamento && distrito) {
            let formData = new FormData();
            formData.append('address_departamento', departamento);
            formData.append('address_distrito', distrito);
            formData.append('txt_peso_kilogramo', peso || '0');
            formData.append('txt_paqueta_medidas', medidas || '0');
            formData.append('txt_paquete_dimencion', dimension || '0');

            console.log('Recargando datos de envío desde el servidor...');

            axios.post('/web_calcular_envio/calcular_envio', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            })
                .then(function (response) {
                    console.log('Datos recargados:', response.data);
                    updateShippingResults(response.data);
                })
                .catch(function (error) {
                    console.error('Error al recargar datos de envío:', error);
                });
        }
    }

    $(window).on('beforeunload', function () {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
    });

    // ============================================
    // FUNCIÓN PARA MOSTRAR ERRORES
    // ============================================
    function showError(message) {
        const toast = `<div class="toast show align-items-center text-white bg-danger position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

        $('body').append(toast);
        setTimeout(() => $('.toast').remove(), 5000);
    }


    function updateRatingSummary(summary) {
        $('.rating-avg').text(parseFloat(summary.promedio).toFixed(1));
        $('.rating-count').text('(' + summary.total + ')');
        $('.popover-avg-text').text(parseFloat(summary.promedio).toFixed(1) + ' de 5');
        $('.popover-total-count').text(summary.total + ' calificaciones globales');

        for (let star in summary.estrellas) {
            const data = summary.estrellas[star];
            $('.popover-bar-row').each(function () {
                if ($(this).find('.bar-label').text().includes(star)) {
                    $(this).find('.bar-progress-fill').css('width', data.porcentaje + '%');
                    $(this).find('.bar-percent').text(data.porcentaje + '%');
                }
            });
        }
    }

    // ============================================
    // LÓGICA DE NAVEGACIÓN DE MINIATURAS (SCROLL)
    // ============================================
    $('#prevThumb').on('click', function(e) {
        e.preventDefault();
        const container = $('#product-zoom-gallery');
        if (window.innerWidth <= 991) {
            // Móvil: Scroll Horizontal hacia la izquierda
            container.animate({ scrollLeft: '-=120' }, 300);
        } else {
            // Desktop: Scroll Vertical hacia arriba
            container.animate({ scrollTop: '-=120' }, 300);
        }
    });

    $('#nextThumb').on('click', function(e) {
        e.preventDefault();
        const container = $('#product-zoom-gallery');
        if (window.innerWidth <= 991) {
            // Móvil: Scroll Horizontal hacia la derecha
            container.animate({ scrollLeft: '+=120' }, 300);
        } else {
            // Desktop: Scroll Vertical hacia abajo
            container.animate({ scrollTop: '+=120' }, 300);
        }
    });
});