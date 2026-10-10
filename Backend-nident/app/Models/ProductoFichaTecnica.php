<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoFichaTecnica extends Model
{
    use HasFactory;

    protected $table = 'administracion_producto_ficha_tecnica';
    protected $primaryKey = 'id_ficha_tecnica';
    protected $guarded = [];
    
}