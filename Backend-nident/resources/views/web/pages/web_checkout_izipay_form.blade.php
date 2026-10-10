<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pago con Izipay - Valento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    {{-- Krypton.js Izipay (micuentaweb) - clave pública --}}
    <script type="text/javascript"
        src="https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
        kr-public-key="{{ $publicKey }}"
        kr-language="es-Es">
    </script>
    <link rel="stylesheet" href="https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.css">
    <script type="text/javascript" src="https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.js"></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #e8e8e8;
            overflow-x: hidden;
            overflow-y: auto;
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0;
            box-sizing: border-box;
        }
        /* Hide Krypton's own header — we show our own above the iframe */
        .kr-header,
        .kr-payment-header,
        .kr-popin-wrapper .kr-brand,
        [class*="kr-header"] { display: none !important; }
    </style>
</head>
<body>
    <!-- Formulario embebido de Izipay (sin kr-popin → formulario completo directo) -->
    <div class="kr-embedded" kr-form-token="{{ $formToken }}"></div>

    <script>
        function _reportIzipayHeight() {
            var h = Math.max(
                document.body.scrollHeight,
                document.documentElement.scrollHeight,
                document.body.offsetHeight
            );
            window.parent.postMessage({ type: 'izipay-resize', height: h }, '*');
        }
        document.addEventListener('DOMContentLoaded', function() {
            _reportIzipayHeight();
            setTimeout(_reportIzipayHeight, 800);
            setTimeout(_reportIzipayHeight, 2000);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var formData = @json($form_data ?? []);
            var email = @json($email);
            var amount = @json($amount);
            var baseUrl = '{{ url("") }}';
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Reportar altura cuando Krypton haya renderizado el formulario completo
            if (typeof KR !== 'undefined') {
                KR.onLoaded(function() {
                    setTimeout(_reportIzipayHeight, 300);
                });

                KR.onSubmit(function(res) {
                    var paymentData = res && res.payload ? res.payload : res;
                    var krAnswer = (paymentData.rawClientAnswer != null) ? paymentData.rawClientAnswer : (paymentData.clientAnswer ? (typeof paymentData.clientAnswer === 'string' ? paymentData.clientAnswer : JSON.stringify(paymentData.clientAnswer)) : (paymentData['kr-answer'] || ''));
                    var krHash = paymentData.hash || paymentData.krHash || '';

                    // ── Validar estado del pago ANTES de enviar al backend ──
                    try {
                        var answerObj = typeof krAnswer === 'string' ? JSON.parse(krAnswer) : krAnswer;
                        var orderStatus = answerObj.orderStatus || '';
                        var detailedStatus = (answerObj.transactions && answerObj.transactions[0])
                            ? (answerObj.transactions[0].detailedStatus || '')
                            : '';

                        if (orderStatus !== 'PAID') {
                            var errorMsg = 'Tu pago no pudo completarse. Por favor intenta con otra tarjeta.';
                            if (detailedStatus === 'REFUSED')         errorMsg = 'Pago rechazado por el banco. Verifica los datos de tu tarjeta.';
                            else if (detailedStatus === 'CANCELLED')  errorMsg = 'El pago fue cancelado.';
                            else if (detailedStatus === 'EXPIRED')    errorMsg = 'El tiempo de pago expiró. Inténtalo de nuevo.';
                            else if (orderStatus)                     errorMsg = 'Pago no aprobado (estado: ' + orderStatus + '). Intenta con otra tarjeta.';

                            var swalTarget = (window.parent && window.parent.Swal) ? window.parent.Swal : Swal;
                            if (window.parent && window.parent.Swal) window.parent.Swal.close();
                            swalTarget.fire({
                                icon: 'error',
                                title: 'Pago No Completado',
                                text: errorMsg,
                                confirmButtonText: 'Intentar de nuevo',
                                confirmButtonColor: '#dc3545'
                            });
                            return false; // No enviar al backend
                        }
                    } catch(parseErr) {
                        console.warn('[Izipay] No se pudo leer kr-answer:', parseErr);
                    }
                    // ── Fin validación ──────────────────────────────────────

                    axios.post(baseUrl + '/web_checkout/enviar_pago_con_izipay', {
                        'kr-answer': krAnswer,
                        'kr-hash': krHash,
                        'kr-hash-algorithm': 'sha256_hmac',
                        'email': email,
                        'amount': amount,
                        'form_data': formData,
                        _token: csrf
                    }, {
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    }).then(function(response) {
                        // Cerrar el modal de Izipay en el padre y mostrar el resultado allí,
                        // para que ningún overlay de Krypton tape el mensaje.
                        if (window.parent && window.parent.Swal) {
                            window.parent.Swal.close();
                            if (response.data && response.data.success) {
                                window.parent.location.href = baseUrl + '/thank-you';
                            } else {
                                window.parent.Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: (response.data && response.data.message) ? response.data.message : 'No se pudo completar el pago.',
                                    confirmButtonText: 'Entendido'
                                });
                            }
                        } else {
                            if (response.data && response.data.success) {
                                window.location.href = baseUrl + '/thank-you';
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.data.message || 'No se pudo completar el pago.', confirmButtonText: 'Entendido' });
                            }
                        }
                    }).catch(function(err) {
                        var msg = (err.response && err.response.data && err.response.data.message) ? err.response.data.message : 'Error de conexión. Intenta de nuevo.';
                        if (window.parent && window.parent.Swal) {
                            window.parent.Swal.close();
                            window.parent.Swal.fire({
                                icon: 'error',
                                title: 'Error en el pago',
                                text: msg,
                                confirmButtonText: 'Entendido'
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error en el pago', text: msg, confirmButtonText: 'Entendido' });
                        }
                    });
                    return false;
                });
            }
        });
    </script>
</body>
</html>
