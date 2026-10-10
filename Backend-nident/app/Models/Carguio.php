<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carguio extends Model
{
    use HasFactory;

    protected $table = 'despacho_carguio';
    protected $primaryKey = 'id_carguio';
    protected $guarded = [];
    
}