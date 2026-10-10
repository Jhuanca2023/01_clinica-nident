'use strict';

(function () {
    if (!window.axios || !window.LIAM_SHOP) return;

    axios.defaults.headers.common['X-CSRF-TOKEN'] = LIAM_SHOP.csrf;

    /* ── Toast SweetAlert2 personalizado ──────── */
    var LvToast = window.Swal
        ? window.Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: function (toast) {
                toast.addEventListener('mouseenter', window.Swal.stopTimer);
                toast.addEventListener('mouseleave', window.Swal.resumeTimer);
            }
        })
        : null;

    function notify(msg, type) {
        if (LvToast) {
            var iconMap = { error: 'error', info: 'info', success: 'success' };
            LvToast.fire({ icon: iconMap[type] || 'success', title: msg });
            return;
        }
        if (window.toastr) {
            if (type === 'error') toastr.error(msg);
            else if (type === 'info') toastr.info(msg);
            else toastr.success(msg);
            return;
        }
        if (type === 'error') window.alert(msg);
    }

    /* Modal de confirmación al agregar favorito */
    function showAddedModal(nombreProducto) {
        if (!window.Swal) return;
        window.Swal.fire({
            html: [
                '<div style="text-align:center;padding:8px 0 4px;">',
                '  <div style="width:64px;height:64px;background:#1a1a1a;border-radius:50%;',
                '       display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">',
                '    <i class="fa fa-heart" style="color:#c0392b;font-size:28px;"></i>',
                '  </div>',
                '  <h3 style="font-family:Montserrat,sans-serif;font-size:18px;font-weight:700;',
                '      color:#1a1a1a;margin:0 0 8px;">¡Agregado a favoritos!</h3>',
                '  <p style="font-family:Montserrat,sans-serif;font-size:14px;color:#666;margin:0 0 20px;">',
                (nombreProducto ? '<strong>' + nombreProducto + '</strong> fue guardado en tu lista.' : 'El producto fue guardado en tu lista de favoritos.'),
                '  </p>',
                '  <a href="/web_wishlist" style="display:inline-block;padding:10px 24px;',
                '     background:#1a1a1a;color:white;border-radius:8px;font-family:Montserrat,sans-serif;',
                '     font-size:13px;font-weight:700;text-decoration:none;letter-spacing:0.5px;">',
                '    <i class="fa fa-heart" style="margin-right:6px;color:#c0392b;"></i> Ver mis favoritos',
                '  </a>',
                '</div>'
            ].join(''),
            showConfirmButton: false,
            showCloseButton: true,
            timer: 4500,
            timerProgressBar: true,
            width: 360,
            padding: '24px',
            background: '#fff',
            customClass: { popup: 'lv-swal-favorito' }
        });
    }

    function setWishButtonAdded(btn, idProducto, idUsuario) {
        btn.classList.add('active');
        btn.classList.remove('clsBotonAgragarFavorito', 'clsActionAdd');
        btn.classList.add('clsBotonEliminarFavorito', 'clsActionDelete');
        btn.disabled = false;
        btn.title = 'Quitar de favoritos';
        btn.setAttribute('aria-label', 'Quitar de favoritos');
        btn.setAttribute('data-user-id', idUsuario);
        btn.innerHTML = '<i class="fa fa-heart"></i>';
    }

    function setWishButtonRemoved(btn, idProducto) {
        btn.classList.remove('active');
        btn.classList.remove('clsBotonEliminarFavorito', 'clsActionDelete');
        btn.classList.add('clsBotonAgragarFavorito', 'clsActionAdd');
        btn.disabled = false;
        btn.title = 'Agregar a favoritos';
        btn.setAttribute('aria-label', 'Agregar a favoritos');
        btn.removeAttribute('data-user-id');
        btn.innerHTML = '<i class="fa fa-heart-o"></i>';
    }

    window.clsActionAdd = function (estado, id_producto, el) {
        var button = el || document.getElementById('idActualizarDivFavorito' + id_producto);
        if (!button) return;
        button.disabled = true;

        /* Nombre del producto (si está en un card con data-name o title del enlace cercano) */
        var card = button.closest('[data-name]') || button.closest('.cat-card') || button.closest('.wl-card');
        var nombreProducto = '';
        if (card) {
            nombreProducto = card.getAttribute('data-name') || '';
        }
        if (!nombreProducto) {
            var titleEl = document.querySelector('[data-product-id="' + id_producto + '"] .product-name, #product-title-' + id_producto);
            if (titleEl) nombreProducto = titleEl.textContent.trim();
        }

        var formData = new FormData();
        formData.append('id_producto', id_producto);
        formData.append('_token', LIAM_SHOP.csrf);

        axios.post('/web_wishlist_agregar', formData)
            .then(function (response) {
                var data = response.data || {};
                if (data.state === 'error') {
                    notify(data.data || 'No se pudo agregar', 'error');
                    button.disabled = false;
                } else if (data.state === 'login') {
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'info',
                            title: 'Inicia sesión',
                            text: data.data || 'Debes iniciar sesión para guardar favoritos.',
                            confirmButtonText: 'Ir al login',
                            confirmButtonColor: '#1a1a1a',
                            showCancelButton: true,
                            cancelButtonText: 'Cancelar'
                        }).then(function (result) {
                            if (result.isConfirmed) window.location.href = '/login';
                        });
                    } else {
                        if (window.confirm((data.data || 'Debe iniciar sesión') + '\n\n¿Ir al login?')) {
                            window.location.href = '/login';
                        }
                    }
                    button.disabled = false;
                } else {
                    setWishButtonAdded(button, id_producto, data.id_usuario);
                    showAddedModal(nombreProducto);
                }
            })
            .catch(function () {
                notify('Error al agregar a favoritos', 'error');
                button.disabled = false;
            });
    };

    window.clsActionDelete = function (estado, id_producto, id_usuario, el) {
        var button = el || document.getElementById('idActualizarDivFavorito' + id_producto);
        if (!button) return;

        if (window.Swal) {
            window.Swal.fire({
                title: '¿Quitar de favoritos?',
                text: 'El producto será eliminado de tu lista.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1a1a1a',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    doDelete(button, id_producto, id_usuario);
                }
            });
        } else {
            doDelete(button, id_producto, id_usuario);
        }
    };

    function doDelete(button, id_producto, id_usuario) {
        button.disabled = true;
        var formData = new FormData();
        formData.append('id_producto', id_producto);
        formData.append('id_usuario', id_usuario);
        formData.append('_token', LIAM_SHOP.csrf);

        axios.post('/web_wishlist_eliminar', formData)
            .then(function (response) {
                var data = response.data || {};
                if (data.state === 'error') {
                    notify(data.data || 'No se pudo eliminar', 'error');
                    button.disabled = false;
                } else if (data.state === 'ok') {
                    notify(data.mensaje || 'Eliminado de favoritos', 'info');
                    setWishButtonRemoved(button, id_producto);
                    var card = document.getElementById('wl-item-' + id_producto);
                    if (card && window.jQuery) {
                        window.jQuery(card).fadeOut(300, function () { this.remove(); });
                    }
                }
            })
            .catch(function () {
                notify('Error al eliminar de favoritos', 'error');
                button.disabled = false;
            });
    }

    if (window.jQuery) {
        jQuery(document).on('click', '.cat-card__wish', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var btn = this;
            var id = btn.getAttribute('data-id');
            if (!id) return;
            if (btn.classList.contains('active')) {
                var uid = btn.getAttribute('data-user-id') || LIAM_SHOP.userId;
                if (uid) {
                    window.clsActionDelete('ESTADO_GALLERY_PRODUCT', id, uid, btn);
                }
            } else {
                window.clsActionAdd('ESTADO_GALLERY_PRODUCT', id, btn);
            }
        });
    }
})();
