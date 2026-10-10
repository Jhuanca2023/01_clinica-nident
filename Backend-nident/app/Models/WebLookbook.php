<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use App\Support\CmsStorageUrl;
use Illuminate\Database\Eloquent\Model;

class WebLookbook extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen', 'lateral'];
protected $table = 'web_lookbook';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $guarded = [];

    protected $appends = ['url_imagen_publica', 'url_imagen_lateral_publica'];

    public function getUrlImagenPublicaAttribute(): ?string
    {
        return CmsStorageUrl::forApi($this->url_imagen);
    }

    public function getUrlImagenLateralPublicaAttribute(): ?string
    {
        return CmsStorageUrl::forApi($this->url_imagen_lateral);
    }
}
