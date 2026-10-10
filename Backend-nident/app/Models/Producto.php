<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductoCategoria;
use App\Models\ProductoCategoriaSub;
use Illuminate\Support\Str;

class Producto extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['imagen'];
protected $table = 'administracion_producto';
    protected $primaryKey = 'id_producto';
    protected $guarded = [];
    protected $appends = ['rating_summary'];

    protected static function booted()
    {
        static::saving(function ($producto) {
            $expected = Str::slug((string) ($producto->nombre ?? ''));
            $current  = trim((string) ($producto->slug ?? ''));

            $needsSlug = $current === ''
                || $producto->isDirty('nombre')
                || ctype_digit($current)
                || ($expected !== '' && ! str_starts_with($current, $expected));

            if ($needsSlug && $expected !== '') {
                $producto->slug = static::uniqueSlugFromNombre(
                    (string) $producto->nombre,
                    (int) ($producto->id_producto ?? 0)
                );
            }
        });
    }

    /**
     * Genera un slug SEO único a partir del nombre del producto (guiones, sin acentos).
     */
    public static function uniqueSlugFromNombre(string $nombre, int $exceptId = 0): string
    {
        $base = Str::slug($nombre);
        if ($base === '') {
            $base = 'producto';
        }

        $slug = $base;
        $n = 2;
        while (
            static::where('slug', $slug)
                ->when($exceptId > 0, fn ($q) => $q->where('id_producto', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    public function userRatings()
    {
        return $this->hasMany(ProductUserRating::class, 'id_producto', 'id_producto');
    }

    public function getRatingSummaryAttribute()
    {
        if (!$this->ratings_enabled) {
            return (object) [
                'promedio' => (float) $this->admin_rating,
                'total' => 0,
                'estrellas' => (object) []
            ];
        }

        $ratings = $this->userRatings;
        $total = $ratings->count();

        $estrellas = [];
        foreach ([1, 2, 3, 4, 5] as $s) {
            $estrellas[$s] = (object) ['cantidad' => 0, 'porcentaje' => 0];
        }

        if ($total === 0) {
            return (object) [
                'promedio' => 0,
                'total' => 0,
                'estrellas' => (object) $estrellas
            ];
        }

        $sum = 0;
        foreach ($ratings as $rating) {
            $sum += $rating->rating;
            $estrellas[$rating->rating]->cantidad++;
        }

        $average = round($sum / $total, 1);

        foreach ($estrellas as $s => $data) {
            $data->porcentaje = round(($data->cantidad / $total) * 100, 1);
        }

        return (object) [
            'promedio' => $average,
            'total' => $total,
            'estrellas' => (object) $estrellas
        ];
    }

    public function ofertasDelDia()
    {
        return $this->hasMany(OfertaDelDia::class, 'id_producto');
    }

    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'id_producto_categoria', 'id_producto_categoria');
    }

    public function subcategoria()
    {
        return $this->belongsTo(ProductoCategoriaSub::class, 'id_producto_categoria_sub', 'id_producto_categoria_sub');
    }

    public function fotos()
    {
        return $this->hasMany(ProductoFoto::class, 'id_producto', 'id_producto')->orderBy('orden');
    }

    public function fichasTecnicas()
    {
        return $this->hasMany(ProductoFichaTecnica::class, 'id_producto', 'id_producto')->orderBy('orden');
    }

    /**
     * ⭐ ENRIQUECER LISTAS DE PRODUCTOS (Desde Stored Procedures)
     */
    public static function enhanceList($products)
    {
        if (empty($products))
            return $products;

        $ids = collect($products)->pluck('id_producto')->filter()->unique()->toArray();
        if (empty($ids))
            return $products;

        $ratingsData = \Illuminate\Support\Facades\DB::table('administracion_producto')
            ->whereIn('id_producto', $ids)
            ->select('id_producto', 'ratings_enabled', 'admin_rating', 'numero_estrellas', 'codigo_producto', 'slug', 'etiqueta_badge', 'codigo_barra')
            ->get()
            ->keyBy('id_producto');

        $userAverages = \Illuminate\Support\Facades\DB::table('product_user_ratings')
            ->whereIn('id_producto', $ids)
            ->select('id_producto', \Illuminate\Support\Facades\DB::raw('AVG(rating) as average'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('id_producto')
            ->get()
            ->keyBy('id_producto');

        $activeOffersPrimary = \App\Models\OfertaDelDia::whereIn('id_producto', $ids)
            ->where('Activo', 'S')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereRaw('cantidad_disponible > cantidad_vendida')
            ->get()->keyBy('id_producto');

        $activeOffersSecondary = \App\Models\OfertaDelDia::whereIn('segundo_id_producto', $ids)
            ->where('Activo', 'S')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereRaw('cantidad_disponible > cantidad_vendida')
            ->get()->keyBy('segundo_id_producto');

        foreach ($products as $p) {
            if (empty($p->titulo) && ! empty($p->nombre)) {
                $p->titulo = $p->nombre;
            }

            $data = $ratingsData->get($p->id_producto);
            if ($data) {
                $p->codigo_producto = $data->codigo_producto;
                $p->slug = $data->slug ?: ($p->id_producto ?? null);
                $p->etiqueta_badge = $data->etiqueta_badge ?? null;
                $p->codigo_barra = $data->codigo_barra ?? ($p->codigo_barra ?? null);
                $p->ratings_enabled = (bool) $data->ratings_enabled;
                $p->admin_rating = $data->admin_rating;

                if ($p->ratings_enabled) {
                    $ratingInfo = $userAverages->get($p->id_producto);
                    $p->display_rating = $ratingInfo ? round($ratingInfo->average, 1) : 0;
                    $p->total_ratings = $ratingInfo ? $ratingInfo->total : 0;
                } else {
                    $p->display_rating = $data->admin_rating ?? (float) ($data->numero_estrellas ?? 0);
                    $p->total_ratings = $data->numero_estrellas ?? 0;
                }
            } else {
                $p->ratings_enabled = false;
                $p->display_rating = (float) ($p->numero_estrellas ?? 0);
                $p->total_ratings = 0;
            }

            //OFERTA DEL DÍA GLOBAL (Sobreescribir precios)
            if ($activeOffersPrimary->has($p->id_producto)) {
                $offer = $activeOffersPrimary->get($p->id_producto);
                $p->precio_old = $offer->precio_original;
                $p->precio = $offer->precio_oferta;
            } elseif ($activeOffersSecondary->has($p->id_producto)) {
                $offer = $activeOffersSecondary->get($p->id_producto);
                $p->precio_old = $p->precio;
                $p->precio = $offer->segundo_precio_oferta;
            }
        }

        return $products;
    }

}