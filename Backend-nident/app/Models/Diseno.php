<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diseno extends Model
{
    use HasFactory;

    protected $table = 'despacho_diseno';
    protected $primaryKey = 'id_diseno';
    protected $guarded = [];
    
}