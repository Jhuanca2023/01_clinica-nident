<?php

namespace App\Support;

use App\Models\WebPopupPromo;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

/**
 * Descuento global del popup (modo oferta_tienda) aplicado con "Comprar".
 * Vigente en sesión/cookie hasta fecha_fin del anuncio.
 */
class PopupPromoDiscount
{
    public const SESSION_KEY = 'lv_popup_promo_discount';

    public const COOKIE_KEY = 'lv_popup_promo_applied';

    /** Cookie/storage: el cliente ya aceptó la oferta → no volver a mostrar el popup. */
    public const COOKIE_APPLIED_UI = 'lv_popup_promo_claimed';

    /**
     * Parsea "15% OFF", "15%", "15" → float.
     */
    public static function parsePorcentaje(?string $texto, $explicit = null): ?float
    {
        if ($explicit !== null && $explicit !== '') {
            $pct = round((float) $explicit, 2);
            if ($pct > 0 && $pct <= 100) {
                return $pct;
            }
        }
        $texto = trim((string) $texto);
        if ($texto === '') {
            return null;
        }
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*%/', $texto, $m)) {
            $pct = round((float) str_replace(',', '.', $m[1]), 2);
            if ($pct > 0 && $pct <= 100) {
                return $pct;
            }
        }

        return null;
    }

    public static function popupVigente(): ?WebPopupPromo
    {
        $popup = WebPopupPromo::query()->first();
        if (! $popup || ($popup->activo ?? 'N') !== 'S') {
            return null;
        }
        if (! self::estaEnRangoFechas($popup)) {
            return null;
        }

        return $popup;
    }

    /**
     * Vigencia estricta: sin fecha inicio o fin → no vigente.
     * Fuera del rango (aún no empieza o ya caducó) → no vigente.
     */
    public static function estaEnRangoFechas(WebPopupPromo $popup): bool
    {
        $iniRaw = $popup->fecha_inicio ?? null;
        $finRaw = $popup->fecha_fin ?? null;

        if ($iniRaw === null || $iniRaw === '' || $finRaw === null || $finRaw === '') {
            return false;
        }

        try {
            $ini = \Carbon\Carbon::parse($iniRaw);
            $fin = \Carbon\Carbon::parse($finRaw);
        } catch (\Throwable $e) {
            return false;
        }

        $now = now();

        return $now->gte($ini) && $now->lte($fin);
    }

    /**
     * Activa el descuento hasta fecha_fin (o 7 días si no hay fin).
     *
     * @return array{porcentaje:float,expires_at:string}|null
     */
    public static function activarDesdePopup(?WebPopupPromo $popup = null): ?array
    {
        $popup = $popup ?: self::popupVigente();
        if (! $popup) {
            return null;
        }

        $pct = self::parsePorcentaje(
            $popup->descuento_texto ?? null,
            $popup->descuento_porcentaje ?? null
        );
        if ($pct === null) {
            return null;
        }

        $expires = null;
        if (! empty($popup->fecha_fin)) {
            try {
                $expires = \Carbon\Carbon::parse($popup->fecha_fin);
            } catch (\Throwable $e) {
                $expires = null;
            }
        }
        if (! $expires || $expires->isPast()) {
            $expires = now()->addDays(7);
        }

        $payload = [
            'porcentaje' => $pct,
            'expires_at' => $expires->toIso8601String(),
            'id_popup' => (int) ($popup->id_popup ?? 1),
            'activated_at' => now()->toIso8601String(),
        ];

        Session::put(self::SESSION_KEY, $payload);

        $minutes = max(1, (int) ceil($expires->diffInSeconds(now()) / 60));
        Cookie::queue(cookie(
            self::COOKIE_KEY,
            json_encode($payload),
            $minutes,
            '/',
            null,
            false,
            false, // readable by JS too
            false,
            'Lax'
        ));
        Cookie::queue(cookie(
            self::COOKIE_APPLIED_UI,
            '1',
            $minutes,
            '/',
            null,
            false,
            false,
            false,
            'Lax'
        ));

        return $payload;
    }

    /** @return array{porcentaje:float,expires_at:string}|null */
    public static function activePayload(): ?array
    {
        $payload = Session::get(self::SESSION_KEY);
        if (! is_array($payload)) {
            $raw = request()->cookie(self::COOKIE_KEY);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                    Session::put(self::SESSION_KEY, $payload);
                }
            }
        }
        if (! is_array($payload)) {
            return null;
        }

        $expiresAt = $payload['expires_at'] ?? null;
        if ($expiresAt) {
            try {
                if (now()->gt(\Carbon\Carbon::parse($expiresAt))) {
                    self::clear();

                    return null;
                }
            } catch (\Throwable $e) {
                // keep
            }
        }

        // Si el anuncio ya no está vigente, no aplicar
        $popup = WebPopupPromo::query()->first();
        if ($popup && ! self::estaEnRangoFechas($popup)) {
            self::clear();

            return null;
        }

        $pct = isset($payload['porcentaje']) ? (float) $payload['porcentaje'] : 0;
        if ($pct <= 0 || $pct > 100) {
            return null;
        }

        return $payload;
    }

    public static function activePercent(): ?float
    {
        $p = self::activePayload();

        return $p ? (float) $p['porcentaje'] : null;
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        Cookie::queue(Cookie::forget(self::COOKIE_KEY));
        Cookie::queue(Cookie::forget(self::COOKIE_APPLIED_UI));
    }

    /**
     * ¿El producto está "libre" (sin descuento/oferta propia)?
     */
    public static function productoEsLibre(object $producto): bool
    {
        $id = (int) ($producto->id_producto ?? 0);
        if ($id <= 0) {
            return false;
        }

        $pctProd = isset($producto->descuento_porcentaje) ? (float) $producto->descuento_porcentaje : 0;
        if ($pctProd > 0) {
            return false;
        }

        $precio = (float) ($producto->precio ?? 0);
        $precioOld = (float) ($producto->precio_old ?? 0);
        if ($precioOld > $precio && $precioOld > 0) {
            return false;
        }

        // Pack / ofertas de bloque ya resueltas en card vía PromoPackOffer
        if (PromoPackOffer::resolveForProducto(
            $id,
            $producto->oferta_maxima_cantidad ?? null,
            $producto->oferta_maxima_cantidad_por_precio ?? null
        )) {
            return false;
        }

        if (PromoPercentOffer::resolveFromCatalog($id)) {
            return false;
        }

        if (Schema::hasTable('oferta_del_dia')) {
            $q = DB::table('oferta_del_dia')->where('id_producto', $id);
            if (Schema::hasColumn('oferta_del_dia', 'Activo')) {
                $q->where('Activo', 'S');
            }
            if ($q->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * % a aplicar en card/detalle si el producto es libre y hay oferta popup activa.
     */
    public static function percentForLibreProducto(object $producto): ?float
    {
        $pct = self::activePercent();
        if ($pct === null) {
            return null;
        }
        if (! self::productoEsLibre($producto)) {
            return null;
        }

        return $pct;
    }
}
