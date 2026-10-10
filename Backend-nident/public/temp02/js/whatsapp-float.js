/* =============================================
   WhatsApp Floating Button - Valento
   Lee configuración de window.HQ_WA (inyectado por blade)
   ============================================= */

(function () {
    'use strict';

    var cfg = window.HQ_WA || {};
    var WA_NUMBER  = cfg.numero  || '51964381275';
    var WA_MESSAGE = cfg.mensaje || '¡Hola Valento! Me gustaría recibir más información sobre sus productos.';
    var WA_BURBUJA1 = cfg.burbuja1 || '👕 ¿Buscas polos de calidad?';
    var WA_BURBUJA2 = cfg.burbuja2 || '¡Escríbenos y te asesoramos!';
    var WA_LABEL   = cfg.label   || 'WhatsApp';
    var WA_BADGE   = cfg.badge   || '1';
    var WA_DELAY   = typeof cfg.delay === 'number' ? cfg.delay * 1000 : 2500;
    var WA_ACTIVO  = cfg.activo  !== false;

    if (!WA_ACTIVO) return;

    var WA_LINK = 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(WA_MESSAGE);

    /* ── 1. Inyectar HTML del botón ── */
    var badge = Number(WA_BADGE) > 0 ? '<span class="rm-wa-badge">' + WA_BADGE + '</span>' : '';
    var html = [
        '<div class="rm-wa-wrap" id="rmWaWrap">',
        '  <div class="rm-wa-bubble">',
        '    <strong style="display:block;font-weight:700;">' + WA_BURBUJA1 + '</strong><span style="font-weight:400;">' + WA_BURBUJA2 + '</span>',
        '  </div>',
        '  <a href="' + WA_LINK + '" target="_blank" rel="noopener noreferrer"',
        '     class="rm-wa-btn" id="rmWaBtn" aria-label="Contactar por WhatsApp">',
        '    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" aria-hidden="true">',
        '      <path d="M16.003 2.667C8.639 2.667 2.667 8.637 2.667 16c0 2.347.641 4.639 1.856 6.641L2.667 29.333l6.875-1.803A13.267 13.267 0 0 0 16.003 29.333C23.365 29.333 29.333 23.363 29.333 16c0-7.363-5.968-13.333-13.33-13.333zm0 24.267a11.04 11.04 0 0 1-5.617-1.533l-.403-.24-4.08 1.069 1.089-3.973-.263-.409A10.999 10.999 0 0 1 5.001 16c0-6.065 4.936-11 11.002-11C22.067 5 27 9.935 27 16c0 6.063-4.933 10.934-10.997 10.934zm6.027-8.2c-.329-.165-1.948-.96-2.25-1.069-.303-.109-.523-.165-.743.165-.22.329-.853 1.069-1.045 1.288-.193.22-.385.247-.714.082-.329-.165-1.391-.513-2.651-1.635-.979-.873-1.641-1.951-1.833-2.28-.193-.329-.021-.507.145-.671.149-.147.329-.385.494-.577.165-.193.22-.329.329-.549.11-.22.055-.412-.027-.577-.082-.165-.743-1.792-1.017-2.453-.268-.645-.54-.557-.743-.568-.192-.009-.412-.011-.632-.011-.22 0-.577.082-.879.412-.302.329-1.155 1.129-1.155 2.754 0 1.626 1.183 3.197 1.348 3.417.165.22 2.328 3.556 5.641 4.988.789.341 1.404.544 1.883.697.791.252 1.511.217 2.08.132.634-.095 1.948-.796 2.222-1.565.275-.769.275-1.428.193-1.565-.082-.137-.302-.22-.632-.385z"/>',
        '    </svg>',
        badge,
        '  </a>',
        '  <span class="rm-wa-label">' + WA_LABEL + '</span>',
        '</div>'
    ].join('\n');

    var container = document.createElement('div');
    container.innerHTML = html;
    document.body.appendChild(container.firstElementChild);

    var wrap = document.getElementById('rmWaWrap');
    var btn  = document.getElementById('rmWaBtn');

    /* ── 2. Tooltip: mostrar/ocultar solo al hover del botón ── */
    if (btn && wrap) {
        btn.addEventListener('mouseenter', function () {
            wrap.classList.add('rm-wa-show-bubble');
        });
        btn.addEventListener('mouseleave', function () {
            wrap.classList.remove('rm-wa-show-bubble');
        });
    }

    /* ── 3. Ocultar badge tras delay ── */
    setTimeout(function () {
        var badgeEl = document.querySelector('.rm-wa-badge');
        if (badgeEl) badgeEl.style.display = 'none';
    }, WA_DELAY);

    /* ── 4. Completar links wa.me sin mensaje ──
       Respeta el número propio de cada enlace (footer, topbar, etc.);
       solo agrega el mensaje por defecto si el enlace no trae ?text=. */
    document.addEventListener('DOMContentLoaded', function () {
        var links = document.querySelectorAll('a[href*="wa.me"]');
        links.forEach(function (link) {
            if (link.hasAttribute('data-wa-cms')) {
                return;
            }
            var href = link.getAttribute('href');
            if (href && href.indexOf('text=') === -1) {
                var sep = href.indexOf('?') === -1 ? '?text=' : '&text=';
                link.setAttribute('href', href + sep + encodeURIComponent(WA_MESSAGE));
            }
        });
    });

})();
