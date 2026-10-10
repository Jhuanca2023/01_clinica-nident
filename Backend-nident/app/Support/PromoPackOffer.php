<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Oferta pack (xN) desde carteles de promociones (web_promo_item).
 * Prioridad: sesión (contexto URL ofertas) → ítem promo activo → campos del producto.
 */
class PromoPackOffer
{
    private const SESSION_KEY = 'promo_pack_offers';

    /**
     * Extrae N de textos como "2X", "X2", "RELOJ X2", "3 x".
     */
    public static function parseMultiplicador(?string $raw): ?int
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (preg_match('/(\d+)\s*[xX×]/u', $raw, $m) || preg_match('/[xX×]\s*(\d+)/u', $raw, $m)) {
            $n = (int) $m[1];

            return $n >= 1 ? $n : null;
        }

        return null;
    }

    /**
     * @return array{pack_qty:int,pack_price:float,id_promo_item:?int,source:string}|null
     */
    public static function fromPromoItem(object $item): ?array
    {
        $tipo = strtolower(trim((string) ($item->tipo_oferta ?? 'pack')));
        if ($tipo === 'porcentaje') {
            return null;
        }
        $qty = self::parseMultiplicador($item->multiplicador ?? null);
        $price = (float) ($item->precio ?? 0);
        if ($qty === null || $price <= 0) {
            return null;
        }

        return [
            'pack_qty' => $qty,
            'pack_price' => round($price, 2),
            'id_promo_item' => isset($item->id_promo_item) ? (int) $item->id_promo_item : null,
            'source' => 'promo_item',
        ];
    }

    /**
     * Guarda oferta activa para un producto (viene de /tienda/ofertas/... o Compra Ahora).
     *
     * @param  array{pack_qty:int,pack_price:float,id_promo_item?:?int,source?:string}  $offer
     */
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
            'pack_qty' => (int) $offer['pack_qty'],
            'pack_price' => round((float) $offer['pack_price'], 2),
            'id_promo_item' => isset($offer['id_promo_item']) ? (int) $offer['id_promo_item'] : null,
            'source' => $offer['source'] ?? 'session',
            'saved_at' => now()->timestamp,
        ];
        session([self::SESSION_KEY => $all]);
    }

    /** @return array{pack_qty:int,pack_price:float,id_promo_item:?int,source:string}|null */
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
        $qty = (int) ($row['pack_qty'] ?? 0);
        $price = (float) ($row['pack_price'] ?? 0);
        if ($qty < 1 || $price <= 0) {
            return null;
        }

        return [
            'pack_qty' => $qty,
            'pack_price' => round($price, 2),
            'id_promo_item' => isset($row['id_promo_item']) ? (int) $row['id_promo_item'] : null,
            'source' => 'session',
        ];
    }

    /**
     * Busca un ítem promo activo vinculado al producto (pivote o legacy id_producto).
     *
     * @return array{pack_qty:int,pack_price:float,id_promo_item:?int,source:string}|null
     */
    public static function resolveFromCatalog(int $idProducto, ?int $idPromoItem = null, ?int $idBloque = null): ?array
    {
        if ($idProducto <= 0 || ! Schema::hasTable('web_promo_item')) {
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
     * Oferta pack a usar en detalle/carrito.
     * 1) Sesión (llegó desde ofertas)
     * 2) Catálogo promo activo
     * 3) Campos del producto (oferta_maxima_*)
     *
     * @return array{pack_qty:int,pack_price:float,id_promo_item:?int,source:string}|null
     */
    public static function resolveForProducto(
        int $idProducto,
        $productoPackQty = null,
        $productoPackPrice = null,
        ?int $idPromoItem = null,
        ?int $idBloque = null
    ): ?array {
        $fromSession = self::fromSession($idProducto);
        if ($fromSession) {
            return $fromSession;
        }

        $fromPromo = self::resolveFromCatalog($idProducto, $idPromoItem, $idBloque);
        if ($fromPromo) {
            return $fromPromo;
        }

        if ($productoPackQty !== null && $productoPackQty !== ''
            && $productoPackPrice !== null && $productoPackPrice !== '') {
            $qty = (int) round((float) $productoPackQty);
            $price = (float) $productoPackPrice;
            if ($qty >= 1 && $price > 0) {
                return [
                    'pack_qty' => $qty,
                    'pack_price' => round($price, 2),
                    'id_promo_item' => null,
                    'source' => 'producto',
                ];
            }
        }

        return null;
    }

    /** Recuerda oferta al entrar a /tienda/ofertas/... */
    public static function rememberFromLanding(int $idProducto, ?int $idBloque = null, ?int $idPromoItem = null): void
    {
        $offer = self::resolveFromCatalog($idProducto, $idPromoItem, $idBloque);
        if ($offer) {
            self::remember($idProducto, $offer);
        }
    }
}
