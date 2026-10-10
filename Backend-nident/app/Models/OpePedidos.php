<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpePedidos extends Model
{
    use HasFactory;

    protected $table = 'ope_pedidos';
    protected $primaryKey = 'id_pedido';
    protected $guarded = [];
    
}