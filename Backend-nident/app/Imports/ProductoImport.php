<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Define la estructura esperada del Excel de productos.
 * Cabeceras: nombre_categoria, nombre_sub_categoria, codigo_producto, nombre, descripcion,
 * precio_costo, precio_normal, precio_descuento, precio_mayorista, stock
 */
class ProductoImport implements WithHeadingRow
{
    public function headingRow(): int
    {
        return 1;
    }
}
