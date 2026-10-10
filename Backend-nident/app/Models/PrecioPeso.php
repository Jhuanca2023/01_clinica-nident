<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioPeso extends Model
{
    use HasFactory;

    protected $table = 'oferta_precios_peso';
    protected $primaryKey = 'id_precio_peso';
    protected $guarded = [];

        public $timestamps = true;
    
    // protected $fillable = [
    //     'rango_min',
    //     'rango_max',
    //     'precio',
    //     'pago_contra_entrega',
    //     'hora_regresiva',
    //     'hora_regresiva_descripcion',
    //     'paquete_medidas',
    //     'paquete_dimencion',
    //     'address_departamento',
    //     'address_distrito',
    //     'address_direccion',
    //     'Activo'
    // ];
    
    // protected $casts = [
    //     'precio' => 'decimal:2',
    //     'rango_min' => 'decimal:3',
    //     'rango_max' => 'decimal:3',
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime',
    //     'deleted_at' => 'datetime',
    // ];
}
