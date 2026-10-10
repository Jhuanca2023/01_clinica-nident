@php extract(\App\Support\LiamPageData::auth('register')); @endphp
@extends('web.base_shop')
@section('title', 'Crear Cuenta | Valento')
@section('head_page')
@include('web.partials.liam.auth_styles')
@endsection
@section('content')
@include('web.partials.liam.header')

{{-- Diseño: temp02/login.html (panel registro) --}}
<main class="auth-page">
    <div class="container">
        <div class="auth-card">

            <!-- Tabs -->
            <div class="auth-tabs">
                <a href="{{ url('/login') }}" class="auth-tab">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="auth-tab active">Crear Cuenta</a>
            </div>

            <!-- ══ PANEL REGISTRO ══ -->
            <div class="auth-panel active" id="panel-register">

                <div class="auth-logo">
                    <img src="{{ asset('imagenes/logo-principal.png') }}" alt="Valento">
                    <span>Semplicemente Valento</span>
                </div>

                <p class="auth-panel-title">Crea tu cuenta</p>

                @if ($errors->any())
                <div class="auth-error show" id="reg-error">
                    <i class="fa fa-exclamation-circle"></i>
                    <span id="reg-error-msg">{{ $errors->first() }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" id="reg-form">
                    @csrf
                    <div class="auth-row-2">
                        <div class="auth-field">
                            <label class="auth-label" for="reg-nombre">Nombre</label>
                            <div class="auth-input-wrap">
                                <input type="text" id="reg-nombre" name="nombre" class="auth-input" value="{{ old('nombre') }}" placeholder="Tu nombre" autocomplete="given-name" required>
                                <i class="fa fa-user-o auth-input-icon"></i>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label class="auth-label" for="reg-apellido">Apellido</label>
                            <div class="auth-input-wrap">
                                <input type="text" id="reg-apellido" name="apellido" class="auth-input" value="{{ old('apellido') }}" placeholder="Tu apellido" autocomplete="family-name">
                            </div>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="reg-email">Correo electrónico</label>
                        <div class="auth-input-wrap">
                            <input type="email" id="reg-email" name="email" class="auth-input" value="{{ old('email') }}" placeholder="correo@ejemplo.com" autocomplete="email" required>
                            <i class="fa fa-envelope-o auth-input-icon"></i>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="reg-tel">Teléfono / WhatsApp</label>
                        <div class="auth-input-wrap">
                            <input type="tel" id="reg-tel" name="telefono" class="auth-input" value="{{ old('telefono') }}" placeholder="+51 9XX XXX XXX" autocomplete="tel">
                            <i class="fa fa-phone auth-input-icon"></i>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="reg-pass">Contraseña</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="reg-pass" name="password" class="auth-input" placeholder="Mínimo 8 caracteres" autocomplete="new-password" minlength="8" required>
                            <i class="fa fa-eye auth-input-icon" id="toggle-reg-pass"></i>
                        </div>
                        <div class="auth-strength"><div class="auth-strength-fill" id="strength-bar"></div></div>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="reg-pass2">Confirmar contraseña</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="reg-pass2" name="password_confirmation" class="auth-input" placeholder="Repite tu contraseña" autocomplete="new-password" required>
                            <i class="fa fa-eye auth-input-icon" id="toggle-reg-pass2"></i>
                        </div>
                    </div>
                    <div class="auth-field" style="margin-bottom:22px;">
                        <label class="auth-check">
                            <input type="checkbox" id="reg-terms" name="acepto_terminos" value="1" {{ old('acepto_terminos') ? 'checked' : '' }} required>
                            <span>Acepto los <a href="{{ url('/terminos-condiciones') }}" target="_blank" rel="noopener" style="color:#8C6B2E;">Términos y Condiciones</a> y la <a href="{{ url('/politica-privacidad') }}" target="_blank" rel="noopener" style="color:#8C6B2E;">Política de Privacidad</a></span>
                        </label>
                    </div>
                    <button type="submit" class="auth-btn" id="reg-btn">
                        <i class="fa fa-user-plus"></i> &nbsp;Crear Cuenta
                    </button>
                </form>

                <div class="auth-footer">
                    ¿Ya tienes cuenta? <a href="{{ url('/login') }}">Inicia sesión</a>
                </div>
            </div>

        </div>
    </div>
</main>

@include('web.partials.liam.footer')
@endsection
@push('scripts')
<script>
// Mostrar / ocultar contraseñas
[['toggle-reg-pass', 'reg-pass'], ['toggle-reg-pass2', 'reg-pass2']].forEach(function (par) {
    document.getElementById(par[0])?.addEventListener('click', function () {
        const input = document.getElementById(par[1]);
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        this.classList.toggle('fa-eye', !isPass);
        this.classList.toggle('fa-eye-slash', isPass);
    });
});

// Barra de fuerza de la contraseña (igual que el template)
document.getElementById('reg-pass')?.addEventListener('input', function () {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const colores = ['#e74c3c', '#e67e22', '#f1c40f', '#27ae60'];
    const bar = document.getElementById('strength-bar');
    bar.style.width = (score * 25) + '%';
    bar.style.background = colores[score - 1] || '#eee';
});
</script>
@endpush
