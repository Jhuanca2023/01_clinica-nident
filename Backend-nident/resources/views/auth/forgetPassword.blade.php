@extends('web.base')

@section('head_page')
<style>
/* ========================================
   FORGOT PASSWORD - DISEÑO MINIMARKET PROFESIONAL
   ======================================== */

:root {
    --primary: #922b21;
    --primary-dark: #a82024;
    --primary-light: #e04347;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --text-dark: #2c2c2c;
    --text-medium: #555555;
    --text-light: #888888;
    --bg-light: #f8f9fa;
    --bg-white: #ffffff;
    --border-color: #e8e8e8;
}

.forgot-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #fdf5f5 0%, #fff 50%, #fdf5f5 100%);
    padding: 40px 20px;
    position: relative;
    overflow: hidden;
}

.forgot-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 10% 20%, rgba(204, 41, 46, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 90% 80%, rgba(204, 41, 46, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(255, 193, 7, 0.02) 0%, transparent 30%);
    pointer-events: none;
}

/* Iconos flotantes decorativos */
.floating-icons {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}

.floating-icon {
    position: absolute;
    color: var(--primary);
    opacity: 0.06;
    font-size: 60px;
}

.floating-icon:nth-child(1) { top: 10%; left: 5%; animation: float1 8s ease-in-out infinite; }
.floating-icon:nth-child(2) { top: 70%; left: 10%; animation: float2 10s ease-in-out infinite; font-size: 45px; }
.floating-icon:nth-child(3) { top: 20%; right: 8%; animation: float3 9s ease-in-out infinite; font-size: 50px; }
.floating-icon:nth-child(4) { top: 80%; right: 5%; animation: float4 7s ease-in-out infinite; }
.floating-icon:nth-child(5) { top: 50%; left: 3%; animation: float5 11s ease-in-out infinite; font-size: 40px; }
.floating-icon:nth-child(6) { top: 30%; right: 3%; animation: float6 8s ease-in-out infinite; font-size: 35px; }

@keyframes float1 { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(10deg); } }
@keyframes float2 { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-15px) rotate(-8deg); } }
@keyframes float3 { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-25px) rotate(15deg); } }
@keyframes float4 { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-18px) rotate(-12deg); } }
@keyframes float5 { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-22px) rotate(8deg); } }
@keyframes float6 { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-12px) rotate(-5deg); } }

/* Contenedor principal */
.forgot-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
}

/* Tarjeta principal */
.forgot-card {
    background: var(--bg-white);
    border-radius: 24px;
    box-shadow:
        0 20px 60px rgba(204, 41, 46, 0.1),
        0 8px 24px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    border: 1px solid rgba(204, 41, 46, 0.08);
}

/* Header de la tarjeta */
.forgot-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    padding: 40px 40px 50px;
    text-align: center;
    position: relative;
}

.forgot-header::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 80px;
    background: var(--bg-white);
    border-radius: 50%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.logo-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.logo-icon-wrapper {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.logo-icon-wrapper i {
    font-size: 32px;
    color: white;
}

.logo-text {
    color: white;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.logo-version {
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
    font-weight: 500;
}

/* Avatar del icono */
.icon-avatar {
    position: absolute;
    bottom: -35px;
    left: 50%;
    transform: translateX(-50%);
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(204, 41, 46, 0.3);
    border: 4px solid white;
    z-index: 2;
}

.icon-avatar i {
    font-size: 28px;
    color: white;
}

/* Cuerpo del formulario */
.forgot-body {
    padding: 60px 40px 40px;
}

.welcome-text {
    text-align: center;
    margin-bottom: 30px;
}

.welcome-text h2 {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0 0 8px 0;
}

.welcome-text p {
    font-size: 15px;
    color: var(--text-light);
    margin: 0;
    line-height: 1.5;
}

/* Ilustración informativa */
.info-box {
    background: linear-gradient(135deg, rgba(204, 41, 46, 0.05) 0%, rgba(255, 193, 7, 0.03) 100%);
    border: 1px solid rgba(204, 41, 46, 0.1);
    border-radius: 16px;
    padding: 18px 20px;
    margin-bottom: 28px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.info-box i {
    font-size: 22px;
    color: var(--primary);
    margin-top: 2px;
    flex-shrink: 0;
}

.info-box span {
    font-size: 14px;
    color: var(--text-medium);
    line-height: 1.5;
}

/* Alertas */
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.alert-success {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%);
    border: 1px solid rgba(40, 167, 69, 0.2);
    color: #1e7e34;
}

.alert-danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
    border: 1px solid rgba(220, 53, 69, 0.2);
    color: #c82333;
}

/* Grupos de input */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    font-size: 18px;
    transition: color 0.3s ease;
}

.input-wrapper input {
    width: 100%;
    padding: 16px 16px 16px 50px;
    font-size: 15px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: var(--bg-light);
    color: var(--text-dark);
    transition: all 0.3s ease;
}

.input-wrapper input:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--bg-white);
    box-shadow: 0 0 0 4px rgba(204, 41, 46, 0.1);
}

.input-wrapper:focus-within i {
    color: var(--primary);
}

.input-wrapper input::placeholder {
    color: var(--text-light);
}

.input-error {
    font-size: 13px;
    color: var(--danger);
    margin-top: 6px;
    display: block;
}

/* Botón principal */
.btn-forgot {
    width: 100%;
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 16px rgba(204, 41, 46, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
    margin-top: 8px;
}

.btn-forgot::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-forgot:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(204, 41, 46, 0.4);
}

.btn-forgot:hover::before {
    left: 100%;
}

.btn-forgot:active {
    transform: translateY(0);
}

/* Link de regreso */
.back-link {
    text-align: center;
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color);
}

.back-link a {
    color: var(--primary);
    font-weight: 700;
    text-decoration: none;
    font-size: 15px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.back-link a:hover {
    color: var(--primary-dark);
    gap: 12px;
}

.back-link a i {
    font-size: 14px;
    transition: transform 0.3s ease;
}

.back-link a:hover i {
    transform: translateX(-4px);
}

/* Footer de la tarjeta */
.forgot-footer {
    background: var(--bg-light);
    padding: 20px 40px;
    text-align: center;
    border-top: 1px solid var(--border-color);
}

.forgot-footer p {
    font-size: 13px;
    color: var(--text-light);
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.forgot-footer i {
    color: var(--primary);
}

/* Responsive */
@media (max-width: 576px) {
    .forgot-page {
        padding: 20px 16px;
    }

    .forgot-header {
        padding: 30px 24px 45px;
    }

    .logo-icon-wrapper {
        width: 60px;
        height: 60px;
    }

    .logo-icon-wrapper i {
        font-size: 26px;
    }

    .logo-text {
        font-size: 20px;
    }

    .icon-avatar {
        width: 60px;
        height: 60px;
        bottom: -30px;
    }

    .icon-avatar i {
        font-size: 24px;
    }

    .forgot-body {
        padding: 50px 24px 30px;
    }

    .welcome-text h2 {
        font-size: 20px;
    }

    .input-wrapper input {
        padding: 14px 14px 14px 46px;
    }

    .forgot-footer {
        padding: 16px 24px;
    }

    .floating-icons {
        display: none;
    }
}
</style>
@endsection

@section('content')
<div class="forgot-page">
    <!-- Iconos flotantes decorativos -->
    <div class="floating-icons">
        <i class="floating-icon fas fa-shopping-cart"></i>
        <i class="floating-icon fas fa-apple-alt"></i>
        <i class="floating-icon fas fa-shopping-basket"></i>
        <i class="floating-icon fas fa-carrot"></i>
        <i class="floating-icon fas fa-lemon"></i>
        <i class="floating-icon fas fa-pepper-hot"></i>
    </div>

    <div class="forgot-container">
        <div class="forgot-card">
            <!-- Header -->
            <div class="forgot-header">
                <div class="logo-container">
                    <div class="logo-icon-wrapper">
                        <i class="fas fa-store"></i>
                    </div>
                    <span class="logo-text">{{ config('app.name') }}</span>
                    <span class="logo-version">v{{ config('app.version') }}</span>
                </div>
                <div class="icon-avatar">
                    <i class="fas fa-key"></i>
                </div>
            </div>

            <!-- Cuerpo -->
            <div class="forgot-body">
                <div class="welcome-text">
                    <h2>¿Olvidaste tu contraseña?</h2>
                    <p>No te preocupes, te enviaremos un enlace para restablecerla</p>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <span>Ingresa el correo electrónico asociado a tu cuenta y recibirás un enlace seguro para crear una nueva contraseña.</span>
                </div>

                @if (Session::has('message'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ Session::get('message') }}</span>
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                <form action="{{ route('forget.password.post') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="email_address">Correo Electrónico</label>
                        <div class="input-wrapper">
                            <input
                                type="email"
                                id="email_address"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="ejemplo@correo.com"
                                required
                                autofocus
                                autocomplete="email"
                            >
                            <i class="fas fa-envelope"></i>
                        </div>
                        @if ($errors->has('email'))
                        <span class="input-error">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <button type="submit" class="btn-forgot">
                        <i class="fas fa-paper-plane"></i>
                        <span>Enviar Enlace de Recuperación</span>
                    </button>

                    <div class="back-link">
                        <a href="{{ route('login') }}">
                            <i class="fas fa-arrow-left"></i>
                            Volver al Inicio de Sesión
                        </a>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="forgot-footer">
                <p>
                    <i class="fas fa-shield-alt"></i>
                    <span>Tu información está protegida y segura</span>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
