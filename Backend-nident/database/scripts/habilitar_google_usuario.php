<?php

/**
 * Habilita Google para un usuario por email.
 * php database/scripts/habilitar_google_usuario.php jorgebasicoa@gmail.com
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SeguridadUsuarioAuthConfig;
use App\Models\User;
use App\Services\Seguridad\UsuarioAuthConfigService;

$email = $argv[1] ?? 'jorgebasicoa@gmail.com';
$user = User::where('email', $email)->first();
if (!$user) {
    echo "No existe usuario: {$email}\n";
    exit(1);
}

$svc = app(UsuarioAuthConfigService::class);
$cfg = $svc->obtener((int) $user->id);
$cfg['permitir_google'] = true;
$cfg['permitir_local'] = $cfg['permitir_local'] ?? false;
$cfg['metodo_predeterminado'] = 'google';
$svc->guardar((int) $user->id, $cfg);

echo "OK: Google habilitado para {$email} (id {$user->id})\n";
