<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'administracion_programacion';
    protected $primaryKey = 'id_programacion';
    protected $guarded = [];
    
}