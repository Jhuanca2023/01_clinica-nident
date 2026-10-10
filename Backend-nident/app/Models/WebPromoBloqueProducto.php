<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPromoBloqueProducto extends Model
{
    protected $table = 'web_promo_bloque_producto';
    protected $primaryKey = 'id_promo_bloque_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_promo_bloque',
        'id_producto',
        'orden',
    ];
}
