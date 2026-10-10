// ::::::::::::::::::::::::::::::REGISTRAR FAVORITO ::::::::::::::::::::::::::::::::::::::::
function clsActionAdd(estado, id_producto, el) {
    // Usar el elemento exacto clickeado (resuelve duplicados del carousel)
    const button = el ? $(el) : $("#idActualizarDivFavorito" + id_producto);
    button.prop('disabled', true).addClass('disabled');

    let formData = new FormData();
    formData.append('id_producto', id_producto);

    axios.post('/web_wishlist_agregar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function (response) {
        if (response.data.state == "error") {
            Swal.fire({ icon: 'error', title: 'Oops...', text: response.data.data });
            button.prop('disabled', false).removeClass('disabled');
        } else if (response.data.state == "login") {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: response.data.data,
                confirmButtonText: 'Ir',
                cancelButtonText: "Cancelar",
                allowEscapeKey: false,
                showCancelButton: true,
                showCloseButton: true
            }).then((result) => {
                if (result['isConfirmed']) {
                    window.location = "/login";
                } else {
                    button.prop('disabled', false).removeClass('disabled');
                }
            });
        } else {
            toastr.success(response.data.mensaje);
            var uid = response.data.id_usuario;
            // Cambiar ícono a check y reasignar evento al elemento exacto
            button
                .removeClass('clsBotonAgragarFavorito clsActionAdd disabled')
                .addClass('clsBotonEliminarFavorito clsActionDelete')
                .prop('disabled', false)
                .attr('onclick', "clsActionDelete('ESTADO_GALLERY_PRODUCT','" + id_producto + "','" + uid + "',this)")
                .html('<i class="fa-solid fa-check"></i>');
        }
    })
    .catch(function (error) {
        console.log('Error:', error);
        toastr.error('Error al agregar a favoritos');
        button.prop('disabled', false).removeClass('disabled');
    });
}

// ::::::::::::::::::::::::::::::ELIMINAR FAVORITO ::::::::::::::::::::::::::::::::::::::::
function clsActionDelete(estado, id_producto, id_usuario, el) {
    // Usar el elemento exacto clickeado (resuelve duplicados del carousel)
    const button = el ? $(el) : $("#idActualizarDivFavorito" + id_producto);
    button.prop('disabled', true).addClass('disabled');

    let formData = new FormData();
    formData.append('id_producto', id_producto);
    formData.append('id_usuario', id_usuario);

    axios.post('/web_wishlist_eliminar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function (response) {
        if (response.data.state == "error") {
            Swal.fire({ icon: 'error', title: 'Oops...', text: response.data.data });
            button.prop('disabled', false).removeClass('disabled');
        } else if (response.data.state == "ok") {
            toastr.info(response.data.mensaje);
            // Cambiar ícono a corazón vacío y reasignar evento al elemento exacto
            button
                .removeClass('clsBotonEliminarFavorito clsActionDelete disabled')
                .addClass('clsBotonAgragarFavorito clsActionAdd')
                .prop('disabled', false)
                .attr('onclick', "clsActionAdd('ESTADO_GALLERY_PRODUCT','" + id_producto + "',this)")
                .html('<i class="fa-regular fa-heart"></i>');
            // Remover el card del DOM si estamos en la página de wishlist
            $("#wl-item-" + id_producto).fadeOut(300, function() {
                $(this).remove();
                let count = parseInt($(".count-number").text());
                let newCount = count - 1;
                $(".count-number").text(newCount);
                $(".count-label").text(newCount === 1 ? 'producto' : 'productos');
                if (newCount === 0) {
                    $(".wishlist-grid").html(`
                        <div class="empty-wishlist-container">
                            <h2 class="empty-wishlist-title">Tu lista de deseos está vacía</h2>
                            <p class="empty-wishlist-description">
                                Aún no has agregado productos a tu lista de deseos.
                            </p>
                            <a href="/" class="btn-explore-products">
                                <i class="fa-solid fa-store"></i> Explorar productos
                            </a>
                        </div>
                    `);
                }
            });
        }
    })
    .catch(function (error) {
        console.log('Error:', error);
        toastr.error('Error al eliminar de favoritos');
        button.prop('disabled', false).removeClass('disabled');
    });
}
