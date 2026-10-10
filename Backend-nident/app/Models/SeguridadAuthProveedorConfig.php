<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SeguridadAuthProveedorConfig extends Model
{
    protected $table = 'seguridad_auth_proveedor_config';
    protected $primaryKey = 'id_seguridad_auth_proveedor_config';
    protected $guarded = [];

    protected $casts = [
        'es_secreto' => 'boolean',
    ];

    public function proveedor()
    {
        return $this->belongsTo(SeguridadAuthProveedor::class, 'id_seguridad_auth_proveedor', 'id_seguridad_auth_proveedor');
    }

    public function getValorDescifradoAttribute(): ?string
    {
        if ($this->valor === null || $this->valor === '') {
            return null;
        }
        if (!$this->es_secreto) {
            return $this->valor;
        }
        try {
            return Crypt::decryptString($this->valor);
        } catch (\Throwable $e) {
            return $this->valor;
        }
    }

    public static function guardarValor(bool $esSecreto, ?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if ($esSecreto) {
            return Crypt::encryptString($valor);
        }
        return $valor;
    }
}
