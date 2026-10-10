@php
$footerData = Helpers::footer_();
$footerLogoSrc = (count($footerData) > 0 && !empty($footerData[0]->logo_footer))
    ? asset($footerData[0]->logo_footer)
    : asset('imagenes/home.svg');
@endphp

<style>
/* Forzar texto Libro de Reclamos: 2 líneas o recorte con "..." */
.ft-reclamos-block { max-width: 180px !important; }
.ft-reclamos-block__text {
    max-width: 180px !important;
    min-width: 0 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    word-break: break-word !important;
    white-space: normal !important;
}
</style>

<!-- ══ FOOTER ══ -->
<footer class="ft-root">

    {{-- Main content --}}
    <div class="">
        <div class="container">
            <div class="row ft-cols">

                {{-- ── Col 1: Brand ── --}}
                <div class="col-12 col-md-6 col-lg-3 ft-col">
                    <img src="{{ $footerLogoSrc }}"
                         class="cls_ft-brand__logo" alt="Valento">

                    @if(count($footerData) > 0)
                    <p class="ft-brand__desc">
                        {!! str_replace("\n", "<br>", Str::limit($footerData[0]->sobre_la_empresa, 160)) !!}
                    </p>
                    @endif

                    @if(count($footerData) > 0)
                    <div class="ft-brand__call">
                        <div class="ft-call-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="ft-call-info">
                            <span>Llámanos 24/7</span>
                            <a href="tel:{{ $footerData[0]->contacto_telefono }}">
                                +51 {{ $footerData[0]->contacto_telefono }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ── Col 2: Links ── --}}
                <div class="col-6 col-md-3 col-lg-2 ft-col">
                    <h5 class="ft-heading">Información</h5>
                    <ul class="ft-links">
                        <li><a href="/servicio-cliente" target="_blank">Servicio al cliente</a></li>
                        <li><a href="/terminos-condiciones" target="_blank">Términos &amp; Condiciones</a></li>
                        <li><a href="/politica-privacidad" target="_blank">Política de privacidad</a></li>
                        <li><a href="/delivery-informacion" target="_blank">Información de Delivery</a></li>
                    </ul>
                </div>
 
                {{-- ── Col 3: Hours ── --}}
                <div class="col-6 col-md-3 col-lg-2 ft-col">
                    <h5 class="ft-heading">Horarios</h5>
                    @if(count($footerData) > 0)
                    <div class="ft-hours">
                        {!! str_replace("\n", "<br>", $footerData[0]->nuestros_horarios) !!}
                    </div>
                    @endif
                </div>

                {{-- ── Col 4: Contact ── --}}
                <div class="col-12 col-md-6 col-lg-3 ft-col">
                    <h5 class="ft-heading">Contáctenos</h5>
                    @if(count($footerData) > 0)
                    <ul class="ft-contact">
                        <li>
                            <div class="ft-contact__icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="ft-contact__text">
                                <span>Dirección</span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($footerData[0]->contacto_direccion) }}" target="_blank">
                                    {{ $footerData[0]->contacto_direccion }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="ft-contact__icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="ft-contact__text">
                                <span>WhatsApp</span>
                                <a href="https://web.whatsapp.com/send?phone={{ $footerData[0]->contacto_telefono }}&text=Bienvenidos%20a%20lucdesoft%2C%20necesito%20asesoramiento." onclick="window.open(this.href,'lucdesoft_wa');return false;">
                                    {{ $footerData[0]->contacto_telefono }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="ft-contact__icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="ft-contact__text">
                                <span>Email</span>
                                <a href="mailto:{{ $footerData[0]->contacto_email }}">
                                    {{ $footerData[0]->contacto_email }}
                                </a>
                            </div>
                        </li>
                    </ul>
                    @endif
                </div>

                {{-- ── Col 5: Libro de Reclamos (cuarta columna de contenido, como en referencia) ── --}}
                <div class="col-12 col-md-6 col-lg-2 ft-col ft-col--reclamos">
                    <h5 class="ft-heading">Libro de Reclamos</h5>
                    <a href="/web_libro_reclamos" class="ft-reclamos-block" target="_blank" rel="noopener">
                        <span class="ft-reclamos-block__icon">
                            <img src="{{ asset('imagenes/libro_reclamo.png') }}" alt="Libro de Reclamos">
                        </span>
                        <span class="ft-reclamos-block__text">LIBRO DE RECLAMACIONES</span>
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Social bar ── --}}
    @php
        $hasSocialLinks = false;
        if(count($footerData) > 0) {
            $f = $footerData[0];
            $hasSocialLinks = !empty($f->red_social_facebook) || 
                              !empty($f->red_social_twitter) || 
                              !empty($f->red_social_instagram) || 
                              !empty($f->red_social_linkedin) || 
                              !empty($f->red_social_youtobe);
        }
    @endphp
    @if($hasSocialLinks)
    <div class="ft-social-bar">
        <div class="container">
            <div class="ft-social-inner">
                <span class="ft-social-label">Síguenos en</span>
                <div class="ft-social-icons">
                    @if(!empty($footerData[0]->red_social_facebook))
                    <a href="{{ $footerData[0]->red_social_facebook }}" class="ft-social-icon ft-social-icon--fb" title="Facebook" target="_blank" rel="noopener">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    @endif
                    @if(!empty($footerData[0]->red_social_twitter))
                    <a href="{{ $footerData[0]->red_social_twitter }}" class="ft-social-icon ft-social-icon--tw" title="Twitter" target="_blank" rel="noopener">
                        <i class="fab fa-twitter"></i>
                    </a>
                    @endif
                    @if(!empty($footerData[0]->red_social_instagram))
                    <a href="{{ $footerData[0]->red_social_instagram }}" class="ft-social-icon ft-social-icon--ig" title="Instagram" target="_blank" rel="noopener">
                        <i class="fab fa-instagram"></i>
                    </a>
                    @endif
                    @if(!empty($footerData[0]->red_social_linkedin))
                    <a href="{{ $footerData[0]->red_social_linkedin }}" class="ft-social-icon ft-social-icon--li" title="LinkedIn" target="_blank" rel="noopener">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    @endif
                    @if(!empty($footerData[0]->red_social_youtobe))
                    <a href="{{ $footerData[0]->red_social_youtobe }}" class="ft-social-icon ft-social-icon--yt" title="YouTube" target="_blank" rel="noopener">
                        <i class="fab fa-youtube"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Espaciador invisible para mantener la misma altura y que no se pegue la franja inferior --}}
    <div class="ft-social-bar" style="opacity: 0; pointer-events: none; border: none;">
        <div class="container">
            <div class="ft-social-inner">
                <div class="ft-social-icons">
                    <div style="width: 40px; height: 40px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Bottom bar ── --}}
    <div class="ft-bottom">
        <div class="container">
            <div class="ft-bottom__inner">
                <p>Copyright &copy; <span class="ft-year"></span> Valento Perú Store. Todos los derechos reservados.</p>
                <a href="https://fabriqadigital.com" target="_blank" rel="noopener">
                    Desarrollado por Fabriqadigital
                </a>
            </div>
        </div>
    </div>

</footer>

{{-- WhatsApp floating button --}}
@if(count($footerData) > 0)
<a href="https://web.whatsapp.com/send?phone={{ $footerData[0]->contacto_telefono }}&text=Bienvenidos%20a%20lucdesoft%2C%20necesito%20asesoramiento."
   onclick="window.open(this.href,'lucdesoft_wa');return false;" class="whatsapp-btn" aria-label="WhatsApp">
    <i class="mdi mdi-whatsapp"></i>
</a>
@endif

<!-- Modal de Términos y Condiciones -->
<div class="modal fade" id="IdOpenModalDocument" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="idBodyDocument"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Año dinámico
    document.querySelectorAll('.ft-year').forEach(function(el) {
        el.textContent = new Date().getFullYear();
    });

    // Modal documentos
    function operModalDocument(id_footer_document) {
        axios.get('/web_terminos', { params: { id_footer_document } })
            .then(function(response) {
                document.getElementById('idBodyDocument').innerHTML = response.data;
                $('#IdOpenModalDocument').modal('show');
            })
            .catch(function(error) {
                console.error('Error:', error);
            });
    }

    // Ajax subscription forms
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ajax-sub-form').forEach(function(form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...';
                submitBtn.disabled = true;

                try {
                    const response = await axios.post(this.action, Object.fromEntries(formData.entries()), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json', 'Accept': 'application/json' }
                    });

                    if (response.data.success) {
                        Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, timer: 3000, showConfirmButton: false, timerProgressBar: true });
                        this.reset();
                    } else {
                        let msg = '';
                        for (const k in response.data.errors) msg += response.data.errors[k] + '<br>';
                        Swal.fire({ icon: 'error', title: 'Error', html: msg, confirmButtonText: 'Entendido' });
                    }
                } catch (err) {
                    let msg = 'Ocurrió un error. Por favor intenta nuevamente.';
                    if (err.response && err.response.status === 422) {
                        msg = '';
                        for (const k in err.response.data.errors) msg += err.response.data.errors[k] + '<br>';
                    }
                    Swal.fire({ icon: 'error', title: 'Error', html: msg, confirmButtonText: 'Entendido' });
                } finally {
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            });
        });
    });
</script>
@endpush
