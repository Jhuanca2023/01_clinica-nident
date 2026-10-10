<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaDelDia extends Model
{
    use HasFactory;

    protected $table = 'oferta_del_dia';
    protected $primaryKey = 'id_oferta_dia';

    protected $fillable = [
        'id_producto',
        'nombre_oferta',
        'precio_oferta',
        'precio_original',
        'start_time',
        'end_time',
        'cantidad_disponible',
        'cantidad_vendida',
        'Activo',
        'segundo_id_producto',
        'segundo_nombre_oferta',
        'segundo_precio_oferta',
    ];

    protected $dates = ['start_time', 'end_time', 'created_at', 'updated_at'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
    
    public function segundo_producto()
    {
        return $this->belongsTo(Producto::class, 'segundo_id_producto', 'id_producto');
    }

    public function getRemainingTimeAttribute()
    {
        $now = now();
        if ($now > $this->end_time) {
            return '00:00:00';
        }

        $interval = $now->diff($this->end_time);
        if ($interval->days > 0) {
            return $interval->format('%a días %H:%I:%S');
        }
        return $interval->format('%H:%I:%S');
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->cantidad_disponible - $this->cantidad_vendida;
    }

    public function getIsActiveAttribute()
    {
        return $this->Activo === 'S' &&
            now() >= $this->start_time &&
            now() <= $this->end_time &&
            $this->remaining_quantity > 0;
    }
}
