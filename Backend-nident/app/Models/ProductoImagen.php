<?php

namespace App\Models;

/**
 * Alias legacy: variantes vendibles viven en administracion_producto_fotos.
 */
class ProductoImagen extends ProductoFoto
{
    public function getIdProductoImagenAttribute(): ?int
    {
        return $this->id_producto_foto;
    }
}
