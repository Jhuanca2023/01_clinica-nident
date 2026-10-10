<aside id="cookies-policy" class="cookies cookies--no-js">
    <div class="cookies__banner">
        <div class="cookies__content">
            <!-- Contenido Principal -->
            <div class="cookies__main" id="cookies-main-view">
                <div class="cookies__icon">
                    <i class="fas fa-cookie-bite"></i>
                </div>
                <div class="cookies__info">
                    <h2 class="cookies__title">Política general sobre cookies</h2>
                    <p class="cookies__text">Al hacer clic en "Aceptar", usted acepta que las cookies se guarden en su dispositivo para mejorar la navegación del sitio, analizar el uso del mismo, y colaborar con nuestros estudios para marketing.</p>
                </div>
                <div class="cookies__actions">
                    <button type="button" class="cookies__btn-accept" id="cookies-accept-btn" data-accept-url="{{ route('cookieconsent.accept.all') }}">Aceptar</button>
                    <a href="#" class="cookies__btn-config" id="cookies-show-config">Configurar cookies</a>
                    @if(isset($policy) && $policy)
                        <a href="{{ $policy }}" class="cookies__policy-link">Políticas de cookies</a>
                    @endif
                </div>
            </div>

            <!-- Panel de Configuración -->
            <div class="cookies__config" id="cookies-config-view" style="display: none;">
                <div class="cookies__config-header">
                    <button type="button" class="cookies__back" id="cookies-back-main">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <h2 class="cookies__title">Configurar cookies</h2>
                </div>

                <form action="{{ route('cookieconsent.accept.configuration') }}" method="post" class="cookies__form">
                    @csrf
                    <div class="cookies__categories">
                        @foreach($cookies->getCategories() as $category)
                        <div class="cookies__category">
                            <label class="cookies__category-label">
                                <div class="cookies__category-info">
                                    <span class="cookies__category-name">
                                        @if($category->key() === 'essentials')
                                            <i class="fas fa-cog"></i> Cookies esenciales
                                        @elseif($category->key() === 'analytics')
                                            <i class="fas fa-chart-bar"></i> Cookies de análisis
                                        @elseif($category->key() === 'marketing')
                                            <i class="fas fa-bullhorn"></i> Cookies de marketing
                                        @else
                                            <i class="fas fa-cookie-bite"></i> Cookies
                                        @endif
                                    </span>
                                    <p class="cookies__category-desc">
                                        @if($category->key() === 'essentials')
                                            Necesarias para el funcionamiento básico del sitio. No requieren consentimiento.
                                        @elseif($category->key() === 'analytics')
                                            Permiten analizar el comportamiento de los usuarios para mejorar nuestros servicios.
                                        @elseif($category->key() === 'marketing')
                                            Utilizadas para mostrar publicidad personalizada según tus intereses.
                                        @endif
                                    </p>
                                </div>
                                <div class="cookies__toggle">
                                    @if ($category->key() === 'essentials')
                                        <input type="hidden" name="categories[]" value="{{ $category->key() }}" />
                                        <input type="checkbox" name="categories[]" value="{{ $category->key() }}" id="cookies-check-{{ $category->key() }}" checked="checked" disabled="disabled" />
                                        <span class="cookies__toggle-slider cookies__toggle-slider--disabled"></span>
                                    @else
                                        <input type="checkbox" name="categories[]" value="{{ $category->key() }}" id="cookies-check-{{ $category->key() }}" />
                                        <span class="cookies__toggle-slider"></span>
                                    @endif
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <div class="cookies__form-actions">
                        <button type="submit" class="cookies__btn-save">
                            <i class="fas fa-check"></i> Guardar preferencias
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</aside>

{{-- STYLES & SCRIPT --}}
<script data-cookie-consent>
    try { {!! file_get_contents(LCC_ROOT . '/dist/script.js') !!} } catch (e) {}
</script>
<script>
(function() {
    var COOKIE_CONSENT_STORAGE_KEY = 'valento_cookie_consent_accepted';
    var banner = document.getElementById('cookies-policy');
    var acceptBtn = document.getElementById('cookies-accept-btn');
    if (!banner || !acceptBtn) return;

    if (localStorage.getItem(COOKIE_CONSENT_STORAGE_KEY)) {
        banner.style.display = 'none';
        return;
    }

    function hideBanner() {
        localStorage.setItem(COOKIE_CONSENT_STORAGE_KEY, '1');
        banner.classList.add('cookies--closing');
        setTimeout(function() {
            banner.style.display = 'none';
            var nodes = document.querySelectorAll('[data-cookie-consent]');
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].parentNode) nodes[i].parentNode.removeChild(nodes[i]);
            }
        }, 250);
    }

    acceptBtn.addEventListener('click', function() {
        var url = acceptBtn.getAttribute('data-accept-url');
        var token = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        acceptBtn.disabled = true;
        acceptBtn.textContent = '...';
        hideBanner();
        if (url) {
            var opts = {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: token ? '_token=' + encodeURIComponent(token) : ''
            };
            if (token) opts.headers['X-CSRF-TOKEN'] = token;
            fetch(url, opts).catch(function() {});
        }
    });
})();
</script>

<style data-cookie-consent>
    {!! file_get_contents(LCC_ROOT . '/dist/style.css') !!}

    /* ========================================
       COOKIES BANNER — ESTILO rossety
       Mismo lenguaje visual que el footer (azul marino + dorado)
       ======================================== */

    #cookies-policy.cookies {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        padding: 0;
        transition: opacity .25s ease, transform .25s ease;
    }

    #cookies-policy.cookies--closing {
        opacity: 0;
        transform: translateY(100%);
    }

    .cookies__banner {
        position: relative;
        width: 100%;
        background:
            radial-gradient(ellipse 60% 120% at 50% 0%, rgba(231, 182, 105, .10), transparent 70%),
            linear-gradient(180deg, #121a36 0%, #0b1126 100%);
        border-top: 1px solid #e7b669;
        box-shadow: 0 -10px 40px rgba(11, 17, 38, .35);
        animation: cookieSlideUp .4s ease;
        isolation: isolate;
    }

    /* Textura del footer */
    .cookies__banner::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background-image:
            radial-gradient(rgba(255, 255, 255, .05) 1px, transparent 1.2px),
            repeating-linear-gradient(45deg, rgba(255, 255, 255, .018) 0 1px, transparent 1px 7px),
            repeating-linear-gradient(-45deg, rgba(255, 255, 255, .018) 0 1px, transparent 1px 7px);
        background-size: 18px 18px, auto, auto;
    }

    @keyframes cookieSlideUp {
        from { opacity: 0; transform: translateY(100%); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .cookies__content {
        max-width: 1400px;
        margin: 0 auto;
        /* Deja espacio al botón flotante de WhatsApp (izquierda) */
        padding: 22px 40px 22px 140px;
    }

    #cookies-policy .cookies__alert,
    #cookies-policy .cookies__container,
    #cookies-policy .cookies__wrapper,
    #cookies-policy .cookies__btn--customize,
    #cookies-policy .cookies__expandable--custom {
        display: none !important;
    }

    /* ========== VISTA PRINCIPAL ========== */
    .cookies__main {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    /* Rombo dorado, como el monograma del footer */
    .cookies__icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border: 1px solid #e7b669;
        transform: rotate(45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(231, 182, 105, .08);
    }

    .cookies__icon i {
        font-size: 18px;
        color: #e7b669;
        transform: rotate(-45deg);
    }

    .cookies__info { flex: 1; }

    #cookies-policy .cookies__title {
        font-family: 'Cinzel Decorative', Georgia, 'Times New Roman', serif;
        font-size: 20px;
        font-weight: 400;
        color: #ffffff;
        margin: 0 0 6px 0;
        line-height: 1.3;
        letter-spacing: .5px;
        /* El kerning de Cinzel Decorative encima las dos "o" de "cookies" */
        font-kerning: none;
    }

    #cookies-policy .cookies__text {
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        color: rgba(255, 255, 255, .72);
        line-height: 1.6;
        margin: 0;
    }

    .cookies__actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .cookies__btn-accept {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 40px;
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #172144 !important;
        background: #e7b669 !important;
        border: 1px solid #e7b669 !important;
        border-radius: 2px !important;
        cursor: pointer;
        transition: background .25s ease, color .25s ease, transform .25s ease;
        text-decoration: none !important;
        white-space: nowrap;
        -webkit-appearance: none;
        appearance: none;
    }

    .cookies__btn-accept:hover {
        color: #e7b669 !important;
        background: transparent !important;
        transform: translateY(-1px);
    }

    .cookies__btn-accept:disabled {
        opacity: .85;
        cursor: not-allowed;
    }

    .cookies__btn-config,
    .cookies__policy-link {
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        letter-spacing: .5px;
        color: rgba(255, 255, 255, .7);
        text-decoration: underline;
        text-underline-offset: 3px;
        font-weight: 500;
        transition: color .25s ease;
        white-space: nowrap;
    }

    .cookies__btn-config:hover,
    .cookies__policy-link:hover {
        color: #e7b669;
    }

    /* ========== VISTA DE CONFIGURACIÓN ========== */
    .cookies__config { padding: 6px 0; }

    .cookies__config-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .cookies__back {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 1px solid rgba(231, 182, 105, .6);
        border-radius: 50%;
        color: #e7b669;
        cursor: pointer;
        transition: all .25s ease;
    }

    .cookies__back:hover {
        background: #e7b669;
        color: #172144;
    }

    .cookies__config-header .cookies__title { margin: 0; font-size: 18px; }

    .cookies__categories {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 18px;
    }

    .cookies__category {
        flex: 1;
        min-width: 280px;
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(231, 182, 105, .25);
        border-radius: 4px;
        padding: 16px;
        transition: border-color .25s ease;
    }

    .cookies__category:hover { border-color: #e7b669; }

    .cookies__category-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        cursor: pointer;
    }

    .cookies__category-info { flex: 1; }

    .cookies__category-name {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .cookies__category-name i { color: #e7b669; font-size: 13px; }

    .cookies__category-desc {
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        color: rgba(255, 255, 255, .6);
        line-height: 1.5;
        margin: 0;
    }

    .cookies__toggle { position: relative; flex-shrink: 0; }

    .cookies__toggle input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }

    .cookies__toggle-slider {
        display: block;
        width: 46px;
        height: 26px;
        background: rgba(255, 255, 255, .18);
        border-radius: 26px;
        position: relative;
        cursor: pointer;
        transition: all .25s ease;
    }

    .cookies__toggle-slider::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background: #ffffff;
        border-radius: 50%;
        transition: all .25s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .25);
    }

    .cookies__toggle input:checked + .cookies__toggle-slider { background: #e7b669; }
    .cookies__toggle input:checked + .cookies__toggle-slider::after { transform: translateX(20px); }

    .cookies__toggle-slider--disabled {
        background: #e7b669;
        opacity: .6;
        cursor: not-allowed;
    }

    .cookies__toggle-slider--disabled::after { transform: translateX(20px); }

    .cookies__form-actions {
        display: flex;
        justify-content: flex-end;
    }

    .cookies__btn-save {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 30px;
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #172144;
        background: #e7b669;
        border: 1px solid #e7b669;
        border-radius: 2px;
        cursor: pointer;
        transition: all .25s ease;
    }

    .cookies__btn-save:hover {
        color: #e7b669;
        background: transparent;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 992px) {
        .cookies__content { padding: 20px 24px 20px 110px; }
        .cookies__main { flex-wrap: wrap; }
        .cookies__info { flex: 1 1 100%; order: 2; }
        .cookies__icon { order: 1; }
        .cookies__actions {
            order: 3;
            width: 100%;
            flex-direction: row;
            justify-content: flex-start;
            gap: 18px;
        }
    }

    @media (max-width: 768px) {
        .cookies__content { padding: 16px 20px; }
        .cookies__main { gap: 14px; }
        .cookies__icon { width: 38px; height: 38px; min-width: 38px; }
        .cookies__icon i { font-size: 15px; }
        .cookies__title { font-size: 16px; }
        .cookies__text { font-size: 12px; }
        .cookies__actions { flex-wrap: wrap; gap: 12px; }
        .cookies__btn-accept { padding: 10px 28px; }
        .cookies__category { min-width: 100%; padding: 14px; }
        .cookies__categories { max-height: 200px; overflow-y: auto; }
    }

    @media (max-width: 480px) {
        .cookies__content { padding: 14px 16px; }
        .cookies__actions { flex-direction: column; align-items: stretch; }
        .cookies__btn-accept { width: 100%; }
        .cookies__btn-config,
        .cookies__policy-link { text-align: center; }
    }
</style>
