@php
    $pc = $paginaContacto ?? null;
    $telefonos = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $pc->telefonos_texto ?? $pc->telefono ?? '')));
    $emails = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $pc->emails_texto ?? $pc->email ?? '')));
    $columnas = $contactoColumnas ?? collect();
@endphp
<section id="contacto" class="jh-contacto-seccion default-padding">
    <div class="container">
        <div class="row jh-contacto-main">

            {{-- Sidebar de información de contacto --}}
            <div class="col-lg-4">
                <aside class="jh-contacto-sidebar">
                    <div class="jh-contacto-sidebar-header">
                        <i class="fas fa-headset"></i>
                        <h4>Información de contacto</h4>
                    </div>
                    <div class="jh-contacto-sidebar-body">

                        @if(count($telefonos))
                        <div class="jh-contacto-sidebar-block">
                            <div class="jh-contacto-sidebar-icon"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <h5>{{ $pc->telefono_etiqueta ?? 'Teléfono' }}</h5>
                                @foreach($telefonos as $tel)
                                <p class="mb-1"><a href="tel:{{ preg_replace('/\D/','',$tel) }}">{{ $tel }}</a></p>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if(count($emails))
                        <div class="jh-contacto-sidebar-block">
                            <div class="jh-contacto-sidebar-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <h5>{{ $pc->email_etiqueta ?? 'Correo Electrónico' }}</h5>
                                @foreach($emails as $em)
                                <p class="mb-1"><a href="mailto:{{ $em }}">{{ $em }}</a></p>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($pc->ubicacion ?? null)
                        <div class="jh-contacto-sidebar-block">
                            <div class="jh-contacto-sidebar-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h5>{{ $pc->ubicacion_etiqueta ?? 'Dirección' }}</h5>
                                <p>{{ $pc->ubicacion }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Redes sociales --}}
                        <div class="jh-contacto-sidebar-social">
                            <a href="#" title="Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="WhatsApp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" title="TikTok" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                        </div>

                    </div>
                </aside>
            </div>

            {{-- Formulario de contacto --}}
            <div class="col-lg-8">
                <div class="jh-contacto-form-wrap">
                    @if($pc->form_titulo ?? null)
                    <h2 class="jh-contacto-form-title">{{ $pc->form_titulo }}</h2>
                    @endif
                    @if($pc->form_descripcion ?? $pc->form_subtitulo ?? null)
                    <div class="jh-contacto-form-desc">{!! nl2br(e($pc->form_descripcion ?? $pc->form_subtitulo)) !!}</div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('contacto.pagina.submit') }}" method="post" class="jh-contacto-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input class="form-control" name="nombre" type="text" required
                                        placeholder="Tu nombre" value="{{ old('nombre') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apellido</label>
                                    <input class="form-control" name="apellido" type="text"
                                        placeholder="Tu apellido" value="{{ old('apellido') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Celular <span class="text-danger">*</span></label>
                                    <input class="form-control" name="telefono" type="text" required
                                        placeholder="Ej: 981629466" value="{{ old('telefono') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Algunos Productos <span class="text-danger">*</span></label>
                                    <input class="form-control" name="producto_interes" type="text" required
                                        placeholder="{{ $pc->productos_placeholder ?? 'Anillos de Plástico' }}"
                                        value="{{ old('producto_interes') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha del Mensaje <span class="text-danger">*</span></label>
                                    <input class="form-control" name="fecha_mensaje" type="date" required
                                        value="{{ old('fecha_mensaje', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Mensaje</label>
                                    <textarea class="form-control" name="mensaje" rows="4"
                                        placeholder="Cuéntanos en qué podemos ayudarte...">{{ old('mensaje') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="jh-contacto-form-submit">
                                    <i class="fas fa-paper-plane"></i> Enviar mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Mapa Google --}}
@if($pc->mapa_embed_url ?? null)
<section class="jh-contacto-mapa default-padding-bottom">
    <div class="container">
        <div class="jh-contacto-mapa-header">
            <h3><i class="fas fa-map-marked-alt" style="color:#ff0605;margin-right:8px;"></i>Encuéntranos aquí</h3>
            <p>Visítanos o escríbenos para coordinar una atención personalizada</p>
        </div>
        <div class="jh-contacto-mapa-wrap">
            <iframe src="{{ $pc->mapa_embed_url }}" width="100%" height="420" style="border:0;"
                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                title="Ubicación Valento"></iframe>
        </div>
    </div>
</section>
@endif

{{-- Tres columnas informativas --}}
@if($columnas->isNotEmpty())
<section class="jh-contacto-columnas default-padding">
    <div class="container">
        <div class="row">
            @foreach($columnas as $idx => $col)
            <div class="col-lg-4 col-md-6 mb-30">
                <div class="jh-contacto-columna-item">
                    <div class="jh-contacto-columna-num">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    @if($col->titulo ?? null)<h4>{{ $col->titulo }}</h4>@endif
                    @if($col->descripcion ?? null)<p>{{ $col->descripcion }}</p>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@include('web.pages.partials.contacto_suscripcion_jh', ['paginaContacto' => $pc])
