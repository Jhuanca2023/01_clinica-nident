<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductoCategoriaSub extends Model
{
    use HasFactory;

    protected $table = 'administracion_producto_categoria_sub';
    protected $primaryKey = 'id_producto_categoria_sub';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $guarded = [];

    protected static function booted()
    {
        static::saving(function (ProductoCategoriaSub $sub) {
            if (! Schema::hasColumn('administracion_producto_categoria_sub', 'slug')) {
                return;
            }

            $expected = Str::slug((string) ($sub->nombre ?? ''));
            $current = trim((string) ($sub->slug ?? ''));
            $needsSlug = $current === ''
                || $sub->isDirty('nombre')
                || ctype_digit($current);

            if ($needsSlug && $expected !== '') {
                $sub->slug = static::uniqueSlugFromNombre(
                    (string) $sub->nombre,
                    (int) ($sub->id_producto_categoria ?? 0),
                    (int) ($sub->id_producto_categoria_sub ?? 0)
                );
            }
        });
    }

    public static function uniqueSlugFromNombre(string $nombre, int $idCategoria = 0, int $exceptId = 0): string
    {
        $base = Str::slug($nombre) ?: ($exceptId > 0 ? 'sub-' . $exceptId : 'subcategoria');
        $slug = $base;
        $n = 2;
        while (
            static::query()
                ->when($idCategoria > 0, fn ($q) => $q->where('id_producto_categoria', $idCategoria))
                ->where('slug', $slug)
                ->when($exceptId > 0, fn ($q) => $q->where('id_producto_categoria_sub', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }
}
