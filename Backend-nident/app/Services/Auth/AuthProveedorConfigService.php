<?php

namespace App\Services\Auth;

use App\Models\SeguridadAuthProveedor;
use App\Models\SeguridadAuthProveedorConfig;
use Illuminate\Support\Facades\Schema;

class AuthProveedorConfigService
{
    public const MASK = '••••••••';

    public function mapaConfig(string $codigoProveedor): array
    {
        $prov = SeguridadAuthProveedor::porCodigo($codigoProveedor);
        if (!$prov) {
            return [];
        }
        $out = [];
        foreach ($prov->configs as $c) {
            $out[$c->clave] = $c->valor_descifrado;
        }
        return $this->mergeEnvFallback($codigoProveedor, $out);
    }

    /** Permite probar con .env si aún no guardaste credenciales en el panel admin. */
    private function mergeEnvFallback(string $codigo, array $out): array
    {
        if ($codigo === 'microsoft') {
            $out['CLIENT_ID'] = $out['CLIENT_ID'] ?: env('MICROSOFT_CLIENT_ID');
            $out['CLIENT_SECRET'] = $out['CLIENT_SECRET'] ?: env('MICROSOFT_CLIENT_SECRET');
            $out['TENANT_ID'] = $out['TENANT_ID'] ?: env('MICROSOFT_TENANT_ID', 'common');
            $out['REDIRECT_URI'] = $out['REDIRECT_URI'] ?: env('MICROSOFT_REDIRECT_URI');
        }
        if ($codigo === 'google') {
            $out['CLIENT_ID'] = $out['CLIENT_ID'] ?: env('GOOGLE_CLIENT_ID');
            $out['CLIENT_SECRET'] = $out['CLIENT_SECRET'] ?: env('GOOGLE_CLIENT_SECRET');
            $out['REDIRECT_URI'] = $out['REDIRECT_URI'] ?: env('GOOGLE_REDIRECT_URI', env('GOOGLE_REDIRECT'));
        }
        return $out;
    }

    public function listarParaAdmin(): array
    {
        return SeguridadAuthProveedor::with('configs')
            ->orderBy('orden')
            ->get()
            ->map(function ($p) {
                return [
                    'id_seguridad_auth_proveedor' => $p->id_seguridad_auth_proveedor,
                    'codigo' => $p->codigo,
                    'nombre' => $p->nombre,
                    'descripcion' => $p->descripcion,
                    'logo_url' => $p->logo_url,
                    'is_habilitado' => $p->is_habilitado,
                    'is_predeterminado' => $p->is_predeterminado,
                    'orden' => $p->orden,
                    'configs' => $p->configs->map(fn ($c) => [
                        'id_seguridad_auth_proveedor_config' => $c->id_seguridad_auth_proveedor_config,
                        'clave' => $c->clave,
                        'etiqueta' => $c->etiqueta,
                        'valor' => ($c->es_secreto && $c->valor) ? self::MASK : ($c->valor ?? ''),
                        'es_secreto' => (bool) $c->es_secreto,
                    ])->values(),
                ];
            })
            ->values()
            ->all();
    }

    public function metodosLoginPublicos(): array
    {
        if (! Schema::hasTable('seguridad_auth_proveedor')) {
            return $this->metodosLoginPorDefecto();
        }

        $rows = SeguridadAuthProveedor::where('is_habilitado', true)
            ->orderBy('orden')
            ->get()
            ->map(fn ($p) => [
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion,
                'is_habilitado' => (bool) $p->is_habilitado,
                'is_predeterminado' => (bool) $p->is_predeterminado,
            ])
            ->values()
            ->all();

        return count($rows) > 0 ? $rows : $this->metodosLoginPorDefecto();
    }

    private function metodosLoginPorDefecto(): array
    {
        $out = [
            [
                'codigo' => 'google',
                'nombre' => 'Google',
                'descripcion' => 'Iniciar con cuenta Google',
                'is_habilitado' => true,
                'is_predeterminado' => true,
            ],
        ];
        if (env('MICROSOFT_CLIENT_ID')) {
            $out[] = [
                'codigo' => 'microsoft',
                'nombre' => 'Microsoft',
                'descripcion' => 'Iniciar con cuenta Microsoft',
                'is_habilitado' => true,
                'is_predeterminado' => false,
            ];
        }
        $out[] = [
            'codigo' => 'local',
            'nombre' => 'Usuario y contraseña',
            'descripcion' => 'Inicio de sesión local',
            'is_habilitado' => true,
            'is_predeterminado' => false,
        ];

        return $out;
    }
}
