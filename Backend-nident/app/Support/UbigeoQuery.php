<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Consultas ubigeo tolerantes a latin1 (cPanel) vs utf8mb4 (Laravel/local).
 */
class UbigeoQuery
{
    public static function idVariants(mixed $id): array
    {
        $raw = trim((string) $id);
        if ($raw === '') {
            return [];
        }

        $trimmed = ltrim($raw, '0');
        if ($trimmed === '') {
            $trimmed = '0';
        }

        return array_values(array_unique([
            $raw,
            $trimmed,
            (string) ((int) $raw),
            str_pad($trimmed, 2, '0', STR_PAD_LEFT),
            str_pad($trimmed, 4, '0', STR_PAD_LEFT),
        ]));
    }

    public static function provincias(mixed $departmentId): Collection
    {
        $ids = self::idVariants($departmentId);
        if ($ids === []) {
            return collect();
        }

        try {
            return DB::table('ubigeo_peru_provinces')
                ->selectRaw('id, CONVERT(name USING utf8mb4) AS name, department_id')
                ->whereIn('department_id', $ids)
                ->orderByRaw('CONVERT(name USING utf8mb4) ASC')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('Ubigeo provincias CONVERT falló, reintento simple: ' . $e->getMessage(), [
                'department_id' => $departmentId,
            ]);
            return DB::table('ubigeo_peru_provinces')
                ->select('id', 'name', 'department_id')
                ->whereIn('department_id', $ids)
                ->get();
        }
    }

    public static function distritos(mixed $departmentId, mixed $provinceId): Collection
    {
        $depIds = self::idVariants($departmentId);
        $provIds = self::idVariants($provinceId);
        if ($depIds === [] || $provIds === []) {
            return collect();
        }

        try {
            return DB::table('ubigeo_peru_districts')
                ->selectRaw('id, CONVERT(name USING utf8mb4) AS name, department_id, province_id')
                ->whereIn('department_id', $depIds)
                ->whereIn('province_id', $provIds)
                ->orderByRaw('CONVERT(name USING utf8mb4) ASC')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('Ubigeo distritos CONVERT falló, reintento simple: ' . $e->getMessage(), [
                'department_id' => $departmentId,
                'province_id' => $provinceId,
            ]);
            return DB::table('ubigeo_peru_districts')
                ->select('id', 'name', 'department_id', 'province_id')
                ->whereIn('department_id', $depIds)
                ->whereIn('province_id', $provIds)
                ->get();
        }
    }
}
