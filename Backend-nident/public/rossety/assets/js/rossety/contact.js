/* Detalles Rossety · Contacto: el formulario arma el mensaje y lo envía por WhatsApp */
(function () {
    var RS = window.RS, form = document.querySelector('.js-contact-form');
    if (!RS || !form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var v = function (n) { return (form.elements[n].value || '').trim(); };
        var lines = [
            (form.dataset.saludo || 'Hola Detalles Rossety, soy {nombre}.').replace('{nombre}', v('nombre')),
            'Motivo: ' + v('motivo') + '.',
            v('fecha') ? 'Fecha de entrega: ' + v('fecha') + '.' : '',
            v('telefono') ? 'Mi teléfono: ' + v('telefono') + '.' : '',
            v('mensaje')
        ].filter(Boolean);
        window.open(RS.waLink(lines.join('\n')), '_blank', 'noopener');
    });
})();
