<?php

namespace App\Services\Seguridad;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminPrincipalService
{
    public const EMAIL_PRINCIPAL_DEFAULT = 'ghiovani666@gmail.com';

    public function obtenerPrincipal(): ?User
    {
        return User::where('es_administrador_principal', true)->first();
    }

    public function asegurarExistePrincipal(): void
    {
        if (!$this->obtenerPrincipal()) {
            throw new \RuntimeException(
                'El sistema requiere un administrador principal activo. Configure uno en Seguridad → Usuarios.'
            );
        }
    }

    public function esPrincipal(User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;
        return (bool) User::where('id', $id)->value('es_administrador_principal');
    }

    /** Designa un nuevo administrador principal (solo el actual principal o sin principal previo). */
    public function transferirPrincipal(int $nuevoIdUsuario, ?int $solicitanteId = null): User
    {
        $nuevo = User::find($nuevoIdUsuario);
        if (!$nuevo || $nuevo->Activo !== 'S') {
            throw new \RuntimeException('El usuario sucesor debe existir y estar activo.');
        }

        $actual = $this->obtenerPrincipal();

        if ($actual && (int) $actual->id === (int) $nuevo->id) {
            throw new \RuntimeException('Ese usuario ya es el administrador principal.');
        }

        DB::transaction(function () use ($nuevo) {
            User::where('es_administrador_principal', true)->update(['es_administrador_principal' => false]);
            $nuevo->update(['es_administrador_principal' => true, 'Activo' => 'S']);
            $this->asignarPerfilAdministrador((int) $nuevo->id);
        });

        return $nuevo->fresh();
    }

    public function puedeEliminar(int $idUsuario): array
    {
        if (!$this->esPrincipal($idUsuario)) {
            return ['allowed' => true];
        }

        $otros = User::where('es_administrador_principal', false)->where('Activo', 'S')->count();
        return [
            'allowed' => false,
            'requires_transfer' => true,
            'message' => 'Debe designar otro administrador principal antes de eliminar o desactivar esta cuenta.',
            'candidatos_disponibles' => $otros > 0,
        ];
    }

    public function asignarPerfilAdministrador(int $idUsuario): void
    {
        $idPerfil = DB::table('seguridad_perfil')->where('nombre', 'Administrador')->value('id_perfil');
        if (!$idPerfil) {
            $idPerfil = DB::table('seguridad_perfil')->insertGetId([
                'nombre' => 'Administrador',
                'Activo' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $idRoles = DB::table('seguridad_roles')->where('nombre', 'ACCESO GENERAL')->value('id_roles');
        if (!$idRoles) {
            return;
        }

        DB::table('seguridad_perfil_users')->updateOrInsert(
            ['id_usuario' => $idUsuario, 'id_perfil' => $idPerfil],
            ['created_at' => now()]
        );

        DB::table('seguridad_roles_perfil')->updateOrInsert(
            ['id_perfil' => $idPerfil, 'id_roles' => $idRoles],
            ['created_at' => now()]
        );
    }

    public function establecerPrincipalPorEmail(string $email): User
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \RuntimeException("No existe usuario con email {$email}.");
        }

        DB::transaction(function () use ($user) {
            User::where('es_administrador_principal', true)->update(['es_administrador_principal' => false]);
            $user->update(['es_administrador_principal' => true, 'Activo' => 'S']);
            $this->asignarPerfilAdministrador((int) $user->id);
        });

        return $user->fresh();
    }
}
