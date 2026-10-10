<?php

/**
 * Sincroniza objetos de menú para el rol ACCESO GENERAL (menús ya asignados).
 * php database/scripts/sync_objetos_rol_acceso_general.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Seguridad\RolPermisosAsignacionService;
use Illuminate\Support\Facades\DB;

$idRoles = (int) DB::table('seguridad_roles')->where('nombre', 'ACCESO GENERAL')->value('id_roles');
if ($idRoles < 1) {
    echo "No existe rol ACCESO GENERAL\n";
    exit(1);
}

$n = app(RolPermisosAsignacionService::class)->sincronizarObjetosDeMenusAsignados($idRoles);
echo "Rol {$idRoles}: insertados {$n} objetos en seguridad_menu_objetos_roles\n";
echo "Cierre sesión y vuelva a entrar, o use validar perfil en la app.\n";
