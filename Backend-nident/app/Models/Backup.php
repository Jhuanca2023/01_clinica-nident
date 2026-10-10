<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $table = 'administracion_backups';
    protected $primaryKey = 'id_backup';
    protected $guarded = [];
}