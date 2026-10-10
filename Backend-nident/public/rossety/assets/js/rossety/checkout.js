/* Detalles Rossety · Finalizar compra (simulación): formulario, validación y envío del pedido por WhatsApp */
(function () {
    var RS = window.RS, root = document.querySelector('.js-checkout');
    if (!RS || !RS.cart || !root) return;

    var UB = window.ROSSETY_UBIGEO;
    var form = root.querySelector('.js-form');
    var $ = function (s) { return root.querySelector(s); };
    var f = function (name) { return form.elements[name]; };

    /* ---------- Resumen lateral ---------- */
    function renderSummary() {
        var lines = RS.cart.lines(), has = lines.length > 0;
        $('.js-has-items').classList.toggle('rs-hidden', !has);
        $('.js-no-items').classList.toggle('rs-hidden', has);
        $('.js-items').innerHTML = lines.map(function (l) {
            return '<div class="rs-co-item"><img class="rs-co-item__img" src="' + l.p.imagen + '" alt="' + RS.esc(l.p.nombre) + '" />' +
                '<div class="rs-co-item__info"><p class="rs-co-item__name">' + RS.esc(l.p.nombre) + '</p><p class="rs-co-item__qty">Cant. ' + l.qty + '</p></div>' +
                '<span class="rs-co-item__price">' + RS.money(l.total) + '</span></div>';
        }).join('');
        $('.js-sub').textContent = RS.money(RS.cart.subtotal());
        $('.js-disc-row').classList.toggle('rs-hidden', !RS.cart.coupon());
        $('.js-disc').textContent = '- ' + RS.money(RS.cart.discount());
        $('.js-coupon-code').textContent = RS.cart.coupon();
        $('.js-total').textContent = RS.money(RS.cart.total());
    }
    var sent = false;
    document.addEventListener('rs:cart', function () { if (!sent) renderSummary(); });

    /* ---------- Ubicación (departamento > provincia > distrito) ---------- */
    function fill(select, list, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>' +
            list.map(function (v) { return '<option>' + RS.esc(v) + '</option>'; }).join('');
    }
    function toggleField(select, text, list, placeholder) {
        var usesSelect = !!list;
        select.classList.toggle('rs-hidden', !usesSelect);
        text.classList.toggle('rs-hidden', usesSelect);
        select.value = '';
        text.value = '';
        clearError(select);
        clearError(text);
        if (usesSelect) fill(select, list, placeholder);
    }
    function provincias() { return UB.ubicaciones[f('departamento').value]; }
    function distritos() { var p = provincias(); return p ? p[f('provincia').value] : null; }

    fill(f('departamento'), UB.departamentos, 'Seleccione un departamento');
    f('departamento').value = 'Ayacucho';
    function onDepartamento() {
        var prov = provincias();
        toggleField(f('provincia'), f('provinciaTxt'), prov ? Object.keys(prov) : null, 'Seleccione una provincia');
        onProvincia();
    }
    function onProvincia() {
        toggleField(f('distrito'), f('distritoTxt'), distritos(), 'Seleccione un distrito');
    }
    f('departamento').addEventListener('change', onDepartamento);
    f('provincia').addEventListener('change', onProvincia);
    onDepartamento();

    function value(name) {
        var el = f(name);
        if (el.classList.contains('rs-hidden')) el = f(name + 'Txt');
        return el ? el.value.trim() : '';
    }

    /* ---------- Fecha mínima, comprobante y ubicación compartida ---------- */
    var today = new Date();
    f('fecha').min = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2);

    function onComprobante() {
        var factura = form.elements.comprobante.value === 'Factura';
        $('.js-factura').classList.toggle('rs-hidden', !factura);
        form.querySelectorAll('.js-factura [data-req]').forEach(function (el) { clearError(el); });
        form.querySelectorAll('.rs-co-choice').forEach(function (c) {
            c.classList.toggle('rs-co-choice--active', c.querySelector('input').checked);
        });
    }
    form.querySelectorAll('input[name="comprobante"]').forEach(function (r) { r.addEventListener('change', onComprobante); });
    onComprobante();

    var geoBtn = $('.js-geo');
    geoBtn.addEventListener('click', function () {
        if (!navigator.geolocation) { geoBtn.querySelector('span').textContent = 'Tu navegador no permite compartir ubicación'; return; }
        geoBtn.querySelector('span').textContent = 'Buscando tu ubicación...';
        navigator.geolocation.getCurrentPosition(function (pos) {
            f('ubicacion').value = 'https://maps.google.com/?q=' + pos.coords.latitude.toFixed(6) + ',' + pos.coords.longitude.toFixed(6);
            geoBtn.classList.add('rs-co-geo--done');
            geoBtn.querySelector('span').textContent = 'Ubicación compartida ✓';
        }, function () {
            f('ubicacion').value = '';
            geoBtn.classList.remove('rs-co-geo--done');
            geoBtn.querySelector('span').textContent = 'No pudimos obtener tu ubicación. Intenta de nuevo';
        }, { timeout: 10000 });
    });

    /* ---------- Validación ---------- */
    var RULES = {
        correo: [/^[^\s@]+@[^\s@]+\.[^\s@]+$/, 'Ingresa un correo válido.'],
        telefono: [/^9\d{8}$/, 'Ingresa un celular de 9 dígitos (9XXXXXXXX).'],
        ruc: [/^(10|15|17|20)\d{9}$/, 'Ingresa un RUC válido de 11 dígitos.']
    };
    function errorEl(el) { return el.closest('.rs-co-field').querySelector('.rs-co-error'); }
    function clearError(el) {
        el.classList.remove('rs-co-input--error');
        var e = errorEl(el);
        if (e) e.classList.remove('rs-co-error--show');
    }
    function setError(el, msg) {
        el.classList.add('rs-co-input--error');
        var e = errorEl(el);
        if (e) { if (msg) e.textContent = msg; e.classList.add('rs-co-error--show'); }
    }
    function validate(el) {
        if (el.classList.contains('rs-hidden') || el.closest('.rs-hidden')) return true;
        var v = el.value.trim(), rule = RULES[el.name];
        if (!v) { setError(el, 'Este campo es obligatorio.'); return false; }
        if (el.name === 'documento') {
            var tipo = f('tipoDoc').value;
            var okDoc = tipo === 'DNI' ? /^\d{8}$/.test(v) : /^[A-Za-z0-9]{6,12}$/.test(v);
            if (!okDoc) { setError(el, tipo === 'DNI' ? 'El DNI tiene 8 dígitos.' : 'Ingresa un documento válido.'); return false; }
        }
        if (rule && !rule[0].test(v.replace(/\s/g, ''))) { setError(el, rule[1]); return false; }
        clearError(el);
        return true;
    }
    form.addEventListener('blur', function (e) { if (e.target.matches && e.target.matches('[data-req]')) validate(e.target); }, true);
    form.addEventListener('input', function (e) { if (e.target.classList.contains('rs-co-input--error')) validate(e.target); });
    form.addEventListener('change', function (e) { if (e.target.matches('select[data-req]')) validate(e.target); });

    /* ---------- Envío del pedido ---------- */
    function orderMessage(o) {
        var L = ['Hola Detalles Rossety, quiero hacer este pedido (N° ' + o.id + '):', '', '*Productos*'];
        o.items.forEach(function (i) {
            L.push('• ' + i.qty + ' x ' + i.nombre + (i.modelo ? ' (Modelo ' + i.modelo + ')' : '') + ' - ' + RS.money(i.total));
        });
        L.push('', 'Subtotal: ' + RS.money(o.subtotal));
        if (o.cupon) L.push('Descuento (' + o.cupon + '): - ' + RS.money(o.descuento));
        L.push('*Total: ' + RS.money(o.total) + '* (envío por coordinar)', '', '*Datos del cliente*',
            'Nombre: ' + o.cliente.nombre + ' ' + o.cliente.apellidos,
            'Documento: ' + o.cliente.tipoDoc + ' ' + o.cliente.documento,
            'Celular: ' + o.cliente.telefono,
            'Correo: ' + o.cliente.correo, '', '*Entrega*',
            'Dirección: ' + o.envio.direccion,
            'Ubicación: ' + o.envio.departamento + ' / ' + o.envio.provincia + ' / ' + o.envio.distrito);
        if (o.envio.mapa) L.push('Mapa: ' + o.envio.mapa);
        if (o.envio.fecha) L.push('Fecha de entrega: ' + o.envio.fecha);
        L.push('Comprobante: ' + o.comprobante.tipo + (o.comprobante.ruc ? ' - RUC ' + o.comprobante.ruc + ' - ' + o.comprobante.razon : ''));
        if (o.dedicatoria) L.push('Dedicatoria: ' + o.dedicatoria);
        if (o.obs) L.push('Observaciones: ' + o.obs);
        return L.join('\n');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!RS.cart.count()) return;
        var ok = true, first = null;
        form.querySelectorAll('[data-req]').forEach(function (el) {
            if (!validate(el)) { ok = false; first = first || el; }
        });
        var terms = f('terminos'), termsErr = $('.js-terms-error');
        termsErr.classList.toggle('rs-co-error--show', !terms.checked);
        if (!terms.checked) { ok = false; first = first || terms; }
        if (!ok) { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }

        var order = {
            id: 'RS-' + Date.now().toString(36).toUpperCase(),
            fecha: new Date().toISOString(),
            items: RS.cart.lines().map(function (l) {
                return { id: l.id, nombre: l.p.nombre, modelo: l.p.modelo || '', imagen: l.p.imagen, qty: l.qty, precio: l.p.precio, total: l.total };
            }),
            subtotal: RS.cart.subtotal(), descuento: RS.cart.discount(), cupon: RS.cart.coupon(), total: RS.cart.total(),
            cliente: { nombre: value('nombre'), apellidos: value('apellidos'), tipoDoc: f('tipoDoc').value, documento: value('documento'), telefono: value('telefono'), correo: value('correo') },
            envio: { direccion: value('direccion'), departamento: f('departamento').value, provincia: value('provincia'), distrito: value('distrito'), mapa: f('ubicacion').value, fecha: f('fecha').value },
            comprobante: { tipo: form.elements.comprobante.value, ruc: form.elements.comprobante.value === 'Factura' ? value('ruc') : '', razon: form.elements.comprobante.value === 'Factura' ? value('razon') : '' },
            dedicatoria: value('dedicatoria'), obs: value('obs')
        };
        order.whatsapp = RS.waLink(orderMessage(order));
        try { sessionStorage.setItem('rs_order', JSON.stringify(order)); } catch (err) { /* sin almacenamiento */ }

        var btn = $('.js-submit');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner"></i> Procesando...';
        if (window.RS_PLACE_ORDER) { window.RS_PLACE_ORDER(order, btn); return; }
        sent = true;
        window.open(order.whatsapp, '_blank', 'noopener');
        RS.cart.clear();
        setTimeout(function () { window.location.href = '/gracias'; }, 900);
    });

    renderSummary();
})();
