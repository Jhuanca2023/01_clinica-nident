@php extract(\App\Support\LiamPageData::auth('login')); @endphp
@extends('web.base_shop')
@section('title', 'Iniciar Sesión | Valento')
@section('head_page')
@include('web.partials.liam.auth_styles')
@endsection
@section('content')
@include('web.partials.liam.header')

{{-- Diseño: temp02/login.html (panel login) --}}
<main class="auth-page">
    <div class="container">
        <div class="auth-card">

            <!-- Tabs -->
            <div class="auth-tabs">
                <a href="{{ url('/login') }}" class="auth-tab active">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="auth-tab">Crear Cuenta</a>
            </div>

            <!-- ══ PANEL LOGIN ══ -->
            <div class="auth-panel active" id="panel-login">

                <div class="auth-logo">
                    <img src="{{ asset('imagenes/logo-principal.png') }}" alt="Valento">
                    <span>Semplicemente Valento</span>
                </div>

                <p class="auth-panel-title">Bienvenido de vuelta</p>

                @if(session('success'))
                <div class="auth-success show">
                    <i class="fa fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if ($errors->any())
                <div class="auth-error show" id="login-error">
                    <i class="fa fa-exclamation-circle"></i>
                    <span id="login-error-msg">{{ $errors->first() }}</span>
                </div>
                @endif

                <form method="POST" action="{{ url('/login') }}" id="login-form">
                    @csrf
                    <div class="auth-field">
                        <label class="auth-label" for="login-email">Correo electrónico</label>
                        <div class="auth-input-wrap">
                            <input type="email" id="login-email" name="email" class="auth-input" value="{{ old('email') }}" placeholder="correo@ejemplo.com" autocomplete="email" required autofocus>
                            <i class="fa fa-envelope-o auth-input-icon"></i>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label class="auth-label" for="login-pass">Contraseña</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="login-pass" name="password" class="auth-input" placeholder="••••••••" autocomplete="current-password" required>
                            <i class="fa fa-eye auth-input-icon" id="toggle-login-pass"></i>
                        </div>
                    </div>
                    <div class="auth-row-extra">
                        <label class="auth-check">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordarme
                        </label>
                        @if (Route::has('forget.password.get'))
                        <a href="{{ route('forget.password.get') }}" class="auth-forgot">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>
                    <button type="submit" class="auth-btn" id="login-btn">
                        <i class="fa fa-sign-in"></i> &nbsp;Ingresar
                    </button>
                </form>

                @if(Route::has('auth.google'))
                <div class="auth-or"><span>o continúa con</span></div>
                <div class="auth-social">
                    <a href="{{ route('auth.google') }}" class="auth-social-btn google"><i class="fa fa-google"></i> Google</a>
                </div>
                @endif

                <div class="auth-footer">
                    ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate gratis</a>
                </div>
            </div>

        </div>
    </div>
</main>

@include('web.partials.liam.footer')
@endsection
@push('scripts')
<script>
document.getElementById('toggle-login-pass')?.addEventListener('click', function () {
    const input = document.getElementById('login-pass');
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    this.classList.toggle('fa-eye', !isPass);
    this.classList.toggle('fa-eye-slash', isPass);
});
</script>
@endpush
