@if(!empty($fraseTexto))
<section class="jh-cta-strip">
    <div class="jh-cta-strip__blobs" aria-hidden="true">
        <span class="jh-cta-strip__blob jh-cta-strip__blob--a"></span>
        <span class="jh-cta-strip__blob jh-cta-strip__blob--b"></span>
        <span class="jh-cta-strip__blob jh-cta-strip__blob--c"></span>
    </div>
    <div class="jh-cta-strip__shine" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="jh-cta-strip__inner">
            <span class="jh-cta-strip__icon-deco" aria-hidden="true">
                <i class="fas fa-award"></i>
            </span>
            <p class="jh-cta-strip__text">
                <span class="jh-cta-strip__ql">&ldquo;</span>{{ $fraseTexto }}<span class="jh-cta-strip__qr">&rdquo;</span>
            </p>
            <span class="jh-cta-strip__icon-deco jh-cta-strip__icon-deco--right" aria-hidden="true">
                <i class="fas fa-gem"></i>
            </span>
        </div>
    </div>
</section>
@endif
