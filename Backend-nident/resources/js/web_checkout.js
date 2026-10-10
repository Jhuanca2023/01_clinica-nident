// El script v4 de Culqi (checkout.culqi.com/js/v4) declara una variable global `$`
// que reemplaza a jQuery en la página. En este módulo `$` es siempre jQuery.
const $ = window.jQuery;

$(document).ready(function () {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let totalCarrito = 0;
    let cantidadInicialCargada = 1;

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
    // HINT DE DELIVERY (reemplaza S/199 estático)
    // ============================================
    function validarEnvioGratis() {
        if (window.DEL_CHECKOUT_CALCULADO) return;
        if (window.DEL_HINT_CHECKOUT) {
            $('.mensajeLlevaloDesde').html(window.DEL_HINT_CHECKOUT);
        }
    }

    // ============================================
    // EVENTOS PARA VALIDAR ENVÍO GRATIS
    // ============================================
    obtenerTotalCarrito().then((response) => {
        cantidadInicialCargada = response || 1;
        validarEnvioGratis();
    });

    function initCheckoutSelect2() {
        const dropdownParent = $(document.body);
        const hasSelect2 = typeof $.fn.select2 === 'function';

        if (!hasSelect2) {
            return;
        }

        ['#txt_listar_departamentos', '#txt_listar_provincias', '#txt_listar_distritos'].forEach(function (sel) {
            const $el = $(sel);
            if (!$el.length) return;
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: dropdownParent,
                placeholder: $el.attr('id') === 'txt_listar_distritos'
                    ? 'Seleccione un distrito'
                    : ($el.attr('id') === 'txt_listar_provincias' ? 'Seleccione una provincia' : 'Seleccione departamento'),
                minimumResultsForSearch: 0
            });
        });
    }

    initCheckoutSelect2();
    $(window).on('load', initCheckoutSelect2);

    $(document).on('keydown', '.select2-search__field', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    ['#txt_listar_distritos', '#txt_listar_provincias', '#txt_listar_departamentos'].forEach(function (sel) {
        $(sel).on('focus', function () {
            const $el = $(this);
            if (!$el.hasClass('select2-hidden-accessible')) {
                initCheckoutSelect2();
            }
        });
    });

    /** Para costos de envío: Lima sigue usando "Lima Metropolitana". */
    function addressDepartamentoParaEnvio() {
        const name = ($.trim($('#txt_listar_departamentos').val() || ''));
        if (/^lima$/i.test(name) || /metropolitana/i.test(name)) {
            return 'Lima Metropolitana';
        }
        return name;
    }

    function resetSelect($el, placeholder, disabled) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.prop('disabled', !!disabled)
            .html(`<option value="" selected disabled>${placeholder}</option>`);
        initCheckoutSelect2();
    }

    function cargarProvincias(departmentId, preferName) {
        const $provincia = $('#txt_listar_provincias');
        const $distrito = $('#txt_listar_distritos');

        resetSelect($distrito, 'Seleccione distrito...', true);

        if (!departmentId) {
            resetSelect($provincia, 'Seleccione provincia...', true);
            initCheckoutSelect2();
            return;
        }

        $provincia.prop('disabled', true);
        $provincia.html('<option value="">Cargando provincias...</option>');

        $.ajax({
            url: '/web_checkout/ubigeo/provincias',
            method: 'GET',
            dataType: 'json',
            data: {
                department_id: departmentId
            },
            success: function (result) {
                const rows = result.provincias || [];
                let options = '<option value="" selected disabled>Seleccione provincia...</option>';
                let autoSelect = null;
                rows.forEach(function (row) {
                    const name = $.trim(row.name || '');
                    options += `<option value="${name}" data-id="${row.id}">${name}</option>`;
                    if (preferName && name.toLowerCase() === String(preferName).toLowerCase()) {
                        autoSelect = name;
                    } else if (!preferName && /^lima$/i.test(name) && !autoSelect) {
                        autoSelect = name;
                    }
                });
                if ($provincia.hasClass('select2-hidden-accessible')) {
                    $provincia.select2('destroy');
                }
                $provincia.html(options).prop('disabled', false);
                initCheckoutSelect2();
                if (autoSelect) {
                    $provincia.val(autoSelect).trigger('change');
                }
            },
            error: function () {
                showError('No se pudieron cargar las provincias');
                resetSelect($provincia, 'Error al cargar', true);
                initCheckoutSelect2();
            }
        });
    }

    function cargarDistritosUbigeo(departmentId, provinceId) {
        const $distrito = $('#txt_listar_distritos');

        if (!departmentId || !provinceId) {
            resetSelect($distrito, 'Seleccione distrito...', true);
            initCheckoutSelect2();
            return;
        }

        if (addressDepartamentoParaEnvio() === 'Lima Metropolitana') {
            $('#idTiempoEntrega').show();
        } else {
            $('#idTiempoEntrega').hide();
        }

        $distrito.prop('disabled', true);
        $distrito.html('<option value="">Cargando distritos...</option>');

        $.ajax({
            url: '/web_checkout/ubigeo/distritos',
            method: 'GET',
            dataType: 'json',
            data: {
                department_id: departmentId,
                province_id: provinceId
            },
            success: function (result) {
                const rows = result.distritos || [];
                let options = '<option value="" selected disabled>Seleccione distrito...</option>';
                rows.forEach(function (row) {
                    const name = $.trim(row.name || '');
                    options += `<option value="${name}" data-id="${row.id}">${name}</option>`;
                });
                if ($distrito.hasClass('select2-hidden-accessible')) {
                    $distrito.select2('destroy');
                }
                $distrito.html(options).prop('disabled', false);
                initCheckoutSelect2();
            },
            error: function () {
                showError('No se pudieron cargar los distritos');
                resetSelect($distrito, 'Error al cargar', true);
                initCheckoutSelect2();
            }
        });
    }

    $('#txt_listar_departamentos').on('change', function () {
        if (window.__liamCheckoutUbigeo) {
            return;
        }
        const departmentId = $(this).find('option:selected').attr('data-id');
        cargarProvincias(departmentId);
    });

    $('#txt_listar_provincias').on('change', function () {
        if (window.__liamCheckoutUbigeo) {
            return;
        }
        const departmentId = $('#txt_listar_departamentos option:selected').attr('data-id');
        const provinceId = $(this).find('option:selected').attr('data-id');
        cargarDistritosUbigeo(departmentId, provinceId);
    });

    // Carga inicial: si Lima está seleccionado, cargar provincias
    if (!window.__liamCheckoutUbigeo) {
        const $depInicial = $('#txt_listar_departamentos option:selected');
        if ($depInicial.length && $depInicial.val()) {
            cargarProvincias($depInicial.attr('data-id'), 'Lima');
        }
    }

    function toggleLoading(show) {
        const $loadingIndicator = $('.loading-shipping-cost');
        if (show) {
            if ($loadingIndicator.length === 0) {
                // placeholder loading (sin UI forzada)
            }
        } else {
            $loadingIndicator.remove();
        }
    }

    // ============================================
    // EVENTO ONCHANGE DEL DISTRITO (AUTOMÁTICO)
    // ============================================
    $('#txt_listar_distritos').on('change', function () {
        const departamento = addressDepartamentoParaEnvio();
        const distrito = $(this).val();

        // Limpiar error de validación cuando el usuario selecciona un distrito
        if (distrito) {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }

        if (!departamento || !distrito) {
            return;
        }

        toggleLoading(true);

        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('address_departamento', departamento);
        formData.append('address_distrito', distrito);
        formData.append('txt_peso_kilogramo', $('input[name="txt_peso_kilogramo"]').val() || '0');
        formData.append('txt_paqueta_medidas', $('input[name="txt_paqueta_medidas"]').val() || '0');
        formData.append('txt_paquete_dimencion', $('input[name="txt_paquete_dimencion"]').val() || '0');
        // 4-case delivery: subtotal y coordenadas del cliente
        formData.append('subtotal_carrito', (window._checkoutTotalCarrito || 0).toFixed(2));
        formData.append('lat_cliente', window._latCliente || '');
        formData.append('lng_cliente', window._lngCliente || '');

        axios.post('/web_calcular_envio/calcular_envio', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        })
            .then(function (response) {
                updateShippingResults(response.data);
                toggleLoading(false);
            })
            .catch(function (error) {
                console.error('Error al calcular envío:', error.response ? error.response.data : error);
                const msg = error.response?.data?.message
                    || (error.response?.data?.errors
                        ? Object.values(error.response.data.errors).flat().join(' ')
                        : null)
                    || 'Ocurrió un error al calcular el envío';
                showError(msg);
                toggleLoading(false);
            });
    });

    // Variable global para guardar el total BASE (sin envío)
    let totalBase = 0;
    let totalBaseGuardado = false;
    let precio_envio = 0;
    window._precioEnvioActual = 0;
 
  
   function updateShippingResults(data) {
    const distrito = data.distrito;
    const address_departamento = data.address_departamento;
    const pago_contra_entrega = data.pago_contra_entrega;
    const hora_regresiva = data.hora_regresiva;
    const hora_regresiva_descripcion = data.hora_regresiva_descripcion;
    // Usar costo del sistema 4 casos si viene, sino costo del SP (fallback)
    var deliveryCosto = (data.delivery_costo !== undefined) ? parseFloat(data.delivery_costo) : (parseFloat(data.precio_envio) || 0);
    precio_envio = deliveryCosto;
    window._precioEnvioActual = precio_envio;
    const contacto_direccion = data.contacto_direccion;
    const totalElement = document.getElementById('total-price');

    // ============================================
    // BLOQUEAR/DESBLOQUEAR PAGO CONTRA ENTREGA
    // ============================================
    const radioContraEntrega = document.getElementById('rd_pago_contra_entrega');
    if (radioContraEntrega) {
        const containerContraEntrega = radioContraEntrega.closest('.co-pay-option')
            || radioContraEntrega.closest('.input-container')
            || radioContraEntrega.closest('label');

        if (containerContraEntrega) {
            const radioTile = containerContraEntrega.querySelector('.radio-tile') || containerContraEntrega;

            if (pago_contra_entrega === '1' || pago_contra_entrega === 1) {
                radioContraEntrega.disabled = false;
                containerContraEntrega.style.opacity = '1';
                containerContraEntrega.style.pointerEvents = 'auto';
                if (radioTile) radioTile.style.cursor = 'pointer';

                const existingMessage = containerContraEntrega.querySelector('.disabled-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
            } else {
                radioContraEntrega.disabled = true;
                radioContraEntrega.checked = false;
                containerContraEntrega.style.opacity = '0.5';
                containerContraEntrega.style.pointerEvents = 'none';
                if (radioTile) radioTile.style.cursor = 'not-allowed';

                if (!containerContraEntrega.querySelector('.disabled-message')) {
                    const mensajeNoDisponible = document.createElement('small');
                    mensajeNoDisponible.className = 'disabled-message text-danger d-block mt-2';
                    mensajeNoDisponible.style.fontStyle = 'italic';
                    mensajeNoDisponible.textContent = 'No disponible para esta ubicación';
                    containerContraEntrega.appendChild(mensajeNoDisponible);
                }

                const radioCredito = document.getElementById('rd_pago_credito');
                if (radioCredito) {
                    radioCredito.checked = true;
                }
            }
        }
    }

    if (!totalElement) {
        console.warn('updateShippingResults: no se encontró #total-price');
        return;
    }
    if (!totalBaseGuardado) {
        totalBase = parseFloat(totalElement.textContent.replace(/,/g, '')) || 0;
        totalBaseGuardado = true;
    }

    // Si el blade cambió el base (selección Yape/Normal), usar ese valor
    if (window._totalBaseYapeNormal !== undefined) {
        totalBase = window._totalBaseYapeNormal;
    }

    // El costo ya viene calculado por la lógica 4 casos del backend

    // 3. Calcular el nuevo total desde el base
    const nuevoTotal = totalBase + precio_envio;
    // 4. Actualizar el total
    totalElement.textContent = nuevoTotal.toFixed(2);

    // Actualizar campos ocultos de delivery
    var elDelCosto = document.getElementById('del_costo_envio');
    if (elDelCosto) elDelCosto.value = precio_envio;
    var elDelRazon = document.getElementById('del_razon_envio');
    if (elDelRazon) elDelRazon.value = data.delivery_razon || '';

    // Mostrar mensaje del sistema 4 casos en el área de delivery hint
    if (data.delivery_mensaje) {
        var isGratis = !!data.delivery_gratis;
        // Ámbar solo cuando se sugiere agregar productos para ahorrar en el delivery
        var isBaja   = !!data.delivery_compra_baja && /Agrega/.test(data.delivery_mensaje);
        var isBloq   = !!data.delivery_bloqueado;
        var isSinDel = !!data.delivery_deshabilitado;
        // Colores Valento (dorado); verde = gratis, ámbar = compra baja, rojo = compra bloqueada
        var bColor   = isBloq ? '#dc2626' : (isGratis ? '#16a34a' : (isBaja ? '#d97706' : '#8C6B2E'));
        var bBg      = isBloq ? '#fef2f2' : (isGratis ? '#f0fdf4' : (isBaja ? '#fffbeb' : '#fbf7ef'));
        var ico      = isBloq ? 'fa-lock' : (isSinDel ? 'fa-info-circle' : (isGratis ? 'fa-gift' : (isBaja ? 'fa-exclamation-triangle' : 'fa-truck')));
        var delHTML  = '<div style="margin-top:8px;padding:10px 14px;background:'+bBg+';border:1px solid '+bColor+';border-left-width:3px;'
                     + 'border-radius:4px;font-family:Montserrat,sans-serif;font-size:12.5px;color:#374151;line-height:1.6;">'
                     + '<div style="font-weight:600;"><i class="fas '+ico+'" style="color:'+bColor+'"></i> '
                     + data.delivery_mensaje+'</div>';
        if (data.delivery_mensaje2) {
            delHTML += '<div style="margin-top:4px;font-style:italic;opacity:0.85;">'+data.delivery_mensaje2+'</div>';
        }
        delHTML += '</div>';
        $('.mensajeLlevaloDesde').html(delHTML);
        window.DEL_CHECKOUT_CALCULADO = true;
    }

    // Recalcular vuelto si ya hay monto ingresado
    var inputMontoPagar = document.getElementById('del_monto_pagar');
    if (inputMontoPagar && inputMontoPagar.value) {
        inputMontoPagar.dispatchEvent(new Event('input'));
    }

    // Limpiar contenedores
    $('.costoEnvio').empty();
    $('.lugarEnvio').empty();

    let htmlResultados = '';

    // ENVÍO A DOMICILIO
    if (distrito) {
        if (cantidadInicialCargada >= 199.00) {
            htmlResultados += `
            <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                <div style="
                    width: 24px;
                    height: 24px;
                    flex-shrink: 0;
                    margin-top: 2px;
                ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13" />
                        <polygon points="16,8 20,8 23,11 23,16 16,16 16,8" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                </div>
                <div style="flex: 1;">
                    <p style="
                        margin: 0;
                        font-size: 14px;
                        color: #212529;
                        line-height: 1.5;
                    ">
                        <strong>Envío a domicilio</strong> a <span style="color: #6c757d;">${distrito}</span> - <strong style="color: #75c278;"> Envío Gratis </strong>
                    </p>
                </div>
            </div>
        `;
        } else {
            htmlResultados += `
            <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                <div style="
                    width: 24px;
                    height: 24px;
                    flex-shrink: 0;
                    margin-top: 2px;
                ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13" />
                        <polygon points="16,8 20,8 23,11 23,16 16,16 16,8" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                </div>   
                <div style="flex: 1;">
                    <p style="
                        margin: 0;
                        font-size: 14px;
                        color: #212529;
                        line-height: 1.5;
                    ">
                        <strong>Envío a domicilio</strong> a <span style="color: #6c757d;">${distrito}</span> por <strong style="color: #212529;">S/${precio_envio.toFixed(2)}</strong>
                    </p>
                </div>
            </div>
        `;
        }
    }

    // RETIRO INMEDIATO EN TIENDA
    if (contacto_direccion && hora_regresiva_descripcion !== 'Retírelo por shalom y marvisur') {
        htmlResultados += `
            <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                <div style="
                    width: 24px;
                    height: 24px;
                    flex-shrink: 0;
                    margin-top: 2px;
                ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0d6efd" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div style="flex: 1;">
                    <p style="
                        margin: 0;
                        font-size: 14px;
                        color: #212529;
                        line-height: 1.5;
                    ">
                        <strong>Retiro inmediato</strong> en <span style="color: #6c757d;">${contacto_direccion}</span>
                    </p>
                </div>
            </div>
        `;
    }

    // OPCIONES DE RECOJO SHALOM Y MARVISUR
    if (hora_regresiva_descripcion === 'Retírelo por shalom y marvisur') {
        htmlResultados += `
            <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                <div style="
                    width: 24px;
                    height: 24px;
                    flex-shrink: 0;
                    margin-top: 2px;
                ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0d6efd" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div style="flex: 1;">
                    <p style="
                        margin: 0 0 4px 0;
                        font-size: 14px;
                        color: #212529;
                        line-height: 1.5;
                    ">
                        <strong>Retiro inmediato</strong> en <span style="color: #6c757d;">${contacto_direccion || ''}</span>
                    </p>
                </div>
            </div>
        `;
    }

    $('.costoEnvio').html(htmlResultados);
    // Módulo Delivery: "sin delivery" → por coordinar; "bloqueado" → no se puede comprar
    var delDeshabilitado = data.delivery_deshabilitado === true || data.delivery_deshabilitado === 1 || data.delivery_deshabilitado === '1';
    var delBloqueado     = data.delivery_bloqueado === true || data.delivery_bloqueado === 1 || data.delivery_bloqueado === '1';
    if (delDeshabilitado) {
        $('.clsCostoEnvio').text('Por coordinar').attr('style', 'font-style:italic;font-weight:500;color:#999;');
    } else if (delBloqueado) {
        $('.clsCostoEnvio').text('—').removeAttr('style');
    } else {
        $('.clsCostoEnvio').text('S/ '+precio_envio.toFixed(2)).removeAttr('style');
    }
    window.DEL_BLOQUEADO = delBloqueado;
    $('.co-submit-btn').prop('disabled', delBloqueado).toggleClass('is-bloqueado', delBloqueado)
        .attr('title', delBloqueado ? (data.delivery_mensaje || '') : null);
    $('#txt_hora_regresiva_descripcion').val(hora_regresiva_descripcion);//Se envia al input hidden para ser guardado la descripcion si es
    // console.log('%c [test]-446', 'font-size:13px; background:pink; color:#bf2c9f;', hora_regresiva_descripcion)
    //Para ennviar por agencia o recojo en tienda o delivery domicilio

    // Iniciar countdown si existe
    if (hora_regresiva) {
        initCountdown(hora_regresiva);
    }
}

    function initCountdown(targetTimeStr) {
        if (window.countdownInterval) {
            clearInterval(window.countdownInterval);
        }

        function updateCountdown() {
            const countdownElement = document.getElementById('corte_tiempo_promocion');
            if (!countdownElement) return;

            let [time, modifier] = targetTimeStr.split(/(am|pm)/i);
            let [hours, minutes] = time.split(':').map(Number);

            if (modifier) {
                hours = modifier.toLowerCase() === 'pm' && hours < 12 ? hours + 12 : hours;
                hours = modifier.toLowerCase() === 'am' && hours === 12 ? 0 : hours;
            }

            const now = new Date();
            let targetTime = new Date();
            targetTime.setHours(hours, minutes || 0, 0, 0);

            if (targetTime <= now) {
                targetTime.setDate(targetTime.getDate() + 1);
            }

            const diff = targetTime - now;
            const totalMinutes = Math.floor(diff / (1000 * 60));
            const hoursLeft = Math.floor(totalMinutes / 60);
            const minutesLeft = totalMinutes % 60;

            let countdownText = '';
            if (hoursLeft > 0) {
                countdownText += `${hoursLeft}h `;
            }
            countdownText += `${minutesLeft}m`;

            countdownElement.textContent = countdownText;
        }

        updateCountdown();
        window.countdownInterval = setInterval(updateCountdown, 30000);
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Entendido',
            toast: true,
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }

    // ============================================
    // COMPARTIR MI UBICACIÓN
    // ============================================
    $('#btnShareLocation').on('click', function () {
        const btn = $(this);
        const btnText = $('#btnShareLocationText');
        const spinner = $('#locationSpinner');

        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'warning',
                title: 'No soportado',
                text: 'Tu navegador no soporta geolocalización.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Mostrar estado de carga
        btn.prop('disabled', true);
        btnText.text('Obteniendo ubicación...');
        spinner.removeClass('d-none');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                // Guardar coordenadas para cálculo 4 casos de delivery
                window._latCliente = lat;
                window._lngCliente = lon;
                var _delLatEl = document.getElementById('del_lat_cliente');
                if (_delLatEl) _delLatEl.value = lat;
                var _delLngEl = document.getElementById('del_lng_cliente');
                if (_delLngEl) _delLngEl.value = lon;

                // Reverse geocoding con OpenStreetMap Nominatim
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1&accept-language=es`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.display_name) {
                            $('#txt_direccion').val(data.display_name).trigger('change');
                            $('#txt_direccion').addClass('is-valid').removeClass('is-invalid');

                            Swal.fire({
                                icon: 'success',
                                title: 'Ubicación obtenida',
                                text: 'Se ha completado tu dirección automáticamente. Puedes editarla si lo necesitas.',
                                confirmButtonText: 'Entendido',
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                    })
                    .catch(function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener la dirección. Inténtalo de nuevo.',
                            confirmButtonText: 'Entendido'
                        });
                    })
                    .finally(function () {
                        btn.prop('disabled', false);
                        btnText.text('Compartir mi ubicación');
                        spinner.addClass('d-none');
                    });
            },
            function (error) {
                btn.prop('disabled', false);
                btnText.text('Compartir mi ubicación');
                spinner.addClass('d-none');

                let mensaje = 'No se pudo obtener tu ubicación.';
                if (error.code === error.PERMISSION_DENIED) {
                    mensaje = 'Permiso de ubicación denegado. Habilítalo en la configuración de tu navegador.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    mensaje = 'La información de ubicación no está disponible.';
                } else if (error.code === error.TIMEOUT) {
                    mensaje = 'La solicitud de ubicación ha expirado. Inténtalo de nuevo.';
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Ubicación no disponible',
                    text: mensaje,
                    confirmButtonText: 'Entendido'
                });
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
});