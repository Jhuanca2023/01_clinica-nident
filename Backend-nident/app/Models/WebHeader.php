<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class WebHeader extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['logo'];
protected $table      = 'web_header';
    protected $primaryKey = 'id_header';
    protected $guarded    = [];
    protected $casts      = [
        'nav_items'         => 'array',
        'ticker_items'      => 'array',
        'side_menu_enlaces' => 'array',
        'redes_side'        => 'array',
    ];
}
