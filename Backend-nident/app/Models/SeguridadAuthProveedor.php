<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadAuthProveedor extends Model
{
    protected $table = 'seguridad_auth_proveedor';
    protected $primaryKey = 'id_seguridad_auth_proveedor';
    protected $guarded = [];

    protected $casts = [
        'is_habilitado' => 'boolean',
        'is_predeterminado' => 'boolean',
    ];

    public function configs()
    {
        return $this->hasMany(SeguridadAuthProveedorConfig::class, 'id_seguridad_auth_proveedor', 'id_seguridad_auth_proveedor')
            ->orderBy('es_secreto')
            ->orderBy('id_seguridad_auth_proveedor_config');
    }

    public static function porCodigo(string $codigo): ?self
    {
        return static::where('codigo', $codigo)->first();
    }
}
