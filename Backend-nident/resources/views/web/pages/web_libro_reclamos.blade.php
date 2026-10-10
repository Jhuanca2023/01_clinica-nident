@php
    $rsTitle = 'Libro de Reclamaciones';
    $rsDescription = 'Registra tu queja o reclamo en Detalles Rossety.';
@endphp
@include('web.rossety.partials.page_open')

<style>
/* ===== VARIABLES Y RESET ===== */
:root {
    /* Colores Rossety (rojo + dorado, igual que base.css) */
    --lr-primary:       #d62839;
    --lr-primary-dark:  #a3131f;
    --lr-primary-light: #fdecee;
    --lr-gold:          #f2c140;
    --lr-danger:        #dc3545;
    --lr-danger-light:  #fde8ea;
    --lr-warning:       #f0ad4e;
    --lr-text:          #2b1a1d;
    --lr-text-muted:    #7b6a6d;
    --lr-border:        #f0dcdf;
    --lr-bg:           #fff8f5;
    --lr-white:         #ffffff;
    --lr-shadow:        0 6px 28px rgba(0,0,0,0.08);
    --lr-shadow-sm:     0 2px 10px rgba(0,0,0,0.05);
    --lr-radius:        12px;
    --lr-radius-sm:     6px;
    --lr-transition:    all 0.25s cubic-bezier(0.4,0,0.2,1);
    --lr-font:          'Montserrat', sans-serif;
}

/* ===== CONTENEDOR PRINCIPAL ===== */
.lr-wrapper {
    background: var(--lr-bg);
    min-height: 60vh;
    padding: 2rem 0 4rem;
}

.lr-container {
    max-width: 780px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* ===== HERO HEADER ===== */
.lr-hero {
    text-align: center;
    margin-bottom: 2.5rem;
}

.lr-hero-icon {
    width: 80px;
    height: 80px;
    background: var(--lr-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 8px 24px rgba(214,40,57,0.25);
    border: 3px solid var(--lr-gold);
}

.lr-hero-icon i {
    font-size: 2rem;
    color: white;
}

.lr-hero h1 {
    font-family: var(--lr-font);
    font-size: 28px;
    font-weight: 800;
    color: var(--lr-text);
    margin-bottom: 0.5rem;
    letter-spacing: 3px;
    text-transform: uppercase;
}

.lr-hero p {
    font-family: var(--lr-font);
    color: var(--lr-text-muted);
    font-size: 13px;
    max-width: 480px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ===== DATOS DEL PROVEEDOR ===== */
.lr-proveedor {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem 1.5rem;
    background: var(--lr-white);
    border: 1px solid var(--lr-border);
    border-left: 3px solid var(--lr-gold);
    border-radius: var(--lr-radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 2rem;
    font-family: var(--lr-font);
}
.lr-proveedor div { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.lr-proveedor span { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--lr-text-muted); }
.lr-proveedor strong { font-size: 13px; color: var(--lr-primary); font-weight: 600; overflow-wrap: anywhere; }
@media (max-width: 600px) { .lr-proveedor { grid-template-columns: 1fr; } }

/* ===== ALERTAS ===== */
.lr-alert {
    border-radius: var(--lr-radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    animation: slideDown 0.4s ease;
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.lr-alert.fade-out {
    opacity: 0;
    transform: translateY(-8px);
}

.lr-alert-error {
    background: var(--lr-danger-light);
    border: 1px solid #f5c6cb;
    color: var(--lr-danger);
}

.lr-alert-success {
    background: var(--lr-primary-light);
    border: 1px solid #f0dcdf;
    color: var(--lr-primary-dark);
}

.lr-alert-icon { font-size: 1.3rem; margin-top: 1px; flex-shrink: 0; }
.lr-alert-body { flex: 1; }
.lr-alert-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.3rem; }
.lr-alert-close {
    background: none; border: none; cursor: pointer;
    color: inherit; opacity: 0.6; font-size: 1rem; padding: 0 0 0 0.5rem;
    transition: opacity 0.2s; flex-shrink: 0;
}
.lr-alert-close:hover { opacity: 1; }

.lr-tracking-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--lr-primary-light);
    border: 1.5px solid var(--lr-primary);
    border-radius: 50px;
    padding: 0.4rem 1rem;
    font-weight: 700;
    font-size: 1rem;
    color: var(--lr-primary-dark);
    margin: 0.5rem 0;
}

/* ===== STEPPER ===== */
.lr-stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2rem;
    gap: 0;
}

.lr-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    position: relative;
    flex: 1;
    max-width: 160px;
}

.lr-step-bubble {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    border: 2.5px solid var(--lr-border);
    background: var(--lr-white);
    color: var(--lr-text-muted);
    transition: var(--lr-transition);
    z-index: 2;
    position: relative;
}

.lr-step-label {
    font-family: var(--lr-font);
    font-size: 12px;
    font-weight: 600;
    color: var(--lr-text-muted);
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--lr-transition);
    line-height: 1.3;
}

.lr-step-line {
    height: 2.5px;
    flex: 1;
    background: var(--lr-border);
    margin-bottom: 1.5rem;
    transition: var(--lr-transition);
    min-width: 32px;
}

/* Activo */
.lr-step.active .lr-step-bubble {
    background: var(--lr-primary);
    border-color: var(--lr-primary);
    color: white;
    box-shadow: 0 4px 12px rgba(214,40,57,0.3);
    transform: scale(1.1);
}
.lr-step.active .lr-step-label {
    font-family: var(--lr-font); color: var(--lr-primary); }

/* Completado */
.lr-step.done .lr-step-bubble {
    background: var(--lr-primary);
    border-color: var(--lr-primary);
    color: white;
}
.lr-step.done .lr-step-label {
    font-family: var(--lr-font); color: var(--lr-primary-dark); }
.lr-step-line.done { background: var(--lr-primary); }

/* ===== CARD DE FORMULARIO ===== */
.lr-card {
    background: var(--lr-white);
    border-radius: var(--lr-radius);
    box-shadow: var(--lr-shadow);
    overflow: hidden;
}

.lr-card-header {
    padding: 1.5rem 2rem 1.25rem;
    border-bottom: 1px solid var(--lr-border);
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, var(--lr-primary-light), var(--lr-white));
}

.lr-card-header-icon {
    width: 44px;
    height: 44px;
    background: var(--lr-primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.lr-card-header h3 {
    font-family: var(--lr-font);
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--lr-text);
}

.lr-card-header p {
    margin: 0.15rem 0 0;
    font-size: 14px;
    color: var(--lr-text-muted);
}

.lr-card-body {
    padding: 1.75rem 2rem;
}

/* ===== PASOS DEL FORMULARIO ===== */
.lr-step-panel {
    display: none;
    animation: fadeInUp 0.4s ease;
}
.lr-step-panel.active { display: block; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== FORM FIELDS ===== */
.lr-field-group {
    margin-bottom: 1.1rem;
}

.lr-label {
    font-family: var(--lr-font);
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--lr-text);
    margin-bottom: 0.35rem;
    letter-spacing: 0.1px;
}

.lr-label .req {
    color: var(--lr-danger);
    margin-left: 2px;
}

.lr-label .opt {
    color: var(--lr-text-muted);
    font-weight: 400;
    font-size: 12px;
    margin-left: 4px;
}

.lr-input, .lr-select, .lr-textarea {
    font-family: var(--lr-font);
    width: 100%;
    border: 1.5px solid var(--lr-border);
    border-radius: var(--lr-radius-sm);
    padding: 0.65rem 0.9rem;
    font-size: 13px;
    color: var(--lr-text);
    background: var(--lr-white);
    transition: var(--lr-transition);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
}

.lr-input:focus, .lr-select:focus, .lr-textarea:focus {
    border-color: var(--lr-primary);
    box-shadow: 0 0 0 3px rgba(214,40,57,0.1);
    background: var(--lr-white);
}

.lr-input.is-invalid, .lr-select.is-invalid, .lr-textarea.is-invalid {
    border-color: var(--lr-danger);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.lr-input.is-valid, .lr-select.is-valid {
    border-color: #27ae60;
    padding-right: 2.5rem;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2327ae60' stroke-width='2.5'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px;
}

.lr-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px;
    padding-right: 2.5rem;
    cursor: pointer;
}

.lr-textarea { resize: vertical; min-height: 120px; line-height: 1.6; }

.lr-field-hint {
    font-size: 0.76rem;
    color: var(--lr-text-muted);
    margin-top: 0.3rem;
}

.lr-field-error {
    font-size: 0.76rem;
    color: var(--lr-danger);
    margin-top: 0.3rem;
    display: none;
}

.lr-field-group:has(.is-invalid) .lr-field-error,
.lr-input.is-invalid ~ .lr-field-error,
.lr-select.is-invalid ~ .lr-field-error,
.lr-textarea.is-invalid ~ .lr-field-error {
    display: block;
}

/* ===== GRID HELPERS ===== */
.lr-row { display: flex; gap: 1rem; flex-wrap: wrap; }
.lr-col { flex: 1; min-width: 0; }
.lr-col-1-3 { flex: 0 0 calc(33.333% - 0.67rem); min-width: 0; }
.lr-col-2-3 { flex: 0 0 calc(66.666% - 0.33rem); min-width: 0; }

@media (max-width: 600px) {
    .lr-col, .lr-col-1-3, .lr-col-2-3 { flex: 0 0 100%; }
    .lr-card-body { padding: 1.25rem; }
    .lr-card-header { padding: 1.1rem 1.25rem; }
    .lr-hero h1 {
    font-family: var(--lr-font); font-size: 1.5rem; }
    .lr-step-label {
    font-family: var(--lr-font); display: none; }
}

/* ===== TIPO DE SOLICITUD (RADIO CARDS) ===== */
.lr-radio-group { display: flex; gap: 1rem; flex-wrap: wrap; }

.lr-radio-card {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.lr-radio-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0; height: 0;
}

.lr-radio-card-label {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 1rem 1.1rem;
    border: 2px solid var(--lr-border);
    border-radius: var(--lr-radius-sm);
    cursor: pointer;
    transition: var(--lr-transition);
    background: var(--lr-white);
}

.lr-radio-card-label:hover {
    border-color: var(--lr-primary);
    background: var(--lr-primary-light);
}

.lr-radio-card input:checked + .lr-radio-card-label {
    border-color: var(--lr-primary);
    background: var(--lr-primary-light);
    box-shadow: 0 0 0 3px rgba(214,40,57,0.1);
}

.lr-radio-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: var(--lr-transition);
}

.lr-radio-icon-complaint { background: #fff3e0; color: #e67e22; }
.lr-radio-icon-claim { background: #fde8ea; color: var(--lr-danger); }

.lr-radio-card input:checked + .lr-radio-card-label .lr-radio-icon-complaint { background: #e67e22; color: white; }
.lr-radio-card input:checked + .lr-radio-card-label .lr-radio-icon-claim { background: var(--lr-danger); color: white; }

.lr-radio-text strong { display: block; font-size: 14px; font-weight: 700; color: var(--lr-text); }
.lr-radio-text span { font-size: 12px; color: var(--lr-text-muted); line-height: 1.4; }

.lr-radio-check {
    margin-left: auto;
    width: 20px;
    height: 20px;
    border: 2px solid var(--lr-border);
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--lr-transition);
    align-self: center;
}

.lr-radio-card input:checked + .lr-radio-card-label .lr-radio-check {
    border-color: var(--lr-primary);
    background: var(--lr-primary);
}

.lr-radio-card input:checked + .lr-radio-card-label .lr-radio-check::after {
    content: '';
    width: 8px; height: 8px;
    border-radius: 50%;
    background: white;
}

/* ===== DROPZONE ===== */
.lr-dropzone {
    border: 2.5px dashed var(--lr-border);
    border-radius: var(--lr-radius-sm);
    padding: 2rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: var(--lr-transition);
    position: relative;
    background: var(--lr-bg);
}

.lr-dropzone:hover, .lr-dropzone.over {
    border-color: var(--lr-primary);
    background: var(--lr-primary-light);
}

.lr-dropzone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%; height: 100%;
}

.lr-dropzone-icon {
    width: 52px; height: 52px;
    background: var(--lr-primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    transition: var(--lr-transition);
}

.lr-dropzone:hover .lr-dropzone-icon,
.lr-dropzone.over .lr-dropzone-icon {
    background: var(--lr-primary);
    color: white;
}

.lr-dropzone-icon i {
    font-size: 1.3rem;
    color: var(--lr-primary);
    transition: var(--lr-transition);
}

.lr-dropzone:hover .lr-dropzone-icon i,
.lr-dropzone.over .lr-dropzone-icon i { color: white; }

.lr-dropzone-title {
    font-weight: 600;
    font-size: 14px;
    color: var(--lr-text);
    margin-bottom: 0.2rem;
}
.lr-dropzone-sub {
    font-size: 12px;
    color: var(--lr-text-muted);
}

.lr-file-list {
    margin-top: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.lr-file-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--lr-primary-light);
    border: 1px solid #f0dcdf;
    border-radius: 6px;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    color: var(--lr-primary-dark);
    font-weight: 500;
    text-align: left;
}

.lr-file-item i { font-size: 0.9rem; }
.lr-file-size { margin-left: auto; color: var(--lr-text-muted); font-weight: 400; }

/* ===== BOTONES DE NAVEGACIÓN ===== */
.lr-nav-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--lr-border);
    flex-wrap: wrap;
}

.lr-btn {
    font-family: var(--lr-font);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.5rem;
    border-radius: var(--lr-radius-sm);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--lr-transition);
    border: none;
    text-decoration: none;
    line-height: 2;
}

.lr-btn-primary {
    background: var(--lr-primary);
    color: white;
    box-shadow: 0 4px 12px rgba(214,40,57,0.25);
}
.lr-btn-primary:hover {
    background: var(--lr-primary-dark);
    box-shadow: 0 6px 16px rgba(214,40,57,0.3);
    transform: translateY(-1px);
    color: white;
}

.lr-btn-ghost {
    background: transparent;
    color: var(--lr-text-muted);
    border: 1.5px solid var(--lr-border);
}
.lr-btn-ghost:hover {
    border-color: var(--lr-text-muted);
    color: var(--lr-text);
    background: var(--lr-bg);
}

.lr-btn-danger-ghost {
    background: transparent;
    color: var(--lr-danger);
    border: 1.5px solid #f5c6cb;
    font-size: 14px;
    padding: 0.5rem 1rem;
}
.lr-btn-danger-ghost:hover { background: var(--lr-danger-light); }

.lr-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.lr-btn-submit {
    background: var(--lr-primary);
    color: white;
    padding: 14px 40px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: var(--lr-font);
    box-shadow: 0 6px 20px rgba(214,40,57,0.3);
    border-radius: 4px;
    border-bottom: 2px solid var(--lr-gold);
}
.lr-btn-submit:hover {
    background: var(--lr-primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(214,40,57,0.4);
    color: white;
}

/* ===== RESUMEN (paso 3) ===== */
.lr-summary-section {
    background: var(--lr-bg);
    border-radius: var(--lr-radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.lr-summary-section-title {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--lr-primary);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.lr-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem 1.5rem;
}

@media (max-width: 480px) { .lr-summary-grid { grid-template-columns: 1fr; } }

.lr-summary-item-label {
    font-size: 14px;
    color: var(--lr-text-muted);
    font-weight: 600;
}
.lr-summary-item-value {
    font-size: 12px;
    color: var(--lr-text);
    font-weight: 500;
}

.lr-char-counter {
    text-align: right;
    font-size: 14px;
    color: var(--lr-text-muted);
    margin-top: 0.25rem;
    transition: color 0.2s;
}
.lr-char-counter.warning { color: var(--lr-warning); }
.lr-char-counter.limit { color: var(--lr-danger); }

/* ===== LOADER DE ENVÍO ===== */
.lr-submit-spinner {
    display: none;
    width: 18px; height: 18px;
    border: 2.5px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
.lr-btn-submit.loading .lr-submit-spinner { display: block; }
.lr-btn-submit.loading .lr-btn-submit-text { opacity: 0.7; }

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== TOOLTIPS DE CAMPO ===== */
.lr-input-wrapper { position: relative; }
.lr-input-icon {
    position: absolute;
    left: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--lr-text-muted);
    font-size: 0.85rem;
    pointer-events: none;
    transition: color 0.2s;
}
.lr-input-wrapper .lr-input { padding-left: 3.2rem; }
.lr-input-wrapper .lr-input:focus ~ .lr-input-icon,
.lr-input-wrapper:has(.lr-input:focus) .lr-input-icon { color: var(--lr-primary); }

/* ===== SEPARADOR ===== */
.lr-divider {
    height: 1px;
    background: var(--lr-border);
    margin: 1.5rem 0;
}

/* ===== ESTADO LEGAL ===== */
.lr-legal-notice {
    background: linear-gradient(135deg, #fff8e1, #fffde7);
    border: 1px solid #ffe082;
    border-radius: var(--lr-radius-sm);
    padding: 0.85rem 1rem;
    display: flex;
    gap: 0.6rem;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}
.lr-legal-notice i { color: #f0ad4e; font-size: 0.95rem; margin-top: 2px; flex-shrink: 0; }
.lr-legal-notice p { margin: 0; font-size: 12px; color: #7a6227; line-height: 1.5; }
.lr-ico { display:inline-block; width:1.7em; height:1.7em; vertical-align:-0.125em; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
.lr-ico-lg { width:1.4em; height:1.4em; }
.lr-ico-xl { width:2em; height:2em; }
.lr-input-icon.lr-ico { width:18px; height:18px; }
</style><svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true"><symbol id="ico-book" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></symbol><symbol id="ico-user" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></symbol><symbol id="ico-location" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></symbol><symbol id="ico-file" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></symbol><symbol id="ico-alert-circle" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol><symbol id="ico-check-circle" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol><symbol id="ico-hash" viewBox="0 0 24 24"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></symbol><symbol id="ico-times" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></symbol><symbol id="ico-id-card" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 12a2 2 0 100-4 2 2 0 000 4zm0 0v3m4-7h4m-4 4h4"/></symbol><symbol id="ico-building" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></symbol><symbol id="ico-phone" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></symbol><symbol id="ico-mail" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></symbol><symbol id="ico-map" viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></symbol><symbol id="ico-home" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></symbol><symbol id="ico-frown" viewBox="0 0 24 24"><path d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol><symbol id="ico-warning" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></symbol><symbol id="ico-upload" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></symbol><symbol id="ico-info" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol><symbol id="ico-arrow-right" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></symbol><symbol id="ico-arrow-left" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></symbol><symbol id="ico-send" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></symbol><symbol id="ico-file-pdf" viewBox="0 0 24 24"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></symbol><symbol id="ico-file-image" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></symbol><symbol id="ico-file-doc" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h4M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></symbol></svg>
<section class="rs-pagehero">
    <div class="rs-container">
        <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><span>Libro de Reclamaciones</span></div>
        <h1 class="rs-pagehero__title">Libro de Reclamaciones</h1>
        <p class="rs-pagehero__text">Registra tu queja o reclamo de forma sencilla. Te responderemos en un plazo no mayor a 15 días hábiles.</p>
    </div>
</section>
<main>
<div class="lr-wrapper">
    <div class="lr-container">

        {{-- Datos del proveedor (obligatorios en la hoja de reclamación, D.S. 011-2011-PCM) --}}
        @php
            $rcl = \App\Support\RossetyContacto::config()['reclamos'];
            $rcDom = trim($rcl['domicilio']) !== '' ? $rcl['domicilio'] : (\Helpers::footer_()[0]->contacto_direccion ?? $rsDireccion);
        @endphp
        <div class="lr-proveedor">
            <div><span>Razón social</span><strong>{{ $rcl['razon_social'] }}</strong></div>
            @if(trim($rcl['ruc']) !== '')<div><span>RUC</span><strong>{{ $rcl['ruc'] }}</strong></div>@endif
            <div><span>Domicilio</span><strong>{{ $rcDom }}</strong></div>
            <div><span>Fecha</span><strong>{{ now()->format('d/m/Y') }}</strong></div>
        </div>

        <!-- ALERTAS -->
        @if($errors->any())
        <div class="lr-alert lr-alert-error" id="errorAlert">
            <div class="lr-alert-icon"><svg class="lr-ico lr-ico-lg"><use href="#ico-alert-circle"/></svg></div>
            <div class="lr-alert-body">
                <div class="lr-alert-title">Por favor corrige los siguientes errores:</div>
                <ul style="margin:0; padding-left:1.2rem; font-size:0.85rem;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button class="lr-alert-close" onclick="closeAlert('errorAlert')"><svg class="lr-ico"><use href="#ico-times"/></svg></button>
        </div>
        @endif

        @if(session('success_reclamo'))
        <div class="lr-alert lr-alert-success" id="successAlert">
            <div class="lr-alert-icon"><svg class="lr-ico lr-ico-lg"><use href="#ico-check-circle"/></svg></div>
            <div class="lr-alert-body">
                <div class="lr-alert-title">¡Tu reclamo fue registrado exitosamente!</div>
                @if(session('numero_reclamo'))
                <div style="margin-top:0.5rem;">
                    <span style="font-size:14px; color:var(--lr-text-muted);">Número de seguimiento:</span>
                    <div class="lr-tracking-badge">
                        <svg class="lr-ico"><use href="#ico-hash"/></svg>
                        {{ session('numero_reclamo') }}
                    </div>
                    <p style="font-size:12px; color:var(--lr-text-muted); margin:0.3rem 0 0;">Guarda este número para hacer seguimiento de tu solicitud.</p>
                </div>
                @endif
            </div>
            <button class="lr-alert-close" onclick="closeAlert('successAlert')"><svg class="lr-ico"><use href="#ico-times"/></svg></button>
        </div>
        @endif

        <!-- STEPPER -->
        <div class="lr-stepper" id="lrStepper">
            <div class="lr-step active" data-step="1">
                <div class="lr-step-bubble"><svg class="lr-ico"><use href="#ico-user"/></svg></div>
                <div class="lr-step-label">Datos<br>personales</div>
            </div>
            <div class="lr-step-line" id="line1"></div>
            <div class="lr-step" data-step="2">
                <div class="lr-step-bubble"><svg class="lr-ico"><use href="#ico-location"/></svg></div>
                <div class="lr-step-label">Domicilio</div>
            </div>
            <div class="lr-step-line" id="line2"></div>
            <div class="lr-step" data-step="3">
                <div class="lr-step-bubble"><svg class="lr-ico"><use href="#ico-file"/></svg></div>
                <div class="lr-step-label">Tu<br>solicitud</div>
            </div>
        </div>

        <!-- BOTÓN DE SEGUIMIENTO -->
        <div style="text-align:center; margin-bottom:2rem;">
            <a href="{{ route('libro.reclamos.estado') }}" class="lr-btn lr-btn-ghost" style="border-color:var(--lr-primary);color:var(--lr-primary);border-radius:999px;padding:.6rem 1.6rem;font-size:13px;font-weight:700;">
                <svg class="lr-ico"><use href="#ico-search"/></svg> Consultar estado de mi reclamo
            </a>
        </div>

        <!-- FORMULARIO -->
        <form action="{{ route('libro_reclamos.enviar') }}" method="POST" enctype="multipart/form-data" id="lrForm" novalidate>
            @csrf

            <!-- ========== PASO 1: DATOS PERSONALES ========== -->
            <div class="lr-step-panel active" id="panel1">
                <div class="lr-card">
                    <div class="lr-card-header">
                        <div class="lr-card-header-icon"><svg class="lr-ico lr-ico-lg"><use href="#ico-user"/></svg></div>
                        <div>
                            <h3>Datos personales</h3>
                            <p>Ingresa tu información de identificación</p>
                        </div>
                    </div>
                    <div class="lr-card-body">

                        <div class="lr-row">
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="document_type">Tipo de documento <span class="req">*</span></label>
                                    <select class="lr-select" id="document_type" name="document_type" required>
                                        <option value="">Selecciona tipo de documento</option>
                                        <option value="DNI" {{ old('document_type') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                        <option value="RUC" {{ old('document_type') == 'RUC' ? 'selected' : '' }}>RUC</option>
                                        <option value="CE" {{ old('document_type') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                                    </select>
                                    <div class="lr-field-error">Selecciona el tipo de documento.</div>
                                </div>
                            </div>
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="document_number">Número de documento <span class="req">*</span></label>
                                    <div class="lr-input-wrapper">
                                        <input type="text" class="lr-input" id="document_number" name="document_number"
                                               placeholder="Ej: 12345678" required maxlength="20"
                                               value="{{ old('document_number') }}">
                                        <svg class="lr-ico lr-input-icon"><use href="#ico-id-card"/></svg>
                                    </div>
                                    <div class="lr-field-error">Ingresa un número de documento válido.</div>
                                </div>
                            </div>
                        </div>

                        <div class="lr-field-group">
                            <label class="lr-label" for="business_name">Razón social <span class="opt">(opcional)</span></label>
                            <div class="lr-input-wrapper">
                                <input type="text" class="lr-input" id="business_name" name="business_name"
                                       placeholder="Solo si aplica para persona jurídica"
                                       value="{{ old('business_name') }}">
                                <svg class="lr-ico lr-input-icon"><use href="#ico-building"/></svg>
                            </div>
                        </div>

                        <div class="lr-divider"></div>

                        <div class="lr-row">
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="first_name">Nombres <span class="req">*</span></label>
                                    <input type="text" class="lr-input" id="first_name" name="first_name"
                                           placeholder="Tus nombres" required
                                           value="{{ old('first_name') }}">
                                    <div class="lr-field-error">Ingresa tus nombres.</div>
                                </div>
                            </div>
                        </div>

                        <div class="lr-row">
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="last_name_father">Apellido paterno <span class="req">*</span></label>
                                    <input type="text" class="lr-input" id="last_name_father" name="last_name_father"
                                           placeholder="Apellido paterno" required
                                           value="{{ old('last_name_father') }}">
                                    <div class="lr-field-error">Ingresa tu apellido paterno.</div>
                                </div>
                            </div>
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="last_name_mother">Apellido materno <span class="opt">(opcional)</span></label>
                                    <input type="text" class="lr-input" id="last_name_mother" name="last_name_mother"
                                           placeholder="Apellido materno"
                                           value="{{ old('last_name_mother') }}">
                                </div>
                            </div>
                        </div>

                        <div class="lr-divider"></div>

                        <div class="lr-row">
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="phone">Teléfono <span class="req">*</span></label>
                                    <div class="lr-input-wrapper">
                                        <input type="tel" class="lr-input" id="phone" name="phone"
                                               placeholder="Ej: 987 654 321" required maxlength="15"
                                               value="{{ old('phone') }}">
                                        <svg class="lr-ico lr-input-icon"><use href="#ico-phone"/></svg>
                                    </div>
                                    <div class="lr-field-error">Ingresa un número de teléfono válido.</div>
                                </div>
                            </div>
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="email">Correo electrónico <span class="req">*</span></label>
                                    <div class="lr-input-wrapper">
                                        <input type="email" class="lr-input" id="email" name="email"
                                               placeholder="correo@ejemplo.com" required maxlength="50"
                                               value="{{ old('email') }}">
                                        <svg class="lr-ico lr-input-icon"><use href="#ico-mail"/></svg>
                                    </div>
                                    <div class="lr-field-error">Ingresa un correo electrónico válido.</div>
                                </div>
                            </div>
                        </div>

                        <div class="lr-nav-buttons">
                            <span style="font-size:12px; color:var(--lr-text-muted);"><span style="font-size:0.6rem; color:var(--lr-danger); font-weight:700;">*</span> Campos obligatorios</span>
                            <button type="button" class="lr-btn lr-btn-primary" id="nextBtn1">
                                Siguiente <svg class="lr-ico"><use href="#ico-arrow-right"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 2: DOMICILIO ========== -->
            <div class="lr-step-panel" id="panel2">
                <div class="lr-card">
                    <div class="lr-card-header">
                        <div class="lr-card-header-icon"><svg class="lr-ico lr-ico-lg"><use href="#ico-location"/></svg></div>
                        <div>
                            <h3>Domicilio</h3>
                            <p>¿Dónde te encontramos?</p>
                        </div>
                    </div>
                    <div class="lr-card-body">

                        <div class="lr-row">
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="department">Departamento <span class="req">*</span></label>
                                    <div class="lr-input-wrapper">
                                        <input type="text" class="lr-input" id="department" name="department"
                                               placeholder="Ej: Lima" required
                                               value="{{ old('department') }}">
                                        <svg class="lr-ico lr-input-icon"><use href="#ico-map"/></svg>
                                    </div>
                                    <div class="lr-field-error">Ingresa tu departamento.</div>
                                </div>
                            </div>
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="province">Provincia <span class="req">*</span></label>
                                    <input type="text" class="lr-input" id="province" name="province"
                                           placeholder="Ej: Lima" required
                                           value="{{ old('province') }}">
                                    <div class="lr-field-error">Ingresa tu provincia.</div>
                                </div>
                            </div>
                            <div class="lr-col">
                                <div class="lr-field-group">
                                    <label class="lr-label" for="district">Distrito <span class="req">*</span></label>
                                    <input type="text" class="lr-input" id="district" name="district"
                                           placeholder="Ej: Miraflores" required
                                           value="{{ old('district') }}">
                                    <div class="lr-field-error">Ingresa tu distrito.</div>
                                </div>
                            </div>
                        </div>

                        <div class="lr-field-group">
                            <label class="lr-label" for="address">Dirección <span class="req">*</span></label>
                            <div class="lr-input-wrapper">
                                <input type="text" class="lr-input" id="address" name="address"
                                       placeholder="Av. / Jr. / Calle, número, referencia" required
                                       value="{{ old('address') }}">
                                <svg class="lr-ico lr-input-icon"><use href="#ico-home"/></svg>
                            </div>
                            <div class="lr-field-error">Ingresa tu dirección completa.</div>
                        </div>

                        <div class="lr-nav-buttons">
                            <button type="button" class="lr-btn lr-btn-ghost" id="prevBtn2">
                                <svg class="lr-ico"><use href="#ico-arrow-left"/></svg> Anterior
                            </button>
                            <button type="button" class="lr-btn lr-btn-primary" id="nextBtn2">
                                Siguiente <svg class="lr-ico"><use href="#ico-arrow-right"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 3: DETALLES DE SOLICITUD ========== -->
            <div class="lr-step-panel" id="panel3">
                <div class="lr-card">
                    <div class="lr-card-header">
                        <div class="lr-card-header-icon"><svg class="lr-ico lr-ico-lg"><use href="#ico-file"/></svg></div>
                        <div>
                            <h3>Detalles de tu solicitud</h3>
                            <p>Cuéntanos qué sucedió para poder ayudarte</p>
                        </div>
                    </div>
                    <div class="lr-card-body">

                        <!-- RESUMEN DE PASOS ANTERIORES -->
                        <div class="lr-summary-section" id="summaryBox" style="display:none;">
                            <div class="lr-summary-section-title">
                                <svg class="lr-ico"><use href="#ico-user"/></svg> Resumen de datos ingresados
                            </div>
                            <div class="lr-summary-grid" id="summaryContent"></div>
                        </div>

                        <!-- TIPO DE SOLICITUD -->
                        <div class="lr-field-group">
                            <label class="lr-label">Tipo de solicitud <span class="req">*</span></label>
                            <div class="lr-radio-group">
                                <div class="lr-radio-card">
                                    <input type="radio" name="request_type" id="complaint" value="complaint"
                                           {{ old('request_type') == 'complaint' ? 'checked' : '' }} required>
                                    <label class="lr-radio-card-label" for="complaint">
                                        <div class="lr-radio-icon lr-radio-icon-complaint">
                                            <svg class="lr-ico"><use href="#ico-frown"/></svg>
                                        </div>
                                        <div class="lr-radio-text">
                                            <strong>Queja</strong>
                                            <span>Disconformidad no relacionada a los productos o servicios; o malestar o descontento respecto a la atención al público</span>
                                        </div>
                                        <div class="lr-radio-check"></div>
                                    </label>
                                </div>
                                <div class="lr-radio-card">
                                    <input type="radio" name="request_type" id="claim" value="claim"
                                           {{ old('request_type') == 'claim' ? 'checked' : '' }}>
                                    <label class="lr-radio-card-label" for="claim">
                                        <div class="lr-radio-icon lr-radio-icon-claim">
                                            <svg class="lr-ico"><use href="#ico-warning"/></svg>
                                        </div>
                                        <div class="lr-radio-text">
                                            <strong>Reclamo</strong>
                                            <span>Disconformidad relacionada a los productos o servicios</span>
                                        </div>
                                        <div class="lr-radio-check"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="lr-field-error" id="requestTypeError">Selecciona el tipo de solicitud.</div>
                        </div>

                        <div class="lr-divider"></div>

                        <!-- DESCRIPCIÓN -->
                        <div class="lr-field-group">
                            <label class="lr-label" for="request_details">¿Cómo podemos ayudarte? <span class="req">*</span></label>
                            <textarea class="lr-textarea" id="request_details" name="request_details"
                                      rows="5" placeholder="Describe detalladamente tu queja o reclamo. Incluye fechas, productos o servicios involucrados y cualquier información relevante..." required maxlength="2000">{{ old('request_details') }}</textarea>
                            <div class="lr-char-counter" id="charCounter">0 / 2000 caracteres</div>
                            <div class="lr-field-error">Por favor describe tu solicitud (mínimo 20 caracteres).</div>
                        </div>

                        <div class="lr-divider"></div>

                        <!-- ADJUNTAR ARCHIVOS -->
                        <div class="lr-field-group">
                            <label class="lr-label">Adjuntar evidencia <span class="opt">(opcional)</span></label>
                            <div class="lr-dropzone" id="lrDropzone">
                                <input type="file" id="uploaded_file" name="uploaded_file[]" multiple
                                       accept=".png,.jpg,.jpeg,.docx,.doc,.pdf">
                                <div class="lr-dropzone-icon">
                                    <svg class="lr-ico lr-ico-lg"><use href="#ico-upload"/></svg>
                                </div>
                                <div class="lr-dropzone-title">Haz clic o arrastra archivos aquí</div>
                                <div class="lr-dropzone-sub">PNG, JPEG, PDF, DOC, DOCX &nbsp;·&nbsp; Máx. 2 archivos de 5 MB cada uno</div>
                            </div>
                            <div class="lr-file-list" id="fileList"></div>
                        </div>

                        <!-- AVISO LEGAL -->
                        <div class="lr-legal-notice">
                            <svg class="lr-ico"><use href="#ico-info"/></svg>
                            <p>Conforme al Código de Protección y Defensa del Consumidor (Ley N° 29571), el proveedor deberá dar respuesta al reclamo o queja en un plazo no mayor a <strong>quince (15) días hábiles improrrogables</strong>. La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el Indecopi. Al enviar, recibirás una copia de tu hoja de reclamación en el correo que indicaste.</p>
                        </div>

                        <div class="lr-nav-buttons">
                            <button type="button" class="lr-btn lr-btn-ghost" id="prevBtn3">
                                <svg class="lr-ico"><use href="#ico-arrow-left"/></svg> Anterior
                            </button>
                            <button type="submit" class="lr-btn lr-btn-submit" id="submitBtn">
                                <div class="lr-submit-spinner"></div>
                                <span class="lr-btn-submit-text"><svg class="lr-ico"><use href="#ico-send"/></svg> Enviar reclamo</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
</main>

@push('scripts')

<div id="lr-data"
    data-errors="{{ json_encode($errors->keys()) }}"
    style="display:none;"
></div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===== ESTADO =====
    let currentStep = 1;
    const totalSteps = 3;

    // ===== ELEMENTOS =====
    const steps = document.querySelectorAll('.lr-step');
    const lines = [document.getElementById('line1'), document.getElementById('line2')];
    const panels = document.querySelectorAll('.lr-step-panel');

    // ===== CONFIGURACIÓN Y REGLAS DE VALIDACIÓN =====
    const FIELD_CONFIG = {
        email: {
            validate: (v) => {
                if (!v.includes('@')) return { ok: false, msg: 'Se requiere el @ en el correo' };
                if (v.length > 50) return { ok: false, msg: 'Maximo 50 caracteres para el correo' };
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? { ok: true } : { ok: false, msg: 'Ingresa un correo valido' };
            }
        },
        phone: {
            filter: (v) => v.replace(/\D/g, '').substring(0, 15),
            validate: (v) => {
                if (!/^\d+$/.test(v)) return { ok: false, msg: 'Solo se aceptan numeros' };
                return v.length >= 7 ? { ok: true } : { ok: false, msg: 'Telefono invalido (7-15 digitos)' };
            }
        },
        document_number: {
            validate: (v) => {
                const type = document.getElementById('document_type')?.value;
                if (!type) return { ok: false, msg: 'Selecciona el tipo de documento primero' };
                if (type === 'DNI' || type === 'RUC') {
                    if (!/^\d+$/.test(v)) return { ok: false, msg: 'Solo se aceptan numeros' };
                    const len = type === 'DNI' ? 8 : 11;
                    return v.length === len ? { ok: true } : { ok: false, msg: `El ${type} debe tener ${len} digitos` };
                }
                return /^[a-zA-Z0-9]{6,20}$/.test(v) ? { ok: true } : { ok: false, msg: 'El carnet debe ser alfanumerico (6-20 car.)' };
            }
        },
        request_details: {
            validate: (v) => v.length >= 20 ? { ok: true } : { ok: false, msg: 'Describe tu solicitud (min. 20 car.)' }
        }
    };

    // ===== FUNCIONES AUXILIARES =====
    function setFieldState(el, isValid, message) {
        const isFilled = el.value.trim() !== '';
        const errorDiv = el.closest('.lr-field-group')?.querySelector('.lr-field-error') || el.parentElement.querySelector('.lr-field-error');
        
        if (!isFilled && !el.hasAttribute('required')) {
            el.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (isFilled || el.hasAttribute('required')) {
            el.classList.toggle('is-valid', isValid);
            el.classList.toggle('is-invalid', !isValid);
            if (!isValid && errorDiv && message) errorDiv.textContent = message;
            if (!isValid && errorDiv && !message && el.hasAttribute('required') && !isFilled) errorDiv.textContent = 'Este campo es obligatorio.';
        }
    }

    function validateField(el) {
        if (!el) return true;
        const val = el.value.trim();
        const config = FIELD_CONFIG[el.id] || FIELD_CONFIG[el.name];
        
        if (val === '') return !el.hasAttribute('required');

        if (config && config.validate) {
            const result = config.validate(val);
            if (!result.ok) {
                setFieldState(el, false, result.msg);
                return false;
            }
        }
        
        setFieldState(el, true);
        return true;
    }

    // ===== MANEJO DE EVENTOS Y RESTRICCIONES =====
    document.querySelectorAll('.lr-input, .lr-select, .lr-textarea').forEach(el => {
        el.addEventListener('input', (e) => {
            const config = FIELD_CONFIG[el.id] || FIELD_CONFIG[el.name];
            if (config?.filter) e.target.value = config.filter(e.target.value);
            
            // Restricción específica de longitud para documento
            if (el.id === 'document_number') {
                const type = document.getElementById('document_type')?.value;
                if (type === 'DNI') e.target.value = e.target.value.substring(0, 8);
                if (type === 'RUC') e.target.value = e.target.value.substring(0, 11);
                if (type === 'DNI' || type === 'RUC') e.target.value = e.target.value.replace(/\D/g, '');
                else if (type === 'CE') e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
            }

            validateField(el);
        });
        el.addEventListener('change', () => {
            if (el.id === 'document_type') {
                const numInput = document.getElementById('document_number');
                if (numInput) {
                    numInput.value = '';
                    numInput.classList.remove('is-valid', 'is-invalid');
                }
            }
            validateField(el);
        });
    });

    // ===== NAVEGACIÓN Y STEPS =====
    function goToStep(step) {
        panels.forEach((p, i) => p.classList.toggle('active', i + 1 === step));
        steps.forEach((s, i) => {
            s.classList.remove('active', 'done');
            if (i + 1 < step) s.classList.add('done');
            else if (i + 1 === step) s.classList.add('active');
        });
        lines.forEach((l, i) => l.classList.toggle('done', i + 1 < step));
        currentStep = step;
        window.scrollTo({ top: document.querySelector('.lr-stepper').offsetTop - 80, behavior: 'smooth' });
        if (step === 3) buildSummary();
    }

    function validateStep(step) {
        const panel = document.getElementById('panel' + step);
        const fields = panel.querySelectorAll('[required]');
        let valid = true;

        fields.forEach(el => {
            if (!validateField(el)) valid = false;
        });

        if (step === 3) {
            const radioChecked = document.querySelector('input[name="request_type"]:checked');
            const errRadio = document.getElementById('requestTypeError');
            if (!radioChecked) { errRadio.style.display = 'block'; valid = false; }
            else errRadio.style.display = 'none';
        }
        return valid;
    }

    // ===== BOTONES DE NAVEGACIÓN =====
    document.getElementById('nextBtn1').addEventListener('click', () => { if (validateStep(1)) goToStep(2); });
    document.getElementById('nextBtn2').addEventListener('click', () => { if (validateStep(2)) goToStep(3); });
    document.getElementById('prevBtn2').addEventListener('click', () => goToStep(1));
    document.getElementById('prevBtn3').addEventListener('click', () => goToStep(2));

    document.getElementById('lrForm').addEventListener('submit', function (e) {
        if (!validateStep(3)) { e.preventDefault(); return; }
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    // ===== RESUMEN Y CONTADORES =====
    function buildSummary() {
        const fields = [
            { label: 'Tipo de doc.', id: 'document_type' }, { label: 'N° documento', id: 'document_number' },
            { label: 'Nombres', id: 'first_name' }, { label: 'Ap. paterno', id: 'last_name_father' },
            { label: 'Ap. materno', id: 'last_name_mother' }, { label: 'Teléfono', id: 'phone' },
            { label: 'Email', id: 'email' }, { label: 'Departamento', id: 'department' },
            { label: 'Provincia', id: 'province' }, { label: 'Distrito', id: 'district' },
        ];
        const content = document.getElementById('summaryContent');
        content.innerHTML = fields.map(item => {
            const val = document.getElementById(item.id)?.value || '';
            return val ? `<div><div class="lr-summary-item-label">${item.label}</div><div class="lr-summary-item-value">${val}</div></div>` : '';
        }).join('');
        document.getElementById('summaryBox').style.display = 'block';
    }

    const textarea = document.getElementById('request_details');
    const counter = document.getElementById('charCounter');
    if (textarea && counter) {
        textarea.addEventListener('input', () => {
            const len = textarea.value.length;
            counter.textContent = `${len} / 2000 caracteres`;
            counter.classList.toggle('warning', len > 1600 && len <= 1900);
            counter.classList.toggle('limit', len > 1900);
        });
        if (textarea.value) textarea.dispatchEvent(new Event('input'));
    }

    // ===== DROPZONE =====
    const dropzone = document.getElementById('lrDropzone');
    const fileInput = document.getElementById('uploaded_file');
    const fileList = document.getElementById('fileList');

    function renderFiles(files) {
        fileList.innerHTML = '';
        if (!files || files.length === 0) return;
        const iconMap = { pdf: 'ico-file-pdf', doc: 'ico-file-doc', docx: 'ico-file-doc', png: 'ico-file-image', jpg: 'ico-file-image', jpeg: 'ico-file-image' };
        Array.from(files).forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            const size = file.size < 1048576 ? `${(file.size / 1024).toFixed(1)} KB` : `${(file.size / 1048576).toFixed(2)} MB`;
            const item = document.createElement('div');
            item.className = 'lr-file-item';
            item.innerHTML = `<svg class="lr-ico" style="flex-shrink:0"><use href="#${iconMap[ext] || 'ico-file'}"/></svg> <span>${file.name}</span> <span class="lr-file-size">${size}</span>`;
            fileList.appendChild(item);
        });
    }

    if (fileInput) fileInput.addEventListener('change', () => renderFiles(fileInput.files));
    if (dropzone) {
        ['dragover', 'dragleave', 'dragend', 'drop'].forEach(evt => dropzone.addEventListener(evt, e => {
            e.preventDefault();
            dropzone.classList.toggle('over', evt === 'dragover');
            if (evt === 'drop' && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                renderFiles(fileInput.files);
            }
        }));
    }

    // ===== ALERTAS Y ERRORES DE SERVIDOR =====
    window.closeAlert = (id) => {
        const el = document.getElementById(id);
        if (el) { el.classList.add('fade-out'); setTimeout(() => el.remove(), 400); }
    };
    const successAlert = document.getElementById('successAlert');
    if (successAlert) setTimeout(() => window.closeAlert('successAlert'), 8000);

    const serverData = document.getElementById('lr-data');
    const serverErrors = JSON.parse(serverData?.dataset.errors || '[]');
    if (serverErrors.length > 0) {
        const step1 = ['document_type','document_number','first_name','last_name_father','phone','email'];
        const step2 = ['department','province','district','address'];
        let errStep = 3;
        serverErrors.forEach(k => {
            if (step1.includes(k)) errStep = Math.min(errStep, 1);
            else if (step2.includes(k) && errStep !== 1) errStep = Math.min(errStep, 2);
        });
        goToStep(errStep);
    }
});
</script>
@endpush
@include('web.rossety.partials.page_close')
