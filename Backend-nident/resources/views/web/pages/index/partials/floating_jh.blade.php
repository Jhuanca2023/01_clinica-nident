@php $urlWa = $urlWhatsapp ?? Helpers::landingWhatsappUrl(isset($footerData[0]) ? $footerData[0] : null); @endphp
<div class="jh-floating-actions">
    <a href="{{ $urlWa }}" target="_blank" rel="noopener" class="jh-whatsapp-btn" aria-label="Escríbenos por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    <button type="button" class="jh-scroll-top-btn" aria-label="Volver arriba" title="Volver arriba">
        <i class="fas fa-chevron-up"></i>
    </button>
</div>
