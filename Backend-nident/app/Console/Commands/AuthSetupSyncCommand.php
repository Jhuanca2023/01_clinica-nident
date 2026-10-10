<?php

namespace App\Console\Commands;

use App\Models\SeguridadAuthProveedor;
use App\Models\SeguridadAuthProveedorConfig;
use Illuminate\Console\Command;

class AuthSetupSyncCommand extends Command
{
    protected $signature = 'auth:setup-sync {--check : Solo mostrar estado sin actualizar URIs}';

    protected $description = 'Sincroniza REDIRECT_URI OAuth con APP_URL y muestra checklist de prueba Microsoft';

    public function handle(): int
    {
        $appUrl = rtrim(config('app.url'), '/');
        // $frontend = rtrim(env('FRONTEND_URL', env('APP_URL_FROND', 'http://localhost:8000')), '/');
        $frontend = rtrim(env('FRONTEND_URL', env('APP_URL_FROND', 'https://rossety.pe')), '/');
        if (!str_contains($frontend, '/admin')) {
            $frontend .= rtrim(env('FRONTEND_OAUTH_PATH', '/admin'), '/');
        }

        $this->info('=== Autenticación multi-proveedor — checklist ===');
        $this->line("APP_URL (config): {$appUrl}");
        $this->line("FRONTEND (OAuth return): {$frontend}");
        $this->newLine();

        $providers = SeguridadAuthProveedor::with('configs')->orderBy('orden')->get();
        if ($providers->isEmpty()) {
            $this->error('No hay proveedores. Ejecuta: php artisan db:seed --class=SeguridadAuthProveedorSeeder');
            return 1;
        }

        foreach ($providers as $p) {
            $hab = $p->is_habilitado ? 'SÍ' : 'no';
            $pred = $p->is_predeterminado ? ' (predeterminado)' : '';
            $this->line("<fg=cyan>{$p->codigo}</> — habilitado: {$hab}{$pred}");

            if (in_array($p->codigo, ['microsoft', 'google'], true)) {
                $expectedRedirect = "{$appUrl}/api/auth/oauth/callback/{$p->codigo}";
                if (!$this->option('check')) {
                    SeguridadAuthProveedorConfig::where('id_seguridad_auth_proveedor', $p->id_seguridad_auth_proveedor)
                        ->where('clave', 'REDIRECT_URI')
                        ->update(['valor' => $expectedRedirect]);

                    $this->syncEnvCredential($p, 'CLIENT_ID', $p->codigo === 'google' ? 'GOOGLE_CLIENT_ID' : 'MICROSOFT_CLIENT_ID');
                    $this->syncEnvCredential($p, 'CLIENT_SECRET', $p->codigo === 'google' ? 'GOOGLE_CLIENT_SECRET' : 'MICROSOFT_CLIENT_SECRET');
                }
                $cfg = $p->configs->firstWhere('clave', 'REDIRECT_URI');
                $cur = $cfg?->valor ?? '(vacío)';
                $this->line("  REDIRECT_URI: {$cur}");
                if ($cur !== $expectedRedirect) {
                    $this->warn("  → Debe ser: {$expectedRedirect}");
                    $this->line('  Ejecuta sin --check para sincronizar.');
                }
                $clientId = $p->configs->firstWhere('clave', 'CLIENT_ID');
                $hasId = $clientId && trim((string) $clientId->valor) !== '';
                $this->line('  CLIENT_ID: ' . ($hasId ? substr((string) $clientId->valor, 0, 24) . '…' : '<fg=red>falta en BD y .env</>'));
            }
        }

        $this->newLine();
        $this->info('=== Google Cloud Console (obligatorio si Error 400 redirect_uri_mismatch) ===');
        $googleRedirect = "{$appUrl}/api/auth/oauth/callback/google";
        $this->line('  1. https://console.cloud.google.com/apis/credentials');
        $this->line('  2. Abre el cliente OAuth con el MISMO Client ID que en .env / panel admin');
        $this->line('  3. URIs de redireccionamiento autorizados → AGREGAR (copiar exacto):');
        $this->line("     <fg=green>{$googleRedirect}</>");
        // $this->line('  4. Quitar la URI antigua si existe: http://localhost:8000/auth/google/callback');
        $this->line('  4. Quitar la URI antigua si existe: https://rossety.pe/auth/google/callback');

        $this->line('  5. Guardar y esperar 1–2 minutos');
        $this->newLine();
        $this->info('URLs para registrar en Azure (Microsoft):');
        $this->line("  Redirect URI: {$appUrl}/api/auth/oauth/callback/microsoft");
        $this->newLine();
        $this->info('Probar en navegador:');
        $this->line("  1. Admin config: {$frontend}/configuracion/auth-proveedor");
        $this->line("  2. Login: {$frontend}/session/signin");
        $this->line('  3. API métodos: ' . $appUrl . '/api/auth/metodos-login');
        $this->newLine();
        $this->comment('Microsoft Entra ID es GRATIS para registro de apps en desarrollo (no pagas por probar login).');
        $this->comment('Guía completa: docs/PRUEBA_MICROSOFT_PASO_A_PASO.md');

        return 0;
    }

    private function syncEnvCredential(SeguridadAuthProveedor $proveedor, string $clave, string $envKey): void
    {
        $valor = trim((string) env($envKey, ''));
        if ($valor === '') {
            return;
        }
        $row = SeguridadAuthProveedorConfig::where('id_seguridad_auth_proveedor', $proveedor->id_seguridad_auth_proveedor)
            ->where('clave', $clave)
            ->first();
        if (!$row) {
            return;
        }
        $row->update([
            'valor' => SeguridadAuthProveedorConfig::guardarValor((bool) $row->es_secreto, $valor),
        ]);
    }
}
