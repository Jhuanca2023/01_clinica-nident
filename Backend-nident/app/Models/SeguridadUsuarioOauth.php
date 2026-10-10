<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadUsuarioOauth extends Model
{
    protected $table = 'seguridad_usuario_oauth';
    protected $primaryKey = 'id_seguridad_usuario_oauth';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
