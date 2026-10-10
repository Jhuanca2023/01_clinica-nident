<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadUsuarioMenuFavorito extends Model
{
    protected $table = 'seguridad_usuario_menu_favorito';

    protected $primaryKey = 'id_favorito';

    protected $fillable = [
        'id_usuario',
        'id_roles',
        'path',
        'id_menu',
        'id_modulo',
        'nombre',
        'icon',
        'orden',
        'visitas',
        'ultima_visita',
    ];

    protected $casts = [
        'ultima_visita' => 'datetime',
    ];
}
