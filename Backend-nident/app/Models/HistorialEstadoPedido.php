<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoPedido extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['evidencia'];
protected $table = 'ope_pedidos_estado_historial';
    protected $primaryKey = 'id_historial';
    protected $fillable = [
        'id_pedido',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'evidencia_url',
        'id_usuario'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(OpePedidos::class, 'id_pedido', 'id_pedido');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario', 'id');
    }
}
