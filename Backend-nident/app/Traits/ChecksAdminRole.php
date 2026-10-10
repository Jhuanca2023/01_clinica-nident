<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ChecksAdminRole
{
    /**
     * Verifica si el usuario autenticado es ADMINISTRADOR (perfil_id = 1).
     */
    protected function isAdminUser(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return DB::table('seguridad_perfil_users')
            ->where('id_usuario', Auth::id())
            ->where('id_perfil', 1)
            ->exists();
    }
}
