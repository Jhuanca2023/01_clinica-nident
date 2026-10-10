<?php

namespace Database\Seeders;

use App\Models\SeguridadAuthProveedor;
use App\Models\SeguridadAuthProveedorConfig;
use Illuminate\Database\Seeder;

class SeguridadAuthProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'codigo' => 'local',
                'nombre' => 'Autenticación local',
                'descripcion' => 'Correo y contraseña con JWT (Laravel). Método predeterminado.',
                'is_habilitado' => true,
                'is_predeterminado' => true,
                'orden' => 0,
                'configs' => [],
            ],
            [
                'codigo' => 'microsoft',
                'nombre' => 'Microsoft (Entra ID)',
                'descripcion' => 'Inicio de sesión con cuenta Microsoft / Azure AD.',
                'is_habilitado' => false,
                'is_predeterminado' => false,
                'orden' => 1,
                'configs' => [
                    ['clave' => 'CLIENT_ID', 'etiqueta' => 'Application (client) ID', 'es_secreto' => false],
                    ['clave' => 'CLIENT_SECRET', 'etiqueta' => 'Client secret', 'es_secreto' => true],
                    ['clave' => 'TENANT_ID', 'etiqueta' => 'Tenant ID (common = multi-tenant)', 'es_secreto' => false],
                    ['clave' => 'REDIRECT_URI', 'etiqueta' => 'Redirect URI (API callback)', 'es_secreto' => false],
                ],
            ],
            [
                'codigo' => 'google',
                'nombre' => 'Google',
                'descripcion' => 'Inicio de sesión con cuenta Google (OAuth 2.0).',
                'is_habilitado' => false,
                'is_predeterminado' => false,
                'orden' => 2,
                'configs' => [
                    ['clave' => 'CLIENT_ID', 'etiqueta' => 'Client ID', 'es_secreto' => false],
                    ['clave' => 'CLIENT_SECRET', 'etiqueta' => 'Client secret', 'es_secreto' => true],
                    ['clave' => 'REDIRECT_URI', 'etiqueta' => 'Redirect URI (API callback)', 'es_secreto' => false],
                ],
            ],
        ];

        $apiBase = rtrim(config('app.url'), '/');

        foreach ($proveedores as $p) {
            $prov = SeguridadAuthProveedor::updateOrCreate(
                ['codigo' => $p['codigo']],
                [
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'],
                    'is_habilitado' => $p['is_habilitado'],
                    'is_predeterminado' => $p['is_predeterminado'],
                    'orden' => $p['orden'],
                ]
            );

            foreach ($p['configs'] as $cfg) {
                $defaultValor = null;
                if ($cfg['clave'] === 'REDIRECT_URI') {
                    $defaultValor = $apiBase . '/api/auth/oauth/callback/' . $p['codigo'];
                }
                if ($cfg['clave'] === 'TENANT_ID') {
                    $defaultValor = 'common';
                }

                SeguridadAuthProveedorConfig::updateOrCreate(
                    [
                        'id_seguridad_auth_proveedor' => $prov->id_seguridad_auth_proveedor,
                        'clave' => $cfg['clave'],
                    ],
                    [
                        'etiqueta' => $cfg['etiqueta'],
                        'es_secreto' => $cfg['es_secreto'],
                        'valor' => $defaultValor,
                    ]
                );
            }
        }
    }
}
