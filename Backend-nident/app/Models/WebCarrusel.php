<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use App\Support\CmsStorageUrl;
use Illuminate\Database\Eloquent\Model;

class WebCarrusel extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table      = 'web_carrusel';
    protected $primaryKey = 'id_carrusel';

    protected $fillable = [
        'label',
        'url_imagen',
        'orden',
        'Activo',
    ];

    protected $appends = [
        'url_imagen_publica',
    ];

    public function getUrlImagenPublicaAttribute(): ?string
    {
        if (empty($this->url_imagen)) {
            return null;
        }

        if (str_starts_with($this->url_imagen, 'temp02/')) {
            return asset($this->url_imagen);
        }

        return CmsStorageUrl::forApi($this->url_imagen);
    }
}
