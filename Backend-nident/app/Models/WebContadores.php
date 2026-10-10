<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebContadores extends Model
{
    protected $table = 'web_contadores';
    protected $primaryKey = 'id_contador';
    protected $guarded = [];
}
