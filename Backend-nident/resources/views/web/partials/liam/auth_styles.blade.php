{{-- Estilos copiados de temp02/login.html (template Valento) --}}
<style>
/* ── Auth page ────────────────────────────── */
.auth-page {
    min-height: calc(100vh - 300px);
    display: flex;
    align-items: center;
    padding: 50px 0 60px;
    background: #f7f7f7;
}

/* Tabs login / registro */
.auth-tabs {
    display: flex;
    margin-bottom: 0;
    border-bottom: 2px solid #e0e0e0;
}
.auth-tab {
    flex: 1;
    padding: 14px;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #aaa;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: color .2s, border-color .2s;
    user-select: none;
}
.auth-tab.active { color: #1a1a1a; border-bottom-color: #8C6B2E; }
.auth-tab:hover { color: #555; }

/* Card */
.auth-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 30px rgba(0,0,0,.09);
    overflow: hidden;
    width: 100%;
    max-width: 440px;
    margin: 0 auto;
}

/* Panel */
.auth-panel { display: none; padding: 36px 36px 30px; }
.auth-panel.active { display: block; }

/* Logo centrado */
.auth-logo {
    text-align: center;
    margin-bottom: 28px;
}
.auth-logo img { height: 78px; width: auto; max-width: 100%; object-fit: contain; }
.auth-logo span {
    display: block;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 18px;
    font-weight: 500;
    font-style: italic;
    letter-spacing: .4px;
    color: #8C6B2E;
    text-transform: none;
    margin-top: 4px;
}

/* Título panel */
.auth-panel-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: 1px;
    margin-bottom: 24px;
    text-align: center;
}

/* Fields */
.auth-field { margin-bottom: 18px; }
.auth-label {
    display: block;
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #555;
    margin-bottom: 6px;
}
.auth-input-wrap { position: relative; }
.auth-input {
    width: 100%;
    border: 1.5px solid #e0e0e0;
    border-radius: 4px;
    padding: 11px 42px 11px 14px;
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    color: #333;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    background: #fafafa;
}
.auth-input::placeholder { color: #bbb; }
.auth-input:focus {
    border-color: #8C6B2E;
    box-shadow: 0 0 0 3px rgba(192,57,43,.07);
    background: #fff;
}
.auth-input.valid  { border-color: #27ae60; }
.auth-input.error  { border-color: #8C6B2E; }
.auth-input-icon {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #bbb;
    font-size: 14px;
    cursor: pointer;
    transition: color .2s;
}
.auth-input-icon:hover { color: #555; }

/* Fila recordar + olvidé */
.auth-row-extra {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}
.auth-check {
    display: flex;
    align-items: center;
    gap: 7px;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: #666;
    cursor: pointer;
}
.auth-check input { accent-color: #8C6B2E; cursor: pointer; }
.auth-forgot {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: #8C6B2E;
    text-decoration: none;
    font-weight: 600;
    transition: opacity .2s;
}
.auth-forgot:hover { opacity: .75; text-decoration: underline; color: #8C6B2E; }

/* Botón principal */
.auth-btn {
    display: block;
    width: 100%;
    padding: 14px;
    background: #172144;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .2s, transform .15s;
    margin-bottom: 18px;
    position: relative;
    overflow: hidden;
}
.auth-btn:hover,
.auth-btn:focus,
.auth-btn:active {
    background: #C4A35A;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(196, 163, 90, 0.4);
}
.auth-btn:active { transform: translateY(0); }

/* Divisor OR */
.auth-or {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.auth-or span {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
    color: #bbb;
    flex-shrink: 0;
}
.auth-or::before, .auth-or::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e8e8e8;
}

/* Social buttons */
.auth-social { display: flex; gap: 10px; margin-bottom: 20px; }
.auth-social-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px;
    border: 1.5px solid #e0e0e0;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    font-weight: 600;
    color: #444;
    transition: border-color .2s, background .2s;
    text-decoration: none;
}
.auth-social-btn:hover { border-color: #8C6B2E; background: #fff8f7; color: #444; text-decoration: none; }
.auth-social-btn i { font-size: 16px; }
.auth-social-btn.google i { color: #ea4335; }
.auth-social-btn.facebook i { color: #1877f2; }
.auth-social-btn.whatsapp i { color: #25d366; }

/* Pie del panel */
.auth-footer {
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: #888;
    padding-top: 18px;
    border-top: 1px solid #f0f0f0;
}
.auth-footer a { color: #8C6B2E; font-weight: 700; text-decoration: none; }
.auth-footer a:hover { text-decoration: underline; }

/* Error message */
.auth-error {
    display: none;
    background: #fff0ee;
    border: 1px solid #f5c6c6;
    border-radius: 4px;
    padding: 10px 14px;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: #8C6B2E;
    font-weight: 600;
    margin-bottom: 16px;
}
.auth-error.show { display: flex; align-items: center; gap: 8px; }

/* Success */
.auth-success {
    display: none;
    background: #e8f5e9;
    border: 1px solid #a5d6a7;
    border-radius: 4px;
    padding: 10px 14px;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    color: #2e7d32;
    font-weight: 600;
    margin-bottom: 16px;
}
.auth-success.show { display: flex; align-items: center; gap: 8px; }

/* Strength bar */
.auth-strength { margin-top: 6px; height: 3px; background: #eee; border-radius: 2px; overflow: hidden; }
.auth-strength-fill { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }

/* Nombre fila */
.auth-row-2 { display: flex; gap: 12px; }
.auth-row-2 .auth-field { flex: 1; }

@media (max-width: 480px) {
    .auth-panel { padding: 24px 20px 20px; }
    .auth-row-2 { flex-direction: column; gap: 0; }
}
</style>
{{-- Mismo orden que el template: fonts.css después de estos estilos --}}
<link rel="stylesheet" href="{{ asset('temp02/css/fonts.css') }}?v={{ filemtime(public_path('temp02/css/fonts.css')) }}">
