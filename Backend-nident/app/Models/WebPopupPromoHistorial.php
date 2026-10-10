<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPopupPromoHistorial extends Model
{
    protected $table = 'web_popup_promo_historial';

    protected $primaryKey = 'id_historial';

    protected $guarded = [];

    protected $casts = [
        'mostrar_email' => 'boolean',
        'mostrar_telefono' => 'boolean',
        'mostrar_opciones' => 'boolean',
        'delay_segundos' => 'integer',
        'cookie_dias' => 'integer',
        'descuento_porcentaje' => 'float',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];
}
