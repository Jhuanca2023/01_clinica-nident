@php
    $f = isset($footerData) && count($footerData) > 0 ? $footerData[0] : null;
    $logoFooter = Helpers::cmsAssetUrl($f->url_logo_footer ?? $f->url_logo ?? 'temp02/assets/image/logos/bibliotecas-rodante-logo-footer.png');
    $tel = $f->contacto_telefono ?? '934200140';
    $telFmt = $f->contacto_telefono ?? '934 200 140';
    $direccion = $f->contacto_direccion ?? 'Lambayeque, Perú';
    $urlWa = $urlWhatsapp ?? Helpers::landingWhatsappUrl($f);
    $messenger = $f->url_facebook ?? 'https://m.me/BibliotecasRodantes';
@endphp
<footer class="footer-bg">
  <div class="container-fluid">
    <div class="row row-gap-5 align-items-center mb-96">
      <div class="col-lg-6">
        <div class="footer-block">
          <a href="{{ url('/') }}" class="footer-logo">
            <img src="{{ $logoFooter }}" alt="Bibliotecas Rodantes">
          </a>
          <h6 class="light-black">Novedades literarias y actividades<br class="d-sm-block d-none">directamente en tu correo</h6>
        </div>
      </div>
      <div class="col-lg-6">
        <form class="news-letter-form w-100" method="post" action="{{ route('subscribe') }}" id="newsletter-form">
          @csrf
          <div class="w-100">
            <p class="subtitle light-black mb-4p">Correo electrónico</p>
            <input type="email" name="email" class="form-control w-100" placeholder="tucorreo@mail.com" required>
          </div>
          <button type="submit" class="cus-btn flex-shrink-0 border-0">
            <span class="text">Suscribirse</span>
          </button>
        </form>
      </div>
    </div>
    <div class="row row-gap-5 mb-64">
      <div class="col-lg-3 col-sm-4">
        <div class="intro-block">
          <h6 class="mb-24 underline-title">Información</h6>
          <div class="d-flex align-items-center gap-8 mb-16">
            <p class="light-black">{{ $direccion }}</p>
          </div>
          <div class="d-flex align-items-center gap-8 mb-16">
            <a href="tel:+51{{ preg_replace('/\D/','',$tel) }}" class="hover-content light-black text-18">{{ $telFmt }}</a>
          </div>
          <div class="d-flex align-items-center gap-8 mb-36">
            <a href="{{ $messenger }}" target="_blank" rel="noopener" class="hover-content text-18 light-black">Bibliotecas Rodantes</a>
          </div>
        </div>
      </div>
      <div class="col-lg-9 col-sm-8">
        <div class="row row-gap-5">
          <div class="col-md-4 col-6">
            <div class="links-block">
              <ul class="unstyled">
                <li class="mb-24"><h5 class="h6 fw-400">Enlaces Rápidos</h5></li>
                <li class="mb-16"><a href="#inicio" class="hover-content">Inicio</a></li>
                <li class="mb-16"><a href="#nosotros" class="hover-content">Nosotros</a></li>
                <li class="mb-16"><a href="#contacto" class="hover-content">Contáctanos</a></li>
                <li><a href="#eventos" class="hover-content">Eventos</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center">
      <p class="black privacy-text">© {{ date('Y') }}. Todos los derechos reservados — Raíces y Letras · Bibliotecas Rodantes LEC · Lambayeque, Perú</p>
    </div>
  </div>
</footer>
