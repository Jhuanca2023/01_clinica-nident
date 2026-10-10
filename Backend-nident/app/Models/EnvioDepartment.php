<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioDepartment extends Model
{
    use HasFactory;

    protected $fillable = ['envio_departments'];

    
    public function districts()
    {
        return $this->hasMany(EnvioDistrict::class);
    }

    public function shippingCosts()
    {
        return $this->hasMany(EnvioShippingCost::class);
    }
}