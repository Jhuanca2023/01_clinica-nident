<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadAuthOauthSesion extends Model
{
    protected $table = 'seguridad_auth_oauth_sesion';
    protected $primaryKey = 'id_seguridad_auth_oauth_sesion';
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
