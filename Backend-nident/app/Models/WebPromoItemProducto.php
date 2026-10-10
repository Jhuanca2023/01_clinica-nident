<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPromoItemProducto extends Model
{
    protected $table = 'web_promo_item_producto';
    protected $primaryKey = 'id_promo_item_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_promo_item',
        'id_producto',
        'orden',
    ];
}
