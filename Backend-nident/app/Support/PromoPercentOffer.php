<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Descuento por porcentaje (%) desde producto o cartel web_promo_item (tipo_oferta=porcentaje).
 */
class PromoPercentOffer
{
    private const SESSION_KEY = 'promo_percent_offers';

    /**
     * @return array{porcentaje:float,id_promo_item:?int,source:string}|null
     */
    public static function normalize(?float $porcentaje, ?int $idPromoItem = null, string $source = 'producto'): ?array
    {
        if ($porcentaje === null) {
            return null;
        }
        $pct = round((float) $porcentaje, 2);
        if ($pct <= 0 || $pct > 100) {
            return null;
        }

        return [
            'porcentaje' => $pct,
            'id_promo_item' => $idPromoItem,
            'source' => $source,
        ];
    }

    public static function remember(int $idProducto, array $offer): void
    {
        if ($idProducto <= 0) {
            return;
        }
        $all = session(self::SESSION_KEY, []);
        if (! is_array($all)) {
            $all = [];
        }
        $all[(string) $idProducto] = [
            'porcentaje' => (float) $offer['porcentaje'],
            'id_promo_item' => $offer['id_promo_item'] ?? null,
            'source' => $offer['source'] ?? 'session',
            'saved_at' => now()->timestamp,
        ];
        session([self::SESSION_KEY => $all]);
    }

    /** @return array{porcentaje:float,id_promo_item:?int,source:string}|null */
    public static function fromSession(int $idProducto): ?array
    {
        if ($idProducto <= 0) {
            return null;
        }
        $all = session(self::SESSION_KEY, []);
        if (! is_array($all)) {
            return null;
        }
        $row = $all[(string) $idProducto] ?? null;
        if (! is_array($row)) {
            return null;
        }

        return self::normalize(
            isset($row['porcentaje']) ? (float) $row['porcentaje'] : null,
            isset($row['id_promo_item']) ? (int) $row['id_promo_item'] : null,
            'session'
        );
    }

    /** @return array{porcentaje:float,id_promo_item:?int,source:string}|null */
    public static function fromPromoItem(object $item): ?array
    {
        $tipo = strtolower(trim((string) ($item->tipo_oferta ?? 'pack')));
        if ($tipo !== 'porcentaje') {
            return null;
        }

        return self::normalize(
            isset($item->descuento_porcentaje) ? (float) $item->descuento_porcentaje : null,
            isset($item->id_promo_item) ? (int) $item->id_promo_item : null,
            'promo_item'
        );
    }

    /** @return array{porcentaje:float,id_promo_item:?int,source:string}|null */
    public static function resolveFromCatalog(int $idProducto, ?int $idPromoItem = null, ?int $idBloque = null): ?array
    {
        if ($idProducto <= 0 || ! Schema::hasTable('web_promo_item')) {
            return null;
        }
        if (! Schema::hasColumn('web_promo_item', 'tipo_oferta')) {
            return null;
        }

        if ($idPromoItem && $idPromoItem > 0) {
            $item = DB::table('web_promo_item')
                ->where('id_promo_item', $idPromoItem)
                ->where('Activo', 'S')
                ->first();
            if ($item) {
                $offer = self::fromPromoItem($item);
                if ($offer) {
                    return $offer;
                }
            }
        }

        $query = DB::table('web_promo_item as pi')
            ->where('pi.Activo', 'S')
            ->where('pi.tipo_oferta', 'porcentaje')
            ->orderByDesc('pi.id_promo_item');

        if ($idBloque && $idBloque > 0) {
            $query->where('pi.id_promo_bloque', $idBloque);
        }

        if (Schema::hasTable('web_promo_item_producto')) {
            $item = (clone $query)
                ->join('web_promo_item_producto as pip', 'pip.id_promo_item', '=', 'pi.id_promo_item')
                ->where('pip.id_producto', $idProducto)
                ->select('pi.*')
                ->first();
            if ($item) {
                $offer = self::fromPromoItem($item);
                if ($offer) {
                    return $offer;
                }
            }
        }

        if (Schema::hasColumn('web_promo_item', 'id_producto')) {
            $item = $query->where('pi.id_producto', $idProducto)->select('pi.*')->first();
            if ($item) {
                return self::fromPromoItem($item);
            }
        }

        return null;
    }

    /**
     * Prioridad:
     * 1) Cartel / submódulo de ofertas (contexto actual o vínculo en catálogo)
     * 2) Campo % del producto
     * 3) Sesión (solo si no hay fuente viva; evita % obsoleto)
     *
     * @return array{porcentaje:float,id_promo_item:?int,source:string}|null
     */
    public static function resolveForProducto(
        int $idProducto,
        $productoPorcentaje = null,
        ?int $idPromoItem = null,
        ?int $idBloque = null
    ): ?array {
        $fromPromo = self::resolveFromCatalog($idProducto, $idPromoItem, $idBloque);
        if ($fromPromo) {
            // Mantener sesión alineada con la fuente actual (bloque/producto)
            self::remember($idProducto, $fromPromo);
            return $fromPromo;
        }

        if ($productoPorcentaje !== null && $productoPorcentaje !== '') {
            $fromProduct = self::normalize((float) $productoPorcentaje, null, 'producto');
            if ($fromProduct) {
                self::remember($idProducto, $fromProduct);
                return $fromProduct;
            }
        }

        // Sin oferta viva: limpiar sesión obsoleta y no devolver % fantasma
        self::forget($idProducto);

        return null;
    }

    public static function forget(int $idProducto): void
    {
        if ($idProducto <= 0) {
            return;
        }
        $all = session(self::SESSION_KEY, []);
        if (! is_array($all)) {
            return;
        }
        unset($all[(string) $idProducto]);
        session([self::SESSION_KEY => $all]);
    }

    public static function rememberFromLanding(int $idProducto, ?int $idBloque = null, ?int $idPromoItem = null): void
    {
        $offer = self::resolveFromCatalog($idProducto, $idPromoItem, $idBloque);
        if ($offer) {
            self::remember($idProducto, $offer);
        }
    }

    /**
     * @return array{precio_lista:float,precio_final:float,descuento:float,porcentaje:float,has_percent:bool}
     */
    public static function applyToPrice(float $precioLista, float $porcentaje): array
    {
        $lista = max(0.0, $precioLista);
        $pct = max(0.0, min(100.0, $porcentaje));
        if ($lista <= 0 || $pct <= 0) {
            return [
                'precio_lista' => $lista,
                'precio_final' => $lista,
                'descuento' => 0.0,
                'porcentaje' => 0.0,
                'has_percent' => false,
            ];
        }
        $final = round($lista * (1 - ($pct / 100)), 2);

        return [
            'precio_lista' => $lista,
            'precio_final' => $final,
            'descuento' => max(0.0, round($lista - $final, 2)),
            'porcentaje' => $pct,
            'has_percent' => true,
        ];
    }
}
