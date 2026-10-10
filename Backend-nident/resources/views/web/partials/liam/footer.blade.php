@php
    $f = $footerCorp ?? (($footerData ?? collect())->first() ?? null);

    $logoFooter = asset('temp02/img/inicio/logo-valento.svg');
    if ($f) {
        $lf = $f->logo_footer ?? $f->url_logo_footer ?? null;
        if ($lf) $logoFooter = \App\Support\LiamPageData::img($lf, 'temp02/img/inicio/logo-valento.svg');
    }

    // Redes sociales (dinámico desde footer_redes JSON)
    $redes = [];
    $iconMap = [
        'facebook'  => 'fa fa-facebook',
        'instagram' => 'fa fa-instagram',
        'tiktok'    => 'fa fa-music',
        'whatsapp'  => 'fa fa-whatsapp',
        'youtube'   => 'fa fa-youtube',
        'linkedin'  => 'fa fa-linkedin',
        'twitter'   => 'fa fa-twitter',
    ];

    if ($f && !empty($f->footer_redes)) {
        $decoded = json_decode($f->footer_redes, true);
        if (is_array($decoded)) {
            usort($decoded, fn ($a, $b) => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));
            foreach ($decoded as $r) {
                $url = trim((string) ($r['url'] ?? ''));
                if ($url === '') {
                    continue;
                }
                $tipo = strtolower((string) ($r['tipo'] ?? 'link'));
                // WhatsApp: si guardaron solo el número, armar el enlace wa.me
                if ($tipo === 'whatsapp' && !preg_match('#^https?://#i', $url)) {
                    $url = \App\Support\LiamPageData::whatsappUrlFromPhone($url);
                    if ($url === '') {
                        continue;
                    }
                }
                $redes[] = [
                    'icon'  => $iconMap[$tipo] ?? 'fa fa-link',
                    'url'   => $url,
                    'label' => $r['etiqueta'] ?? ucfirst($tipo),
                ];
            }
        }
    }

    // Fallback: campos legacy si aún no hay footer_redes
    if (empty($redes) && $f) {
        if (!empty($f->red_social_instagram)) $redes[] = ['icon'=>'fa fa-instagram','url'=>$f->red_social_instagram,'label'=>'Instagram'];
        if (!empty($f->url_facebook) || !empty($f->red_social_facebook))
            $redes[] = ['icon'=>'fa fa-facebook','url'=>$f->url_facebook ?? $f->red_social_facebook,'label'=>'Facebook'];
        if (!empty($f->red_social_tiktok))  $redes[] = ['icon'=>'fa fa-music','url'=>$f->red_social_tiktok,'label'=>'TikTok'];
        if (!empty($urlWhatsapp))            $redes[] = ['icon'=>'fa fa-whatsapp','url'=>$urlWhatsapp,'label'=>'WhatsApp'];
        if (!empty($f->red_social_youtobe) || !empty($f->red_social_youtube))
            $redes[] = ['icon'=>'fa fa-youtube','url'=>$f->red_social_youtobe ?? $f->red_social_youtube,'label'=>'YouTube'];
    }

    // JSON fields — cada enlace: string legacy o {label, url}
    $linksT  = $f && $f->links_tienda   ? json_decode($f->links_tienda,  true) : [];
    $linksL  = $f && $f->links_legales  ? json_decode($f->links_legales, true) : [];
    if (!is_array($linksT) || count($linksT) === 0) {
        $linksT = [
            ['label' => 'Ropa laboral', 'url' => '/tienda'],
            ['label' => 'Textil promocional', 'url' => '/tienda'],
            ['label' => 'Vestuario deportivo', 'url' => '/tienda'],
            ['label' => 'Protección personal', 'url' => '/tienda'],
            ['label' => 'Uniformes', 'url' => '/tienda'],
            ['label' => 'Guía de tallas', 'url' => '/guia-de-tallas'],
        ];
    }
    if (!is_array($linksL) || count($linksL) === 0) {
        $linksL = [
            ['label' => 'Términos y condiciones', 'url' => '/terminos-condiciones'],
            ['label' => 'Políticas de privacidad', 'url' => '/politica-privacidad'],
            ['label' => 'Libro de reclamaciones', 'url' => '/libro-reclamaciones'],
        ];
    }
    $horarios = $f && $f->horarios_data  ? json_decode($f->horarios_data, true) : [];
    $metodos  = $f && $f->metodos_pago   ? json_decode($f->metodos_pago,  true) : [];
    if (!is_array($horarios)) $horarios = [];
    if (!is_array($metodos))  $metodos  = [];

    // Fallbacks por nombre (compatibilidad con JSON solo-texto)
    $legalesUrls = [
        'términos y condiciones'    => '/terminos-condiciones',
        'terminos y condiciones'    => '/terminos-condiciones',
        'políticas de privacidad'   => '/politica-privacidad',
        'politicas de privacidad'   => '/politica-privacidad',
        'libro de reclamaciones'    => '/libro-reclamaciones',
    ];

    $tiendaUrls = [
        'polos básicos'     => '/tienda',
        'estampados'        => '/tienda',
        'sport & dry fit'   => '/tienda',
        'premium'           => '/tienda',
        'ofertas'           => '/tienda',
        'guía de tallas'    => '/guia-de-tallas',
        'guia de tallas'    => '/guia-de-tallas',
        'catálogo'          => '/tienda',
        'carrito'           => '/shop-detail-lista',
        'pagar'             => '/web_checkout/index',
    ];

    $resolveFooterLink = static function ($link, array $fallbackMap, string $defaultHref): array {
        if (is_array($link)) {
            $label = trim((string) ($link['label'] ?? $link['texto'] ?? $link['nombre'] ?? ''));
            $href  = trim((string) ($link['url'] ?? $link['href'] ?? ''));
            if ($href === '') {
                $href = $fallbackMap[mb_strtolower($label)] ?? $defaultHref;
            }
            return [$label, $href];
        }
        $label = trim((string) $link);
        $href  = $fallbackMap[mb_strtolower($label)] ?? $defaultHref;
        return [$label, $href];
    };

    $footerHref = static function (string $href): string {
        if ($href === '' || $href === '#') {
            return '#';
        }
        if (preg_match('#^(https?:)?//#i', $href) || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
            return $href;
        }
        return url($href);
    };

    // Enlaces legales: se separa el libro de reclamaciones (tiene tratamiento propio)
    $legalLinks = [];
    $libroLink  = null;
    foreach ($linksL as $link) {
        [$label, $href] = $resolveFooterLink($link, $legalesUrls, '/terminos-condiciones');
        if ($label === '') {
            continue;
        }
        $entry = ['label' => $label, 'href' => $footerHref($href)];
        $legalLinks[] = $entry;
        if ($libroLink === null && str_contains(mb_strtolower($label), 'reclamacion')) {
            $libroLink = $entry;
        }
    }

    $telFooter    = trim((string) ($f->contacto_telefono ?? '')) ?: '+51 964 381 275';
    $waFooter     = \App\Support\LiamPageData::whatsappUrlFromPhone($telFooter);
    if ($waFooter === '' || ! str_contains($waFooter, '/51')) {
        $waFooter = $urlWhatsapp ?: 'https://wa.me/51964381275';
    }
    $emailFooter  = trim((string) ($f->contacto_email ?? '')) ?: 'ventas@rossety.pe';
    $direcFooter  = trim((string) ($f->contacto_direccion ?? '')) ?: 'La Victoria, Lima';

    $descFooter = trim((string) ($f->descripcion_footer ?? ''))
        ?: 'Ropa laboral, textil promocional y vestuario deportivo para empresas y colectividades. Stock permanente y prendas listas para personalizar.';
    $copyright = $f->texto_copyright ?? ('&copy; ' . date('Y') . ' <strong>Valento</strong>. Todos los derechos reservados.');
    $ctaSub    = trim((string) ($f->footer_cta_subtitulo ?? '')) ?: 'Semplicemente Valento · Lima 1994';
@endphp

<!-- FOOTER -->
<footer class="vf" aria-label="Pie de página Valento">

    {{-- Línea dorada superior con monograma --}}
    <div class="vf-rule" aria-hidden="true"><span class="vf-rule__mark">V</span></div>

    <div class="vf-wrap">

        {{-- Marca centrada --}}
        <div class="vf-brand">
            <a href="{{ url('/') }}" class="vf-brand__logo"><img src="{{ $logoFooter }}" alt="Valento"></a>
            <p class="vf-brand__desc">{{ $descFooter }}</p>
            @if(count($redes))
            <ul class="vf-social" aria-label="Redes sociales">
                @foreach($redes as $r)
                <li>
                    <a href="{{ $r['url'] }}" target="_blank" rel="noopener"
                       aria-label="{{ $r['label'] ?? '' }}" title="{{ $r['label'] ?? '' }}" data-wa-cms="1">
                        <i class="{{ $r['icon'] }}" aria-hidden="true"></i>
                    </a>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Columnas con divisores verticales --}}
        <div class="vf-grid">

            <nav class="vf-col" aria-label="Tienda">
                <h5 class="vf-col__title"><span>01</span>Tienda</h5>
                <ul class="vf-links">
                    @foreach($linksT as $link)
                    @php [$label, $href] = $resolveFooterLink($link, $tiendaUrls, '/tienda'); @endphp
                    @if($label !== '')
                    <li><a href="{{ $footerHref($href) }}">{{ $label }}</a></li>
                    @endif
                    @endforeach
                </ul>
            </nav>

            <nav class="vf-col" aria-label="Legales">
                <h5 class="vf-col__title"><span>02</span>Legales</h5>
                <ul class="vf-links">
                    @foreach($legalLinks as $link)
                    <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
                @if($libroLink)
                <a href="{{ $libroLink['href'] }}" class="vf-libro">
                    <img src="{{ asset('temp02/img/icons/libro-reclamaciones.png') }}" alt="" width="40" height="15">
                    <span>Libro de<br>Reclamaciones</span>
                </a>
                @endif
            </nav>

            <div class="vf-col">
                <h5 class="vf-col__title"><span>03</span>Contacto</h5>
                <ul class="vf-contact">
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l1.2-4A8 8 0 1 1 8 18.8z"/></svg>
                        <a href="{{ $waFooter }}" target="_blank" rel="noopener" data-wa-cms="1">{{ $telFooter }}</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="M3.5 6l8.5 7 8.5-7"/></svg>
                        <a href="mailto:{{ $emailFooter }}">{{ $emailFooter }}</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3"/></svg>
                        <span>{{ $direcFooter }}</span>
                    </li>
                </ul>
            </div>

            @if(count($horarios))
            <div class="vf-col">
                <h5 class="vf-col__title"><span>04</span>Horarios</h5>
                <dl class="vf-hours">
                    @foreach($horarios as $h)
                    @php
                        $dia  = trim((string) ($h['dia'] ?? ''));
                        $hora = trim((string) ($h['hora'] ?? ''));
                        $cerrado = str_contains(mb_strtolower($hora), 'cerrado');
                    @endphp
                    @if($dia !== '')
                    <div><dt>{{ $dia }}</dt><dd class="{{ $cerrado ? 'is-closed' : '' }}">{{ $hora }}</dd></div>
                    @endif
                    @endforeach
                </dl>
            </div>
            @endif

        </div>

        {{-- Separador V + barra inferior --}}
        <div class="vf-bottom">
            <div class="vf-sep" aria-hidden="true">
                <span class="vf-sep__line"></span>
                <svg class="vf-sep__v" viewBox="0 0 40 24"><path d="M4 2l16 20L36 2"/><path d="M11 2l9 11"/></svg>
                <span class="vf-sep__line"></span>
            </div>
            <div class="vf-bottom__row">
                <p class="vf-copy">{!! $copyright !!}</p>
                @if(count($metodos))
                <div class="vf-pay" aria-label="Métodos de pago">
                    @foreach($metodos as $m)
                    @if(is_array($m) && !empty($m['imagen']))<img src="{{ asset($m['imagen']) }}" alt="{{ $m['nombre'] ?? 'Medio de pago' }}" loading="lazy" style="height:32px;max-width:100px;object-fit:contain" />@endif
                    @endforeach
                </div>
                @else
                <div class="vf-pay"></div>
                @endif
                <p class="vf-bottom__tag">{!! implode(' <span>·</span> ', array_map(fn ($t) => e(trim($t)), explode('·', $ctaSub))) !!}</p>
            </div>
        </div>

    </div>
</footer>
<!-- Footer Section End -->

@once
<style>
/* Iconos de redes: el template usa SVG, el CMS entrega clases Font Awesome */
.vf-social a i { font-size: 19px; line-height: 1; }
</style>
@endonce

@include('web.partials.liam.whatsapp')
@include('web.partials.liam.scrolltop')
