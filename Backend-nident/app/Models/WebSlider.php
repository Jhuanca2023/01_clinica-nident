<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSlider extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
/** Registro reservado para imagen de fondo interna (no va al carrusel del inicio). */
    public const ID_BANNER_INTERNO = 4;

    protected $table = 'web_slider';
    protected $primaryKey = 'id_slider';
    protected $guarded = [];

    public function scopeForHeroCarousel($query)
    {
        return $query->where('id_slider', '!=', self::ID_BANNER_INTERNO);
    }
}
