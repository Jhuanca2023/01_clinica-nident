<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebFooter extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['logo_menu', 'logo_footer', 'imagen_central'];
protected $table = 'web_footer';
    protected $primaryKey = 'id_footer';
    protected $guarded = [];
    
}