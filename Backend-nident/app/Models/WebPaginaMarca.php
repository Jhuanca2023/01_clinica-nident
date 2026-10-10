<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPaginaMarca extends Model
{
    protected $table = 'web_pagina_marca';
    protected $primaryKey = 'id_marca';
    protected $guarded = [];

    protected $appends = ['url_imagen_publica'];

    public function getUrlImagenPublicaAttribute(): ?string
    {
        return $this->url_imagen ? asset($this->url_imagen) : null;
    }
}
