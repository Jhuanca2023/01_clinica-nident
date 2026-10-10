/**
 * WEB CUSTOMIZER
 *
 * Responsabilidades separadas:
 *  1. Aplicar estilos guardados (BD / localStorage) → TODOS los usuarios
 *  2. Mostrar el panel de personalización           → solo ADMINISTRADORES
 */
(function () {
    'use strict';

    const INHERIT = 'inherit';

    const DEFAULTS = {
        primary:          INHERIT,
        bgPage:           INHERIT,
        contentText:      INHERIT,
        headerTop:        INHERIT,
        headerNav:        INHERIT,
        productCard:      INHERIT,
        productCardText:  INHERIT,
        ofertaBg:         INHERIT,
        ofertaText:       INHERIT,
        footerBg:         INHERIT,
        footerText:       INHERIT,
    };

    // ── Paletas (solo se usan en el panel admin) ──────────────────────────────
    const PALETTES = {
        primary: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#922b21',  label: 'Rojo Valento' },
            { color: '#e74c3c',  label: 'Rojo suave' },
            { color: '#2980b9',  label: 'Azul' },
            { color: '#8e44ad',  label: 'Morado' },
            { color: '#16a085',  label: 'Verde azulado' },
            { color: '#27ae60',  label: 'Verde' },
            { color: '#e67e22',  label: 'Naranja' },
            { color: '#f39c12',  label: 'Amarillo' },
        ],
        bgPage: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f5f5f5',  label: 'Gris claro' },
            { color: '#eef2f7',  label: 'Azul hielo' },
            { color: '#f0f7f0',  label: 'Verde suave' },
            { color: '#fff8f0',  label: 'Melocotón' },
            { color: '#f9f0ff',  label: 'Lavanda' },
            { color: '#1a1a2e',  label: 'Oscuro' },
        ],
        contentText: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#1a202c',  label: 'Negro' },
            { color: '#333333',  label: 'Gris oscuro' },
            { color: '#4a5568',  label: 'Gris medio' },
            { color: '#718096',  label: 'Gris claro' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f0f0f0',  label: 'Blanco hueso' },
            { color: '#922b21',  label: 'Rojo Valento' },
            { color: '#2980b9',  label: 'Azul' },
            { color: '#27ae60',  label: 'Verde' },
        ],
        headerTop: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#333333',  label: 'Negro' },
            { color: '#1a1a2e',  label: 'Azul oscuro' },
            { color: '#2c3e50',  label: 'Gris oscuro' },
            { color: '#922b21',  label: 'Rojo' },
            { color: '#2980b9',  label: 'Azul' },
            { color: '#16a085',  label: 'Verde azulado' },
            { color: '#8e44ad',  label: 'Morado' },
        ],
        headerNav: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#922b21',  label: 'Rojo Valento' },
            { color: '#333333',  label: 'Negro' },
            { color: '#1a1a2e',  label: 'Azul oscuro' },
            { color: '#2c3e50',  label: 'Gris oscuro' },
            { color: '#2980b9',  label: 'Azul' },
            { color: '#16a085',  label: 'Verde azulado' },
            { color: '#8e44ad',  label: 'Morado' },
        ],
        productCard: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f9f9f9',  label: 'Gris muy claro' },
            { color: '#eef2f7',  label: 'Azul hielo' },
            { color: '#f0f7f0',  label: 'Verde suave' },
            { color: '#fff8f0',  label: 'Melocotón' },
            { color: '#f9f0ff',  label: 'Lavanda' },
            { color: '#1e1e2e',  label: 'Oscuro' },
        ],
        ofertaBg: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f5f5f5',  label: 'Gris claro' },
            { color: '#eef2f7',  label: 'Azul hielo' },
            { color: '#fff3cd',  label: 'Amarillo suave' },
            { color: '#fde8e8',  label: 'Rojo suave' },
            { color: '#e8f5e9',  label: 'Verde suave' },
            { color: '#1a1a2e',  label: 'Oscuro' },
            { color: '#0d1b2a',  label: 'Azul marino' },
            { color: '#1b0000',  label: 'Rojo oscuro' },
            { color: '#922b21',  label: 'Rojo Valento' },
            { color: '#2980b9',  label: 'Azul' },
        ],
        ofertaText: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#1a202c',  label: 'Negro' },
            { color: '#333333',  label: 'Gris oscuro' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f0f0f0',  label: 'Blanco hueso' },
            { color: '#922b21',  label: 'Rojo Valento' },
            { color: '#2980b9',  label: 'Azul' },
            { color: '#27ae60',  label: 'Verde' },
            { color: '#e67e22',  label: 'Naranja' },
            { color: '#f39c12',  label: 'Amarillo' },
            { color: '#8e44ad',  label: 'Morado' },
        ],
        productCardText: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#1a202c',  label: 'Negro' },
            { color: '#333333',  label: 'Gris oscuro' },
            { color: '#4a5568',  label: 'Gris medio' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f0f0f0',  label: 'Blanco hueso' },
            { color: '#922b21',  label: 'Rojo Valento' },
            { color: '#2980b9',  label: 'Azul' },
            { color: '#27ae60',  label: 'Verde' },
            { color: '#e67e22',  label: 'Naranja' },
            { color: '#8e44ad',  label: 'Morado' },
        ],
        footerBg: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#1a1a2e',  label: 'Azul noche (default)' },
            { color: '#111111',  label: 'Negro' },
            { color: '#1c2833',  label: 'Gris oscuro' },
            { color: '#0d1b2a',  label: 'Azul marino' },
            { color: '#1b0000',  label: 'Rojo oscuro' },
            { color: '#0d2137',  label: 'Azul acero' },
            { color: '#1a2e1a',  label: 'Verde oscuro' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#f5f5f5',  label: 'Gris claro' },
        ],
        footerText: [
            { color: INHERIT,    label: 'Original (tema)' },
            { color: '#ffffff',  label: 'Blanco' },
            { color: '#cccccc',  label: 'Gris claro' },
            { color: '#aaaaaa',  label: 'Gris medio' },
            { color: '#922b21',  label: 'Rojo' },
            { color: '#90caf9',  label: 'Azul claro' },
            { color: '#a5d6a7',  label: 'Verde claro' },
            { color: '#f48fb1',  label: 'Rosa' },
            { color: '#333333',  label: 'Oscuro' },
        ],
    };

    const LS_KEY = 'pm_customizer_v4';

    // ═════════════════════════════════════════════════════════════════════════
    // SECCIÓN 1: Aplicar estilos — se ejecuta para TODOS los usuarios
    // ═════════════════════════════════════════════════════════════════════════

    // Elemento <style> inyectado en el <head> para sobrescribir el tema
    const styleEl = document.createElement('style');
    styleEl.id = 'pm-customizer-styles';
    document.head.appendChild(styleEl);

    // ── Helpers de color ──────────────────────────────────────────────────────
    function hexToRgb(hex) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return { r, g, b };
    }

    function isDark(hex) {
        if (!hex || hex === INHERIT) return false;
        try {
            const { r, g, b } = hexToRgb(hex);
            return (r * 299 + g * 587 + b * 114) / 1000 < 128;
        } catch { return false; }
    }

    function darken(hex, pct) {
        try {
            let { r, g, b } = hexToRgb(hex);
            const f = 1 - pct / 100;
            r = Math.max(0, Math.round(r * f));
            g = Math.max(0, Math.round(g * f));
            b = Math.max(0, Math.round(b * f));
            return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('');
        } catch { return hex; }
    }

    // ── Construir e inyectar CSS dinámico ─────────────────────────────────────
    function applyStyles(s) {
        const chunks = [];

        if (s.primary !== INHERIT) {
            const p = s.primary;
            chunks.push(`
                #pm-content-area a,
                .header-2 a { color: ${p} !important; }
                #pm-content-area a:hover, #pm-content-area a:focus,
                .header-2 a:hover, .header-2 a:focus { color: ${darken(p, 15)} !important; }
                .btn-primary, .btn-secondary {
                    background-color: ${p} !important;
                    border-color: ${p} !important;
                }
                .product-price { color: ${p} !important; }
                .btn-product, .btn-product-icon { color: ${p} !important; }
                .btn-product-icon:hover, .btn-product-icon:focus { background-color: ${p} !important; }
                .product-label.label-primary { background-color: ${p} !important; }
                .header-bottom .menu > li > a::before { background-color: ${p} !important; }
                .category-dropdown .dropdown-toggle:hover,
                .category-dropdown .dropdown-toggle:focus,
                .category-dropdown.show .dropdown-toggle { background-color: ${p} !important; }
                .cart-dropdown .cart-count,
                .wishlist a .wishlist-count { background-color: ${p} !important; }
                .tip { background-color: ${p} !important; }
                .owl-theme .owl-nav [class*='owl-']:not(.disabled):hover {
                    border-color: ${p} !important; background: ${p} !important;
                }
            `);
        }

        if (s.bgPage !== INHERIT) {
            const bg = s.bgPage;
            chunks.push(`
                body.clsFondoGris { background-color: ${bg} !important; }
                #pm-content-area,
                #pm-content-area .main,
                #pm-content-area > section,
                #pm-content-area > div,
                #pm-content-area section[class] { background: ${bg} !important; background-color: ${bg} !important; }
                ${isDark(bg) ? `#pm-content-area .section-title h2, #pm-content-area h2 { color: #fff !important; }` : ''}
            `);
        }

        if (s.contentText !== INHERIT) {
            const ct = s.contentText;
            chunks.push(`
                #pm-content-area p,
                #pm-content-area span:not(.badge):not(.pc-discount-badge):not(.pc-mayorista-tag),
                #pm-content-area li,
                #pm-content-area label,
                #pm-content-area h1,
                #pm-content-area h2,
                #pm-content-area h3,
                #pm-content-area h4,
                #pm-content-area h5,
                #pm-content-area h6,
                #pm-content-area .about-title,
                #pm-content-area .about-text,
                #pm-content-area .about-text *,
                #pm-content-area .section-title,
                #pm-content-area .section-title h2,
                #pm-content-area .intro-title,
                #pm-content-area .intro-subtitle,
                #pm-content-area .heading .title { color: ${ct} !important; }
            `);
        }

        if (s.headerTop !== INHERIT) {
            const ht = s.headerTop;
            chunks.push(`
                .header.header-2 .header-top { background-color: ${ht} !important; }
                .header.header-2 .header-top .text-white,
                .header.header-2 .header-top .nav-link,
                .header.header-2 .header-top p,
                .header.header-2 .header-top a {
                    color: ${isDark(ht) ? '#fff' : '#333'} !important;
                }
            `);
        }

        if (s.headerNav !== INHERIT) {
            const hn = s.headerNav;
            chunks.push(`
                .header-bottom,
                .header-bottom.sticky-header,
                .sticky-header.fixed { background-color: ${hn} !important; }
                .header-bottom .menu > li > a {
                    color: ${isDark(hn) ? '#fff' : '#333'} !important;
                }
                .header-bottom .dropdown.category-dropdown .dropdown-toggle {
                    color: ${isDark(hn) ? '#fff' : '#333'} !important;
                }
            `);
        }

        if (s.productCard !== INHERIT) {
            const pc = s.productCard;
            const textColor  = isDark(pc) ? '#eee' : '#333';
            const priceColor = isDark(pc) ? '#ff8080' : '';
            chunks.push(`
                .product-layout-1 { background-color: ${pc} !important; }
                .mv-card { background-color: ${pc} !important; }
                .product { background-color: ${pc} !important; }
                .product .product-body { background-color: ${pc} !important; }
                .clscardProductos { background-color: ${pc} !important; }
                .clscardProductos_MasVendidos { background-color: ${pc} !important; }
                ${isDark(pc) ? `
                    .product-layout-1 .pc-title a,
                    .mv-title,
                    .product-title a { color: ${textColor} !important; }
                    .product-layout-1 .pc-price-current,
                    .product-layout-1 .pc-price-only,
                    .mv-card .pc-price-current,
                    .mv-card .pc-price-only,
                    .product-price { color: ${priceColor} !important; }
                    .ratings-val { color: #ffd700 !important; }
                ` : ''}
            `);
        }

        if (s.ofertaBg !== INHERIT) {
            const ob = s.ofertaBg;
            chunks.push(`
                #pm-oferta-area .deal-container { background-color: ${ob} !important; }
                #pm-oferta-area .deal-container .bg-white,
                #pm-oferta-area .deal-container .row { background-color: ${ob} !important; }
            `);
        }

        if (s.ofertaText !== INHERIT) {
            const ot = s.ofertaText;
            chunks.push(`
                #pm-oferta-area .title,
                #pm-oferta-area .deal-content h2,
                #pm-oferta-area .deal-content h4,
                #pm-oferta-area .deal-content h5,
                #pm-oferta-area .deal-content p,
                #pm-oferta-area .deal-content span:not(.new-price):not(.old-price),
                #pm-oferta-area .deal-content .countdown-label,
                #pm-oferta-area .deal-content .countdown-separator,
                #pm-oferta-area .banner-subtitle,
                #pm-oferta-area .banner-content h3 { color: ${ot} !important; }
                #pm-oferta-area #countdown .countdown-item span:not(.countdown-label) { color: ${ot} !important; }
            `);
        }

        if (s.productCardText !== INHERIT) {
            const pct = s.productCardText;
            chunks.push(`
                .pc-title,
                .pc-title:hover,
                .mv-title,
                .mv-title:hover,
                .gal-list-card__title,
                .gal-list-card__title a,
                .gal-list-card__desc { color: ${pct} !important; }
            `);
        }

        if (s.footerBg !== INHERIT) {
            const fb = s.footerBg;
            chunks.push(`
                .ft-root { background: ${fb} !important; }
                .ft-root .ft-bottom { background: ${darken(fb, 8)} !important; }
            `);
        }

        if (s.footerText !== INHERIT) {
            const ft = s.footerText;
            chunks.push(`
                .ft-root,
                .ft-root p,
                .ft-root span,
                .ft-root .ft-hours,
                .ft-root .ft-hours * { color: ${ft} !important; }
                .ft-root a { color: ${ft} !important; }
                .ft-root .ft-heading { color: #ffffff !important; }
                .ft-root .ft-col-title { color: #ffffff !important; }
                .ft-root .ft-link { color: ${ft} !important; }
                .ft-root .ft-link:hover { color: #ffffff !important; }
                .ft-root .ft-bottom,
                .ft-root .ft-bottom p,
                .ft-root .ft-bottom span,
                .ft-root .ft-bottom a { color: ${ft} !important; }
            `);
        }

        styleEl.textContent = chunks.join('\n');
    }

    // ── Persistencia ─────────────────────────────────────────────────────────
    function saveLocalState(s) {
        try { localStorage.setItem(LS_KEY, JSON.stringify(s)); } catch {}
    }

    function loadState() {
        // Prioridad 1: estilos guardados en BD (inyectados desde PHP en base.blade.php)
        if (window.PM_CUSTOMIZER_STYLES && typeof window.PM_CUSTOMIZER_STYLES === 'object') {
            const dbState = Object.assign({}, DEFAULTS, window.PM_CUSTOMIZER_STYLES);
            try { localStorage.setItem(LS_KEY, JSON.stringify(dbState)); } catch {}
            return dbState;
        }
        // Prioridad 2: localStorage (fallback offline)
        try {
            const saved = JSON.parse(localStorage.getItem(LS_KEY) || 'null');
            return saved ? Object.assign({}, DEFAULTS, saved) : Object.assign({}, DEFAULTS);
        } catch { return Object.assign({}, DEFAULTS); }
    }

    // Cargar estado y aplicar estilos inmediatamente para TODOS los usuarios
    let state = loadState();
    applyStyles(state);

    // ═════════════════════════════════════════════════════════════════════════
    // SECCIÓN 2: Panel de personalización — solo para ADMINISTRADORES
    // ═════════════════════════════════════════════════════════════════════════
    if (!window.PM_IS_ADMIN) return;

    // ── Guardar en BD via AJAX ────────────────────────────────────────────────
    function saveToDatabase(s, callback) {
        const token = window.PM_CSRF_TOKEN || '';
        fetch('/web_customizer_guardar', {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            body: JSON.stringify({ styles: s }),
        })
        .then(r => r.json())
        .then(data => { if (callback) callback(data.success, data.message); })
        .catch(() => { if (callback) callback(false, 'Error de conexión'); });
    }

    // ── Reset en BD via AJAX ──────────────────────────────────────────────────
    function resetDatabase(callback) {
        const token = window.PM_CSRF_TOKEN || '';
        fetch('/web_customizer_reset', {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => { if (callback) callback(data.success, data.message); })
        .catch(() => { if (callback) callback(false, 'Error de conexión'); });
    }

    // ── Feedback visual en botones ────────────────────────────────────────────
    function setBtnState(btn, loading, successMsg) {
        if (loading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i>Guardando...';
        } else {
            btn.disabled = false;
            btn.innerHTML = successMsg || btn.dataset.originalHtml || btn.innerHTML;
            setTimeout(() => {
                if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
            }, 2200);
        }
    }

    // ── Construcción del HTML del panel ──────────────────────────────────────
    function buildPanel() {
        const btn = document.createElement('button');
        btn.className = 'customizer-toggle-btn';
        btn.id = 'customizerToggleBtn';
        btn.setAttribute('title', 'Personalizar (Admin)');
        btn.innerHTML = '<i class="fa-solid fa-gear"></i>';

        const panel = document.createElement('div');
        panel.className = 'customizer-panel';
        panel.id = 'customizerPanel';
        panel.innerHTML = `
            <div class="customizer-header">
                <h5><i class="fa-solid fa-palette" style="margin-right:8px;"></i>Personalizar</h5>
                <button class="customizer-close" id="customizerCloseBtn" title="Cerrar">&times;</button>
            </div>
            <div class="customizer-body">
                <div class="customizer-section">
                    <span class="customizer-label">Fondo de Página</span>
                    <div class="customizer-palette" id="palette-bgPage"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Texto del Contenido</span>
                    <div class="customizer-palette" id="palette-contentText"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Header Superior</span>
                    <div class="customizer-palette" id="palette-headerTop"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Header Navegación</span>
                    <div class="customizer-palette" id="palette-headerNav"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">En Oferta — Fondo</span>
                    <div class="customizer-palette" id="palette-ofertaBg"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">En Oferta — Texto</span>
                    <div class="customizer-palette" id="palette-ofertaText"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Tarjetas de Productos — Fondo</span>
                    <div class="customizer-palette" id="palette-productCard"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Tarjetas de Productos — Texto</span>
                    <div class="customizer-palette" id="palette-productCardText"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Footer — Fondo</span>
                    <div class="customizer-palette" id="palette-footerBg"></div>
                </div>
                <hr class="customizer-divider">
                <div class="customizer-section">
                    <span class="customizer-label">Footer — Texto</span>
                    <div class="customizer-palette" id="palette-footerText"></div>
                </div>
                <hr class="customizer-divider">
                <button class="customizer-save-btn" id="customizerSaveBtn">
                    <i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Guardar cambios
                </button>
                <button class="customizer-reset-btn" id="customizerResetBtn" style="margin-top:8px;">
                    <i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i>Restablecer original
                </button>
            </div>
        `;

        document.body.appendChild(btn);
        document.body.appendChild(panel);
        return { btn, panel };
    }

    // ── Renderizar swatches de color ──────────────────────────────────────────
    function renderPalette(key, palette, currentColor) {
        const container = document.getElementById('palette-' + key);
        if (!container) return;
        container.innerHTML = '';

        palette.forEach(({ color, label }) => {
            const sw = document.createElement('span');
            sw.className = 'customizer-swatch' + (color === currentColor ? ' active' : '');

            if (color === INHERIT) {
                sw.style.background = 'linear-gradient(135deg, #eee 50%, #ccc 50%)';
                sw.style.border = '2px solid #aaa';
                sw.style.position = 'relative';
                sw.innerHTML = '<i class="fa-solid fa-rotate-left" style="font-size:10px;color:#555;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></i>';
            } else {
                sw.style.backgroundColor = color;
            }

            sw.title = label;
            sw.addEventListener('click', () => {
                state[key] = color;
                applyStyles(state);
                saveLocalState(state);
                container.querySelectorAll('.customizer-swatch').forEach(s => s.classList.remove('active'));
                sw.classList.add('active');
            });
            container.appendChild(sw);
        });

        // Color picker nativo
        const custom = document.createElement('span');
        custom.className = 'customizer-swatch-custom';
        custom.title = 'Elegir color personalizado';
        const picker = document.createElement('input');
        picker.type = 'color';
        picker.value = (currentColor && currentColor !== INHERIT) ? currentColor : '#ffffff';
        picker.addEventListener('input', (e) => {
            state[key] = e.target.value;
            applyStyles(state);
        });
        picker.addEventListener('change', (e) => {
            state[key] = e.target.value;
            applyStyles(state);
            saveLocalState(state);
            container.querySelectorAll('.customizer-swatch').forEach(s => s.classList.remove('active'));
        });
        custom.appendChild(picker);
        container.appendChild(custom);
    }

    function renderAllPalettes() {
        Object.keys(PALETTES).forEach(key => {
            renderPalette(key, PALETTES[key], state[key]);
        });
    }

    // ── Init del panel ────────────────────────────────────────────────────────
    function initPanel() {
        const { btn, panel } = buildPanel();

        renderAllPalettes();

        function openPanel()  { panel.classList.add('is-open');    btn.classList.add('is-open'); }
        function closePanel() { panel.classList.remove('is-open'); btn.classList.remove('is-open'); }

        btn.addEventListener('click', () => {
            panel.classList.contains('is-open') ? closePanel() : openPanel();
        });

        document.getElementById('customizerCloseBtn').addEventListener('click', closePanel);

        // Guardar en BD
        document.getElementById('customizerSaveBtn').addEventListener('click', function () {
            const saveBtn = this;
            setBtnState(saveBtn, true);
            saveToDatabase(state, (success, msg) => {
                if (success) {
                    setBtnState(saveBtn, false, '<i class="fa-solid fa-check" style="margin-right:6px;color:#27ae60;"></i>¡Guardado!');
                    window.PM_CUSTOMIZER_STYLES = Object.assign({}, state);
                } else {
                    setBtnState(saveBtn, false, '<i class="fa-solid fa-xmark" style="margin-right:6px;color:#e74c3c;"></i>' + (msg || 'Error al guardar'));
                }
            });
        });

        // Reset completo (local + BD)
        document.getElementById('customizerResetBtn').addEventListener('click', function () {
            const resetBtn = this;
            if (!confirm('¿Restablecer todos los colores al diseño original? Esta acción borrará los cambios guardados.')) return;

            setBtnState(resetBtn, true);
            resetBtn.dataset.originalHtml = '<i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i>Restablecer original';

            resetDatabase((success, msg) => {
                if (success) {
                    state = Object.assign({}, DEFAULTS);
                    applyStyles(state);
                    saveLocalState(state);
                    renderAllPalettes();
                    window.PM_CUSTOMIZER_STYLES = null;
                    setBtnState(resetBtn, false, '<i class="fa-solid fa-check" style="margin-right:6px;color:#27ae60;"></i>¡Restablecido!');
                } else {
                    setBtnState(resetBtn, false, '<i class="fa-solid fa-xmark" style="margin-right:6px;color:#e74c3c;"></i>' + (msg || 'Error'));
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPanel);
    } else {
        initPanel();
    }

})();
