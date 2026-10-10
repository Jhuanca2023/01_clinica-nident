<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class WebMaquinariasItem extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['front', 'back'];
protected $table = 'web_maquinarias_item';
    protected $guarded = [];

    public function hasFlip(): bool
    {
        return !empty($this->url_imagen_front) && !empty($this->url_imagen_back);
    }
}
