<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SistemaMenu extends Model
{
    protected $table = 'sistema_menu';

    protected $primaryKey = 'id_menu';

    public $timestamps = true;

    protected $guarded = [];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(SistemaModulo::class, 'id_modulo', 'id_modulo');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_menu_padre', 'id_menu');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'id_menu_padre', 'id_menu');
    }

    public function scopeRaizSinModulo($query)
    {
        $query->whereNull('id_modulo');
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'id_menu_padre')) {
            $query->whereNull('id_menu_padre');
        }

        return $query;
    }

    public function scopeActivos($query)
    {
        return $query->where('Activo', 'S');
    }
}
