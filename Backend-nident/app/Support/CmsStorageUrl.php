<?php

namespace App\Support;

/**
 * URLs públicas de archivos CMS (public/storage_/ y assets temp02).
 */
class CmsStorageUrl
{
    public static function forWeb(?string $relativePath, ?string $fallbackAsset = null): string
    {
        if (empty($relativePath)) {
            return $fallbackAsset ? asset($fallbackAsset) : '';
        }

        if (preg_match('#^https?://#i', $relativePath)) {
            return $relativePath;
        }

        $resolved = self::resolveExistingPath($relativePath);
        if ($resolved !== null) {
            return asset($resolved);
        }

        if ($fallbackAsset) {
            return asset(ltrim($fallbackAsset, '/'));
        }

        $normalized = self::normalizePath($relativePath);

        return $normalized !== '' ? asset($normalized) : '';
    }

    /** Para JSON del admin (asset con APP_URL). */
    public static function forApi(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        if (preg_match('#^https?://#i', $relativePath)) {
            return $relativePath;
        }

        $resolved = self::resolveExistingPath($relativePath);

        return asset($resolved ?? self::normalizePath($relativePath));
    }

    /** @return list<string> */
    private static function candidatePaths(string $relativePath): array
    {
        $path = self::normalizePath($relativePath);
        if ($path === '') {
            return [];
        }

        $candidates = [$path];

        // Legacy: producto_principal/... → storage_/producto_principal/...
        if (preg_match('#^producto_principal(?:_fotos)?/#i', $path)) {
            $candidates[] = 'storage_/' . $path;
        }
        if (preg_match('#^storage_cliente/#i', $path)) {
            $candidates[] = 'storage_/cliente/' . substr($path, strlen('storage_cliente/'));
            // Legacy cart/order lines often store storage_cliente/producto_principal/…
            if (preg_match('#^storage_cliente/producto_principal/#i', $path)) {
                $candidates[] = 'storage_/producto_principal/' . basename($path);
            }
        }

        // Slider / header / footer: nueva carpeta storage_/{slider,footer,header} ⇄ antigua temp02/img/inicio.
        // Las imágenes subidas antes del cambio siguen resolviéndose (y viceversa).
        if (preg_match('#^storage_/(slider|footer|header)/([^/]+)$#i', $path, $m)) {
            $candidates[] = 'temp02/img/inicio/' . $m[2];
        } elseif (preg_match('#^temp02/img/inicio/([^/]+)$#i', $path, $m)) {
            $candidates[] = 'storage_/slider/' . $m[1];
            $candidates[] = 'storage_/footer/' . $m[1];
        }

        // Rutas con subcarpeta (p. ej. storage_/categorias/…) → solo ruta exacta.
        // El fallback por basename provocaba imágenes repetidas en producción.
        if (!str_contains($path, '/')) {
            $basename = basename($path);
            if (!str_starts_with($path, 'storage_/')) {
                $candidates[] = 'storage_/galeria_royal/' . $basename;
                $candidates[] = 'storage_/producto_principal/' . $basename;
            }
            if (!str_starts_with($path, 'temp02/img/')) {
                $candidates[] = 'temp02/img/' . $basename;
            }
        }

        return array_values(array_unique($candidates));
    }

    private static function resolveExistingPath(string $relativePath): ?string
    {
        foreach (self::candidatePaths($relativePath) as $candidate) {
            if (is_file(public_path($candidate))) {
                return $candidate;
            }
        }

        return null;
    }

    private static function normalizePath(string $relativePath): string
    {
        return ltrim(str_replace('\\', '/', trim($relativePath)), '/');
    }
}
