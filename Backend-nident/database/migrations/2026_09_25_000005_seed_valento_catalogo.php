<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo Valento según la plantilla temp02 (index.html):
 * - Categorías del carrusel (.lv-categories-section)
 * - Productos de "Líneas Valento" (.latest-products)
 * Los productos y categorías del cliente anterior se desactivan (Activo = 'N'), no se borran,
 * para no romper pedidos históricos.
 */
return new class extends Migration
{
    private const CATEGORIAS = [
        // nombre, slug, imagen
        ['Ropa laboral',        'ropa-laboral',        'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Polos promocionales', 'polos-promocionales', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Camisetas',           'camisetas',           'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Vestuario deportivo', 'vestuario-deportivo', 'https://images.unsplash.com/photo-1519058082700-08a0b56da9b4?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Protección personal', 'proteccion-personal', 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Uniformes',           'uniformes',           'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Abrigo técnico',      'abrigo-tecnico',      'https://images.unsplash.com/photo-1574180566232-aaad1b5b8450?auto=format&fit=crop&w=600&h=600&q=75'],
        ['Personalización',     'personalizacion',     'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=600&h=600&q=75'],
    ];

    private const PRODUCTOS = [
        // código, nombre, slug, categoría (slug), precio, badge, imagen, color [nombre, hex]
        ['PV001', 'Polo Laboral Atlas',          'polo-laboral-atlas',          'ropa-laboral',        33.00, 'NUEVO',    'temp02/img/productos/polo/polo-azul-marino.jpg', ['Azul Marino', '#1a2d5a']],
        ['PV002', 'Camiseta Promocional Norte',  'camiseta-promocional-norte',  'camisetas',           33.00, 'NUEVO',    'temp02/img/productos/polo/camiseta-blanca-logo.jpg', ['Blanco', '#ffffff']],
        ['PV003', 'Polo Técnico Dry Sport',      'polo-tecnico-dry-sport',      'vestuario-deportivo', 20.00, 'NUEVO',    'temp02/img/productos/polo/polo-celeste.jpg', ['Celeste', '#8fc3e6']],
        ['PV004', 'Camisa Oxford Uniforme',      'camisa-oxford-uniforme',      'uniformes',           33.00, 'POPULAR',  'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80', ['Azul', '#4a6fa5']],
        ['PV005', 'Softshell Alta Visibilidad',  'softshell-alta-visibilidad',  'proteccion-personal', 33.00, 'NUEVO',    'https://images.unsplash.com/photo-1594938291221-94f18cbb5660?auto=format&fit=crop&w=800&q=80', ['Naranja', '#ff7a00']],
        ['PV006', 'Pantalón Cargo Industrial',   'pantalon-cargo-industrial',   'ropa-laboral',        19.00, 'NUEVO',    'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=800&q=80', ['Beige', '#c2b280']],
        ['PV007', 'Polo Manga Larga Classic',    'polo-manga-larga-classic',    'polos-promocionales', 30.00, null,       'temp02/img/productos/polo/polo-gris-carbon.jpg', ['Gris Carbón', '#4a4d52']],
        ['PV008', 'Chaqueta Polar Work',         'chaqueta-polar-work',         'abrigo-tecnico',      30.00, 'LIMITADO', 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=800&q=80', ['Negro', '#222222']],
    ];

    private const TALLAS = ['S', 'M', 'L', 'XL', 'XXL'];

    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto_categoria') || ! Schema::hasTable('administracion_producto')) {
            return;
        }

        DB::transaction(fn () => $this->seed());
    }

    private function seed(): void
    {
        $now = now();
        $valentoCats = array_column(self::CATEGORIAS, 1);
        $valentoProds = array_column(self::PRODUCTOS, 2);

        // 1. Desactivar catálogo anterior
        DB::table('administracion_producto_categoria')
            ->whereNotIn('slug', $valentoCats)
            ->update(['Activo' => 'N', 'mostrar_panel_principal' => 'N', 'updated_at' => $now]);

        DB::table('administracion_producto')
            ->whereNotIn('slug', $valentoProds)
            ->update(['Activo' => 'N', 'updated_at' => $now]);

        // 2. Categorías. El home las ordena por id descendente: se insertan al revés
        $idCategoria = [];
        foreach (array_reverse(self::CATEGORIAS) as [$nombre, $slug, $imagen]) {
            $datos = [
                'nombre'                  => $nombre,
                'url_imagen'              => $imagen,
                'Activo'                  => 'S',
                'mostrar_panel_principal' => 'S',
                'updated_at'              => $now,
            ];
            $existente = DB::table('administracion_producto_categoria')->where('slug', $slug)->first();
            if ($existente) {
                DB::table('administracion_producto_categoria')
                    ->where('id_producto_categoria', $existente->id_producto_categoria)
                    ->update($datos);
                $idCategoria[$slug] = $existente->id_producto_categoria;
            } else {
                // La PK de esta tabla no es AUTO_INCREMENT
                $id = (int) DB::table('administracion_producto_categoria')->max('id_producto_categoria') + 1;
                DB::table('administracion_producto_categoria')
                    ->insert($datos + ['id_producto_categoria' => $id, 'slug' => $slug, 'created_at' => $now]);
                $idCategoria[$slug] = $id;
            }
        }

        // 3. Productos
        $idProducto = [];
        foreach (self::PRODUCTOS as [$codigo, $nombre, $slug, $cat, $precio, $badge, $imagen, $color]) {
            $variantes = array_map(
                fn ($talla) => ['stock' => 10, 'talla' => $talla, 'precio' => $precio],
                self::TALLAS
            );
            $datos = [
                'codigo_producto'       => $codigo,
                'nombre'                => $nombre,
                'descripcion'           => $nombre . ' — Valento. Prenda lista para personalizar con el logo de tu empresa.',
                'precio'                => $precio,
                'stock'                 => 10 * count(self::TALLAS),
                'etiqueta_badge'        => $badge,
                'id_producto_categoria' => $idCategoria[$cat],
                'url_imagen'            => $imagen,
                'color'                 => $color[1],
                'tallas_disponibles'    => implode(',', self::TALLAS),
                'tallas_variantes'      => json_encode($variantes),
                'colores_disponibles'   => json_encode([['hex' => $color[1], 'nombre' => $color[0]]], JSON_UNESCAPED_UNICODE),
                'Activo'                => 'S',
                'updated_at'            => $now,
            ];
            $existente = DB::table('administracion_producto')->where('slug', $slug)->first();
            if ($existente) {
                DB::table('administracion_producto')->where('id_producto', $existente->id_producto)->update($datos);
                $idProducto[] = $existente->id_producto;
            } else {
                $idProducto[] = DB::table('administracion_producto')
                    ->insertGetId($datos + ['slug' => $slug, 'created_at' => $now]);
            }
        }

        // 4. Productos del home ("Líneas Valento"), en el orden de la plantilla
        if (Schema::hasTable('web_home_coleccion')) {
            DB::table('web_home_coleccion')->update(['Activo' => 'N', 'updated_at' => $now]);
            foreach ($idProducto as $i => $id) {
                DB::table('web_home_coleccion')->updateOrInsert(
                    ['id_producto' => $id],
                    ['orden' => $i + 1, 'Activo' => 'S', 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        // Seed de contenido: no se revierte.
    }
};
