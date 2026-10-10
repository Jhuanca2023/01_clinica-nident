<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebPromoBloque extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['hero'];
protected $table = 'web_promo_bloque';
    protected $primaryKey = 'id_promo_bloque';
    public $timestamps = false;

    protected $fillable = [
        'titulo_seccion', 'subtitulo_seccion', 'slug',
        'hero_etiqueta', 'hero_titulo', 'hero_subtitulo',
        'imagen_hero', 'orden', 'Activo',
    ];

    protected static function booted(): void
    {
        static::saving(function (WebPromoBloque $bloque) {
            if (empty($bloque->slug) || $bloque->isDirty('subtitulo_seccion') || $bloque->isDirty('titulo_seccion')) {
                $bloque->slug = static::uniqueSlug(
                    Str::slug($bloque->subtitulo_seccion ?: $bloque->titulo_seccion ?: 'ofertas-promociones') ?: 'ofertas-promociones',
                    (int) ($bloque->id_promo_bloque ?? 0)
                );
            }
        });
    }

    public static function uniqueSlug(string $base, int $exceptId = 0): string
    {
        $slug = $base;
        $n = 1;
        while (static::where('slug', $slug)->when($exceptId > 0, fn ($q) => $q->where('id_promo_bloque', '!=', $exceptId))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    public function items()
    {
        return $this->hasMany(WebPromoItem::class, 'id_promo_bloque', 'id_promo_bloque')
                    ->orderBy('orden');
    }

    /** Productos del catálogo vinculados al bloque (multiselección admin). */
    public function productosVinculados()
    {
        return $this->belongsToMany(
            Producto::class,
            'web_promo_bloque_producto',
            'id_promo_bloque',
            'id_producto',
            'id_promo_bloque',
            'id_producto'
        )->withPivot('orden', 'id_promo_bloque_producto')
         ->orderBy('web_promo_bloque_producto.orden');
    }
}
