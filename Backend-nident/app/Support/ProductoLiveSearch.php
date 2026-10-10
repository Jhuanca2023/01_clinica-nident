<?php

namespace App\Support;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Búsqueda live de catálogo (ORM) — reutilizable entre proyectos ecommerce.
 *
 * Prefijo CSS/JS del widget: pls- (Producto Live Search).
 */
class ProductoLiveSearch
{
    public const DEFAULT_SUGGEST_LIMIT = 8;

    public const DEFAULT_SEARCH_LIMIT = 48;

    public const MIN_QUERY_LENGTH = 2;

    /**
     * Autocomplete: productos cercanos + sugerencias de palabras.
     *
     * @return array{success:bool,query:string,products:array<int,array>,suggestions:array<int,string>,meta:array}
     */
    public static function suggest(?string $query, int $limit = self::DEFAULT_SUGGEST_LIMIT): array
    {
        $q = self::normalizeQuery($query);
        if (mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return self::emptyResult($q, $limit, 'suggest');
        }

        $limit = max(1, min(20, $limit));
        $rows = self::rankedQuery($q, $limit * 3)->take($limit);

        return [
            'success' => true,
            'query' => $q,
            'products' => $rows->map(fn ($p) => self::mapProduct($p))->values()->all(),
            'suggestions' => self::buildSuggestions($q, $rows),
            'meta' => [
                'mode' => 'suggest',
                'limit' => $limit,
                'min_chars' => self::MIN_QUERY_LENGTH,
            ],
        ];
    }

    /**
     * Búsqueda completa (lupa / submit): más resultados, misma relevancia.
     *
     * @return array{success:bool,query:string,products:array<int,array>,suggestions:array<int,string>,meta:array}
     */
    public static function search(?string $query, int $limit = self::DEFAULT_SEARCH_LIMIT): array
    {
        $q = self::normalizeQuery($query);
        if (mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return self::emptyResult($q, $limit, 'search');
        }

        $limit = max(1, min(100, $limit));
        $rows = self::rankedQuery($q, $limit);

        return [
            'success' => true,
            'query' => $q,
            'products' => $rows->map(fn ($p) => self::mapProduct($p))->values()->all(),
            'suggestions' => self::buildSuggestions($q, $rows),
            'meta' => [
                'mode' => 'search',
                'limit' => $limit,
                'count' => $rows->count(),
                'min_chars' => self::MIN_QUERY_LENGTH,
            ],
        ];
    }

    /**
     * Query ordenada por score de relevancia (galería / tienda / autocomplete).
     *
     * Busca en: nombre, descripción, códigos, categoría y subcategoría.
     * Expande plurales simples (polos → polo) para catálogo fashion.
     *
     * @return Collection<int, object>
     */
    public static function rankedQuery(string $query, int $limit): Collection
    {
        $q = self::normalizeQuery($query);
        if ($q === '') {
            return collect();
        }

        $variants = self::queryVariants($q);
        $likeFull = '%' . self::escapeLike($q) . '%';
        $likeStart = self::escapeLike($q) . '%';

        $hasCodigoNew = Schema::hasColumn('administracion_producto', 'codigo_producto_new');
        $hasCodigoBarra = Schema::hasColumn('administracion_producto', 'codigo_barra');
        $hasEstrellas = Schema::hasColumn('administracion_producto', 'numero_estrellas');
        $hasCatTable = Schema::hasTable('administracion_producto_categoria');
        $hasSubTable = Schema::hasTable('administracion_producto_categoria_sub');

        $select = [
            'administracion_producto.id_producto',
            'administracion_producto.nombre',
            'administracion_producto.descripcion',
            'administracion_producto.precio',
            'administracion_producto.precio_old',
            'administracion_producto.url_imagen',
            'administracion_producto.codigo_producto',
            'administracion_producto.slug',
            'administracion_producto.stock',
            'administracion_producto.Activo',
        ];
        if ($hasEstrellas) {
            $select[] = 'administracion_producto.numero_estrellas';
        }
        if ($hasCodigoNew) {
            $select[] = 'administracion_producto.codigo_producto_new';
        }

        $builder = Producto::query()
            ->where('administracion_producto.Activo', 'S')
            ->select($select);

        if ($hasCatTable) {
            $builder->leftJoin(
                'administracion_producto_categoria as pls_cat',
                'administracion_producto.id_producto_categoria',
                '=',
                'pls_cat.id_producto_categoria'
            );
            $select[] = 'pls_cat.nombre as pls_categoria';
            $builder->addSelect('pls_cat.nombre as pls_categoria');
        }
        if ($hasSubTable) {
            $builder->leftJoin(
                'administracion_producto_categoria_sub as pls_sub',
                'administracion_producto.id_producto_categoria_sub',
                '=',
                'pls_sub.id_producto_categoria_sub'
            );
            $builder->addSelect('pls_sub.nombre as pls_subcategoria');
        }

        $builder->where(function ($outer) use ($variants, $hasCodigoNew, $hasCodigoBarra, $hasCatTable, $hasSubTable) {
            foreach ($variants as $variant) {
                $like = '%' . self::escapeLike($variant) . '%';
                $outer->orWhere(function ($group) use ($like, $hasCodigoNew, $hasCodigoBarra, $hasCatTable, $hasSubTable) {
                    $group->where('administracion_producto.nombre', 'like', $like)
                        ->orWhere('administracion_producto.descripcion', 'like', $like)
                        ->orWhere('administracion_producto.codigo_producto', 'like', $like);
                    if ($hasCodigoNew) {
                        $group->orWhere('administracion_producto.codigo_producto_new', 'like', $like);
                    }
                    if ($hasCodigoBarra) {
                        $group->orWhere('administracion_producto.codigo_barra', 'like', $like);
                    }
                    if ($hasCatTable) {
                        $group->orWhere('pls_cat.nombre', 'like', $like);
                    }
                    if ($hasSubTable) {
                        $group->orWhere('pls_sub.nombre', 'like', $like);
                    }
                });
            }
        });

        $qLower = mb_strtolower($q);
        $scoreBindings = [
            $qLower,
            mb_strtolower($likeStart),
            mb_strtolower($likeFull),
            mb_strtolower($likeFull),
            mb_strtolower($likeFull),
        ];

        $catScoreSql = '0';
        if ($hasCatTable) {
            $catScoreSql = '(CASE
                WHEN LOWER(COALESCE(pls_cat.nombre, \'\')) LIKE ? THEN 85
                WHEN LOWER(COALESCE(pls_cat.nombre, \'\')) LIKE ? THEN 70
                ELSE 0
            END)';
            $scoreBindings[] = mb_strtolower($likeStart);
            $scoreBindings[] = mb_strtolower($likeFull);
        }
        if ($hasSubTable) {
            $catScoreSql = '(' . $catScoreSql . ' + (CASE
                WHEN LOWER(COALESCE(pls_sub.nombre, \'\')) LIKE ? THEN 80
                WHEN LOWER(COALESCE(pls_sub.nombre, \'\')) LIKE ? THEN 65
                ELSE 0
            END))';
            $scoreBindings[] = mb_strtolower($likeStart);
            $scoreBindings[] = mb_strtolower($likeFull);
        }

        $builder->selectRaw(
            '(CASE
                WHEN LOWER(administracion_producto.nombre) = ? THEN 100
                WHEN LOWER(administracion_producto.nombre) LIKE ? THEN 92
                WHEN LOWER(COALESCE(administracion_producto.codigo_producto, \'\')) LIKE ? THEN 88
                WHEN LOWER(administracion_producto.nombre) LIKE ? THEN 75
                WHEN LOWER(COALESCE(administracion_producto.descripcion, \'\')) LIKE ? THEN 45
                ELSE 20
            END) + ' . $catScoreSql . ' AS pls_score',
            $scoreBindings
        );

        $builder->orderByDesc('pls_score')
            ->orderByRaw('CASE WHEN administracion_producto.stock > 0 THEN 0 ELSE 1 END')
            ->orderByRaw('CHAR_LENGTH(administracion_producto.nombre) ASC')
            ->limit($limit);

        return $builder->get()->map(function ($row) use ($q) {
            $row->pls_score = (int) ($row->pls_score ?? 0) + self::phpProximityBonus($q, (string) $row->nombre);
            $cat = (string) ($row->pls_categoria ?? '');
            if ($cat !== '') {
                $row->pls_score += self::phpProximityBonus($q, $cat);
            }

            return $row;
        })->sortByDesc(fn ($r) => (int) $r->pls_score)->values();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<int, string>
     */
    public static function buildSuggestions(string $query, Collection $rows): array
    {
        $q = self::normalizeQuery($query);
        if ($q === '') {
            return [];
        }

        $bag = [];
        foreach ($rows as $row) {
            $parts = [
                (string) ($row->nombre ?? ''),
                (string) ($row->pls_categoria ?? ''),
                (string) ($row->pls_subcategoria ?? ''),
            ];
            foreach ($parts as $nombre) {
                foreach (preg_split('/[\s\-_,.;:\/|()]+/u', $nombre) ?: [] as $word) {
                    $word = self::normalizeQuery($word);
                    if (mb_strlen($word) < 3) {
                        continue;
                    }
                    if (! self::isNearbyWord($q, $word)) {
                        continue;
                    }
                    $bag[$word] = ($bag[$word] ?? 0) + 1;
                }
            }
        }

        arsort($bag);
        $out = [];
        foreach (array_keys($bag) as $word) {
            if (mb_strtolower($word) === mb_strtolower($q)) {
                continue;
            }
            $out[] = $word;
            if (count($out) >= 6) {
                break;
            }
        }

        return $out;
    }

    public static function mapProduct(object $p): array
    {
        $slug = trim((string) ($p->slug ?? ''));
        if ($slug === '') {
            $slug = (string) ($p->id_producto ?? '');
        }
        $imgRel = (string) ($p->url_imagen ?? '');
        $precio = (float) ($p->precio ?? 0);
        $precioOld = (float) ($p->precio_old ?? 0);
        $categoria = trim((string) ($p->pls_categoria ?? ''));

        return [
            'id_producto' => (int) ($p->id_producto ?? 0),
            'nombre' => (string) ($p->nombre ?? ''),
            'categoria' => $categoria !== '' ? $categoria : null,
            'slug' => $slug,
            'url' => url('/web_shopDetail/' . $slug),
            'url_imagen' => $imgRel !== '' ? LiamPageData::img($imgRel) : asset('temp02/img/inicio/productos/1.jpg'),
            'url_imagen_rel' => $imgRel !== '' ? $imgRel : null,
            'precio' => $precio,
            'precio_old' => $precioOld > $precio ? $precioOld : null,
            'codigo_producto' => (string) ($p->codigo_producto ?? $p->codigo_producto_new ?? ''),
            'score' => (int) ($p->pls_score ?? 0),
            'stock' => (int) ($p->stock ?? 0),
        ];
    }

    public static function normalizeQuery(?string $query): string
    {
        $q = trim((string) $query);
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;

        return mb_substr($q, 0, 120);
    }

    /**
     * Variantes de búsqueda: query original + singularización ES simple.
     * Ej: "polos" → ["polos", "polo"]
     *
     * @return array<int, string>
     */
    public static function queryVariants(string $query): array
    {
        $q = self::normalizeQuery($query);
        $out = [$q];

        foreach (self::tokens($q) as $token) {
            $out[] = $token;
            $stem = self::spanishStem($token);
            if ($stem !== '' && $stem !== $token) {
                $out[] = $stem;
            }
        }

        $fullStem = self::spanishStem($q);
        if ($fullStem !== '' && $fullStem !== $q) {
            $out[] = $fullStem;
        }

        $out = array_values(array_unique(array_filter($out, fn ($v) => mb_strlen($v) >= 2)));

        return $out ?: [$q];
    }

    private static function spanishStem(string $word): string
    {
        $w = mb_strtolower(trim($word));
        if (mb_strlen($w) < 4) {
            return $w;
        }
        // polos → polo, camisetas → camiseta, hoodies → hoodi (ok-ish)
        if (str_ends_with($w, 'es') && mb_strlen($w) >= 5) {
            $cut = mb_substr($w, 0, -2);
            if (mb_strlen($cut) >= 3) {
                return $cut;
            }
        }
        if (str_ends_with($w, 's') && ! str_ends_with($w, 'ss')) {
            return mb_substr($w, 0, -1);
        }

        return $w;
    }

    /** @return array<int, string> */
    private static function tokens(string $query): array
    {
        $parts = preg_split('/\s+/u', $query) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if (mb_strlen($p) >= 2) {
                $out[] = $p;
            }
        }

        return array_values(array_unique($out));
    }

    private static function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    private static function isNearbyWord(string $query, string $word): bool
    {
        $q = mb_strtolower($query);
        $w = mb_strtolower($word);
        if (str_contains($w, $q) || str_starts_with($w, $q)) {
            return true;
        }
        $stem = self::spanishStem($q);
        if ($stem !== $q && (str_contains($w, $stem) || str_starts_with($w, $stem))) {
            return true;
        }
        if (mb_strlen($q) >= 3 && mb_strlen($w) >= 3) {
            $dist = levenshtein(
                Str::ascii($q),
                Str::ascii(mb_substr($w, 0, 32))
            );
            $max = max(1, (int) floor(mb_strlen($q) / 3));

            return $dist <= $max;
        }

        return false;
    }

    private static function phpProximityBonus(string $query, string $nombre): int
    {
        $q = mb_strtolower($query);
        $n = mb_strtolower($nombre);
        if ($n === $q) {
            return 8;
        }
        if (str_starts_with($n, $q) || str_contains($n, $q)) {
            return str_starts_with($n, $q) ? 5 : 3;
        }
        $stem = self::spanishStem($q);
        if ($stem !== $q && (str_starts_with($n, $stem) || str_contains($n, $stem))) {
            return 4;
        }

        return 0;
    }

    private static function emptyResult(string $query, int $limit, string $mode): array
    {
        return [
            'success' => true,
            'query' => $query,
            'products' => [],
            'suggestions' => [],
            'meta' => [
                'mode' => $mode,
                'limit' => $limit,
                'min_chars' => self::MIN_QUERY_LENGTH,
                'empty_reason' => mb_strlen($query) < self::MIN_QUERY_LENGTH ? 'min_chars' : 'none',
            ],
        ];
    }
}
