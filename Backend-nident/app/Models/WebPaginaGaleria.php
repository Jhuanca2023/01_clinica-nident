<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class WebPaginaGaleria extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table = 'web_pagina_galeria';
    protected $primaryKey = 'id_galeria';
    protected $guarded = [];

    protected $appends = ['url_imagen_publica'];

    public function getUrlImagenPublicaAttribute(): ?string
    {
        return $this->url_imagen ? \App\Support\CmsStorageUrl::forWeb($this->url_imagen) : null;
    }

    public static function categoriasTabs(): array
    {
        return [
            'todas'      => 'Todas',
            'sensorial'  => 'Sensorial',
            'relajacion' => 'Relajación',
            'tantrico'   => 'Tántrico',
            'vip'        => 'Privado VIP',
        ];
    }
}
