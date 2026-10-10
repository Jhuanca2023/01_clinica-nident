<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguridadUsuario2fa extends Model
{
    protected $table = 'seguridad_usuario_2fa';

    protected $primaryKey = 'id_seguridad_usuario_2fa';

    protected $fillable = [
        'id_usuario',
        'secret_cifrado',
        'is_habilitado',
        'habilitado_at',
        'recovery_codes_hash',
    ];

    protected $casts = [
        'is_habilitado' => 'boolean',
        'habilitado_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
