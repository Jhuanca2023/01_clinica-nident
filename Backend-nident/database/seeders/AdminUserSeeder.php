<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * Crea el usuario admin@gmail.com / 123456789 y le asigna un perfil y rol
 * para que el login devuelva perfil con al menos un registro y pueda acceder al dashboard.
 *
 * Ejecutar: php artisan db:seed --class=AdminUserSeeder
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'ADMIN',
                'password' => Hash::make('123456789'),
                'Activo'   => 'S',
            ]
        );

        $userId = $user->id;

        // Perfil por defecto
        $idPerfil = $this->firstOrInsertPerfil();
        // Rol por defecto
        $idRoles = $this->firstOrInsertRol();

        // Usuario -> Perfil
        $exists = DB::table('seguridad_perfil_users')
            ->where('id_usuario', $userId)
            ->where('id_perfil', $idPerfil)
            ->exists();
        if (!$exists) {
            DB::table('seguridad_perfil_users')->insert([
                'id_usuario'   => $userId,
                'id_perfil'    => $idPerfil,
                'created_at'   => now(),
            ]);
        }

        // Perfil -> Rol
        $existsRole = DB::table('seguridad_roles_perfil')
            ->where('id_perfil', $idPerfil)
            ->where('id_roles', $idRoles)
            ->exists();
        if (!$existsRole) {
            DB::table('seguridad_roles_perfil')->insert([
                'id_perfil'    => $idPerfil,
                'id_roles'     => $idRoles,
                'created_at'   => now(),
            ]);
        }

        // Asegurar que exista al menos un (id_menu, id_objetos) para que el login devuelva perfil
        $firstMenu = DB::table('sistema_menu')->value('id_menu');
        $firstObj = $firstMenu ? DB::table('sistema_objetos')->value('id_objetos') : null;
        if ($firstMenu && $firstObj) {
            $existsMo = DB::table('sistema_menu_objetos')
                ->where('id_menu', $firstMenu)
                ->where('id_objetos', $firstObj)
                ->exists();
            if (!$existsMo) {
                DB::table('sistema_menu_objetos')->insert([
                    'id_menu' => $firstMenu,
                    'id_objetos' => $firstObj,
                    'Activo' => 'S',
                    'orden' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Rol -> Menús (al menos el menú que tiene objetos para que getPerfilData devuelva filas)
        $menusToAssign = $firstMenu ? [$firstMenu] : [15, 16, 20, 21, 22];
        foreach ($menusToAssign as $idMenu) {
            $existsMenu = DB::table('seguridad_roles_menu')
                ->where('id_roles', $idRoles)
                ->where('id_menu', $idMenu)
                ->exists();
            if (!$existsMenu) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRoles,
                    'id_menu'  => $idMenu,
                ]);
            }
        }

        $this->command->info('Usuario admin@gmail.com / 123456789 creado o actualizado con perfil y rol.');
    }

    private function firstOrInsertPerfil(): int
    {
        $row = DB::table('seguridad_perfil')->where('nombre', 'Administrador')->first();
        if ($row) {
            return (int) $row->id_perfil;
        }
        return (int) DB::table('seguridad_perfil')->insertGetId([
            'nombre'     => 'Administrador',
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function firstOrInsertRol(): int
    {
        $row = DB::table('seguridad_roles')->where('nombre', 'ACCESO GENERAL')->first();
        if ($row) {
            return (int) $row->id_roles;
        }
        return (int) DB::table('seguridad_roles')->insertGetId([
            'nombre'     => 'ACCESO GENERAL',
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
