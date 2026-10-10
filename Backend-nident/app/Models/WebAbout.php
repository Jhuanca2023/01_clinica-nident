<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebAbout extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen', 'imagen_2'];
protected $table = 'web_about';
    protected $primaryKey = 'id_about';
    protected $guarded = [];
    
}