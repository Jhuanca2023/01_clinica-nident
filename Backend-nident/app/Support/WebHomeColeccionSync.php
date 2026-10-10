<?php

namespace App\Support;

use App\Models\Producto;
use App\Models\WebHomeColeccion;
use Illuminate\Support\Facades\Schema;

/**
 * Sincroniza web_home_coleccion con el catálogo que la web muestra por defecto
 * cuando la tabla de colección está vacía (misma lógica que LiamPageData::listarProductosHome).
 */
class WebHomeColeccionSync
{
    public static function defaultLimit(): int
    {
        return 8;
    }

    /**
     * @return int Cantidad de productos insertados o actualizados en la colección
     */
    public static function syncFromCatalogFallback(int $limit = 8, bool $onlyIfEmpty = true): int
    {
        if (! Schema::hasTable('web_home_coleccion')) {
            return 0;
        }

        if ($onlyIfEmpty && WebHomeColeccion::where('Activo', 'S')->exists()) {
            return 0;
        }

        $ids = Producto::query()
            ->where('Activo', 'S')
            ->orderBy('id_producto')
            ->limit($limit)
            ->pluck('id_producto');

        if ($ids->isEmpty()) {
            return 0;
        }

        $orden = 1;
        foreach ($ids as $idProducto) {
            WebHomeColeccion::updateOrCreate(
                ['id_producto' => $idProducto],
                ['orden' => $orden, 'Activo' => 'S']
            );
            $orden++;
        }

        return $ids->count();
    }
}
