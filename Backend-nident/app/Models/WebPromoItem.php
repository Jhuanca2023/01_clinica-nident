<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class WebPromoItem extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table = 'web_promo_item';
    protected $primaryKey = 'id_promo_item';
    public $timestamps = false;

    protected $fillable = [
        'id_promo_bloque', 'id_producto', 'categoria', 'nombre',
        'tipo_oferta', 'multiplicador', 'precio', 'descuento_porcentaje',
        'mostrar_precio_portada', 'id_producto_portada',
        'imagen', 'descripcion', 'orden', 'Activo',
    ];

    public function bloque()
    {
        return $this->belongsTo(WebPromoBloque::class, 'id_promo_bloque', 'id_promo_bloque');
    }

    /** Productos del catálogo asignados a este submódulo (tarjeta). */
    public function productosVinculados()
    {
        return $this->belongsToMany(
            Producto::class,
            'web_promo_item_producto',
            'id_promo_item',
            'id_producto',
            'id_promo_item',
            'id_producto'
        )->withPivot('orden', 'id_promo_item_producto')
         ->orderBy('web_promo_item_producto.orden');
    }
}
