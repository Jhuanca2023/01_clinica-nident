/* Detalles Rossety · Gracias por tu pedido: muestra el resumen guardado por el checkout */
(function () {
    var RS = window.RS, root = document.querySelector('.js-order');
    if (!RS || !root) return;

    var order = null;
    try { order = JSON.parse(sessionStorage.getItem('rs_order')); } catch (e) { /* sin almacenamiento */ }

    var ok = root.querySelector('.js-ok'), none = root.querySelector('.js-none');
    if (!order) { ok.classList.add('rs-hidden'); none.classList.remove('rs-hidden'); return; }

    var $ = function (s) { return root.querySelector(s); };
    $('.js-name').textContent = order.cliente.nombre;
    $('.js-id').textContent = order.id;
    $('.js-date').textContent = new Date(order.fecha).toLocaleString('es-PE', { dateStyle: 'long', timeStyle: 'short' });
    $('.js-items').innerHTML = order.items.map(function (i) {
        return '<div class="rs-co-item"><img class="rs-co-item__img" src="' + i.imagen + '" alt="' + RS.esc(i.nombre) + '" />' +
            '<div class="rs-co-item__info"><p class="rs-co-item__name">' + RS.esc(i.nombre) + '</p><p class="rs-co-item__qty">Cant. ' + i.qty + '</p></div>' +
            '<span class="rs-co-item__price">' + RS.money(i.total) + '</span></div>';
    }).join('');
    $('.js-sub').textContent = RS.money(order.subtotal);
    $('.js-disc-row').classList.toggle('rs-hidden', !order.cupon);
    $('.js-disc').textContent = '- ' + RS.money(order.descuento);
    $('.js-total').textContent = RS.money(order.total);

    var e = order.envio, c = order.cliente;
    var data = [
        ['Cliente', c.nombre + ' ' + c.apellidos],
        ['Documento', c.tipoDoc + ' ' + c.documento],
        ['Celular', c.telefono],
        ['Correo', c.correo],
        ['Dirección', e.direccion],
        ['Ubicación', e.departamento + ' / ' + e.provincia + ' / ' + e.distrito],
        ['Fecha de entrega', e.fecha || 'Por coordinar'],
        ['Comprobante', order.comprobante.tipo + (order.comprobante.ruc ? ' · RUC ' + order.comprobante.ruc : '')]
    ];
    if (order.dedicatoria) data.push(['Dedicatoria', order.dedicatoria]);
    if (order.obs) data.push(['Observaciones', order.obs]);
    $('.js-data').innerHTML = data.map(function (d) {
        return '<div class="rs-order__line"><dt>' + d[0] + '</dt><dd>' + RS.esc(d[1]) + '</dd></div>';
    }).join('');

    $('.js-pays').innerHTML = RS.data.negocio.pagos.map(function (p) {
        return '<li class="rs-order__pay"><strong>' + RS.esc(p.banco) + '</strong><span>' + RS.esc(p.tipo) + ': ' + RS.esc(p.numero) + '</span><small>Titular: ' + RS.esc(p.titular) + '</small></li>';
    }).join('');

    var resend = $('.js-resend');
    resend.href = order.whatsapp;
    resend.target = '_blank';
    resend.rel = 'noopener';
})();
