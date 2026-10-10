<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use App\Support\CmsStorageUrl;
use Illuminate\Database\Eloquent\Model;

class WebFooterBanner extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table = 'web_footer_banner';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $guarded = [];

    protected $appends = ['url_imagen_publica'];

    public function getUrlImagenPublicaAttribute(): ?string
    {
        return CmsStorageUrl::forApi($this->url_imagen);
    }
}
