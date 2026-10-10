<div class="jh-floating-actions">
  <a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener noreferrer" class="jh-whatsapp-btn" aria-label="Escríbenos por WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>
  <button type="button" class="scrollToTopBtn" aria-label="Volver arriba" title="Volver arriba">
    <i class="fa fa-arrow-up"></i>
  </button>
</div>
<div class="mobile-nav__wrapper">
  <div class="mobile-nav__overlay mobile-nav__toggler"></div>
  <div class="mobile-nav__content">
    <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
    <div class="logo-box">
      <a href="{{ url('/') }}" aria-label="logo"><img src="{{ Helpers::cmsAssetUrl($webHeader->url_logo ?? '') }}" alt=""></a>
    </div>
    <div class="mobile-nav__container"></div>
  </div>
</div>
<div id="pub-image-modal" role="dialog" aria-modal="true" aria-labelledby="pim-caption" aria-hidden="true">
  <button type="button" class="pim-close" aria-label="Cerrar">&times;</button>
  <div class="pim-overlay" aria-hidden="true"></div>
  <div class="pim-panel">
    <img class="pim-img" src="" alt="">
    <p class="pim-caption" id="pim-caption"></p>
  </div>
</div>
