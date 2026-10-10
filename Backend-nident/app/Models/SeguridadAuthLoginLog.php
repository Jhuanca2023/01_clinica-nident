<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadAuthLoginLog extends Model
{
    public $timestamps = false;
    protected $table = 'seguridad_auth_login_log';
    protected $primaryKey = 'id_seguridad_auth_login_log';
    protected $guarded = [];

    public static function registrar(string $proveedor, bool $exito, ?int $userId = null, ?string $email = null, ?string $ip = null, ?string $mensaje = null): void
    {
        static::create([
            'proveedor' => $proveedor,
            'id_usuario' => $userId,
            'email' => $email,
            'ip' => $ip,
            'exito' => $exito ? 'S' : 'N',
            'mensaje' => $mensaje,
        ]);
    }
}
