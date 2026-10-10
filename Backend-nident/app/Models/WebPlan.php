<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebPlan extends Model
{
    use HasFactory;

    protected $table      = 'web_planes';
    protected $primaryKey = 'id_plan';
    protected $guarded    = [];

    protected $casts = [
        'caracteristicas' => 'array',
        'es_destacado'    => 'boolean',
    ];
}
