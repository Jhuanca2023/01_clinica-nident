<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class WebPaginaVideos extends Model implements HasMedia
{
    use CompressesCmsImages;


    /** @var list<string> */
    protected array $cmsMediaSingle = ['banner'];
protected $table = 'web_pagina_videos';
    protected $guarded = [];
}
