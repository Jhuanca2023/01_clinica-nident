<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class WebPopupPromo extends Model implements HasMedia
{
    use CompressesCmsImages;

    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];

    protected $table      = 'web_popup_promo';
    protected $primaryKey = 'id_popup';
    protected $guarded    = [];

    protected $casts = [
        'mostrar_email'    => 'boolean',
        'mostrar_telefono' => 'boolean',
        'mostrar_opciones' => 'boolean',
        'delay_segundos'   => 'integer',
        'cookie_dias'      => 'integer',
    ];
}
