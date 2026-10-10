<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuHistorialSessiones extends Model
{
    protected $table = 'menu_historial_sessiones';

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_usuario',
        'path',
        'titulo',
        'icon',
        'visitas',
        'ultima_visita',
    ];

    protected $casts = [
        'visitas' => 'integer',
        'ultima_visita' => 'datetime',
    ];
}
