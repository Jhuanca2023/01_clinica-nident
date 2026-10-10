<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class ProductoFoto extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table = 'administracion_producto_fotos';
    protected $primaryKey = 'id_producto_foto';
    protected $guarded = [];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
