<?php

namespace App\Services\Auth;

use App\Models\SeguridadAuthLoginLog;
use App\Models\SeguridadAuthOauthSesion;
use App\Models\SeguridadAuthProveedor;
use App\Models\SeguridadUsuarioOauth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthOAuthService
{
    public function __construct(
        private readonly AuthProveedorConfigService $configService,
        private readonly AuthTokenService $tokenService
    ) {
    }

    public function iniciar(string $proveedor, ?string $ip = null, ?string $loginHint = null): array
    {
        $prov = $this->assertProveedorHabilitado($proveedor);
        $cfg = $this->configService->mapaConfig($proveedor);

        $state = Str::random(40);
        $verifier = Str::random(64);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        SeguridadAuthOauthSesion::create([
            'state' => $state,
            'code_verifier' => $challenge ? $verifier : $verifier,
            'proveedor' => $proveedor,
            'ip' => $ip,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $redirectUri = $cfg['REDIRECT_URI'] ?? $this->defaultRedirectUri($proveedor);
        $clientId = $cfg['CLIENT_ID'] ?? '';

        if ($proveedor === 'microsoft') {
            $tenant = $cfg['TENANT_ID'] ?? 'common';
            $url = 'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/authorize?' . http_build_query([
                'client_id' => $clientId,
                'response_type' => 'code',
                'redirect_uri' => $redirectUri,
                'response_mode' => 'query',
                'scope' => 'openid profile email User.Read',
                'state' => $state,
                'code_challenge' => $challenge,
                'code_challenge_method' => 'S256',
            ]);
        } elseif ($proveedor === 'google') {
            $googleParams = [
                'client_id' => $clientId,
                'response_type' => 'code',
                'redirect_uri' => $redirectUri,
                'scope' => 'openid email profile',
                'state' => $state,
                'access_type' => 'offline',
                'code_challenge' => $challenge,
                'code_challenge_method' => 'S256',
            ];
            if ($loginHint) {
                $googleParams['login_hint'] = $loginHint;
            } else {
                $googleParams['prompt'] = 'select_account';
            }
            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($googleParams);
        } else {
            throw new \InvalidArgumentException('Proveedor OAuth no soportado.');
        }

        return ['authorization_url' => $url, 'state' => $state];
    }

    public function callback(string $proveedor, string $code, string $state, ?string $ip = null): string
    {
        $sesion = SeguridadAuthOauthSesion::where('state', $state)
            ->where('proveedor', $proveedor)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$sesion) {
            SeguridadAuthLoginLog::registrar($proveedor, false, null, null, $ip, 'State inválido o expirado');
            return $this->frontendErrorUrl('Sesión OAuth inválida o expirada.');
        }

        $cfg = $this->configService->mapaConfig($proveedor);
        $redirectUri = $cfg['REDIRECT_URI'] ?? $this->defaultRedirectUri($proveedor);
        $tokenData = $this->exchangeCodeForTokens($proveedor, $code, $redirectUri, $cfg, $sesion->code_verifier);
        $profile = $this->fetchUserProfile($proveedor, $tokenData['access_token'] ?? '');

        $user = $this->resolveOrCreateUser($proveedor, $profile);
        if (!$user) {
            SeguridadAuthLoginLog::registrar($proveedor, false, null, $profile['email'] ?? null, $ip, 'Usuario no vinculado');
            return $this->frontendErrorUrl('No se pudo vincular la cuenta.');
        }

        try {
            app(\App\Services\Seguridad\UsuarioAuthConfigService::class)->assertMetodoPermitido($user, $proveedor);
        } catch (\Throwable $e) {
            SeguridadAuthLoginLog::registrar($proveedor, false, $user->id, $user->email, $ip, $e->getMessage());
            return $this->frontendErrorUrl($e->getMessage());
        }

        $exchangeCode = Str::random(48);
        $sesion->update([
            'id_usuario' => $user->id,
            'exchange_code' => $exchangeCode,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        SeguridadAuthLoginLog::registrar($proveedor, true, $user->id, $user->email, $ip);

        return $this->frontendSuccessUrl($exchangeCode);
    }

    public function intercambiarCodigo(string $exchangeCode, ?string $ip = null, ?string $trustedDeviceToken = null): array
    {
        $sesion = SeguridadAuthOauthSesion::where('exchange_code', $exchangeCode)
            ->where('expires_at', '>', Carbon::now())
            ->whereNotNull('id_usuario')
            ->first();

        if (!$sesion) {
            $reciente = SeguridadAuthOauthSesion::where('exchange_code', 'USED:' . $exchangeCode)
                ->where('expires_at', '>', Carbon::now()->subMinutes(2))
                ->whereNotNull('id_usuario')
                ->first();
            if ($reciente) {
                $user = User::find($reciente->id_usuario);
                if ($user) {
                    return $this->tokenService->emitirRespuestaLogin($user, true, $ip, $trustedDeviceToken);
                }
            }
            throw new \RuntimeException('Código de intercambio inválido o expirado.');
        }

        $user = User::find($sesion->id_usuario);
        if (!$user) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        $sesion->update(['exchange_code' => 'USED:' . $exchangeCode]);

        return $this->tokenService->emitirRespuestaLogin($user, true, $ip, $trustedDeviceToken);
    }

    private function assertProveedorHabilitado(string $codigo): SeguridadAuthProveedor
    {
        if ($codigo === 'local') {
            throw new \InvalidArgumentException('El proveedor local no usa OAuth.');
        }
        $prov = SeguridadAuthProveedor::porCodigo($codigo);
        if (!$prov || !$prov->is_habilitado) {
            throw new \RuntimeException("El proveedor «{$codigo}» no está habilitado.");
        }
        return $prov;
    }

    private function defaultRedirectUri(string $proveedor): string
    {
        return rtrim(config('app.url'), '/') . '/api/auth/oauth/callback/' . $proveedor;
    }

    private function frontendBase(): string
    {
        $base = env('FRONTEND_URL');
        if (!$base) {
            // $frond = rtrim((string) env('APP_URL_FROND', 'http://localhost:8000'), '/');
            $frond = rtrim((string) env('APP_URL_FROND', 'https://rossety.pe'), '/');
            $path = rtrim((string) env('FRONTEND_OAUTH_PATH', '/admin'), '/');
            $base = str_contains($frond, '/admin') ? $frond : $frond . $path;
        }
        if (!str_contains($base, '/admin') && env('FRONTEND_OAUTH_PATH', '/admin') !== '') {
            $base = rtrim($base, '/') . rtrim(env('FRONTEND_OAUTH_PATH', '/admin'), '/');
        }
        return rtrim($base, '/');
    }

    private function frontendSuccessUrl(string $exchangeCode): string
    {
        return $this->frontendBase() . '/session/oauth-callback?code=' . urlencode($exchangeCode);
    }

    private function frontendErrorUrl(string $message): string
    {
        return $this->frontendBase() . '/session/oauth-callback?error=' . urlencode($message);
    }

    private function exchangeCodeForTokens(string $proveedor, string $code, string $redirectUri, array $cfg, ?string $codeVerifier): array
    {
        $clientId = $cfg['CLIENT_ID'] ?? '';
        $clientSecret = $cfg['CLIENT_SECRET'] ?? '';

        if ($proveedor === 'microsoft') {
            $tenant = $cfg['TENANT_ID'] ?? 'common';
            $tokenUrl = 'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/token';
        } else {
            $tokenUrl = 'https://oauth2.googleapis.com/token';
        }

        $body = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'code_verifier' => $codeVerifier,
        ];

        $response = Http::asForm()->post($tokenUrl, $body);
        if (!$response->successful()) {
            throw new \RuntimeException('Error al obtener token OAuth: ' . $response->body());
        }
        return $response->json();
    }

    private function fetchUserProfile(string $proveedor, string $accessToken): array
    {
        if ($proveedor === 'microsoft') {
            $response = Http::withToken($accessToken)->get('https://graph.microsoft.com/v1.0/me');
            if (!$response->successful()) {
                throw new \RuntimeException('No se pudo leer el perfil de Microsoft.');
            }
            $data = $response->json();
            return [
                'id' => $data['id'] ?? null,
                'email' => $data['mail'] ?? $data['userPrincipalName'] ?? null,
                'name' => $data['displayName'] ?? 'Usuario Microsoft',
                'avatar' => null,
            ];
        }

        $response = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        if (!$response->successful()) {
            throw new \RuntimeException('No se pudo leer el perfil de Google.');
        }
        $data = $response->json();
        return [
            'id' => $data['sub'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? 'Usuario Google',
            'avatar' => $data['picture'] ?? null,
        ];
    }

    /** URLs de Google/Microsoft pueden superar VARCHAR(255); TEXT en BD + recorte defensivo. */
    private function normalizeAvatar(?string $avatar): ?string
    {
        if ($avatar === null || $avatar === '') {
            return null;
        }
        $avatar = trim($avatar);

        return strlen($avatar) > 65000 ? substr($avatar, 0, 65000) : $avatar;
    }

    private function resolveOrCreateUser(string $proveedor, array $profile): ?User
    {
        $providerId = $profile['id'] ?? null;
        $email = $profile['email'] ?? null;
        if (!$providerId || !$email) {
            return null;
        }

        $profile['avatar'] = $this->normalizeAvatar($profile['avatar'] ?? null);

        $link = SeguridadUsuarioOauth::where('proveedor', $proveedor)
            ->where('provider_user_id', $providerId)
            ->first();

        if ($link) {
            $user = User::find($link->id_usuario);
            if ($user && $profile['avatar'] && empty($user->avatar)) {
                $user->update(['avatar' => $profile['avatar']]);
            }
            return $user;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            if ($profile['avatar'] && empty($user->avatar)) {
                $user->update(['avatar' => $profile['avatar']]);
            }
            $this->vincularProveedor($user, $proveedor, $profile);
            return $user;
        }

        $userData = [
            'name' => $profile['name'],
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'avatar' => $profile['avatar'],
            'is_email_verified' => 1,
            'email_verified_at' => now(),
        ];
        if ($proveedor === 'google') {
            $userData['google_id'] = $providerId;
        } else {
            $userData['microsoft_id'] = $providerId;
        }
        $user = User::create($userData);

        $this->vincularProveedor($user, $proveedor, $profile);
        return $user;
    }

    private function vincularProveedor(User $user, string $proveedor, array $profile): void
    {
        SeguridadUsuarioOauth::updateOrCreate(
            ['proveedor' => $proveedor, 'provider_user_id' => $profile['id']],
            [
                'id_usuario' => $user->id,
                'email' => $profile['email'],
                'avatar' => $profile['avatar'],
            ]
        );

        if ($proveedor === 'google' && empty($user->google_id)) {
            $user->update(['google_id' => $profile['id']]);
        }
        if ($proveedor === 'microsoft' && empty($user->microsoft_id)) {
            $user->update(['microsoft_id' => $profile['id']]);
        }
    }
}
