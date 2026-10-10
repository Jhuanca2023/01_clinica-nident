<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioShippingAgency extends Model
{
    use HasFactory;

    protected $table = 'envio_shipping_agencies';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
}