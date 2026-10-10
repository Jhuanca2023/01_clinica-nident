<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Seguridad\AdminPrincipalService;
use App\Services\Seguridad\UsuarioAuthConfigService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Administrador principal: ghiovani666@gmail.com + perfil Administrador + ACCESO GENERAL.
 *
 * php artisan db:seed --class=PrincipalAdminSeeder
 */
class PrincipalAdminSeeder extends Seeder
{
    public const EMAIL = 'ghiovani666@gmail.com';

    public function run(): void
    {
        if (!Schema::hasColumn('users', 'es_administrador_principal')) {
            $this->command->error('Ejecute antes la migración es_administrador_principal.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('AdminPrincipal2026!'),
                'Activo' => 'S',
            ]
        );

        app(AdminPrincipalService::class)->establecerPrincipalPorEmail(self::EMAIL);

        if (Schema::hasTable('seguridad_usuario_auth_config')) {
            app(UsuarioAuthConfigService::class)->guardar((int) $user->id, [
                'permitir_local' => true,
                'permitir_google' => true,
                'permitir_microsoft' => true,
                'requiere_2fa' => false,
                'permite_2fa_voluntario' => true,
                'metodo_predeterminado' => 'google',
            ]);
            $this->command->info('Auth config: Local + Google + Microsoft; 2FA opcional desde perfil.');
        }

        $this->command->info('Administrador principal: ' . self::EMAIL);
        $this->command->info('Perfil: Administrador | Rol: ACCESO GENERAL');
        $this->command->warn('Password local (solo si usa login Local): AdminPrincipal2026!');
    }

}
