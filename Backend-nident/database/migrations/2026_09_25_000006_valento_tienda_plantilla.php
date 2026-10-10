<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tienda (/tienda) según la plantilla temp02 (categories.html + js/categories.js):
 * 12 productos con su color y tallas, para que los filtros coincidan con la plantilla.
 */
return new class extends Migration
{
    private const PRODUCTOS = [
        // slug, código, nombre, categoría (slug), precio, imagen, color [nombre, hex], tallas
        ['polo-laboral-atlas',         'PV001', 'Polo Laboral Atlas',         'ropa-laboral',        33.00, 'temp02/img/productos/polo/polo-azul-marino.jpg', ['Azul', '#1a2d5a'],   ['S', 'M', 'L', 'XL']],
        ['camiseta-promocional-norte', 'PV002', 'Camiseta Promocional Norte', 'camisetas',           33.00, 'temp02/img/productos/polo/camiseta-blanca-logo.jpg', ['Blanco', '#ffffff'], ['M', 'L', 'XL']],
        ['polo-tecnico-dry-sport',     'PV003', 'Polo Técnico Dry Sport',     'vestuario-deportivo', 20.00, 'temp02/img/productos/polo/polo-celeste.jpg', ['Azul', '#8fc3e6'],   ['S', 'M', 'L']],
        ['camisa-oxford-uniforme',     'PV004', 'Camisa Oxford Uniforme',     'uniformes',           33.00, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80', ['Beige', '#c2b280'], ['S', 'M', 'L', 'XL']],
        ['softshell-alta-visibilidad', 'PV005', 'Softshell Alta Visibilidad', 'proteccion-personal', 33.00, 'https://images.unsplash.com/photo-1594938291221-94f18cbb5660?auto=format&fit=crop&w=800&q=80', ['Negro', '#222222'], ['M', 'L', 'XL']],
        ['pantalon-cargo-industrial',  'PV006', 'Pantalón Cargo Industrial',  'ropa-laboral',        19.00, 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=800&q=80', ['Gris', '#4a4d52'], ['S', 'M']],
        ['polo-manga-larga-classic',   'PV007', 'Polo Manga Larga Classic',   'polos-promocionales', 30.00, 'temp02/img/productos/polo/polo-gris-carbon.jpg', ['Gris', '#4a4d52'],   ['S', 'M', 'L', 'XL']],
        ['chaqueta-polar-work',        'PV008', 'Chaqueta Polar Work',        'abrigo-tecnico',      30.00, 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=800&q=80', ['Negro', '#222222'], ['M', 'L', 'XL']],
        ['polo-pique-basic-azul',      'PV009', 'Polo Piqué Basic Azul',      'polos-promocionales', 25.00, 'temp02/img/productos/polo/polo-celeste.jpg', ['Azul', '#8fc3e6'],   ['S', 'M', 'L', 'XL', '2XL']],
        ['polo-pique-basic-blanco',    'PV010', 'Polo Piqué Basic Blanco',    'polos-promocionales', 22.00, 'temp02/img/productos/polo/polo-blanco.jpg', ['Blanco', '#ffffff'],  ['S', 'M', 'L']],
        ['camiseta-deportiva-roja',    'PV011', 'Camiseta Deportiva Roja',    'vestuario-deportivo', 28.00, 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=800&q=80', ['Rojo', '#c0392b'], ['M', 'L', 'XL']],
        ['polo-tecnico-premium',       'PV012', 'Polo Técnico Premium',       'proteccion-personal', 45.00, 'temp02/img/productos/polo/polo-burdeos.jpg', ['Rojo', '#6d1f2c'],   ['S', 'M', 'L', 'XL']],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto') || ! Schema::hasTable('administracion_producto_categoria')) {
            return;
        }

        DB::transaction(function () {
            $now = now();
            $categorias = DB::table('administracion_producto_categoria')->pluck('id_producto_categoria', 'slug');

            foreach (self::PRODUCTOS as [$slug, $codigo, $nombre, $cat, $precio, $imagen, $color, $tallas]) {
                $variantes = array_map(
                    fn ($talla) => ['stock' => 10, 'talla' => $talla, 'precio' => $precio],
                    $tallas
                );
                $datos = [
                    'codigo_producto'       => $codigo,
                    'nombre'                => $nombre,
                    'precio'                => $precio,
                    'id_producto_categoria' => $categorias[$cat] ?? null,
                    'url_imagen'            => $imagen,
                    'color'                 => $color[1],
                    'tallas_disponibles'    => implode(',', $tallas),
                    'tallas_variantes'      => json_encode($variantes),
                    'colores_disponibles'   => json_encode([['hex' => $color[1], 'nombre' => $color[0]]], JSON_UNESCAPED_UNICODE),
                    'stock'                 => 10 * count($tallas),
                    'Activo'                => 'S',
                    'updated_at'            => $now,
                ];

                $existente = DB::table('administracion_producto')->where('slug', $slug)->first();
                if ($existente) {
                    DB::table('administracion_producto')->where('id_producto', $existente->id_producto)->update($datos);
                } else {
                    DB::table('administracion_producto')->insert($datos + [
                        'slug'        => $slug,
                        'descripcion' => $nombre . ' — Valento. Prenda lista para personalizar con el logo de tu empresa.',
                        'created_at'  => $now,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        //
    }
};
