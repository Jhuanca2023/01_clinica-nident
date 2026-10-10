<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Almacén CMS. Las fotos pasan por Spatie (WebP); PDF y demás quedan en storage_/{modulo}/.
 */
class CmsImageStorage
{
    public const DISK = 'public_imagenes';
    public const ROOT = 'storage_';

    /** Carpetas de tema/legacy: no borrar al reemplazar una imagen CMS. */
    public const PROTECTED_PREFIXES = [
        'temp02/',
        'temp/',
        'imagenes/',
        'favicon/',
        'icon/',
        'wa-og/',
        'assets/',
        'build/',
        'spaville/',
    ];

    public static function disk(): string
    {
        return self::DISK;
    }

    /** Ruta relativa canónica: storage_/{folder}/{filename} */
    public static function path(string $folder, string $filename): string
    {
        $folder = self::normalizeFolder($folder);
        $filename = ltrim(str_replace('\\', '/', $filename), '/');

        return self::ROOT . '/' . $folder . '/' . $filename;
    }

    public static function normalizeFolder(string $folder): string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');
        if (str_starts_with($folder, self::ROOT)) {
            $folder = substr($folder, strlen(self::ROOT));
        }

        return trim($folder, '/');
    }

    public static function isUnderStorage(?string $relativePath): bool
    {
        if ($relativePath === null || $relativePath === '') {
            return false;
        }
        $path = ltrim(str_replace('\\', '/', $relativePath), '/');

        return str_starts_with($path, self::ROOT);
    }

    public static function isProtectedThemePath(?string $relativePath): bool
    {
        if ($relativePath === null || $relativePath === '') {
            return false;
        }
        $path = ltrim(str_replace('\\', '/', $relativePath), '/');
        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Guarda un UploadedFile. Las imágenes se comprimen a WebP (Spatie);
     * el resto (PDF, etc.) queda en storage_/{folder}/.
     */
    public static function store(UploadedFile $file, string $folder, ?string $filename = null): string
    {
        if (OptimizedMedia::isImage($file)) {
            return OptimizedMedia::storeOrphan($file, $folder);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        $name = $filename ?: (time() . '_' . Str::random(6) . '.' . $ext);
        if (!str_contains($name, '.')) {
            $name .= '.' . $ext;
        }

        $relative = self::path($folder, $name);
        $stored = $file->storeAs(
            dirname($relative),
            basename($relative),
            ['disk' => self::DISK]
        );

        if (!$stored) {
            throw new \RuntimeException('No se pudo guardar el archivo en storage_.');
        }

        return $stored;
    }

    /** Escribe bytes crudos (p.ej. base64) comprimiendo si es imagen raster. */
    public static function put(string $folder, string $contents, string $extension = 'jpg'): string
    {
        $ext = strtolower($extension) === 'jpeg' ? 'jpg' : strtolower($extension);
        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cms-put-'.Str::lower(Str::random(8)).'.'.$ext;
        file_put_contents($tmp, $contents);
        try {
            $uploaded = new UploadedFile($tmp, 'foto.'.$ext, null, null, true);
            if (OptimizedMedia::isImage($uploaded)) {
                return OptimizedMedia::storeOrphan($uploaded, $folder);
            }
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }

        $relative = self::path($folder, uniqid('img_', true) . '.' . $ext);
        Storage::disk(self::DISK)->put($relative, $contents);

        return $relative;
    }

    /**
     * Elimina archivos CMS (Spatie o storage_ legacy). Nunca temp02/tema.
     */
    public static function deleteIfOwned(?string $relativePath): bool
    {
        if ($relativePath === null || $relativePath === '') {
            return false;
        }

        $path = ltrim(str_replace('\\', '/', $relativePath), '/');

        if (self::isProtectedThemePath($path)) {
            return false;
        }

        if (OptimizedMedia::isManaged($path) || self::isUnderStorage($path)) {
            OptimizedMedia::deletePath($path);

            return true;
        }

        return false;
    }
}
