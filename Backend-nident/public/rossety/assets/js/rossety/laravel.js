/* Puente: el carrito visual sigue en el navegador y el pedido se registra en Laravel. */
(function () {
    function token() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    window.RS_PLACE_ORDER = function (order, btn) {
        fetch('/rossety/pedido', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(order)
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (!res.ok || res.body.success === false) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-whatsapp" aria-hidden="true"></i> Enviar pedido por WhatsApp';
                    alert(res.body.message || 'No se pudo registrar el pedido.');
                    return;
                }
                var codigo = null;
                if (Array.isArray(res.body.result)) codigo = res.body.result[0];
                if (codigo) order.id = codigo;
                try { sessionStorage.setItem('rs_order', JSON.stringify(order)); } catch (e) { /* sin almacenamiento */ }
                var wa = res.body.whatsapp_url || (window.RS && order.whatsapp);
                if (wa) window.open(wa, '_blank', 'noopener');
                if (window.RS && RS.cart) RS.cart.clear();
                window.location.href = '/gracias';
            }).catch(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-whatsapp" aria-hidden="true"></i> Enviar pedido por WhatsApp';
                alert('No se pudo conectar con la tienda.');
            });
    };
})();
