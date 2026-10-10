<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Detalle de "Polo Laboral Atlas" según la plantilla temp02 (shop-detail.html):
 * galería de 4 vistas, 5 colores (cada uno con su foto) y descripción.
 */
return new class extends Migration
{
    private const COLORES = [
        // nombre, hex, imagen (la primera es la imagen principal del producto)
        ['Azul Marino', '#1a2d5a', 'temp02/img/productos/polo/polo-azul-marino.jpg'],
        ['Gris Carbón', '#4a4d52', 'temp02/img/productos/polo/polo-gris-carbon.jpg'],
        ['Celeste',     '#8fc3e6', 'temp02/img/productos/polo/polo-celeste.jpg'],
        ['Burdeos',     '#6d1f2c', 'temp02/img/productos/polo/polo-burdeos.jpg'],
        ['Blanco',      '#ffffff', 'temp02/img/productos/polo/polo-blanco.jpg'],
    ];

    private const DESCRIPCION = "El Polo Laboral Atlas de Valento está pensado para uniformes de trabajo y para personalizar con el logo de tu empresa. Tejido resistente, tallaje amplio y reposición desde stock permanente.\n"
        . "• Uso: ropa laboral y textil promocional\n"
        . "• Corte regular, cómodo en jornada completa\n"
        . "• Cuello reforzado para bordado o estampado\n"
        . "• Costuras dobles en mangas y dobladillo\n"
        . "• Tallas y colores en serie para colectividades\n"
        . "Ideal para equipos de industria, servicios y promociones. Cotización mayorista por WhatsApp al +51 964 381 275.";

    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto') || ! Schema::hasTable('administracion_producto_fotos')) {
            return;
        }

        $producto = DB::table('administracion_producto')->where('slug', 'polo-laboral-atlas')->first();
        if (! $producto) {
            return;
        }

        DB::transaction(function () use ($producto) {
            $now = now();

            DB::table('administracion_producto')->where('id_producto', $producto->id_producto)->update([
                'descripcion'         => self::DESCRIPCION,
                'url_imagen'          => self::COLORES[0][2],
                'color'               => self::COLORES[0][1],
                'colores_disponibles' => json_encode(
                    array_map(fn ($c) => ['hex' => $c[1], 'nombre' => $c[0]], self::COLORES),
                    JSON_UNESCAPED_UNICODE
                ),
                'updated_at'          => $now,
            ]);

            // Galería: una foto por color adicional (el color se enlaza por hex)
            DB::table('administracion_producto_fotos')->where('id_producto', $producto->id_producto)->delete();
            foreach (array_slice(self::COLORES, 1) as $i => [$nombre, $hex, $imagen]) {
                DB::table('administracion_producto_fotos')->insert([
                    'id_producto' => $producto->id_producto,
                    'url_imagen'  => $imagen,
                    'orden'       => $i + 2,
                    'titulo'      => $producto->nombre . ' — ' . $nombre,
                    'color'       => $hex,
                    'Activo'      => 'S',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
