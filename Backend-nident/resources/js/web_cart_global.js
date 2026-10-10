$(document).ready(function () {
    console.log("Cargando web_cart_global.js...");

    let productosAgregadosModal = []; // Track de productos agregados en la sesión del modal

    // ============================================
    // AGREGAR AL CARRITO GLOBAL (HOME/CATEGORÍAS)
    // ============================================
    $(document).on('click', '.clsBtnAgregarCarritoGlobal', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const $btn = $(this);
        const isInModal = $btn.closest('#idOpenModalDetalle').length > 0;
        
        console.log("Click en agregar al carrito global:", $btn.data('id'), "IsInModal:", isInModal);
        const codigoProducto = $btn.data('codigo');
        const idProducto = $btn.data('id');
        const cantidad = 1;

        if ($btn.hasClass('disabled')) return;
        $btn.addClass('disabled').prop('disabled', true);
        const originalHtml = $btn.html();
        
        const hasIcon = $btn.find('i').length > 0;
        if (hasIcon) {
            $btn.find('i').attr('class', 'fas fa-spinner fa-spin');
        } else {
            $btn.html('<i class="fas fa-spinner fa-spin"></i>');
        }

        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('codigo_producto', codigoProducto);
        formData.append('cantidad', cantidad);

        axios.post('/web_shopDetail_agregar', formData)
            .then(function (response) {
                if (response.data.status == "error") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.data.message,
                    });
                } else {
                    const result = response.data.result;

                    // Si NO está en el modal, es la primera adición
                    if (!isInModal) {
                        productosAgregadosModal = [];
                        
                        // Ocultar vista múltiple, mostrar individual
                        $('#modalVistaSingle').show();
                        $('#modalVistaMultiple').hide();
                    }

                    // Agregar producto a la lista del modal
                    let imgPath = result["descripcion_imagen"];
                    if (!imgPath.startsWith('http') && !imgPath.startsWith('/')) {
                        imgPath = '/' + imgPath;
                    }

                    productosAgregadosModal.push({
                        nombre: result["descripcion_nombre"],
                        precio: parseFloat(result["descripcion_precio"]) || 0,
                        cantidad: parseInt(result["descripcion_cantidad"]) || 1,
                        imagen: imgPath,
                        subtotal: parseFloat(result["descripcion_t_subtotal"]) || 0
                    });

                    // Actualizar campos comunes del modal
                    $('.clsDescripcion_CountCart').text(result["descripcion_cantidad"]);
                    $('.clsDescripcion_Precio').text(result["descripcion_precio"]);
                    $('.clsDescripcion_Nombre').text(result["descripcion_nombre"]);
                    $('#idDescripcion_Imagen').attr('src', imgPath);
                    
                    $('.clsDetalle_CountCart').text(result["detalle_cantidad"]);
                    $('.clsDescripcion_SubTotal').text(result["descripcion_t_subtotal"]);
                    $('.clsDescripcion_Igv').text('S/' + result["descripcion_t_igv"]);
                    $('.clsDetalle_Total').text('S/' + result["descripcion_t_total"]);

                    // Si hay más de un producto, renderizar vista múltiple
                    if (productosAgregadosModal.length > 1) {
                        renderizarVistaMultipleGlobal(productosAgregadosModal);
                    }

                    // Mostrar el modal si no está visible
                    if (!isInModal) {
                        $('#idOpenModalDetalle').modal('show');
                    } else {
                        // Si ya está en el modal, marcar el botón como "Agregado"
                        $btn.addClass('sugerido-agregado').html('<i class="fas fa-check"></i> Agregado');
                    }

                    // Refrescar el dropdown del carrito en el header
                    if (typeof updateDiv === 'function') {
                        updateDiv();
                    }

                    // Cargar productos sugeridos solo si es la primera adición
                    if (!isInModal && idProducto) {
                        cargarProductosSugeridosModal(idProducto);
                    }
                }
            })
            .catch(function (error) {
                console.error('Error al agregar al carrito global:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo agregar el producto al carrito.',
                });
            })
            .finally(function () {
                if (!$btn.hasClass('sugerido-agregado')) {
                    $btn.removeClass('disabled').prop('disabled', false).html(originalHtml);
                }
            });
    });

    /**
     * Renderiza la vista múltiple del modal en web_cart_global.js
     * @param {Array} productos 
     */
    function renderizarVistaMultipleGlobal(productos) {
        if (productos.length <= 1) return;

        // Ocultar vista individual, mostrar multiple
        $('#modalVistaSingle').hide();
        $('#modalVistaMultiple').show();
        $('#modalCantidadProductos').text(productos.length);

        let html = '';
        productos.forEach(function (prod, index) {
            html += `
                <div class="modal-producto-item ${index === productos.length - 1 ? 'modal-producto-nuevo' : ''}">
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
});

/**
 * Carga los productos sugeridos para el modal
 * @param {number} idProducto 
 */
function cargarProductosSugeridosModal(idProducto) {
    const section = $('#sugeridosSection');
    const carousel = $('#sugeridosCarousel');
    if (!section.length || !carousel.length) return;

    carousel.empty();
    section.hide();

    fetch('/web_shopDetail_sugeridos/' + idProducto, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.sugeridos || data.sugeridos.length === 0) return;

        data.sugeridos.forEach(function (prod) {
            const nombreProducto = prod.nombre || 'Producto';
            const precio = parseFloat(prod.precio).toFixed(2);
            const precioOld = prod.precio_old ? parseFloat(prod.precio_old).toFixed(2) : null;
            const imagen = prod.url_imagen.startsWith('http') ? prod.url_imagen : '/' + prod.url_imagen;
            
            
            let estrellasHtml = '';
            const stars = parseInt(prod.numero_estrellas) || 0;
            for (let i = 1; i <= 5; i++) {
                estrellasHtml += i <= stars ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
            }

            
            let descuentoHtml = '';
            if (precioOld && parseFloat(precioOld) > parseFloat(precio)) {
                const dcto = Math.round(((parseFloat(precioOld) - parseFloat(precio)) / parseFloat(precioOld)) * 100);
                if (dcto > 0) {
                    descuentoHtml = `<span class="sugerido-descuento">-${dcto}%</span>`;
                }
            }

            const card = `
                <div class="sugerido-card">
                    ${descuentoHtml}
                    <a href="/web_shopDetail/${prod.slug || prod.id_producto}" class="sugerido-img-link">
                        <img src="${imagen}" alt="${nombreProducto}" class="sugerido-img">
                    </a>
                    <div class="sugerido-info">
                        <div class="sugerido-estrellas">${estrellasHtml}</div>
                        <a href="/web_shopDetail/${prod.slug || prod.id_producto}" class="sugerido-nombre" title="${nombreProducto}">
                            ${nombreProducto.length > 35 ? nombreProducto.substring(0, 35) + '...' : nombreProducto}
                        </a>
                        <div class="sugerido-precios">
                            ${precioOld ? `<span class="sugerido-precio-old">S/ ${precioOld}</span>` : ''}
                            <span class="sugerido-precio">S/ ${precio}</span>
                        </div>
                        <button type="button" class="sugerido-btn-agregar clsBtnAgregarCarritoGlobal"
                                data-codigo="${prod.codigo_producto}"
                                data-id="${prod.id_producto}">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            `;
            carousel.append(card);
        });

        section.fadeIn(300);
        
        
        $('#sugeridosPrev').off('click').on('click', function () {
            carousel.animate({ scrollLeft: '-=200' }, 300);
        });
        $('#sugeridosNext').off('click').on('click', function () {
            carousel.animate({ scrollLeft: '+=200' }, 300);
        });
    })
    .catch(err => console.error('Error cargando sugeridos modal:', err));
}
