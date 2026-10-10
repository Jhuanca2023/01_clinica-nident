<?php

namespace App\Support;

use App\Models\ProductoCategoria;
use App\Models\ProductoCategoriaSub;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * URLs SEO de tienda por categoría: /tienda/{slug-categoria}/{slug-sub?}
 */
class CategoriaTiendaUrl
{
    public static function slugFromNombre(string $nombre, string $fallback = 'categoria'): string
    {
        $slug = Str::slug(trim($nombre));
        if ($slug === '' || $slug === 'ofertas') {
            $slug = $fallback;
        }

        return $slug;
    }

    public static function categorySlug(?ProductoCategoria $cat): string
    {
        if (! $cat) {
            return 'categoria';
        }
        $slug = trim((string) ($cat->slug ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        return self::slugFromNombre((string) $cat->nombre, 'categoria-' . (int) $cat->id_producto_categoria);
    }

    public static function subcategorySlug(?ProductoCategoriaSub $sub): string
    {
        if (! $sub) {
            return 'subcategoria';
        }
        $slug = trim((string) ($sub->slug ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        return self::slugFromNombre((string) $sub->nombre, 'sub-' . (int) $sub->id_producto_categoria_sub);
    }

    public static function tienda(?ProductoCategoria $cat, ?ProductoCategoriaSub $sub = null): string
    {
        if (! $cat) {
            return url('/tienda');
        }
        $path = '/tienda/' . self::categorySlug($cat);
        if ($sub) {
            $path .= '/' . self::subcategorySlug($sub);
        }

        return url($path);
    }

    public static function tiendaById(int $idCategoria, ?int $idSub = null): string
    {
        $cat = ProductoCategoria::find($idCategoria);
        $sub = ($idSub && $cat)
            ? ProductoCategoriaSub::where('id_producto_categoria', $idCategoria)
                ->where('id_producto_categoria_sub', $idSub)
                ->first()
            : null;

        return self::tienda($cat, $sub);
    }

    public static function hasSlugColumn(): bool
    {
        return Schema::hasColumn('administracion_producto_categoria', 'slug');
    }
}
