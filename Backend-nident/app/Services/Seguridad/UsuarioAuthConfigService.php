<?php

namespace App\Services\Seguridad;

use App\Models\SeguridadAuthProveedor;
use App\Models\SeguridadUsuarioAuthConfig;
use App\Models\User;
use App\Services\Auth\AuthProveedorConfigService;
use Illuminate\Support\Facades\DB;

class UsuarioAuthConfigService
{
    public function defaults(): array
    {
        return [
            'permitir_local' => true,
            'permitir_google' => true,
            'permitir_microsoft' => true,
            'requiere_2fa' => false,
            'permite_2fa_voluntario' => true,
            'metodo_predeterminado' => null,
        ];
    }

    public function obtener(int $idUsuario): array
    {
        $row = SeguridadUsuarioAuthConfig::where('id_usuario', $idUsuario)->first();
        if (!$row) {
            return array_merge($this->defaults(), ['id_usuario' => $idUsuario, 'personalizado' => false]);
        }

        return [
            'id_usuario' => $idUsuario,
            'personalizado' => true,
            'permitir_local' => (bool) $row->permitir_local,
            'permitir_google' => (bool) $row->permitir_google,
            'permitir_microsoft' => (bool) $row->permitir_microsoft,
            'requiere_2fa' => (bool) $row->requiere_2fa,
            'permite_2fa_voluntario' => (bool) $row->permite_2fa_voluntario,
            'metodo_predeterminado' => $row->metodo_predeterminado,
        ];
    }

    public function guardar(int $idUsuario, array $data): array
    {
        if (
            empty($data['permitir_local'])
            && empty($data['permitir_google'])
            && empty($data['permitir_microsoft'])
        ) {
            throw new \RuntimeException('Debe habilitar al menos un método de inicio de sesión para el usuario.');
        }

        $row = SeguridadUsuarioAuthConfig::updateOrCreate(
            ['id_usuario' => $idUsuario],
            [
                'permitir_local' => !empty($data['permitir_local']),
                'permitir_google' => !empty($data['permitir_google']),
                'permitir_microsoft' => !empty($data['permitir_microsoft']),
                'requiere_2fa' => !empty($data['requiere_2fa']),
                'permite_2fa_voluntario' => array_key_exists('permite_2fa_voluntario', $data)
                    ? !empty($data['permite_2fa_voluntario'])
                    : true,
                'metodo_predeterminado' => $data['metodo_predeterminado'] ?? null,
            ]
        );

        return $this->obtener((int) $row->id_usuario);
    }

    /** Métodos globales del sistema ∩ preferencias del usuario. */
    public function metodosLoginParaUsuario(User $user): array
    {
        $cfg = $this->obtener((int) $user->id);
        $globales = collect(app(AuthProveedorConfigService::class)->metodosLoginPublicos())
            ->keyBy('codigo');

        $out = [];
        foreach (['local', 'google', 'microsoft'] as $codigo) {
            $flag = match ($codigo) {
                'local' => $cfg['permitir_local'],
                'google' => $cfg['permitir_google'],
                'microsoft' => $cfg['permitir_microsoft'],
                default => false,
            };
            $global = $globales->get($codigo);
            if ($flag && $global && !empty($global['is_habilitado'])) {
                $out[] = $global;
            }
        }

        // Sin fallback: si usuario pide solo Local pero Local está deshabilitado a nivel sistema, no mostrar otro método.

        return $out;
    }

    public function metodosLoginPorEmail(string $email): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return [
                'usuario_existe' => false,
                'metodos' => app(AuthProveedorConfigService::class)->metodosLoginPublicos(),
                'requiere_2fa' => false,
            ];
        }

        return [
            'usuario_existe' => true,
            'id_usuario' => $user->id,
            'metodos' => $this->metodosLoginParaUsuario($user),
            'requiere_2fa' => $this->requiere2faEfectivo($user),
            'config' => $this->obtener((int) $user->id),
        ];
    }

    public function requiere2faEfectivo(User $user): bool
    {
        $cfg = $this->obtener((int) $user->id);
        if ($cfg['requiere_2fa']) {
            return true;
        }

        if (!DB::getSchemaBuilder()->hasColumn('seguridad_roles', 'requiere_2fa')) {
            return false;
        }

        return DB::table('seguridad_perfil_users as spu')
            ->join('seguridad_roles_perfil as srp', 'srp.id_perfil', '=', 'spu.id_perfil')
            ->join('seguridad_roles as sr', 'sr.id_roles', '=', 'srp.id_roles')
            ->where('spu.id_usuario', $user->id)
            ->where('sr.requiere_2fa', true)
            ->exists();
    }

    public function assertMetodoPermitido(User $user, string $metodo): void
    {
        $metodo = strtolower($metodo);
        $cfg = $this->obtener((int) $user->id);
        $map = [
            'local' => $cfg['permitir_local'],
            'google' => $cfg['permitir_google'],
            'microsoft' => $cfg['permitir_microsoft'],
        ];
        if (empty($map[$metodo])) {
            throw new \RuntimeException("Este usuario no tiene permitido iniciar sesión con «{$metodo}».");
        }

        $prov = SeguridadAuthProveedor::porCodigo($metodo === 'local' ? 'local' : $metodo);
        if (!$prov || !$prov->is_habilitado) {
            throw new \RuntimeException("El método «{$metodo}» no está habilitado a nivel del sistema.");
        }
    }

    public function puedeConfigurar2faEnPerfil(User $user): bool
    {
        return (bool) $this->obtener((int) $user->id)['permite_2fa_voluntario'];
    }
}
