<?php

namespace App\Services\Auth;

use App\Http\Controllers\Api\AuthController;
use App\Models\SeguridadAuth2faChallenge;
use App\Models\SeguridadAuthLoginLog;
use App\Models\SeguridadUsuario2fa;
use App\Models\SeguridadUsuario2faDispositivoConfiable;
use App\Models\User;
use App\Services\Seguridad\UsuarioAuthConfigService;
use Illuminate\Support\Facades\DB;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class AuthTwoFactorService
{
    private const CHALLENGE_MINUTES = 5;
    private const RECOVERY_CODE_COUNT = 8;
    private const TRUSTED_DEVICE_DAYS_DEFAULT = 5;

    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function estaHabilitado(User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;
        $row = SeguridadUsuario2fa::where('id_usuario', $id)->first();

        return $row && $row->is_habilitado && !empty($row->secret_cifrado);
    }

    /** Usuario con al menos un rol marcado con requiere_2fa. */
    public function tieneRolCon2faObligatorio(User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;

        if (!DB::getSchemaBuilder()->hasColumn('seguridad_roles', 'requiere_2fa')) {
            return false;
        }

        return DB::table('seguridad_perfil_users as spu')
            ->join('seguridad_roles_perfil as srp', 'srp.id_perfil', '=', 'spu.id_perfil')
            ->join('seguridad_roles as sr', 'sr.id_roles', '=', 'srp.id_roles')
            ->where('spu.id_usuario', $id)
            ->where('sr.requiere_2fa', true)
            ->where(function ($q) {
                $q->where('sr.Activo', 'S')->orWhereNull('sr.Activo');
            })
            ->exists();
    }

    public function debeConfigurar2faObligatorio(User $user): bool
    {
        $cfg = app(UsuarioAuthConfigService::class);
        if (!$cfg->requiere2faEfectivo($user)) {
            return false;
        }

        return !$this->estaHabilitado($user);
    }

    /** Tras login/OAuth: setup obligatorio, challenge 2FA o JWT. */
    public function respuestaLoginOChallenge(
        User $user,
        bool $remember,
        ?string $ip = null,
        ?string $trustedDeviceToken = null
    ): array {
        if ($this->debeConfigurar2faObligatorio($user)) {
            return $this->crearRespuestaSetupObligatorio($user, $remember, $ip);
        }

        if ($this->estaHabilitado($user)) {
            if ($this->dispositivoConfiableValido($user, $trustedDeviceToken)) {
                SeguridadAuthLoginLog::registrar('2fa_trusted', true, $user->id, $user->email, $ip, 'Dispositivo confiable');

                return app(AuthController::class)->buildLoginResponseForUser($user, $remember);
            }

            return $this->crearRespuestaChallenge($user, $remember, $ip);
        }

        return app(AuthController::class)->buildLoginResponseForUser($user, $remember);
    }

    public function dispositivoConfiableValido(User|int $user, ?string $plainToken): bool
    {
        if (!$plainToken || strlen($plainToken) < 32) {
            return false;
        }

        $id = $user instanceof User ? $user->id : $user;
        $hash = hash('sha256', $plainToken);

        return SeguridadUsuario2faDispositivoConfiable::where('id_usuario', $id)
            ->where('token_hash', $hash)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    public function revocarDispositivosConfiables(int $idUsuario): void
    {
        SeguridadUsuario2faDispositivoConfiable::where('id_usuario', $idUsuario)->delete();
    }

    private function crearDispositivoConfiable(User $user, ?string $ip = null): string
    {
        $days = (int) env('AUTH_2FA_TRUSTED_DEVICE_DAYS', self::TRUSTED_DEVICE_DAYS_DEFAULT);
        $days = max(1, min(90, $days));

        $plain = Str::random(64);
        SeguridadUsuario2faDispositivoConfiable::create([
            'id_usuario' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => Carbon::now()->addDays($days),
            'ip' => $ip,
            'user_agent' => substr((string) request()->userAgent(), 0, 500) ?: null,
        ]);

        return $plain;
    }

    /** Primer acceso: debe escanear QR y activar 2FA antes de recibir JWT. */
    public function crearRespuestaSetupObligatorio(User $user, bool $remember, ?string $ip = null): array
    {
        $token = Str::random(64);
        $this->invalidarChallengesActivos($user->id);

        SeguridadAuth2faChallenge::create([
            'challenge_token' => hash('sha256', $token),
            'tipo' => SeguridadAuth2faChallenge::TIPO_SETUP_MANDATORY,
            'id_usuario' => $user->id,
            'remember' => $remember,
            'ip' => $ip,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        return [
            'status' => 200,
            'requires_2fa_setup' => true,
            'setup_token' => $token,
            'expires_in' => 15 * 60,
            'message' => 'Su rol exige verificación en dos pasos. Configure la app autenticadora antes de continuar.',
        ];
    }

    /** Inicia QR para enrolamiento obligatorio (sin JWT). */
    public function iniciarSetupObligatorio(string $setupToken): array
    {
        $challenge = $this->resolverChallengePorTipo($setupToken, SeguridadAuth2faChallenge::TIPO_SETUP_MANDATORY);
        if (!$challenge) {
            throw new \RuntimeException('Sesión de configuración inválida o expirada.');
        }

        $user = User::find($challenge->id_usuario);
        if (!$user) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        if (!$this->tieneRolCon2faObligatorio($user)) {
            throw new \RuntimeException('Este usuario no requiere configuración obligatoria de 2FA.');
        }

        $setup = $this->iniciarSetup($user);

        return array_merge($setup, [
            'email' => $user->email,
            'mandatory' => true,
        ]);
    }

    /** Confirma 2FA obligatorio y emite JWT + códigos de recuperación. */
    public function confirmarSetupObligatorio(string $setupToken, string $code, ?string $ip = null): array
    {
        $challenge = $this->resolverChallengePorTipo($setupToken, SeguridadAuth2faChallenge::TIPO_SETUP_MANDATORY);
        if (!$challenge) {
            throw new \RuntimeException('Sesión de configuración inválida o expirada.');
        }

        $user = User::find($challenge->id_usuario);
        if (!$user) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        $confirm = $this->confirmarSetup($user, $code);
        $challenge->delete();

        SeguridadAuthLoginLog::registrar('2fa_setup', true, $user->id, $user->email, $ip, 'Enrolamiento obligatorio por rol');

        $login = app(AuthController::class)->buildLoginResponseForUser($user, (bool) $challenge->remember);

        return array_merge($login, [
            'recovery_codes' => $confirm['recovery_codes'] ?? [],
            'setup_message' => $confirm['message'] ?? null,
        ]);
    }

    public function crearRespuestaChallenge(User $user, bool $remember, ?string $ip = null): array
    {
        $token = Str::random(64);
        $this->invalidarChallengesActivos($user->id);

        SeguridadAuth2faChallenge::create([
            'challenge_token' => hash('sha256', $token),
            'tipo' => SeguridadAuth2faChallenge::TIPO_VERIFY,
            'id_usuario' => $user->id,
            'remember' => $remember,
            'ip' => $ip,
            'expires_at' => Carbon::now()->addMinutes(self::CHALLENGE_MINUTES),
        ]);

        return [
            'status' => 200,
            'requires_2fa' => true,
            'challenge_token' => $token,
            'expires_in' => self::CHALLENGE_MINUTES * 60,
            'message' => 'Ingrese el código de su aplicación de autenticación.',
        ];
    }

    /** Verifica TOTP o código de recuperación y emite JWT. */
    public function verificarChallenge(
        string $challengeToken,
        string $code,
        ?string $ip = null,
        bool $trustDevice = false
    ): array {
        $challenge = $this->resolverChallenge($challengeToken);
        if (!$challenge) {
            throw new \RuntimeException('Sesión de verificación inválida o expirada.');
        }

        $user = User::find($challenge->id_usuario);
        if (!$user) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        $row = SeguridadUsuario2fa::where('id_usuario', $user->id)->first();
        if (!$row || !$row->is_habilitado) {
            throw new \RuntimeException('La verificación en dos pasos no está activa para este usuario.');
        }

        $code = preg_replace('/\s+/', '', $code);
        $valid = $this->verificarCodigo($row, $code);

        if (!$valid) {
            SeguridadAuthLoginLog::registrar('2fa', false, $user->id, $user->email, $ip, 'Código incorrecto');
            throw new \RuntimeException('Código incorrecto. Intente de nuevo.');
        }

        $challenge->delete();
        SeguridadAuthLoginLog::registrar('2fa', true, $user->id, $user->email, $ip);

        $login = app(AuthController::class)->buildLoginResponseForUser($user, (bool) $challenge->remember);

        if ($trustDevice) {
            $login['trusted_device_token'] = $this->crearDispositivoConfiable($user, $ip);
            $login['trusted_device_days'] = (int) env('AUTH_2FA_TRUSTED_DEVICE_DAYS', self::TRUSTED_DEVICE_DAYS_DEFAULT);
        }

        return $login;
    }

    public function estado(User $user): array
    {
        $row = SeguridadUsuario2fa::where('id_usuario', $user->id)->first();
        $cfgSvc = app(UsuarioAuthConfigService::class);
        $mandatory = $cfgSvc->requiere2faEfectivo($user);
        $puedeVoluntario = $cfgSvc->puedeConfigurar2faEnPerfil($user);

        return [
            'enabled' => $row && $row->is_habilitado,
            'pending_setup' => $row && !$row->is_habilitado && !empty($row->secret_cifrado),
            'habilitado_at' => $row?->habilitado_at?->toIso8601String(),
            'mandatory' => $mandatory,
            'can_disable' => !$mandatory,
            'can_self_configure' => $puedeVoluntario,
            'auth_config' => $cfgSvc->obtener((int) $user->id),
        ];
    }

    /** Inicia enrolamiento: genera secreto y QR (aún no activo hasta confirmar). */
    public function iniciarSetup(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $issuer = config('app.name', 'Valento');
        $label = $issuer . ':' . $user->email;
        $otpauth = $this->google2fa->getQRCodeUrl($issuer, $user->email, $secret);

        SeguridadUsuario2fa::updateOrCreate(
            ['id_usuario' => $user->id],
            [
                'secret_cifrado' => Crypt::encryptString($secret),
                'is_habilitado' => false,
                'habilitado_at' => null,
                'recovery_codes_hash' => null,
            ]
        );

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauth,
            'qr_code' => $this->generarQrDataUri($otpauth),
        ];
    }

    /** Confirma enrolamiento con un código TOTP válido; devuelve códigos de recuperación (una sola vez). */
    public function confirmarSetup(User $user, string $code): array
    {
        $row = SeguridadUsuario2fa::where('id_usuario', $user->id)->first();
        if (!$row || empty($row->secret_cifrado)) {
            throw new \RuntimeException('Primero inicie la configuración de 2FA.');
        }
        if ($row->is_habilitado) {
            throw new \RuntimeException('La verificación en dos pasos ya está activa.');
        }

        $code = preg_replace('/\s+/', '', $code);
        if (!$this->verificarTotp($row->secret_cifrado, $code)) {
            throw new \RuntimeException('Código incorrecto. Escanee el QR e ingrese el código actual de la app.');
        }

        $plainCodes = $this->generarRecoveryCodes();
        $hashes = array_map(fn (string $c) => password_hash($c, PASSWORD_BCRYPT), $plainCodes);

        $row->update([
            'is_habilitado' => true,
            'habilitado_at' => Carbon::now(),
            'recovery_codes_hash' => json_encode($hashes),
        ]);

        return [
            'enabled' => true,
            'recovery_codes' => $plainCodes,
            'message' => 'Guarde los códigos de recuperación en un lugar seguro. No se volverán a mostrar.',
        ];
    }

    /** Resumen para el panel de administración de usuarios. */
    public function resumenAdministrador(int $idUsuario): array
    {
        $user = User::find($idUsuario);
        $row = SeguridadUsuario2fa::where('id_usuario', $idUsuario)->first();
        $cfgSvc = app(UsuarioAuthConfigService::class);

        return [
            'activo' => $row && $row->is_habilitado,
            'pendiente_configuracion' => $row && !$row->is_habilitado && !empty($row->secret_cifrado),
            'tiene_registro' => (bool) $row,
            'requiere_2fa_efectivo' => $user ? $cfgSvc->requiere2faEfectivo($user) : false,
            'rol_2fa_obligatorio' => $user ? $this->tieneRolCon2faObligatorio($user) : false,
            'habilitado_at' => $row?->habilitado_at?->toIso8601String(),
        ];
    }

    /**
     * Elimina secreto, códigos de recuperación y desafíos pendientes.
     * El usuario podrá iniciar sesión y, si la política lo exige, volverá a configurar el QR.
     */
    public function resetearPorAdministrador(int $idUsuario, ?int $adminId = null): array
    {
        $user = User::find($idUsuario);
        if (!$user) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        $row = SeguridadUsuario2fa::where('id_usuario', $idUsuario)->first();
        if (!$row) {
            throw new \RuntimeException('Este usuario no tiene verificación en dos pasos configurada.');
        }

        DB::transaction(function () use ($idUsuario, $row) {
            $row->delete();
            $this->invalidarChallengesActivos($idUsuario);
            $this->revocarDispositivosConfiables($idUsuario);
        });

        $adminEmail = $adminId ? User::find($adminId)?->email : null;
        SeguridadAuthLoginLog::registrar(
            'admin_2fa_reset',
            true,
            $user->id,
            $user->email,
            request()?->ip(),
            $adminEmail ? "Reset 2FA por administrador: {$adminEmail}" : 'Reset 2FA por administrador'
        );

        $debeReconfigurar = app(UsuarioAuthConfigService::class)->requiere2faEfectivo($user)
            || $this->tieneRolCon2faObligatorio($user);

        return [
            'message' => $debeReconfigurar
                ? '2FA restablecido. En el próximo inicio de sesión el usuario deberá escanear un nuevo código QR.'
                : '2FA restablecido. El usuario puede iniciar sesión sin código hasta que active 2FA de nuevo en su perfil.',
            'debe_reconfigurar_en_login' => $debeReconfigurar,
        ];
    }

    public function deshabilitar(User $user, string $code): void
    {
        if (app(UsuarioAuthConfigService::class)->requiere2faEfectivo($user)) {
            throw new \RuntimeException('La política de acceso exige tener la verificación en dos pasos activa. No puede desactivarla.');
        }

        $row = SeguridadUsuario2fa::where('id_usuario', $user->id)->first();
        if (!$row || !$row->is_habilitado) {
            throw new \RuntimeException('La verificación en dos pasos no está activa.');
        }

        $code = preg_replace('/\s+/', '', $code);
        if (!$this->verificarCodigo($row, $code, true)) {
            throw new \RuntimeException('Código incorrecto.');
        }

        $row->update([
            'secret_cifrado' => null,
            'is_habilitado' => false,
            'habilitado_at' => null,
            'recovery_codes_hash' => null,
        ]);

        SeguridadAuth2faChallenge::where('id_usuario', $user->id)->delete();
    }

    public function regenerarRecoveryCodes(User $user, string $code): array
    {
        $row = SeguridadUsuario2fa::where('id_usuario', $user->id)->first();
        if (!$row || !$row->is_habilitado) {
            throw new \RuntimeException('La verificación en dos pasos no está activa.');
        }

        $code = preg_replace('/\s+/', '', $code);
        if (!$this->verificarTotp($row->secret_cifrado, $code)) {
            throw new \RuntimeException('Código TOTP incorrecto.');
        }

        $plainCodes = $this->generarRecoveryCodes();
        $hashes = array_map(fn (string $c) => password_hash($c, PASSWORD_BCRYPT), $plainCodes);
        $row->update(['recovery_codes_hash' => json_encode($hashes)]);

        return [
            'recovery_codes' => $plainCodes,
            'message' => 'Nuevos códigos generados. Los anteriores ya no son válidos.',
        ];
    }

    private function invalidarChallengesActivos(int $userId): void
    {
        SeguridadAuth2faChallenge::where('id_usuario', $userId)->delete();
    }

    private function resolverChallenge(string $plainToken): ?SeguridadAuth2faChallenge
    {
        return $this->resolverChallengePorTipo($plainToken, SeguridadAuth2faChallenge::TIPO_VERIFY);
    }

    private function resolverChallengePorTipo(string $plainToken, string $tipo): ?SeguridadAuth2faChallenge
    {
        $hash = hash('sha256', $plainToken);

        return SeguridadAuth2faChallenge::where('challenge_token', $hash)
            ->where('tipo', $tipo)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    private function verificarCodigo(SeguridadUsuario2fa $row, string $code, bool $totpOnly = false): bool
    {
        if (strlen($code) === 6 && ctype_digit($code)) {
            return $this->verificarTotp($row->secret_cifrado, $code);
        }

        if ($totpOnly) {
            return false;
        }

        return $this->consumirRecoveryCode($row, $code);
    }

    private function verificarTotp(?string $encryptedSecret, string $code): bool
    {
        if (!$encryptedSecret) {
            return false;
        }
        try {
            $secret = Crypt::decryptString($encryptedSecret);
        } catch (\Throwable) {
            return false;
        }

        return $this->google2fa->verifyKey($secret, $code, 1);
    }

    private function consumirRecoveryCode(SeguridadUsuario2fa $row, string $code): bool
    {
        $normalized = strtoupper(preg_replace('/[\s-]/', '', $code));
        if (strlen($normalized) < 8) {
            return false;
        }

        $candidates = [
            substr($normalized, 0, 4) . '-' . substr($normalized, 4, 4),
            $normalized,
        ];

        $hashes = json_decode($row->recovery_codes_hash ?? '[]', true);
        if (!is_array($hashes) || $hashes === []) {
            return false;
        }

        foreach ($hashes as $i => $hash) {
            foreach ($candidates as $candidate) {
                if (password_verify($candidate, $hash)) {
                    unset($hashes[$i]);
                    $row->update(['recovery_codes_hash' => json_encode(array_values($hashes))]);
                    return true;
                }
            }
        }

        return false;
    }

    private function generarRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $part1 = strtoupper(Str::random(4));
            $part2 = strtoupper(Str::random(4));
            $codes[] = $part1 . '-' . $part2;
        }

        return $codes;
    }

    private function generarQrDataUri(string $otpauthUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(220),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($otpauthUrl);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
