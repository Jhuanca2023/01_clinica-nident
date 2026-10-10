{{-- ═══ Modal Popup Promocional - Valento ═══════════════════════════ --}}
<div id="lv-popup-promo" class="lv-popup-promo" style="display:none;" aria-modal="true" role="dialog" aria-label="Oferta especial"
     data-tienda-libres="{{ url('/tienda') }}?filtros=libres">
    <div class="lv-popup-promo__backdrop"></div>
    <div class="lv-popup-promo__modal">

        <button class="lv-popup-promo__close" id="lv-popup-close" aria-label="Cerrar">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M13 1L1 13M1 1l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="lv-popup-promo__content" id="lv-pop-content">
            <p class="lv-popup-promo__eyebrow" id="lv-pop-titulo">OBTÉN</p>
            <h2 class="lv-popup-promo__headline" id="lv-pop-descuento">10% OFF</h2>
            <p class="lv-popup-promo__sub" id="lv-pop-subtitulo">EN TU PRIMERA COMPRA</p>

            <div class="lv-popup-promo__divider" id="lv-pop-divider"></div>

            <form class="lv-popup-promo__form" id="lv-popup-form" novalidate>
                <div class="lv-popup-promo__opciones" id="lv-pop-opciones-wrap" style="display:none; text-align:left; margin-bottom:10px;">
                    <p class="lv-popup-promo__opciones-label" id="lv-pop-opciones-label">¿Qué te interesa?</p>
                    <div id="lv-pop-opciones-list"></div>
                </div>
                <div class="lv-popup-promo__field" id="lv-pop-email-wrap">
                    <input type="email" id="lv-pop-email" class="lv-popup-promo__input" placeholder="Email">
                </div>
                <div class="lv-popup-promo__field" id="lv-pop-tel-wrap">
                    <input type="tel" id="lv-pop-tel" class="lv-popup-promo__input" placeholder="Número de teléfono">
                </div>
                <button type="submit" class="lv-popup-promo__cta" id="lv-pop-btn">
                    <span class="lv-pop-btn__icon" id="lv-pop-btn-icon" style="display:none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:6px;margin-bottom:2px;">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.554 4.121 1.523 5.853L.057 23.571a.5.5 0 0 0 .612.612l5.718-1.466A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.896 0-3.672-.524-5.188-1.434l-.372-.22-3.853.988.988-3.853-.22-.372A9.956 9.956 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                        </svg>
                    </span>
                    <span id="lv-pop-btn-text">¡Lo quiero!</span>
                </button>
            </form>

            <a href="{{ url('/politica-privacidad') }}" class="lv-popup-promo__privacy" id="lv-pop-privacy">
                Consultar Políticas de Privacidad
            </a>
        </div>

        <div class="lv-popup-promo__image-panel" id="lv-pop-img-panel">
            <img src="" alt="Oferta Valento" id="lv-pop-img" class="lv-popup-promo__image" style="display:none;">
            <div class="lv-popup-promo__img-placeholder" id="lv-pop-img-placeholder">
                <span>👕</span>
            </div>
        </div>

    </div>
</div>

<style>
.lv-popup-promo {
    position: fixed; inset: 0; z-index: 99999;
    display: flex; align-items: center; justify-content: center;
    padding: 16px; opacity: 0; transition: opacity .35s ease;
}
.lv-popup-promo.lv-popup--visible { opacity: 1; }
.lv-popup-promo.lv-popup--out { opacity: 0; pointer-events: none; }

.lv-popup-promo__backdrop {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.58); backdrop-filter: blur(3px); cursor: pointer;
}

.lv-popup-promo__modal {
    position: relative; display: flex; align-items: stretch;
    width: 100%; max-width: 780px; min-height: 460px;
    max-height: min(92vh, 720px);
    border-radius: 14px; overflow: hidden; background: #fff;
    box-shadow: 0 24px 60px rgba(0,0,0,.35);
    transform: translateY(24px) scale(.97);
    transition: transform .4s cubic-bezier(.22,1,.36,1); z-index: 1;
}
.lv-popup--visible .lv-popup-promo__modal { transform: translateY(0) scale(1); }

.lv-popup-promo__close {
    position: absolute; top: 14px; right: 14px; width: 30px; height: 30px;
    border-radius: 50%; border: 1.5px solid #d0d0d0; background: #fff; color: #555;
    display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 5;
}
.lv-popup-promo__close:hover { background: #f0f0f0; border-color: #999; color: #111; }

.lv-popup-promo__content {
    flex: 0 0 52%; padding: 40px 36px 32px;
    display: flex; flex-direction: column; align-items: center; text-align: center;
    justify-content: center;
    overflow-y: auto;
    min-height: 0;
    max-height: 100%;
    -webkit-overflow-scrolling: touch;
}
.lv-popup-promo__eyebrow {
    font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 600;
    letter-spacing: 3px; text-transform: uppercase; color: #888; margin: 0 0 8px;
    flex-shrink: 0;
}
.lv-popup-promo__headline {
    font-family: 'Montserrat', sans-serif; font-size: clamp(32px, 6vw, 52px); font-weight: 900; line-height: 1;
    color: var(--lv-pop-accent, #121a36); margin: 0 0 8px; letter-spacing: -1px;
    flex-shrink: 0;
}
.lv-popup-promo__sub {
    font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase; color: #333; margin: 0 0 18px;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
    max-width: 100%;
    line-height: 1.45;
}
.lv-popup-promo__divider {
    width: 60px; height: 2px; background: var(--lv-pop-accent, #121a36);
    border-radius: 2px; margin-bottom: 20px; flex-shrink: 0;
}
.lv-popup-promo__form { width: 100%; display: flex; flex-direction: column; gap: 10px; flex-shrink: 0; }
.lv-popup-promo__field { width: 100%; }
.lv-popup-promo__input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #d8d8d8; border-radius: 6px;
    font-family: 'Montserrat', sans-serif; font-size: 13px; color: #333; outline: none;
    box-sizing: border-box; background: #fafafa;
}
.lv-popup-promo__input:focus { border-color: var(--lv-pop-accent, #121a36); background: #fff; }
.lv-popup-promo__cta {
    width: 100%; padding: 13px; background: var(--lv-pop-accent, #121a36); color: #fff;
    border: none; border-radius: 6px; font-family: 'Montserrat', sans-serif; font-size: 13px;
    font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer;
    margin-top: 4px; text-decoration: none; display: inline-block; text-align: center; box-sizing: border-box;
    flex-shrink: 0;
}
.lv-popup-promo__cta:hover { filter: brightness(1.12); transform: translateY(-1px); color: #fff; }
.lv-popup-promo__cta.lv-cta--whatsapp { background: #25d366; box-shadow: 0 4px 14px rgba(37,211,102,.35); }
.lv-popup-promo__privacy {
    display: block; margin-top: 14px; font-size: 11px; color: #888; text-decoration: underline;
    flex-shrink: 0;
}
.lv-popup-promo__image-panel {
    flex: 1 1 48%;
    position: relative;
    background: #121a36;
    overflow: hidden;
    min-height: 0;
    align-self: stretch;
}
.lv-popup-promo__image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    display: block;
    opacity: 1;
    filter: none;
    image-rendering: auto;
}
.lv-popup-promo__img-placeholder {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: 72px; opacity: .25; pointer-events: none; z-index: 0;
}
.lv-popup-promo__image-panel.has-image .lv-popup-promo__img-placeholder {
    display: none !important;
}
.lv-popup-promo__image-panel.has-image .lv-popup-promo__image {
    z-index: 1;
}
.lv-popup-promo__opciones { width: 100%; }
.lv-popup-promo__opciones-label {
    font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 600; color: #555; margin: 0 0 8px;
}
.lv-popup-promo__radio-item {
    display: flex; align-items: center; gap: 8px; margin-bottom: 7px; cursor: pointer;
}
.lv-popup-promo__radio-item input[type="radio"] {
    appearance: none; -webkit-appearance: none; width: 16px; height: 16px;
    border: 2px solid #ccc; border-radius: 50%; flex-shrink: 0; cursor: pointer;
}
.lv-popup-promo__radio-item input[type="radio"]:checked {
    border-color: var(--lv-pop-accent, #121a36);
    background: radial-gradient(circle at center, var(--lv-pop-accent, #121a36) 45%, transparent 45%);
}
.lv-popup-promo__radio-item span { font-family: 'Montserrat', sans-serif; font-size: 13px; color: #333; }

/* Modo imagen completa */
.lv-popup-promo--imagen .lv-popup-promo__modal {
    max-width: 520px; min-height: 0; aspect-ratio: 3 / 4; max-height: min(88vh, 720px);
}
.lv-popup-promo--imagen .lv-popup-promo__content { display: none !important; }
.lv-popup-promo--imagen .lv-popup-promo__image-panel { flex: 1 1 100%; width: 100%; min-height: 100%; }
.lv-popup-promo--imagen .lv-popup-promo__close { background: rgba(255,255,255,.92); }

/* Modo oferta → tienda: altura = proporción de la imagen */
.lv-popup-promo--oferta_tienda .lv-popup-promo__modal {
    max-width: 780px;
    min-height: 0;
    height: auto;
    max-height: min(90vh, 680px);
    align-items: stretch;
}
.lv-popup-promo--oferta_tienda .lv-popup-promo__content {
    flex: 0 0 52%;
    padding: 28px 28px 24px;
    justify-content: center;
    overflow-y: auto;
    min-height: 0;
}
.lv-popup-promo--oferta_tienda .lv-popup-promo__image-panel {
    display: block;
    flex: 1 1 48%;
    min-height: 0;
    background: #121a36;
    align-self: stretch;
}
.lv-popup-promo--oferta_tienda .lv-popup-promo__image {
    position: absolute !important;
    inset: 0;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
    object-position: center top;
}
.lv-popup-promo--oferta_tienda .lv-popup-promo__sub {
    text-transform: none;
    letter-spacing: 0.2px;
    font-weight: 500;
    font-size: 14px;
    line-height: 1.5;
    color: #555;
    max-width: 100%;
    margin: 0 0 16px;
    flex: 0 1 auto;
    overflow-y: auto;
    min-height: 0;
}
.lv-popup-promo--oferta_tienda .lv-popup-promo__headline {
    font-size: clamp(28px, 5.5vw, 44px);
}

@media (max-width: 640px) {
    .lv-popup-promo__modal { flex-direction: column; min-height: auto; }
    .lv-popup-promo__content { flex: none; padding: 32px 24px 24px; }
    .lv-popup-promo__image-panel { min-height: 220px; flex: none; }
    .lv-popup-promo__headline { font-size: 40px; }
    .lv-popup-promo--imagen .lv-popup-promo__modal { aspect-ratio: auto; max-height: 85vh; min-height: 420px; }
    .lv-popup-promo--oferta_tienda .lv-popup-promo__modal {
        height: auto !important;
        max-height: 90vh;
    }
    .lv-popup-promo--oferta_tienda .lv-popup-promo__image-panel {
        min-height: 240px;
        max-height: 42vh;
    }
}
</style>

<script>
(function () {
    var COOKIE_KEY = 'lv_popup_closed';
    var STORAGE_KEY = 'lv_popup_dismissed';

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    function dismissDurationMs(valor, unidad) {
        var n = Math.max(0, parseFloat(valor) || 0);
        if (unidad === 'minutos') return n * 60 * 1000;
        if (unidad === 'horas') return n * 60 * 60 * 1000;
        if (unidad === 'dias') return n * 24 * 60 * 60 * 1000;
        return 0;
    }

    function setCookie(name, valor, unidad) {
        if (unidad === 'siempre') return;
        var ms = dismissDurationMs(valor, unidad);
        if (ms <= 0) return;
        var d = new Date();
        d.setTime(d.getTime() + ms);
        document.cookie = name + '=1;expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }

    /** Persistencia: cookie (tiempo) + storage (backup / sesión si "siempre"). */
    function markDismissed(valor, unidad) {
        unidad = unidad || 'dias';
        valor = valor || 7;
        try {
            if (unidad === 'siempre') {
                // Admin: cada visita → solo ocultar el resto de esta sesión (p.ej. redirect a tienda)
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ until: 'session', at: Date.now() }));
                localStorage.removeItem(STORAGE_KEY);
            } else {
                var ms = dismissDurationMs(valor, unidad);
                var until = Date.now() + (ms > 0 ? ms : 7 * 24 * 60 * 60 * 1000);
                localStorage.setItem(STORAGE_KEY, JSON.stringify({ until: until, at: Date.now() }));
                sessionStorage.removeItem(STORAGE_KEY);
                setCookie(COOKIE_KEY, valor, unidad);
            }
        } catch (e) {
            setCookie(COOKIE_KEY, valor, unidad);
        }
    }

    function isDismissed() {
        if (getCookie(COOKIE_KEY)) return true;
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY) || localStorage.getItem(STORAGE_KEY);
            if (!raw) return false;
            var data = JSON.parse(raw);
            if (!data) return false;
            if (data.until === 'session') {
                return !!sessionStorage.getItem(STORAGE_KEY);
            }
            if (typeof data.until === 'number') {
                if (Date.now() < data.until) return true;
                localStorage.removeItem(STORAGE_KEY);
                return false;
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    function syncOfertaModalHeight() {
        var root = document.getElementById('lv-popup-promo');
        if (!root || root.getAttribute('data-modo') !== 'oferta_tienda') return;
        if (window.matchMedia('(max-width: 640px)').matches) {
            var modalMobile = root.querySelector('.lv-popup-promo__modal');
            if (modalMobile) modalMobile.style.height = '';
            return;
        }
        var img = document.getElementById('lv-pop-img');
        var modal = root.querySelector('.lv-popup-promo__modal');
        if (!img || !modal || !img.naturalWidth || !img.naturalHeight) return;

        var modalW = modal.clientWidth || Math.min(780, window.innerWidth - 32);
        var imgPanelW = modalW * 0.48;
        var h = imgPanelW * (img.naturalHeight / img.naturalWidth);
        var maxH = Math.min(window.innerHeight * 0.9, 680);
        var minH = 280;
        h = Math.max(minH, Math.min(maxH, h));
        modal.style.height = Math.round(h) + 'px';
        modal.style.minHeight = '0';
    }

    function applyPopupData(p) {
        var el = document.getElementById('lv-popup-promo');
        if (!el) return;

        var modo = (p.modo === 'imagen' || p.modo === 'oferta_tienda') ? p.modo : 'formulario';
        el.classList.remove('lv-popup-promo--formulario', 'lv-popup-promo--imagen', 'lv-popup-promo--oferta_tienda');
        el.classList.add('lv-popup-promo--' + modo);
        el.setAttribute('data-modo', modo);

        var modalEl = el.querySelector('.lv-popup-promo__modal');
        if (modalEl && modo !== 'oferta_tienda') {
            modalEl.style.height = '';
            modalEl.style.minHeight = '';
        }

        if (p.color_acento) el.style.setProperty('--lv-pop-accent', p.color_acento);

        var tieneWa = !!(p.whatsapp_numero || '').replace(/\D/g, '');

        setText('lv-pop-titulo', p.titulo);
        setText('lv-pop-descuento', p.descuento_texto);
        setText('lv-pop-subtitulo', p.subtitulo);
        var btnTextEl = document.getElementById('lv-pop-btn-text');
        if (btnTextEl && p.btn_texto) btnTextEl.textContent = p.btn_texto;

        if (p.placeholder_email) setAttr('lv-pop-email', 'placeholder', p.placeholder_email);
        if (p.placeholder_telefono) setAttr('lv-pop-tel', 'placeholder', p.placeholder_telefono);

        if (modo === 'oferta_tienda') {
            toggleEl('lv-pop-email-wrap', false);
            toggleEl('lv-pop-tel-wrap', false);
            toggleEl('lv-pop-opciones-wrap', false);
            toggleEl('lv-pop-privacy', false);
            var btnOferta = document.getElementById('lv-pop-btn');
            var btnIconOferta = document.getElementById('lv-pop-btn-icon');
            if (btnOferta) { btnOferta.classList.remove('lv-cta--whatsapp'); btnOferta.type = 'button'; }
            if (btnIconOferta) btnIconOferta.style.display = 'none';
        } else if (modo === 'formulario') {
            toggleEl('lv-pop-email-wrap', p.mostrar_email !== false && p.mostrar_email !== 0);
            toggleEl('lv-pop-tel-wrap', p.mostrar_telefono !== false && p.mostrar_telefono !== 0);
            toggleEl('lv-pop-privacy', true);

            var opWrap = document.getElementById('lv-pop-opciones-wrap');
            var opList = document.getElementById('lv-pop-opciones-list');
            var opLabel = document.getElementById('lv-pop-opciones-label');
            if (opWrap && opList) {
                var opciones = Array.isArray(p.opciones) ? p.opciones : [];
                if ((p.mostrar_opciones === true || p.mostrar_opciones === 1) && opciones.length > 0) {
                    if (opLabel && p.opciones_label) opLabel.textContent = p.opciones_label;
                    opList.innerHTML = '';
                    opciones.forEach(function (op, i) {
                        var label = document.createElement('label');
                        label.className = 'lv-popup-promo__radio-item';
                        var radio = document.createElement('input');
                        radio.type = 'radio'; radio.name = 'lv_pop_opcion'; radio.value = op;
                        if (i === 0) radio.checked = true;
                        var span = document.createElement('span');
                        span.textContent = op;
                        label.appendChild(radio); label.appendChild(span);
                        opList.appendChild(label);
                    });
                    opWrap.style.display = '';
                } else {
                    opWrap.style.display = 'none';
                }
            }

            var btn = document.getElementById('lv-pop-btn');
            var btnIcon = document.getElementById('lv-pop-btn-icon');
            if (btn) {
                btn.type = 'submit';
                if (tieneWa) {
                    btn.classList.add('lv-cta--whatsapp');
                    if (btnIcon) btnIcon.style.display = 'inline';
                } else {
                    btn.classList.remove('lv-cta--whatsapp');
                    if (btnIcon) btnIcon.style.display = 'none';
                }
            }
        }

        var img = document.getElementById('lv-pop-img');
        var ph = document.getElementById('lv-pop-img-placeholder');
        var imgPanel = document.getElementById('lv-pop-img-panel');
        if (p.url_imagen && img) {
            img.onload = function () {
                img.style.display = 'block';
                img.style.opacity = '1';
                if (ph) ph.style.display = 'none';
                if (imgPanel) imgPanel.classList.add('has-image');
                syncOfertaModalHeight();
            };
            img.onerror = function () {
                img.style.display = 'none';
                if (ph) ph.style.display = '';
                if (imgPanel) imgPanel.classList.remove('has-image');
            };
            img.src = p.url_imagen;
            if (img.complete && img.naturalWidth > 0) {
                img.style.display = 'block';
                img.style.opacity = '1';
                if (ph) ph.style.display = 'none';
                if (imgPanel) imgPanel.classList.add('has-image');
                syncOfertaModalHeight();
            }
        } else if (imgPanel) {
            imgPanel.classList.remove('has-image');
        }

        return p;
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el && val) el.textContent = val;
    }
    function setAttr(id, attr, val) {
        var el = document.getElementById(id);
        if (el) el.setAttribute(attr, val);
    }
    function toggleEl(id, show) {
        var el = document.getElementById(id);
        if (el) el.style.display = show ? '' : 'none';
    }

    function showPopup() {
        var el = document.getElementById('lv-popup-promo');
        if (!el) return;
        el.style.display = 'flex';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { el.classList.add('lv-popup--visible'); });
        });
    }

    function closePopup(cookieValor, cookieUnidad) {
        var el = document.getElementById('lv-popup-promo');
        if (!el) return;
        el.classList.add('lv-popup--out');
        el.classList.remove('lv-popup--visible');
        markDismissed(cookieValor || 7, cookieUnidad || 'dias');
        setTimeout(function () { el.style.display = 'none'; }, 400);
    }

    function fetchAndShow() {
        // Si ya aplicó "Comprar", no mostrar hasta que venza la promo
        if (getCookie('lv_popup_promo_claimed') || getCookie('lv_popup_promo_applied')) return;
        if (isDismissed()) return;

        fetch('/api/web-popup/publico')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var p = data.result;
                if (!p || p.activo !== 'S' || p.vigente !== true) return;
                if (p.descuento_aplicado) return;

                var unidad = p.cookie_unidad || 'dias';
                if (isDismissed()) return;

                applyPopupData(p);

                var delay = (p.delay_segundos || 3) * 1000;
                setTimeout(function () {
                    if (isDismissed()) return;
                    if (getCookie('lv_popup_promo_claimed')) return;
                    showPopup();
                    syncOfertaModalHeight();
                }, delay);

                window.addEventListener('resize', syncOfertaModalHeight);

                var cVal = p.cookie_dias || 7;
                var cUnidad = unidad;
                var modo = (p.modo === 'imagen' || p.modo === 'oferta_tienda') ? p.modo : 'formulario';

                function cerrar() { closePopup(cVal, cUnidad); }

                document.getElementById('lv-popup-close')?.addEventListener('click', cerrar);
                document.querySelector('.lv-popup-promo__backdrop')?.addEventListener('click', cerrar);

                if (modo === 'imagen') {
                    document.getElementById('lv-pop-img-panel')?.addEventListener('click', cerrar);
                }

                if (modo === 'oferta_tienda') {
                    var btnOferta = document.getElementById('lv-pop-btn');
                    if (btnOferta) {
                        btnOferta.type = 'button';
                        btnOferta.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                            btnOferta.disabled = true;
                            fetch('/web_popup_aplicar_descuento', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: '{}'
                            })
                            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                            .then(function (res) {
                                if (!res.ok || !res.j || !res.j.success) {
                                    btnOferta.disabled = false;
                                    alert((res.j && res.j.message) || 'No se pudo aplicar el descuento.');
                                    return;
                                }
                                // Ocultar anuncio hasta fecha_fin
                                try {
                                    var exp = res.j.result && res.j.result.expires_at
                                        ? new Date(res.j.result.expires_at).getTime()
                                        : (Date.now() + 7 * 24 * 60 * 60 * 1000);
                                    localStorage.setItem(STORAGE_KEY, JSON.stringify({ until: exp, at: Date.now(), claimed: true }));
                                    sessionStorage.removeItem(STORAGE_KEY);
                                    var d = new Date(exp);
                                    document.cookie = 'lv_popup_promo_claimed=1;expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
                                } catch (err) {}

                                try {
                                    sessionStorage.setItem('lvShopState', JSON.stringify({
                                        filter: 'libres',
                                        order: 'relevancia',
                                        page: 1,
                                        scrollY: 0,
                                        path: '/tienda'
                                    }));
                                } catch (err2) {}

                                var url = (res.j.redirect)
                                    || ((document.getElementById('lv-popup-promo') || {}).getAttribute
                                        && document.getElementById('lv-popup-promo').getAttribute('data-tienda-libres'))
                                    || '/tienda?filtros=libres';
                                window.location.assign(url);
                            })
                            .catch(function () {
                                btnOferta.disabled = false;
                                alert('Error de red al aplicar el descuento.');
                            });
                        });
                    }
                } else if (modo === 'formulario') {
                    document.getElementById('lv-popup-form')?.addEventListener('submit', function (e) {
                        e.preventDefault();
                        var email = document.getElementById('lv-pop-email')?.value.trim() || '';
                        var tel = document.getElementById('lv-pop-tel')?.value.trim() || '';
                        var opCheck = document.querySelector('input[name="lv_pop_opcion"]:checked');
                        var opcion = opCheck ? opCheck.value : '';
                        var waNumero = (p.whatsapp_numero || '').replace(/\D/g, '');
                        var waMensaje = (p.whatsapp_mensaje || '').trim();

                        if (waNumero && !tel) {
                            var telInput = document.getElementById('lv-pop-tel');
                            if (telInput) {
                                telInput.style.borderColor = '#e74c3c';
                                telInput.style.boxShadow = '0 0 0 2px rgba(231,76,60,.25)';
                                var origPlaceholder = telInput.placeholder;
                                telInput.placeholder = '¡Ingresa tu numero para continuar!';
                                telInput.focus();
                                telInput.addEventListener('input', function () {
                                    telInput.style.borderColor = '';
                                    telInput.style.boxShadow = '';
                                    telInput.placeholder = origPlaceholder;
                                }, { once: true });
                            }
                            return;
                        }

                        cerrar();

                        if (waNumero) {
                            var partes = [];
                            if (tel) partes.push('Tel: ' + tel);
                            if (email) partes.push('Email: ' + email);
                            if (opcion) partes.push('Me interesa: ' + opcion);
                            if (waMensaje) partes.push(waMensaje);
                            var msgText = encodeURIComponent(partes.join('. '));
                            var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                            if (isMobile) {
                                var appUrl = 'whatsapp://send?phone=' + waNumero + '&text=' + msgText;
                                var webUrl = 'https://web.whatsapp.com/send?phone=' + waNumero + '&text=' + msgText;
                                window.location.href = appUrl;
                                setTimeout(function () {
                                    window.open(webUrl, '_blank', 'noopener,noreferrer');
                                }, 1500);
                            } else {
                                window.open('https://web.whatsapp.com/send?phone=' + waNumero + '&text=' + msgText, '_blank', 'noopener,noreferrer');
                            }
                        }
                    });
                }

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') cerrar();
                });
            })
            .catch(function () {});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchAndShow);
    } else {
        fetchAndShow();
    }
})();
</script>
