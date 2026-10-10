<?php

namespace Database\Seeders;

use App\Services\Seguridad\RolPermisosAsignacionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Limpieza drástica de permisos por rol (basura / ids mezclados).
 * Deja usuarios y estructura de menús; vacía asignaciones para configurar de nuevo en Roles.
 *
 * php artisan db:seed --class=PermisosLimpiezaSeeder
 * php artisan db:seed --class=PrincipalAdminSeeder
 * php artisan db:seed --class=QaRoleAndUserSeeder
 */
class PermisosLimpiezaSeeder extends Seeder
{
    public function run(): void
    {
        $svc = app(RolPermisosAsignacionService::class);

        $this->command->warn('Limpiando tablas de permisos por rol...');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $eliminadas = $svc->eliminarAsignacionesMenuCorruptas();
        if ($eliminadas > 0) {
            $this->command->info("Eliminadas {$eliminadas} filas corruptas (id_modulo guardado como id_menu).");
        }

        $svc->limpiarTodasLasAsignaciones();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->command->warn('Sin permisos el sidebar y los botones (Crear, Editar…) desaparecen hasta reasignar.');
        $this->command->info('Opción A — Admin con todo: php artisan db:seed --class=AdminFullPermissionsSeeder');
        $this->command->info('Opción B — Manual: Seguridad → Roles → ACCESO GENERAL → Guardar');
        $this->command->info('Luego cierre sesión y vuelva a entrar (el JWT guarda menu_objetos al login).');
    }
}
