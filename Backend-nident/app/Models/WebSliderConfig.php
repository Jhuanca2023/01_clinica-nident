<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSliderConfig extends Model
{
    protected $table = 'web_slider_config';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'autoplay',
        'interval_ms',
        'pause_on_hover',
    ];
}
