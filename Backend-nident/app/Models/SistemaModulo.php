<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SistemaModulo extends Model
{
    protected $table = 'sistema_modulo';

    protected $primaryKey = 'id_modulo';

    public $timestamps = true;

    protected $guarded = [];

    public function menus(): HasMany
    {
        return $this->hasMany(SistemaMenu::class, 'id_modulo', 'id_modulo');
    }

    public function scopeActivos($query)
    {
        return $query->where('Activo', 'S');
    }
}
