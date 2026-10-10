<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguridadUsuario2faDispositivoConfiable extends Model
{
    protected $table = 'seguridad_usuario_2fa_dispositivo_confiable';

    protected $fillable = [
        'id_usuario',
        'token_hash',
        'expires_at',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function isValid(): bool
    {
        return $this->expires_at instanceof Carbon
            ? $this->expires_at->isFuture()
            : false;
    }
}
