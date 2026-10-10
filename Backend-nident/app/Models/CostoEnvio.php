<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostoEnvio extends Model
{
    use HasFactory;

    protected $table = 'oferta_costo_envios';
    protected $fillable = [
        'distrito', 
        'costo_base', 
        'costo_por_kg', 
        'monto_minimo_envio_gratis',
        'Activo'
    ];
    protected $casts = [
        'costo_base' => 'decimal:2',
        'costo_por_kg' => 'decimal:2',
        'monto_minimo_envio_gratis' => 'decimal:2',
        'Activo' => 'boolean'
    ];
}