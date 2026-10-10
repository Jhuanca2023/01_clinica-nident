<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPasarela extends Model
{
    protected $table      = 'pago_pasarela';
    protected $primaryKey = 'id_pago_pasarela';
    protected $guarded    = [];

    protected $casts = [
        'is_activo' => 'boolean',
    ];

    /**
     * Configuraciones (claves API) de esta pasarela.
     */
    public function configs()
    {
        return $this->hasMany(PagoPasarelaConfig::class, 'id_pago_pasarela', 'id_pago_pasarela')
                    ->orderBy('es_secreto')
                    ->orderBy('id_pago_pasarela_config');
    }
}
