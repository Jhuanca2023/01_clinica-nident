<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebPreguntasFrecuentes extends Model
{
    use HasFactory;

    protected $table = 'web_preguntas_frecuentes';
    protected $primaryKey = 'id_pregunta';
    protected $guarded = [];
}
