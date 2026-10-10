<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Basado en bd_analisis:
 * 1) Trunca todas las tablas de gestión de menús (empezar de cero).
 * 2) Reinserta sistema_modulo, sistema_menu, sistema_menu_objetos según bd_analisis.
 * 3) Asigna TODOS los permisos (menús + objetos) al rol ACCESO GENERAL (administrador).
 *
 * No toca: users, seguridad_perfil, seguridad_roles, seguridad_perfil_users, seguridad_roles_perfil, sistema_objetos.
 *
 * Ejecutar: php artisan db:seed --class=MenuResetYPermisosCompletosSeeder
 */
class MenuResetYPermisosCompletosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Truncando tablas de gestión de menús...');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        DB::table('seguridad_roles_menu')->truncate();
        if (Schema::hasTable('seguridad_menu_objetos_roles')) {
            DB::table('seguridad_menu_objetos_roles')->truncate();
        }
        DB::table('sistema_menu_objetos')->truncate();
        DB::table('sistema_menu')->truncate();
        DB::table('sistema_modulo')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->command->info('Reinsertando sistema_modulo, sistema_menu, sistema_menu_objetos (bd_analisis)...');

        // sistema_modulo (bd_analisis: 1 Programacion, 2 Carguios, 3 Diseños, 4 Administracion, 5 Seguridad)
        $now = now()->format('Y-m-d H:i:s');
        DB::table('sistema_modulo')->insert([
            ['id_modulo' => 1, 'nombre' => 'Programacion', 'Activo' => 'S', 'Icon' => null, 'Nivel' => null, 'Orden' => null, 'expanded' => null, 'updated_at' => $now, 'created_at' => $now],
            ['id_modulo' => 2, 'nombre' => 'Carguios', 'Activo' => 'S', 'Icon' => null, 'Nivel' => null, 'Orden' => null, 'expanded' => null, 'updated_at' => $now, 'created_at' => $now],
            ['id_modulo' => 3, 'nombre' => 'Diseños', 'Activo' => 'S', 'Icon' => null, 'Nivel' => null, 'Orden' => null, 'expanded' => null, 'updated_at' => $now, 'created_at' => $now],
            ['id_modulo' => 4, 'nombre' => 'Administracion', 'Activo' => 'S', 'Icon' => null, 'Nivel' => null, 'Orden' => null, 'expanded' => null, 'updated_at' => $now, 'created_at' => $now],
            ['id_modulo' => 5, 'nombre' => 'Seguridad', 'Activo' => 'S', 'Icon' => null, 'Nivel' => null, 'Orden' => null, 'expanded' => null, 'updated_at' => $now, 'created_at' => $now],
        ]);

        // sistema_menu (id_menu 12-22, id_modulo 1-5)
        DB::table('sistema_menu')->insert([
            ['id_menu' => 12, 'id_modulo' => 1, 'nombre' => null, 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 13, 'id_modulo' => 2, 'nombre' => null, 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 14, 'id_modulo' => 3, 'nombre' => null, 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 15, 'id_modulo' => 4, 'nombre' => 'Clientes', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 16, 'id_modulo' => 4, 'nombre' => 'Empleados', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 17, 'id_modulo' => 4, 'nombre' => 'Vendedor', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 18, 'id_modulo' => 4, 'nombre' => 'Unidad de Medida', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 19, 'id_modulo' => 4, 'nombre' => 'Tipo de Bomba', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 20, 'id_modulo' => 5, 'nombre' => 'Usuarios', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 21, 'id_modulo' => 5, 'nombre' => 'Perfil', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
            ['id_menu' => 22, 'id_modulo' => 5, 'nombre' => 'Roles', 'url' => null, 'created_at' => $now, 'updated_at' => $now, 'Activo' => 'S'],
        ]);

        // sistema_menu_objetos: cada menú 12-22 tiene objetos 23-33 (11 objetos por menú = 121 filas). Estructura bd_analisis.
        $objetos = [23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33];
        $id_mo = 1;
        foreach ([12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22] as $id_menu) {
            foreach ($objetos as $id_objeto) {
                DB::table('sistema_menu_objetos')->insert([
                    'id_menu_objetos' => $id_mo,
                    'id_menu' => $id_menu,
                    'id_objetos' => $id_objeto,
                    'selected' => null,
                    'orden' => null,
                    'estado' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'Activo' => 'S',
                ]);
                $id_mo++;
            }
        }

        $this->command->info('Asignando todos los permisos al rol ACCESO GENERAL (id_roles=1)...');

        $idRoles = 1; // ACCESO GENERAL

        // seguridad_roles_menu: todos los menús (12-22). El backend hace JOIN por id_menu con sistema_menu_objetos.
        foreach ([12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22] as $id_menu) {
            DB::table('seguridad_roles_menu')->insert(['id_roles' => $idRoles, 'id_menu' => $id_menu]);
        }

        // seguridad_menu_objetos_roles: todos los id_menu_objetos (1-121)
        if (Schema::hasTable('seguridad_menu_objetos_roles')) {
            for ($id_mo = 1; $id_mo <= 121; $id_mo++) {
                DB::table('seguridad_menu_objetos_roles')->insert([
                    'id_roles' => $idRoles,
                    'id_menu_objetos' => $id_mo,
                ]);
            }
        }

        $this->command->info('Listo: tablas de menús reiniciadas y todos los permisos asignados al administrador (rol ACCESO GENERAL).');
    }
}
