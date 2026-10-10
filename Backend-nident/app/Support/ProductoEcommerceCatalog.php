<?php

namespace App\Support;

/**
 * Catálogo ecommerce basado en tablas administracion_producto / administracion_producto_fotos.
 */
final class ProductoEcommerceCatalog
{
    public const TABLA_FOTOS = 'administracion_producto_fotos';

    /** @return array<int, array{hex: string, nombre: string}> */
    public static function paletaColoresPorDefecto(): array
    {
        return [
            ['hex' => '#1a2d5a', 'nombre' => 'Azul Marino'],
            ['hex' => '#e8d5b7', 'nombre' => 'Beige'],
            ['hex' => '#87ceeb', 'nombre' => 'Celeste'],
            ['hex' => '#8B4513', 'nombre' => 'Marrón'],
            ['hex' => '#c0c8d0', 'nombre' => 'Gris'],
            ['hex' => '#2e8b8b', 'nombre' => 'Teal'],
            ['hex' => '#2e7d32', 'nombre' => 'Verde'],
            ['hex' => '#1565c0', 'nombre' => 'Azul'],
            ['hex' => '#0d47a1', 'nombre' => 'Azul Oscuro'],
            ['hex' => '#7a8c1e', 'nombre' => 'Oliva'],
        ];
    }

    /** @return array<int, array{hex: string, nombre: string, url_imagen: ?string, id_producto_foto: ?int, codigo_producto: ?string}> */
    public static function resolverColoresUi(object $producto, iterable $fotos): array
    {
        $paleta = self::decodificarColoresProducto($producto);
        $fotosPorColor = [];
        $fotosPorOrden = [];

        foreach ($fotos as $foto) {
            $hex = self::normalizarHex((string) ($foto->color ?? ''));
            if ($hex !== '') {
                $fotosPorColor[$hex] = $foto;
            }
            $orden = (int) ($foto->orden ?? 0);
            if ($orden > 0) {
                $fotosPorOrden[$orden] = $foto;
            }
        }

        $mainUrl = trim((string) ($producto->url_imagen ?? ''));
        $mainColor = self::normalizarHex((string) ($producto->color ?? ''));

        $resultado = [];
        foreach ($paleta as $idx => $color) {
            $hex = self::normalizarHex($color['hex'] ?? '');
            $foto = $fotosPorColor[$hex] ?? $fotosPorOrden[$idx + 1] ?? null;
            $url = $foto?->url_imagen ?? ($idx === 0 ? $mainUrl : null);

            if ($url === null && $idx === 0 && $mainUrl !== '') {
                $url = $mainUrl;
            }

            $resultado[] = [
                'hex'              => $color['hex'],
                'nombre'           => $color['nombre'],
                'url_imagen'       => $url ?: null,
                'id_producto_foto' => $foto->id_producto_foto ?? null,
                'codigo_producto'  => $foto->codigo_producto ?? ($idx === 0 ? ($producto->codigo_producto ?? null) : null),
            ];
        }

        return $resultado;
    }

    /** @return array<int, string> */
    public static function resolverTallas(object $producto): array
    {
        $variantes = self::resolverTallasVariantes($producto);
        if (! empty($variantes)) {
            return array_values(array_map(static fn ($v) => (string) $v['talla'], $variantes));
        }

        $raw = trim((string) ($producto->tallas_disponibles ?? ''));
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * Tallas con precio y stock propios.
     *
     * @return array<int, array{talla: string, precio: float, stock: int}>
     */
    public static function resolverTallasVariantes(object $producto): array
    {
        $raw = $producto->tallas_variantes ?? null;
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                // [] explícito = producto sin tallas
                if (count($decoded) === 0) {
                    return [];
                }

                return self::normalizarTallasVariantes($decoded, $producto);
            }
        }
        if (is_array($raw)) {
            if (count($raw) === 0) {
                return [];
            }

            return self::normalizarTallasVariantes($raw, $producto);
        }

        // Fallback: tallas_disponibles CSV (si está vacío → sin tallas)
        $csv = trim((string) ($producto->tallas_disponibles ?? ''));
        if ($csv === '') {
            return [];
        }
        $tallas = array_values(array_filter(array_map('trim', explode(',', $csv))));
        if (empty($tallas)) {
            return [];
        }

        $precio = (float) ($producto->precio ?? 0);
        $stock = max(0, (int) ($producto->stock ?? 0));

        return array_map(static fn ($t) => [
            'talla'  => (string) $t,
            'precio' => round($precio, 2),
            'stock'  => $stock,
        ], $tallas);
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array{talla: string, precio: float, stock: int}>
     */
    public static function normalizarTallasVariantes(array $items, ?object $producto = null): array
    {
        $fallbackPrecio = (float) ($producto->precio ?? 0);
        $out = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $talla = trim($item);
                if ($talla === '') {
                    continue;
                }
                $out[] = [
                    'talla'  => $talla,
                    'precio' => round($fallbackPrecio, 2),
                    'stock'  => 0,
                ];
                continue;
            }
            if (! is_array($item)) {
                continue;
            }
            $talla = trim((string) ($item['talla'] ?? $item['nombre'] ?? ''));
            if ($talla === '') {
                continue;
            }
            $out[] = [
                'talla'  => $talla,
                'precio' => round((float) ($item['precio'] ?? $fallbackPrecio), 2),
                'stock'  => max(0, (int) ($item['stock'] ?? 0)),
            ];
        }

        return array_values($out);
    }

    public static function buscarVariantePorTalla(object $producto, ?string $talla): ?array
    {
        $talla = trim((string) $talla);
        if ($talla === '') {
            return null;
        }
        foreach (self::resolverTallasVariantes($producto) as $v) {
            if (strcasecmp((string) $v['talla'], $talla) === 0) {
                return $v;
            }
        }

        return null;
    }

    /** Variantes vendibles (filas en administracion_producto_fotos con código S-). */
    public static function listarVariantesVenta(int $idProducto): array
    {
        return \Illuminate\Support\Facades\DB::table(self::TABLA_FOTOS)
            ->where('id_producto', $idProducto)
            ->where('Activo', 'S')
            ->whereNotNull('codigo_producto')
            ->where('codigo_producto', 'like', 'S-%')
            ->orderBy('orden')
            ->get()
            ->map(fn ($f) => self::mapearFilaVariante($f))
            ->all();
    }

    /** Galería visual (todas las fotos activas del producto). */
    public static function listarGaleriaFotos(int $idProducto): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table(self::TABLA_FOTOS)
            ->where('id_producto', $idProducto)
            ->where(function ($q) {
                $q->where('Activo', 'S')->orWhereNull('Activo');
            })
            ->orderBy('orden')
            ->get();
    }

    public static function mapearFilaVariante(object $f): object
    {
        return (object) [
            'id_producto_imagen'     => $f->id_producto_foto,
            'id_producto_foto'       => $f->id_producto_foto,
            'img_descripcion'        => $f->descripcion ?? '',
            'img_titulo'             => $f->titulo ?? '',
            'img_precio'             => $f->precio ?? 0,
            'img_precio_old'         => $f->precio_old ?? null,
            'img_stock'              => $f->stock ?? 0,
            'img_url_imagen'         => $f->url_imagen ?? '',
            'img_codigo_producto'    => $f->codigo_producto ?? '',
            'img_numero_estrellas'   => $f->numero_estrellas ?? 0,
            'img_color'              => $f->color ?? '',
        ];
    }

    /** @return array<int, array{hex: string, nombre: string}> */
    private static function decodificarColoresProducto(object $producto): array
    {
        $raw = $producto->colores_disponibles ?? null;
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                // [] explícito = sin colores en la ficha
                if (count($decoded) === 0) {
                    return [];
                }

                return array_values(array_map(function ($item) {
                    return [
                        'hex'    => (string) ($item['hex'] ?? '#888888'),
                        'nombre' => (string) ($item['nombre'] ?? 'Color'),
                    ];
                }, $decoded));
            }
        }
        if (is_array($raw)) {
            if (count($raw) === 0) {
                return [];
            }

            return array_values(array_map(function ($item) {
                return [
                    'hex'    => (string) ($item['hex'] ?? '#888888'),
                    'nombre' => (string) ($item['nombre'] ?? 'Color'),
                ];
            }, $raw));
        }

        return [];
    }

    private static function normalizarHex(string $hex): string
    {
        $hex = strtolower(trim($hex));
        if ($hex === '') {
            return '';
        }
        if ($hex[0] !== '#') {
            $hex = '#' . $hex;
        }

        return $hex;
    }
}
