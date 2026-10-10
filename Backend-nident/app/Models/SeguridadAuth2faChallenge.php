<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguridadAuth2faChallenge extends Model
{
    protected $table = 'seguridad_auth_2fa_challenge';

    protected $primaryKey = 'id_seguridad_auth_2fa_challenge';

    public const TIPO_VERIFY = 'verify';
    public const TIPO_SETUP_MANDATORY = 'setup_mandatory';

    protected $fillable = [
        'challenge_token',
        'tipo',
        'id_usuario',
        'remember',
        'ip',
        'expires_at',
    ];

    protected $casts = [
        'remember' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
