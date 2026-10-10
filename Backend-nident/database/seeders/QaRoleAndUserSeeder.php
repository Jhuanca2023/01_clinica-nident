<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Rol QA con 2FA obligatorio, permisos totales y usuario de prueba.
 *
 * Ejecutar: php artisan db:seed --class=QaRoleAndUserSeeder
 *
 * Credenciales de prueba:
 *   Email:    qa@valento.test
 *   Password: QaTest2026!
 */
class QaRoleAndUserSeeder extends Seeder
{
    public const ROL_QA_NOMBRE = 'QA PRUEBAS';
    public const PERFIL_QA_NOMBRE = 'Equipo QA';
    public const QA_EMAIL = 'qa@valento.test';
    public const QA_PASSWORD = 'QaTest2026!';

    public function run(): void
    {
        $idRoles = $this->crearRolQa();
        $idPerfil = $this->crearPerfilQa();
        $this->vincularPerfilRol($idPerfil, $idRoles);
        $this->crearUsuarioQa($idPerfil);

        $this->command->info('Rol QA PRUEBAS (2FA obligatorio) y usuario de prueba listos.');
        $this->command->info('  Email:    ' . self::QA_EMAIL);
        $this->command->info('  Password: ' . self::QA_PASSWORD);
    }

    private function crearRolQa(): int
    {
        $row = DB::table('seguridad_roles')->where('nombre', self::ROL_QA_NOMBRE)->first();
        $data = [
            'nombre' => self::ROL_QA_NOMBRE,
            'Activo' => 'S',
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('seguridad_roles', 'requiere_2fa')) {
            $data['requiere_2fa'] = true;
        }

        if ($row) {
            DB::table('seguridad_roles')->where('id_roles', $row->id_roles)->update($data);
            return (int) $row->id_roles;
        }

        $data['created_at'] = now();
        if (!isset($data['requiere_2fa'])) {
            unset($data['requiere_2fa']);
        }

        return (int) DB::table('seguridad_roles')->insertGetId($data);
    }

    private function crearPerfilQa(): int
    {
        $row = DB::table('seguridad_perfil')->where('nombre', self::PERFIL_QA_NOMBRE)->first();
        if ($row) {
            return (int) $row->id_perfil;
        }

        return (int) DB::table('seguridad_perfil')->insertGetId([
            'nombre' => self::PERFIL_QA_NOMBRE,
            'Activo' => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function vincularPerfilRol(int $idPerfil, int $idRoles): void
    {
        $exists = DB::table('seguridad_roles_perfil')
            ->where('id_perfil', $idPerfil)
            ->where('id_roles', $idRoles)
            ->exists();
        if (!$exists) {
            DB::table('seguridad_roles_perfil')->insert([
                'id_perfil' => $idPerfil,
                'id_roles' => $idRoles,
                'created_at' => now(),
            ]);
        }
    }

    private function crearUsuarioQa(int $idPerfil): void
    {
        $user = User::updateOrCreate(
            ['email' => self::QA_EMAIL],
            [
                'name' => 'Usuario QA Pruebas',
                'password' => Hash::make(self::QA_PASSWORD),
                'Activo' => 'S',
            ]
        );

        DB::table('seguridad_perfil_users')->updateOrInsert(
            ['id_usuario' => $user->id, 'id_perfil' => $idPerfil],
            ['created_at' => now()]
        );

        DB::table('seguridad_usuario_2fa')->where('id_usuario', $user->id)->delete();
    }
}
