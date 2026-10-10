<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguridadRolesSidebarOrden extends Model
{
    protected $table = 'seguridad_roles_sidebar_orden';

    protected $primaryKey = 'id_seguridad_roles_sidebar_orden';

    protected $fillable = [
        'id_roles',
        'tipo',
        'id_menu',
        'id_modulo',
        'orden',
        'nombre_sidebar',
        'icon_sidebar',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'id_roles', 'id_roles');
    }

    public function scopeParaRol($query, int $idRoles)
    {
        return $query->where('id_roles', $idRoles);
    }
}
