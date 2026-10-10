'use strict';

(function ($) {
    if (!window.LIAM_SHOP) return;

    function updateBadge(count) {
        var n = parseInt(count, 10) || 0;
        ['#lv-cart-count', '#lv-cart-count-sticky'].forEach(function (sel) {
            var $b = $(sel);
            if (!$b.length) return;
            if (n > 0) {
                $b.text(n).show();
            } else {
                $b.hide();
            }
        });
    }

    function refreshCartFromServer() {
        return $.getJSON(LIAM_SHOP.routes.totalCarrito).then(function (res) {
            if (res && res.success) {
                updateBadge(res.items_count);
            }
            return res;
        });
    }

    function showError(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: msg || 'No se pudo completar la operación.' });
        } else {
            alert(msg || 'No se pudo completar la operación.');
        }
    }

    function afterAddSuccess(data) {
        var result = data && data.result ? data.result : {};
        var itemsCount = parseInt(result.detalle_cantidad, 10);
        if (!isNaN(itemsCount)) {
            updateBadge(itemsCount);
        } else {
            refreshCartFromServer();
        }

        if (window.LVCart && typeof window.LVCart.openCart === 'function') {
            window.LVCart.openCart();
        }

        var $qty = $('#txt_cantidad');
        if ($qty.length) {
            $qty.val(1).trigger('change');
        }

        if (typeof window.validarEnvioGratis === 'function') {
            window.validarEnvioGratis();
        }
        if (typeof window.aplicarPrecioVolumenDetalle === 'function') {
            window.aplicarPrecioVolumenDetalle();
        }

        var idProducto = $('input[name="txt_id_producto"]').val();
        if (idProducto && typeof window.cargarProductosSugeridos === 'function') {
            window.cargarProductosSugeridos(idProducto);
        }
    }

    window.LiamAddToCart = function (codigoProducto, cantidad, talla, color) {
        var fd = new FormData();
        fd.append('_token', LIAM_SHOP.csrf);
        fd.append('codigo_producto', codigoProducto);
        fd.append('cantidad', cantidad || 1);
        if (talla) {
            fd.append('talla', talla);
        }
        if (color) {
            fd.append('color', color);
        }

        return axios.post(LIAM_SHOP.routes.agregar, fd).then(function (res) {
            if (res.data && res.data.status === 'success') {
                afterAddSuccess(res.data);
                return res.data;
            }
            if (res.data && res.data.status === 'error') {
                return Promise.reject(res.data);
            }
            return res.data;
        });
    };

    function runAddAction($el, onSuccess) {
        if ($el.prop('disabled')) {
            return;
        }

        var codigo = $('#codigo_producto').val();
        var cantidad = parseInt($('#txt_cantidad').val(), 10) || 1;
        var talla = ($('#txt_talla').val() || '').trim();
        var color = ($('#txt_color').val() || '').trim();

        if (!codigo) {
            showError('Producto sin código disponible.');
            return;
        }
        if ($('#sd-size-btns').length && !talla) {
            showError('Selecciona una talla.');
            return;
        }

        var originalHtml = $el.html();
        $el.prop('disabled', true);
        $el.html('<i class="fa fa-spinner fa-spin"></i> Agregando...');

        LiamAddToCart(codigo, cantidad, talla, color)
            .then(function (data) {
                if (typeof onSuccess === 'function') {
                    onSuccess(data);
                }
            })
            .catch(function (err) {
                showError(err && err.message ? err.message : 'No se pudo agregar al carrito.');
            })
            .finally(function () {
                $el.prop('disabled', false);
                $el.html(originalHtml);
            });
    }

    $(document).on('click', '#addToCart', function (e) {
        e.preventDefault();
        runAddAction($(this));
    });

    $(document).on('submit', '#formAgregarProducto', function (e) {
        e.preventDefault();
        runAddAction($('#addToCart'));
    });

    $(document).on('click', '.clsComprarRapido', function (e) {
        e.preventDefault();
        runAddAction($(this), function () {
            window.location.href = LIAM_SHOP.routes.checkout;
        });
    });

    $(document).on('click', '.lv-cart-btn[data-codigo]', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var codigo = $btn.data('codigo');
        if (!codigo) return;
        $btn.prop('disabled', true);
        LiamAddToCart(codigo, $btn.data('qty') || 1)
            .catch(function (err) {
                showError(err && err.message ? err.message : 'No se pudo agregar al carrito.');
            })
            .finally(function () { $btn.prop('disabled', false); });
    });

    // "Agregar" de las tarjetas (inicio / tienda): como la plantilla, agrega 1 unidad
    // con la talla y el color por defecto y muestra "Agregado" 1.5 s
    $(document).on('click', '.lv-cat-add-btn[data-codigo]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Agregando');
        LiamAddToCart($btn.data('codigo'), 1, String($btn.data('talla') || ''), String($btn.data('color') || ''))
            .then(function () {
                $btn.html('<i class="fa fa-check"></i> Agregado').css('background', '#27ae60');
                setTimeout(function () {
                    $btn.html(originalHtml).css('background', '').prop('disabled', false);
                }, 1500);
            })
            .catch(function (err) {
                $btn.html(originalHtml).prop('disabled', false);
                showError(err && err.message ? err.message : 'No se pudo agregar al carrito.');
            });
    });

    $(document).on('click', '.clsBtnAgregarCarritoGlobal', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var codigo = $btn.data('codigo');
        if (!codigo) {
            showError('Producto sin código disponible.');
            return;
        }
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        LiamAddToCart(codigo, 1)
            .catch(function (err) {
                showError(err && err.message ? err.message : 'No se pudo agregar al carrito.');
            })
            .finally(function () {
                $btn.prop('disabled', false).html(originalHtml);
            });
    });

    $(function () {
        refreshCartFromServer();
    });
})(jQuery);
