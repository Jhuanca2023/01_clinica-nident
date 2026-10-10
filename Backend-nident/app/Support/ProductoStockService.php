<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Descuento y sincronización de stock de productos (general vs por talla).
 *
 * Reglas:
 * - Con medidas (tallas_variantes): se descuenta la talla comprada y stock = suma de tallas.
 * - Sin medidas: se descuenta solo el stock general.
 */
class ProductoStockService
{
    /**
     * Descuenta unidades tras una venta confirmada (p. ej. pasarela online).
     *
     * @throws RuntimeException si no hay stock suficiente
     */
    public static function descontarVenta(int $idProducto, int $qty, ?string $talla = null): void
    {
        $qty = max(0, (int) $qty);
        if ($qty < 1 || $idProducto < 1) {
            return;
        }

        $producto = DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->lockForUpdate()
            ->first();

        if (! $producto) {
            throw new RuntimeException('Producto no encontrado al descontar stock.');
        }

        $talla = strtoupper(trim((string) $talla));
        $variantes = self::variantesActivas($producto);

        if (! empty($variantes)) {
            if ($talla === '') {
                throw new RuntimeException(
                    'El producto tiene medidas/tallas: se requiere la talla comprada para descontar stock.'
                );
            }

            $encontrada = false;
            foreach ($variantes as &$v) {
                if (strcasecmp((string) $v['talla'], $talla) === 0) {
                    $encontrada = true;
                    $stockTalla = (int) $v['stock'];
                    if ($stockTalla < $qty) {
                        throw new RuntimeException(
                            "Stock insuficiente en talla {$talla}. Disponible: {$stockTalla}."
                        );
                    }
                    $v['stock'] = $stockTalla - $qty;
                    break;
                }
            }
            unset($v);

            if (! $encontrada) {
                throw new RuntimeException("La talla \"{$talla}\" no existe en el producto.");
            }

            self::persistirVariantesYStock($idProducto, $variantes);

            return;
        }

        $stockActual = max(0, (int) ($producto->stock ?? 0));
        if ($stockActual < $qty) {
            throw new RuntimeException(
                'Stock insuficiente al confirmar el pedido. Actualice el carrito e intente de nuevo.'
            );
        }

        $filas = DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->where('stock', '>=', $qty)
            ->update(['stock' => DB::raw('stock - ' . $qty)]);

        if ($filas === 0) {
            throw new RuntimeException(
                'Stock insuficiente al confirmar el pedido. Actualice el carrito e intente de nuevo.'
            );
        }
    }

    /**
     * Si el producto tiene tallas, fuerza stock general = suma de stocks por talla.
     */
    public static function sincronizarStockGeneral(int $idProducto): void
    {
        if ($idProducto < 1 || ! Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            return;
        }

        $producto = DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->first();

        if (! $producto) {
            return;
        }

        $variantes = self::variantesActivas($producto);
        if (empty($variantes)) {
            return;
        }

        self::persistirVariantesYStock($idProducto, $variantes);
    }

    /**
     * @return array<int, array{talla: string, precio: float, stock: int}>
     */
    private static function variantesActivas(object $producto): array
    {
        if (! Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            return [];
        }

        // Solo JSON explícito de variantes con stock propio.
        // No usar fallback CSV (asignaría el stock total a cada talla).
        $raw = $producto->tallas_variantes ?? null;
        if ($raw === null || $raw === '' || $raw === '[]' || $raw === 'null') {
            return [];
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (! is_array($decoded) || count($decoded) === 0) {
                return [];
            }

            return ProductoEcommerceCatalog::normalizarTallasVariantes($decoded, $producto);
        }

        if (is_array($raw) && count($raw) > 0) {
            return ProductoEcommerceCatalog::normalizarTallasVariantes($raw, $producto);
        }

        return [];
    }

    /**
     * @param  array<int, array{talla: string, precio: float, stock: int}>  $variantes
     */
    private static function persistirVariantesYStock(int $idProducto, array $variantes): void
    {
        $suma = 0;
        foreach ($variantes as $v) {
            $suma += max(0, (int) ($v['stock'] ?? 0));
        }

        $data = [
            'stock' => $suma,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            $data['tallas_variantes'] = json_encode(array_values($variantes), JSON_UNESCAPED_UNICODE);
        }

        if (Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
            $data['tallas_disponibles'] = implode(',', array_map(
                static fn ($v) => (string) $v['talla'],
                $variantes
            ));
        }

        DB::table('administracion_producto')
            ->where('id_producto', $idProducto)
            ->update($data);
    }
}
