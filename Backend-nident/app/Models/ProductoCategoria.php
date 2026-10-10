<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductoCategoria extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table = 'administracion_producto_categoria';
    protected $primaryKey = 'id_producto_categoria';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $guarded = [];

    protected static function booted()
    {
        static::saving(function (ProductoCategoria $cat) {
            if (! Schema::hasColumn('administracion_producto_categoria', 'slug')) {
                return;
            }

            $expected = Str::slug((string) ($cat->nombre ?? ''));
            if ($expected === 'ofertas') {
                $expected = 'categoria-ofertas';
            }
            $current = trim((string) ($cat->slug ?? ''));

            $needsSlug = $current === ''
                || $cat->isDirty('nombre')
                || ctype_digit($current)
                || ($expected !== '' && ! str_starts_with($current, $expected) && $expected !== '');

            if ($needsSlug && $expected !== '') {
                $cat->slug = static::uniqueSlugFromNombre(
                    (string) $cat->nombre,
                    (int) ($cat->id_producto_categoria ?? 0)
                );
            }
        });
    }

    public static function uniqueSlugFromNombre(string $nombre, int $exceptId = 0): string
    {
        $base = Str::slug($nombre);
        if ($base === '' || $base === 'ofertas') {
            $base = $exceptId > 0 ? 'categoria-' . $exceptId : 'categoria';
        }

        $slug = $base;
        $n = 2;
        while (
            static::where('slug', $slug)
                ->when($exceptId > 0, fn ($q) => $q->where('id_producto_categoria', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }
}
