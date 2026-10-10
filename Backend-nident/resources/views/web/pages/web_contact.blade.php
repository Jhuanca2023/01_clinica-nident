@extends('web.base')
<!-- Contenido en el Head de la pagina -->
@section('head_page')

@section('title', $metaData->titulo_pagina ?? 'Valento')
@section('meta_description', $metaData->descripcion_pagina ?? '')
<!-- extras -->
@vite(['resources/sass/web_contact.scss', 'resources/js/web_contact.js'])
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site') }}"></script>
@endsection
<!-- COOKIES AND POLICE : HEADER-->
<!-- Contenido en el Body -->
@section('content')
<!-- Start breadcrumb -->
@include('web.partials.breadcrumb')

@php
$footerData = Helpers::footer_();
@endphp

<section class="contact-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="row">
                    <!-- Columna de información de contacto -->
                    <div class="col-lg-5 contact-info-col">
                        <div class="contact-card">
                            <h4 class="section-title">Información de Contacto</h4>

                            <div class="mb-5">
                                <h5 class="mb-3">La oficina</h5>
                                <ul class="contact-list">
                                    <li>
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span style="margin-left: 4rem;">{{ $footerData[0]->contacto_direccion }}</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-phone-alt"></i>
                                        <span style="margin-left: 4rem;"><a href="tel:{{ $footerData[0]->contacto_telefono }}">{{ $footerData[0]->contacto_telefono }}</a></span>
                                    </li>
                                    <li>
                                       <i class="fas fa-envelope"></i>
                                       <span style="margin-left: 4rem;"><a href="mailto:{{ $footerData[0]->contacto_email }}">{{ $footerData[0]->contacto_email }}</a></span>
                                    </li>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h5 class="mb-3">Horarios de Atención</h5>
                                <ul class="contact-list contact-time">

                                <li>
                                      <i class="fas fa-clock"></i>
                                       <span style="margin-left: 4rem;">{!! (count($footerData)!=0?str_replace("\n", "<br />", $footerData[0]->nuestros_horarios):'')!!}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Columna del formulario -->
                    <div class="col-lg-7">
                        <div class="contact-form-wrapper">
                            <h4 class="section-title">¿Tienes alguna pregunta?</h4>
                            <p class="mb-4">Complete el siguiente formulario y nos pondremos en contacto con usted a la brevedad.</p>

                            @if(session('success'))
                            <div class="alert-success">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            </div>
                            @endif

                            @if($errors->any())
                            <div class="alert-error">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Por favor, corrige los siguientes errores:</strong>
                                <ul class="mt-2 mb-0">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form action="{{ route('contact.submit') }}" method="POST" id="contactForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="">
                                            <label for="txt_nombre" class="form-label">Nombre completo *</label>
                                            <input type="text" class="form-control" id="txt_nombre" name="txt_nombre" placeholder="Tu nombre completo" maxlength="100" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="clsMarginBottom-0">
                                            <label for="txt_email" class="form-label">Correo electrónico *</label>
                                            <input type="email" class="form-control" id="txt_email" name="txt_email" placeholder="ejemplo@correo.com" maxlength="120" required>
                                            <small id="txt_email_feedback" class="text-danger d-none"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="clsMarginBottom-0">
                                            <label for="txt_telefono" class="form-label">Teléfono *</label>
                                            <input type="tel" class="form-control" id="txt_telefono" name="txt_telefono" placeholder="+51 970048451" inputmode="tel" maxlength="16" required>
                                            <small id="txt_telefono_feedback" class="text-danger d-none"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="">
                                            <label for="txt_asunto" class="form-label">Asunto *</label>
                                            <input type="text" class="form-control" id="txt_asunto" name="txt_asunto" placeholder="Motivo de tu consulta" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="txt_mensaje" class="form-label">Mensaje *</label>
                                    <textarea class="form-control" id="txt_mensaje" name="txt_mensaje" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-token">
                                    <small id="recaptcha_error" class="text-danger d-none"></small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary-custom text-white">
                                        <i class="fas fa-paper-plane me-2"></i> Enviar mensaje
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
<!-- COOKIES AND POLICE: FOOTER -->
@section('footer_page')
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Desplazarse suavemente hacia el formulario después del envío exitoso
        document.getElementById('contactForm').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });
</script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const txtNombre = document.getElementById('txt_nombre');
        const txtEmail = document.getElementById('txt_email');
        const txtTelefono = document.getElementById('txt_telefono');
        const txtEmailFeedback = document.getElementById('txt_email_feedback');
        const txtTelefonoFeedback = document.getElementById('txt_telefono_feedback');

        const regexSoloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        const regexTelefono = /^\+?\d[\d\s]{7,14}$/;

        function pintarEstado(input, feedbackEl, esValido, mensaje = '') {
            if (!input) return;
            input.classList.toggle('is-invalid', !esValido);
            input.classList.toggle('is-valid', esValido && input.value.trim() !== '');

            if (feedbackEl) {
                if (!esValido && mensaje) {
                    feedbackEl.textContent = mensaje;
                    feedbackEl.classList.remove('d-none');
                } else {
                    feedbackEl.textContent = '';
                    feedbackEl.classList.add('d-none');
                }
            }
        }

        function validarNombre() {
            if (!txtNombre) return;
            txtNombre.value = txtNombre.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
            const valor = txtNombre.value.trim();
            if (!valor) {
                txtNombre.setCustomValidity('');
                return;
            }
            txtNombre.setCustomValidity(regexSoloLetras.test(valor) ? '' : 'El nombre solo permite letras.');
        }

        function validarEmail() {
            if (!txtEmail) return;
            const valor = txtEmail.value.trim();
            if (!valor) {
                txtEmail.setCustomValidity('');
                pintarEstado(txtEmail, txtEmailFeedback, true);
                return;
            }
            const esValido = regexEmail.test(valor);
            const mensaje = 'Ingrese un correo electronico valido.';
            txtEmail.setCustomValidity(esValido ? '' : mensaje);
            pintarEstado(txtEmail, txtEmailFeedback, esValido, mensaje);
        }

        function validarTelefono() {
            if (!txtTelefono) return;
            let valor = txtTelefono.value.replace(/[^\d+\s]/g, '');
            if (valor.includes('+')) {
                valor = (valor.startsWith('+') ? '+' : '') + valor.replace(/\+/g, '');
            }
            valor = valor.replace(/\s{2,}/g, ' ').trimStart();
            txtTelefono.value = valor;

            const digitos = valor.replace(/[^\d]/g, '');
            if (!valor) {
                txtTelefono.setCustomValidity('');
                pintarEstado(txtTelefono, txtTelefonoFeedback, true);
                return;
            }
            if (digitos.length < 9 || digitos.length > 12 || !regexTelefono.test(valor)) {
                const mensaje = 'Ingrese un telefono valido. Ejemplo: +51 970048451';
                txtTelefono.setCustomValidity(mensaje);
                pintarEstado(txtTelefono, txtTelefonoFeedback, false, mensaje);
                return;
            }
            txtTelefono.setCustomValidity('');
            pintarEstado(txtTelefono, txtTelefonoFeedback, true);
        }

        if (txtNombre) {
            txtNombre.addEventListener('input', validarNombre);
            txtNombre.addEventListener('blur', validarNombre);
        }
        if (txtEmail) {
            txtEmail.addEventListener('input', validarEmail);
            txtEmail.addEventListener('blur', validarEmail);
        }
        if (txtTelefono) {
            txtTelefono.addEventListener('input', validarTelefono);
            txtTelefono.addEventListener('blur', validarTelefono);
        }

        // Validación del formulario antes de enviar (reCAPTCHA v3)
        const form = document.getElementById('contactForm');
        const recaptchaSiteKey = '{{ config("services.recaptcha.site") }}';

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const recaptchaError = document.getElementById('recaptcha_error');
                recaptchaError.classList.add('d-none');

                // Si no hay sitekey configurada, enviar directo (entorno sin reCAPTCHA)
                if (!recaptchaSiteKey) {
                    form.submit();
                    return;
                }

                // v3: ejecutar en background y obtener token
                grecaptcha.ready(function () {
                    grecaptcha.execute(recaptchaSiteKey, { action: 'contact' })
                        .then(function (token) {
                            document.getElementById('g-recaptcha-response-token').value = token;
                            form.submit();
                        })
                        .catch(function () {
                            recaptchaError.textContent = 'Error al verificar reCAPTCHA. Recarga la página e inténtalo de nuevo.';
                            recaptchaError.classList.remove('d-none');
                        });
                });
            });
        }
    });
</script>
@endsection