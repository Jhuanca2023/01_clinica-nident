<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSuscriptores extends Model
{
    use HasFactory;

    protected $table = 'web_suscriptores';
    protected $primaryKey = 'id_suscriptores';
    protected $guarded = [];
    
}