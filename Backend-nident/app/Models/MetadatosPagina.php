<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetadatosPagina extends Model
{
    use HasFactory;

    protected $table = 'metadatos_paginas';

    protected $fillable = [
        'nombre_pagina',
        'titulo_pagina',
        'descripcion_pagina',
        'activo',
    ];

    /**
     * Metadatos usados en el sitio público (solo activos).
     */
    public static function metaVigente(string $nombrePagina): ?self
    {
        return static::query()
            ->where('nombre_pagina', $nombrePagina)
            ->where('activo', 'S')
            ->first();
    }
}
