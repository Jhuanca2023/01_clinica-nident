<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promociones extends Model
{
    use HasFactory;

    protected $table = 'oferta_promociones';
    protected $primaryKey = 'id_promociones';
    protected $guarded = [];
    
}