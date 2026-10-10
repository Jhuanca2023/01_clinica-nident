<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPasarelaConfig extends Model
{
    protected $table      = 'pago_pasarela_config';
    protected $primaryKey = 'id_pago_pasarela_config';
    protected $guarded    = [];

    /**
     * Pasarela a la que pertenece esta configuración.
     */
    public function pasarela()
    {
        return $this->belongsTo(PagoPasarela::class, 'id_pago_pasarela', 'id_pago_pasarela');
    }
}
