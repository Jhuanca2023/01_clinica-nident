<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$u = DB::table('users')->where('email', 'jorgebasicoa@gmail.com')->first();
if (!$u) {
    echo "Usuario no encontrado\n";
    exit(1);
}
echo "id_usuario={$u->id}\n";
$c = DB::table('seguridad_usuario_auth_config')->where('id_usuario', $u->id)->first();
if ($c) {
    echo "permitir_local={$c->permitir_local} permitir_google={$c->permitir_google} permitir_microsoft={$c->permitir_microsoft}\n";
} else {
    echo "Sin fila personalizada (usa defaults: todos true)\n";
}
