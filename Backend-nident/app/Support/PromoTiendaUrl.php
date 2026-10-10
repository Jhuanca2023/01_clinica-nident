<?php

namespace App\Support;

use App\Models\Producto;
use App\Models\WebPromoBloque;
use Illuminate\Support\Str;

class PromoTiendaUrl
{
    /** Slug SEO del bloque de ofertas. */
    public static function bloqueSlug(?WebPromoBloque $bloque): string
    {
        if (! $bloque) {
            return 'ofertas-promociones';
        }
        $slug = trim((string) ($bloque->slug ?? ''));
        if ($slug !== '') {
            return $slug;
        }
        $generated = Str::slug($bloque->subtitulo_seccion ?: $bloque->titulo_seccion ?: 'ofertas-promociones');

        return $generated !== '' ? $generated : 'ofertas-promociones';
    }

    /** URL amigable: /tienda/ofertas/{bloque} o /tienda/ofertas/{bloque}/{producto} */
    public static function tiendaOfertas(?WebPromoBloque $bloque, ?Producto $producto = null, ?int $idProducto = null): string
    {
        if (! $bloque) {
            return url('/tienda');
        }

        $path = '/tienda/ofertas/' . self::bloqueSlug($bloque);

        $prod = $producto;
        if (! $prod && $idProducto) {
            $prod = Producto::select('id_producto', 'slug', 'nombre')
                ->where('id_producto', $idProducto)
                ->where('Activo', 'S')
                ->first();
        }

        if ($prod) {
            $pSlug = trim((string) ($prod->slug ?? ''));
            if ($pSlug === '') {
                $pSlug = Str::slug($prod->nombre ?? '') ?: (string) $prod->id_producto;
            }
            $path .= '/' . $pSlug;
        }

        return url($path);
    }
}
