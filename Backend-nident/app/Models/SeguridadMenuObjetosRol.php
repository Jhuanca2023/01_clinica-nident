<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguridadMenuObjetosRol extends Model
{
    protected $table = 'seguridad_menu_objetos_roles';

    public $incrementing = false;
    protected $fillable = ['id_roles', 'id_menu_objetos'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'id_roles', 'id_roles');
    }
}
