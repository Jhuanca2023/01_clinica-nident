@php
    $f = isset($footerData) && count($footerData) > 0 ? $footerData[0] : null;
    $urlWa = $urlWhatsapp ?? Helpers::landingWhatsappUrl($f);

    $parseLineas = static function (?string $texto): array {
        if (!$texto) {
            return [];
        }
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $texto))));
    };

    $telefonos = $parseLineas($f->footer_telefonos ?? null);
    if (empty($telefonos)) {
        foreach (['contacto_telefono', 'contacto_telefono_secundario'] as $campo) {
            if (!empty($f->{$campo})) {
                $telefonos[] = $f->{$campo};
            }
        }
    }

    $emails = $parseLineas($f->footer_emails ?? null);
    if (empty($emails) && !empty($f->contacto_email)) {
        $emails[] = $f->contacto_email;
    }

    $redesRaw = $f->footer_redes ?? null;
    $redes = [];
    if ($redesRaw) {
        $decoded = is_string($redesRaw) ? json_decode($redesRaw, true) : $redesRaw;
        if (is_array($decoded)) {
            usort($decoded, fn ($a, $b) => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));
            $redes = $decoded;
        }
    }
    if (empty($redes)) {
        $legacy = [
            ['tipo' => 'whatsapp', 'url' => $urlWa,                             'etiqueta' => null],
            ['tipo' => 'tiktok',   'url' => $f->url_tiktok   ?? $f->red_social_tiktok   ?? null, 'etiqueta' => 'TikTok'],
            ['tipo' => 'facebook', 'url' => $f->url_facebook ?? $f->red_social_facebook ?? null, 'etiqueta' => 'Facebook'],
        ];
        if (!empty($f->contacto_email)) {
            $legacy[] = ['tipo' => 'correo', 'url' => 'mailto:' . $f->contacto_email, 'etiqueta' => 'Correo'];
        }
        $orden = 1;
        foreach ($legacy as $item) {
            if (!empty($item['url']) && $item['url'] !== '#') {
                $redes[] = array_merge($item, ['orden' => $orden++]);
            }
        }
    }

    $iconoRed = static function (string $tipo): string {
        return match (strtolower($tipo)) {
            'facebook'        => 'fab fa-facebook-f',
            'whatsapp'        => 'fab fa-whatsapp',
            'tiktok'          => 'fab fa-tiktok',
            'instagram'       => 'fab fa-instagram',
            'youtube'         => 'fab fa-youtube',
            'linkedin'        => 'fab fa-linkedin-in',
            'twitter', 'x'   => 'fab fa-x-twitter',
            'correo', 'email' => 'fas fa-envelope',
            default           => 'fas fa-link',
        };
    };

    $navFooter = is_array($f->nav_footer ?? null) ? $f->nav_footer : (json_decode($f->nav_footer ?? '[]', true) ?: [
        ['label' => 'Nosotros',   'href' => '/nosotros'],
        ['label' => 'Productos',  'href' => '/productos'],
        ['label' => 'Contáctanos','href' => '/contacto'],
    ]);

    $descripcion = $f->descripcion_footer ?? $f->sobre_la_empresa
        ?? 'En Valento, distribuimos y fabricamos productos de alta calidad para tus necesidades de oficina.';
    $direccion   = $f->contacto_direccion ?? '';
    $logoUrl     = !empty($f->url_logo) ? asset('storage/' . ltrim($f->url_logo, '/'))
                 : asset('temp02/assets/img/jh_importaciones/inicio/logo_jh.png');
@endphp

<footer class="bg-dark default-padding-top text-light">
    <div class="container">
        <div class="f-items">
            <div class="row">

                {{-- Columna 1: Logo + descripción + formulario de suscripción --}}
                <div class="col-lg-4 col-md-6 item">
                    <div class="f-item">
                        <img src="{{ $logoUrl }}" alt="{{ $f->nombre_empresa ?? 'Valento' }}">
                        <p>{{ $descripcion }}</p>
                        @if($f && !empty($f->texto_suscripcion))
                        <p class="text-italic">{{ $f->texto_suscripcion }}</p>
                        @else
                        <p class="text-italic">Escríbenos tu correo y te contactaremos</p>
                        @endif
                        <div class="subscribe">
                            <form action="#">
                                <div class="input-group stylish-input-group">
                                    <input type="email" placeholder="Tu correo electrónico" class="form-control" name="email">
                                    <button type="submit">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Columna 2: Enlaces de navegación --}}
                <div class="col-lg-2 col-md-6 item">
                    <div class="f-item link">
                        <h4>{{ $f->titulo_enlaces ?? 'Enlaces' }}</h4>
                        <ul>
                            @foreach($navFooter as $lnk)
                            <li><a href="{{ $lnk['href'] ?? '#' }}">{{ $lnk['label'] ?? '' }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Columna 3: Redes y contacto --}}
                <div class="col-lg-2 col-md-6 item">
                    <div class="f-item link">
                        <h4>{{ $f->titulo_redes ?? 'Redes y contacto' }}</h4>
                        <ul>
                            {{-- Teléfonos: cada uno abre su propio WhatsApp con tooltip del número --}}
                            @foreach($telefonos as $tel)
                            @php
                                $telLimpio = preg_replace('/\D/', '', $tel);
                                $telWaUrl = 'https://web.whatsapp.com/send?phone=51' . $telLimpio . '&text=' . rawurlencode('Hola Valento, necesito información.');
                            @endphp
                            <li>
                                <a href="{{ $telWaUrl }}" onclick="window.open(this.href,'lucdesoft_wa');return false;" rel="noopener noreferrer" title="WhatsApp: {{ $tel }}">
                                    <i class="fab fa-whatsapp"></i> {{ $tel }}
                                </a>
                            </li>
                            @endforeach
                            {{-- Redes sociales: omitir whatsapp y correo (ya se muestran arriba/abajo) --}}
                            @foreach($redes as $red)
                                @php $tipoRed = strtolower($red['tipo'] ?? ''); @endphp
                                @if(!empty($red['url']) && !in_array($tipoRed, ['whatsapp', 'correo', 'email']))
                                <li>
                                    <a href="{{ $red['url'] }}" target="_blank" rel="noopener noreferrer">
                                        <i class="{{ $iconoRed($tipoRed) }}"></i>
                                        {{ $red['etiqueta'] ?? ucfirst($tipoRed ?: 'Red') }}
                                    </a>
                                </li>
                                @endif
                            @endforeach
                            {{-- Correos: mostrar el email real, no solo "Correo" --}}
                            @foreach($emails as $mail)
                            <li>
                                <a href="mailto:{{ $mail }}" title="{{ $mail }}">
                                    <i class="fas fa-envelope"></i> {{ $mail }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Columna 4: Ubícanos (dirección, teléfono, correo con iconos) --}}
                <div class="col-lg-4 col-md-6 item">
                    <div class="f-item address">
                        <h4>{{ $f->titulo_ubicacion ?? 'Ubícanos' }}</h4>
                        <ul>
                            @foreach($emails as $mail)
                            <li>
                                <i class="fas fa-envelope"></i>
                                <p>Correo <span><a href="mailto:{{ $mail }}">{{ $mail }}</a></span></p>
                            </li>
                            @endforeach

                            @if(count($telefonos) > 0)
                            <li>
                                <i class="fas fa-phone"></i>
                                <p>Celular <span>{{ implode(' · ', $telefonos) }}</span></p>
                            </li>
                            @endif

                            @if($direccion)
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <p>Dirección <span>{{ $direccion }}</span></p>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Footer Bottom --}}
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <p>&copy; {{ date('Y') }} J&amp;H Importaciones · {{ $f->texto_copyright ?? 'Web Creada Por Valento' }}</p>
                </div>
                <div class="col-lg-6 text-end link">
                    <ul>
                        @foreach($navFooter as $lnk)
                        <li><a href="{{ $lnk['href'] ?? '#' }}">{{ $lnk['label'] ?? '' }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
