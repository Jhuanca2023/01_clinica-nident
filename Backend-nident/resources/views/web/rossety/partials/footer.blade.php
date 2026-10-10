@php
    $f = (array) (Helpers::footer_()[0] ?? []);
    $arr = static function ($value, $fallback) { if ($value === null || $value === '') return $fallback; $v = is_array($value) ? $value : json_decode($value, true); return is_array($v) ? $v : $fallback; };
    $conf = $arr($f['rossety_footer_config'] ?? null, []);
    $cats = $arr($f['links_tienda'] ?? null, json_decode('[{"label":"Ramos y Detalles","url":"/tienda?cat=ramos"},{"label":"Peluches","url":"/tienda?cat=peluches"},{"label":"Cajas de Regalo","url":"/tienda?cat=cajas"},{"label":"Accesorios","url":"/tienda?cat=accesorios"},{"label":"Hot Wheels y Motos","url":"/tienda?cat=hotwheels"},{"label":"Globos y Complementos","url":"/tienda?cat=complementos"},{"label":"Temporada Girasoles","url":"/tienda?cat=girasoles"}]', true));
    $links = $arr($f['links_legales'] ?? null, json_decode('[{"label":"Inicio","url":"/"},{"label":"Tienda","url":"/tienda"},{"label":"Nosotros","url":"/nosotros"},{"label":"Contacto","url":"/contacto"},{"label":"Métodos de pago","url":"/contacto#pagos"},{"label":"Preguntas frecuentes","url":"/contacto#faq"}]', true));
    $pagos = array_filter($arr($f['metodos_pago'] ?? null, []), static fn($p) => is_array($p) && !empty($p['imagen']));
    $horarios = $arr($f['horarios_data'] ?? null, [['dia'=>'Lun - Sáb', 'hora'=>'9:00 - 20:00'], ['dia'=>'Domingos', 'hora'=>'con pedido previo']]);
    $redes = $arr($f['footer_redes'] ?? null, null);
    if ($redes === null) { $redes = []; foreach (['facebook','tiktok','instagram','twitter','linkedin'] as $tipo) { if (!empty($f['red_social_'.$tipo])) $redes[] = ['tipo'=>$tipo, 'url'=>$f['red_social_'.$tipo]]; } $redes[] = ['tipo'=>'whatsapp', 'url'=>Helpers::whatsappUrl((object)$f)]; }
    usort($redes, static fn($a,$b) => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));
    $icons = ['facebook'=>'ph-facebook-logo','instagram'=>'ph-instagram-logo','tiktok'=>'ph-tiktok-logo','whatsapp'=>'ph-whatsapp-logo','youtube'=>'ph-youtube-logo','twitter'=>'ph-x-logo','linkedin'=>'ph-linkedin-logo'];
    $footerLogo = !empty($f['logo_footer']) ? asset($f['logo_footer']) : $rsLogo;
@endphp
    <footer class="rs-footer">
        <span class="rs-footer__glow rs-footer__glow--1"></span>
        <span class="rs-footer__glow rs-footer__glow--2"></span>
        <div class="rs-container rs-footer__top">
            <div class="rs-footer__col rs-footer__brand">
                <a href="{{ route('home') }}"><img class="rs-footer__logo" src="{{ $footerLogo }}" alt="Detalles Rossety" /></a>
                <p class="rs-footer__about">{{ $f['descripcion_footer'] ?? 'Detalles únicos para cada ocasión. Ramos, peluches, cajas y accesorios hechos con amor para sorprender a quien más quieres.' }}</p>
                <div class="rs-footer__social">@foreach($redes as $red)<a href="{{ $red['url'] ?? '#' }}" aria-label="{{ $red['etiqueta'] ?? $red['tipo'] }}" target="_blank" rel="noopener"><i class="ph-fill {{ $icons[$red['tipo']] ?? 'ph-link' }}"></i></a>@endforeach</div>
            </div>
            <div class="rs-footer__col">
                <h3 class="rs-footer__title"><span class="rs-footer__icon"><i class="ph-fill ph-gift"></i></span>{{ $f['titulo_sobre_nosotros'] ?? 'Categorías' }}</h3>
                <ul class="rs-footer__links">@foreach($cats as $link)<li><a href="{{ is_array($link) ? ($link['url'] ?? '/tienda') : '/tienda' }}"><i class="ph-bold ph-caret-right"></i>{{ is_array($link) ? ($link['label'] ?? '') : $link }}</a></li>@endforeach</ul>
            </div>
            <div class="rs-footer__col">
                <h3 class="rs-footer__title"><span class="rs-footer__icon"><i class="ph-fill ph-compass"></i></span>{{ $f['titulo_escribenos'] ?? 'Enlaces' }}</h3>
                <ul class="rs-footer__links">@foreach($links as $link)<li><a href="{{ is_array($link) ? ($link['url'] ?? '/tienda') : '/tienda' }}"><i class="ph-bold ph-caret-right"></i>{{ is_array($link) ? ($link['label'] ?? '') : $link }}</a></li>@endforeach</ul>
                <p class="rs-footer__mini">{{ $conf['pagos_titulo'] ?? 'Medios de pago' }}</p>
                <div class="rs-footer__pay">@foreach($pagos as $p)<img class="rs-footer__payment-logo" src="{{ asset($p['imagen']) }}" alt="{{ $p['nombre'] ?? 'Medio de pago' }}" loading="lazy" />@endforeach</div>
            </div>
            <div class="rs-footer__col">
                <h3 class="rs-footer__title"><span class="rs-footer__icon"><i class="ph-fill ph-chat-circle-dots"></i></span>{{ $f['titulo_llamanos'] ?? 'Contáctanos' }}</h3>
                <ul class="rs-footer__contact">
                    <li><span class="rs-footer__ci"><i class="fa fa-whatsapp" aria-hidden="true"></i></span><span><small>{{ $conf['whatsapp_etiqueta'] ?? 'WhatsApp' }}</small><a href="{{ Helpers::whatsappUrl((object)$f) }}">{{ $f['contacto_telefono'] ?? $rsTelefono }}</a></span></li>
                    <li><span class="rs-footer__ci"><i class="ph-fill ph-map-pin"></i></span><span><small>{{ $conf['direccion_etiqueta'] ?? 'Dirección' }}</small>{{ $f['contacto_direccion'] ?? $rsDireccion }}</span></li>
                    <li><span class="rs-footer__ci"><i class="ph-fill ph-clock"></i></span><span><small>{{ $conf['horario_etiqueta'] ?? 'Horario' }}</small>@foreach($horarios as $h){{ $h['dia'] ?? '' }} {{ $h['hora'] ?? '' }}@if(!$loop->last)<br>@endif@endforeach</span></li>
                </ul>
                <a class="rs-claims" href="{{ route('liam.reclamaciones') }}" aria-label="Libro de reclamaciones">
                    <img src="{{ asset($conf['reclamos_imagen'] ?? 'rossety/assets/images/inicio/libro-reclamaciones.png') }}" alt="Libro de Reclamaciones" />
                    <span class="rs-claims__text"><b>{{ $conf['reclamos_titulo'] ?? 'Libro de Reclamaciones' }}</b><small>{{ $conf['reclamos_subtitulo'] ?? 'Tu opinión nos importa' }}</small></span>
                    <i class="ph-bold ph-arrow-up-right"></i>
                </a>
            </div>
        </div>
        <div class="rs-footer__divider"><span><i class="ph-fill ph-heart"></i></span></div>
        <div class="rs-container rs-footer__bottom">
            <span>{{ strip_tags($f['texto_copyright'] ?? ('© '.date('Y').' Detalles Rossety. Todos los derechos reservados.')) }}</span>
            <span class="rs-footer__slogan">{{ $f['footer_cta_subtitulo'] ?? 'Detalles que enamoran, momentos que perduran.' }}</span>
        </div>
    </footer>
