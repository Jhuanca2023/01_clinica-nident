<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioDistrict extends Model
{
    use HasFactory;

    protected $table = 'envio_districts';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
}