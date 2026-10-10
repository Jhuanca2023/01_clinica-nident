@php
    $f = isset($footerData) && count($footerData) > 0 ? $footerData[0] : null;

    $logoFooter = !empty($f?->logo_footer)
        ? asset($f->logo_footer)
        : asset('temp02/assets/img/inicio/impacto-gigante-logo-con-fondo.png');

    $descripcion = $f?->sobre_la_empresa
        ?: 'Agencia de marketing digital especializada en redes sociales. Ayudamos a negocios en Lima y todo el Perú a crecer, vender más y conectar con sus clientes.';

    $ctaSub = $f?->footer_cta_subtitulo ?: '¿Listo para que tu negocio explote en redes sociales?';
    $ctaTitulo = $f?->footer_cta_titulo ?: 'Empieza hoy y recibe mensajes de clientes desde la primera semana';
    $urlWhatsapp = isset($urlWhatsapp) ? $urlWhatsapp : Helpers::landingWhatsappUrl($f);

    $telPrincipal = $f?->contacto_telefono ?: '939691536';
    $telSecundario = $f?->contacto_telefono_secundario ?: '901967564';
    $telPrincipalFmt = trim(chunk_split(preg_replace('/\D/', '', $telPrincipal), 3, ' '));
    $telSecundarioFmt = trim(chunk_split(preg_replace('/\D/', '', $telSecundario), 3, ' '));

    $ubicacion = $f?->contacto_direccion ?: 'Breña, Lima, Perú';
    $anio = date('Y');

    $redesSociales = [
        ['url' => $f?->red_social_facebook ?? '',  'label' => 'Facebook',  'icon' => 'fa-brands fa-facebook-f'],
        ['url' => $f?->red_social_instagram ?? '', 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram'],
        ['url' => $f?->red_social_youtobe ?? '',   'label' => 'YouTube',   'icon' => 'fa-brands fa-youtube'],
        ['url' => $f?->red_social_twitter ?? '',   'label' => 'Twitter',   'icon' => 'fa-brands fa-twitter'],
        ['url' => $f?->red_social_linkedin ?? '',  'label' => 'LinkedIn',  'icon' => 'fa-brands fa-linkedin-in'],
        ['url' => $f?->red_social_tiktok ?? '',    'label' => 'TikTok',    'icon' => 'fa-brands fa-tiktok'],
    ];

    $enlacesRapidos = [
        ['label' => 'Inicio', 'href' => '#inicio'],
        ['label' => 'Nosotros', 'href' => '#about'],
        ['label' => 'Servicios', 'href' => '#services'],
        ['label' => 'Planes', 'href' => '#pricing'],
        ['label' => 'Contacto', 'href' => '#contact'],
        ['label' => 'Términos y Condiciones', 'href' => route('terminos.condiciones'), 'external' => true],
    ];

    $btnSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3.97 3.97a.75.75 0 0 1 1.06 0l13.72 13.72V8.25a.75.75 0 0 1 1.5 0V19.5a.75.75 0 0 1-.75.75H8.25a.75.75 0 0 1 0-1.5h9.44L3.97 5.03a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path></svg>';
@endphp

<footer class="gap no-bottom" style="background-image: url('{{ asset('temp02/assets/img/black-bg-img.jpg') }}');">
  <div class="container" style="margin-top: -4rem;">
    <div class="footer-top sec-title-animation animation-style2">
      <span class="title-animation">{{ $ctaSub }}</span>
      <h2 class="title-animation">{{ $ctaTitulo }}</h2>
      <a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn btn--whatsapp">
        <span>Escríbenos por WhatsApp</span>
        {!! $btnSvg !!}
      </a>
    </div>
    <div class="row">
      <div class="col-lg-4 col-md-6">
        <div class="footer-logo">
          <a href="{{ url('/') }}">
            <img style="border-radius: 1em;" src="{{ $logoFooter }}" alt="Valento">
          </a>
          <p>{{ $descripcion }}</p>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="widget-title">
          <h3>Enlaces Rápidos</h3>
          <ul>
            @foreach ($enlacesRapidos as $enlace)
            <li>
              <a href="{{ $enlace['href'] }}"
                @if(!empty($enlace['external'])) target="_blank" rel="noopener noreferrer" @endif>
                {{ $enlace['label'] }}
              </a>
            </li>
            @endforeach
            @foreach ($redesSociales as $red)
              @if(!empty($red['url']))
              <li><a href="{{ $red['url'] }}" target="_blank" rel="noopener noreferrer">{{ $red['label'] }}</a></li>
              @endif
            @endforeach
            @if($urlWhatsapp)
            <li><a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
            @endif
          </ul>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="widget-title">
          <h3>Contáctanos</h3>
          @if($telPrincipal)
          <div class="mailnumber">
            <i class="fa-solid fa-mobile-screen-button"></i>
            <div>
              <span>Llámanos:</span>
              <a href="tel:{{ preg_replace('/\D/', '', $telPrincipal) }}">{{ $telPrincipalFmt }}</a>
            </div>
          </div>
          @endif
          @if($telSecundario)
          <div class="mailnumber">
            <i class="fa-solid fa-mobile-screen-button"></i>
            <div>
              <span>Call Center:</span>
              <a href="tel:{{ preg_replace('/\D/', '', $telSecundario) }}">{{ $telSecundarioFmt }}</a>
            </div>
          </div>
          @endif
          @if($urlWhatsapp)
          <div class="mailnumber">
            <div><h4>@</h4></div>
            <div>
              <span>Escríbenos por WhatsApp:</span>
              <a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener noreferrer">Clic aquí</a>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
    <div class="all-rights">
      <p>© {{ $anio }} Valento. Todos los derechos reservados. {{ $ubicacion }}.</p>
      <ul class="social-media">
        @foreach ($redesSociales as $red)
          @if(!empty($red['url']))
          <li>
            <a href="{{ $red['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $red['label'] }}">
              <i class="{{ $red['icon'] }}"></i>
            </a>
          </li>
          @endif
        @endforeach
        @if($urlWhatsapp)
        <li><a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener noreferrer" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a></li>
        @endif
      </ul>
    </div>
  </div>
  <img src="{{ asset('temp02/assets/img/color-bg-3.png') }}" alt="" class="color-bg-3" style="width: 33rem;">
</footer>
