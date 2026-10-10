<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPromoMultiplicador extends Model
{
    protected $table = 'web_promo_multiplicador';

    protected $primaryKey = 'id_promo_multiplicador';

    public $timestamps = false;

    protected $fillable = [
        'etiqueta',
        'orden',
        'Activo',
    ];
}
