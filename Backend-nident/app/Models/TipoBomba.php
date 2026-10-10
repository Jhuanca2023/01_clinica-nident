<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoBomba extends Model
{
    use HasFactory;

    protected $table = 'despacho_tipo_bomba';
    protected $primaryKey = 'id_tipo_bomba';
    protected $guarded = [];
    
}