<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioShippingCost extends Model
{
    use HasFactory;

    protected $table = 'envio_shipping_costs';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
}