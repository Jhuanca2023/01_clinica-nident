<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguridadUsuarioAuthConfig extends Model
{
    protected $table = 'seguridad_usuario_auth_config';

    protected $primaryKey = 'id_seguridad_usuario_auth_config';

    protected $fillable = [
        'id_usuario',
        'permitir_local',
        'permitir_google',
        'permitir_microsoft',
        'requiere_2fa',
        'permite_2fa_voluntario',
        'metodo_predeterminado',
    ];

    protected $casts = [
        'permitir_local' => 'boolean',
        'permitir_google' => 'boolean',
        'permitir_microsoft' => 'boolean',
        'requiere_2fa' => 'boolean',
        'permite_2fa_voluntario' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
