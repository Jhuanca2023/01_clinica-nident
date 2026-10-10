<?php

namespace Database\Seeders;

use App\Services\Seguridad\RolPermisosAsignacionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vacía las tablas de permisos por rol y asigna TODOS los menús y objetos
 * al rol Administrador (ACCESO GENERAL) para que admin tenga acceso total.
 *
 * Ejecutar: php artisan db:seed --class=AdminFullPermissionsSeeder
 */
class AdminFullPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $rol = DB::table('seguridad_roles')->where('nombre', 'ACCESO GENERAL')->first();
        if (!$rol) {
            $this->command->error('No existe el rol ACCESO GENERAL. Ejecuta antes: php artisan db:seed --class=AdminUserSeeder');
            return;
        }

        $idRoles = (int) $rol->id_roles;
        $permisos = app(RolPermisosAsignacionService::class);
        $permisos->limpiarRol($idRoles);
        $permisos->eliminarAsignacionesMenuCorruptas();

        // Si no hay módulos/menús, crear estructura mínima para que el sidebar y permisos tengan contenido
        if (DB::table('sistema_modulo')->count() === 0) {
            DB::table('sistema_modulo')->insert([
                ['nombre' => 'Productos', 'Activo' => 'S', 'Icon' => 'layers', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Ventas', 'Activo' => 'S', 'Icon' => 'store', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Seguridad', 'Activo' => 'S', 'Icon' => 'security', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Configuraciones', 'Activo' => 'S', 'Icon' => 'settings', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('sistema_menu')->count() === 0) {
            $idModulo = DB::table('sistema_modulo')->orderBy('id_modulo')->value('id_modulo');
            if ($idModulo) {
                $menus = [
                    ['Productos', '/producto/index'],
                    ['Importar Productos', '/producto/importar'],
                    ['Categorías', '/categoria/index'],
                    ['Pedidos', '/pedidos/index'],
                    ['Facturas', '/facturas/index'],
                    ['Usuarios', '/usuarios/index'],
                    ['Perfil', '/perfiles/index'],
                    ['Roles', '/roles/index'],
                    ['Menús', '/menu/index'],
                    ['Objetos', '/objetos/index'],
                    ['Dashboard', '/dashboard/default'],
                ];
                foreach ($menus as $m) {
                    DB::table('sistema_menu')->insert([
                        'id_modulo' => $idModulo,
                        'nombre' => $m[0],
                        'url' => $m[1],
                        'Activo' => 'S',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $permisos->asignarTodoAlRol($idRoles);

        $this->command->info('Permisos asignados correctamente al rol ACCESO GENERAL (módulos en seguridad_roles_modulo).');
    }
}
