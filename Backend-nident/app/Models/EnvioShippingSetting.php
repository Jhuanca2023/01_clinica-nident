<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioShippingSetting extends Model
{
    use HasFactory;

    protected $table = 'envio_shipping_settings';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
}