<?php

namespace App\Support;

use App\Models\CmsMediaHolder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Única puerta de subida de imágenes del CMS: Spatie Media Library + WebP.
 * La web pública sigue leyendo columnas url_* (ruta relativa a public/).
 *
 * Uso típico (modelo ya persistido, necesita id):
 *
 *   OptimizedMedia::saveUploads($row, $request, [
 *       'image' => 'url_imagen',
 *       'hero'  => ['column' => 'url_hero', 'collection' => 'hero', 'clear' => 'quitar_hero'],
 *   ]);
 *
 * Sin modelo Eloquent (SP, CKEditor): OptimizedMedia::storeOrphan($file, 'ckeditor').
 */
class OptimizedMedia
{
    public const DISK = 'media';

    public const CONVERSION = 'optimized';

    public const PUBLIC_PREFIX = 'storage_/media/';

    /** Validación Laravel (KB). Acepta fotos pesadas; Spatie las comprime. */
    public const MAX_KB = 20480;

    public const RULE = 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:'.self::MAX_KB;

    public const RULE_REQUIRED = 'required|image|mimes:jpeg,png,jpg,webp,gif|max:'.self::MAX_KB;

    /** Logos (SVG no pasa la regla `image` de Laravel). */
    public const RULE_LOGO = 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,avif|max:'.self::MAX_KB;

    public static function isManaged(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return str_starts_with(str_replace('\\', '/', $path), self::PUBLIC_PREFIX);
    }

    public static function isThemeAsset(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return self::isProtectedThemePath(ltrim(str_replace('\\', '/', $path), '/'));
    }

    public static function isIngestibleImagePath(?string $path): bool
    {
        if (! $path || self::isThemeAsset($path) || preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return false;
        }
        $ext = strtolower((string) pathinfo(str_replace('\\', '/', $path), PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif', 'svg'], true);
    }

    /** Ruta absoluta en public/ o storage/app, o null si no hay archivo. */
    public static function absolutePath(?string $path): ?string
    {
        if (! $path || preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return null;
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $candidates = [public_path($path)];
        foreach (['local', 'public'] as $disk) {
            try {
                $candidates[] = Storage::disk($disk)->path($path);
            } catch (\Throwable) {
            }
        }
        foreach ($candidates as $full) {
            if ($full && is_file($full)) {
                return $full;
            }
        }

        return null;
    }

    /**
     * Copia un archivo ya guardado al disco media (WebP) sin borrar el original
     * hasta que el caller actualice la columna y llame deleteLegacy().
     */
    public static function ingestFromDisk(HasMedia $model, string $collection, string $storedPath): ?string
    {
        $abs = self::absolutePath($storedPath);
        if (! $abs) {
            return null;
        }
        $ext = strtolower((string) (pathinfo($abs, PATHINFO_EXTENSION) ?: 'jpg'));
        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cms-ingest-'.Str::lower(Str::random(10)).'.'.$ext;
        if (! @copy($abs, $tmp)) {
            return null;
        }
        try {
            self::preparePathForGd($tmp);
            $model->clearMediaCollection($collection);
            $media = self::withGdTolerance(function () use ($model, $tmp, $collection) {
                return $model
                    ->addMedia($tmp)
                    ->usingFileName(self::safeNameFromPath($tmp))
                    ->toMediaCollection($collection, self::DISK);
            });
            $media->refresh();

            return self::publicRelativePath($media);
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }

    public static function isImage(?UploadedFile $file): bool
    {
        if (! $file) {
            return false;
        }
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'mp3', 'wav'], true)) {
            return false;
        }
        $mime = strtolower((string) ($file->getMimeType() ?: ''));

        return str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'], true);
    }

    public static function replace(HasMedia $model, string $collection, UploadedFile $file): string
    {
        $model->clearMediaCollection($collection);

        return self::add($model, $collection, $file)['path'];
    }

    /** @return array{media_id:int, path:string} */
    public static function add(HasMedia $model, string $collection, UploadedFile $file): array
    {
        $real = $file->getRealPath();
        if ($real && is_file($real)) {
            self::preparePathForGd($real);
        }

        $media = self::withGdTolerance(function () use ($model, $file, $collection) {
            return $model
                ->addMedia($file)
                ->usingFileName(self::safeName($file))
                ->toMediaCollection($collection, self::DISK);
        });

        $media->refresh();

        return [
            'media_id' => (int) $media->id,
            'path'     => self::publicRelativePath($media),
        ];
    }

    public static function replaceFromBase64(HasMedia $model, string $collection, string $base64, string $filename = 'foto.jpg'): ?string
    {
        $base64 = trim($base64);
        if ($base64 === '') {
            return null;
        }
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $m)) {
            $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
            $binary = base64_decode(substr($base64, strpos($base64, ',') + 1), true);
        } else {
            $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)) ?: 'jpg';
            $binary = base64_decode($base64, true);
        }
        if ($binary === false || $binary === '') {
            return null;
        }
        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cms-b64-'.Str::lower(Str::random(10)).'.'.$ext;
        file_put_contents($tmp, $binary);
        try {
            $uploaded = new UploadedFile($tmp, $filename ?: ('foto.'.$ext), null, null, true);

            return self::replace($model, $collection, $uploaded);
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }

    /**
     * Reemplaza un archivo y borra la ruta anterior (media Spatie o legacy).
     */
    public static function put(HasMedia $model, string $collection, UploadedFile $file, ?string $oldPath = null): string
    {
        self::deletePath($oldPath);

        return self::replace($model, $collection, $file);
    }

    /**
     * Aplica subidas del request sobre un modelo ya guardado (necesita id).
     * No hace save(); usa saveUploads() si quieres persistir las columnas.
     *
     * @param  array<string, string|array{column?:string,collection?:string,clear?:string}>  $map
     * @return array<string, string|null>
     */
    public static function applyUploads(HasMedia $model, Request $request, array $map): array
    {
        $changed = [];

        foreach ($map as $input => $cfg) {
            if (is_string($cfg)) {
                $cfg = ['column' => $cfg];
            }
            $column = $cfg['column'] ?? $input;
            $collection = $cfg['collection'] ?? self::inferCollection($column);
            $old = $model->{$column} ?? null;
            $file = $request->file($input);

            if ($file instanceof UploadedFile && self::isImage($file)) {
                $model->{$column} = self::put($model, $collection, $file, $old);
                $changed[$column] = $model->{$column};
                continue;
            }

            $clearKey = $cfg['clear'] ?? null;
            if ($clearKey && self::wantsClear($request, $clearKey)) {
                self::clear($model, $collection);
                self::deletePath($old);
                $model->{$column} = null;
                $changed[$column] = null;
            }
        }

        return $changed;
    }

    /**
     * applyUploads + save si hay cambios en columnas de imagen.
     *
     * @param  array<string, string|array{column?:string,collection?:string,clear?:string}>  $map
     * @return array<string, string|null>
     */
    public static function saveUploads(HasMedia $model, Request $request, array $map): array
    {
        $changed = self::applyUploads($model, $request, $map);
        if ($changed !== []) {
            $model->save();
        }

        return $changed;
    }

    /** Subida sin modelo de negocio (CKEditor, SPs, evidencias sueltas). */
    public static function storeOrphan(UploadedFile $file, string $origen = 'cms'): string
    {
        $holder = CmsMediaHolder::create(['origen' => $origen]);

        return self::replace($holder, 'file', $file);
    }

    public static function clear(HasMedia $model, string $collection): void
    {
        $model->clearMediaCollection($collection);
    }

    public static function deleteById(?int $mediaId): void
    {
        if (! $mediaId) {
            return;
        }
        Media::query()->find($mediaId)?->delete();
    }

    /** Borra media Spatie (parseando el id de la ruta) o un archivo legacy. */
    public static function deletePath(?string $path): void
    {
        if (! $path || preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return;
        }
        $id = self::mediaIdFromPath($path);
        if ($id) {
            self::deleteById($id);

            return;
        }
        self::deleteLegacy($path);
    }

    /** @param  list<string>  $columns */
    public static function purgeLegacy(HasMedia $model, array $columns): void
    {
        foreach ($columns as $column) {
            self::deletePath($model->{$column} ?? null);
        }
    }

    public static function publicRelativePath(Media $media): string
    {
        $conversion = $media->hasGeneratedConversion(self::CONVERSION)
            ? self::CONVERSION
            : '';

        $rel = $media->getPathRelativeToRoot($conversion);

        return self::PUBLIC_PREFIX.ltrim(str_replace('\\', '/', $rel), '/');
    }

    /** Lee bytes (avatar JWT, preview admin) desde media, public/ o storage/app. */
    public static function contents(?string $path): ?string
    {
        if (! $path || preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return null;
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $public = public_path($path);
        if (is_file($public)) {
            $bin = @file_get_contents($public);

            return $bin === false ? null : $bin;
        }
        try {
            if (Storage::exists($path)) {
                return Storage::get($path);
            }
        } catch (\Throwable) {
        }
        try {
            if (Storage::disk('public_imagenes')->exists($path)) {
                return Storage::disk('public_imagenes')->get($path);
            }
        } catch (\Throwable) {
        }

        return null;
    }

    public static function deleteLegacy(?string $path, ?string $disk = null): void
    {
        if (! $path || self::isManaged($path) || preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return;
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if ($path === '' || self::isProtectedThemePath($path)) {
            return;
        }

        $disks = array_values(array_unique(array_filter([$disk, 'public_imagenes', 'public', 'local'])));
        foreach ($disks as $d) {
            try {
                if (Storage::disk($d)->exists($path)) {
                    Storage::disk($d)->delete($path);

                    return;
                }
            } catch (\Throwable) {
            }
        }

        $full = public_path($path);
        if (is_file($full)) {
            @unlink($full);

            return;
        }

        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable) {
        }
    }

    public static function inferCollection(string $column): string
    {
        return match ($column) {
            'url_imagen', 'pro_imagen' => 'imagen',
            'url_imagen_2', 'url_imagen2' => 'imagen_2',
            'url_imagen_lateral' => 'lateral',
            'url_imagen_front' => 'front',
            'url_imagen_back' => 'back',
            'url_hero', 'hero_url_imagen', 'url_imagen_hero', 'imagen_hero' => 'hero',
            'url_avatar', 'avatar' => 'avatar',
            'url_captura' => 'captura',
            'url_icono' => 'icono',
            'url_foto' => 'foto',
            'url_logo', 'url_logo_estatico' => 'logo',
            'url_logo_movil' => 'logo_movil',
            'url_imagen_portada' => 'portada',
            'url_imagen_izquierda' => 'izquierda',
            'url_imagen_centro' => 'centro',
            'logo_menu' => 'logo_menu',
            'logo_footer' => 'logo_footer',
            'url_imagen_central' => 'imagen_central',
            'banner_url_imagen', 'banner_imagen' => 'banner',
            'url_imagen_form' => 'form',
            'imagen_lateral' => 'lateral',
            'url_imagen_fondo' => 'fondo',
            'intro_url_logo', 'intro_url_imagen' => 'intro',
            'catalogo_url_imagen' => 'catalogo',
            'historia_imagen_principal' => 'historia',
            'historia_imagen_secundaria' => 'historia_2',
            'prioridad_url_imagen' => 'prioridad',
            'firma_imagen' => 'firma',
            'fondo_imagen' => 'fondo',
            'media_image', 'url_media' => 'media',
            'empresa_qr_path' => 'qr',
            'evidencia_url' => 'evidencia',
            'hero_bg' => 'hero_bg',
            'img_lateral' => 'img_lateral',
            default => $column,
        };
    }

    private static function mediaIdFromPath(?string $path): ?int
    {
        if (! $path || ! self::isManaged($path)) {
            return null;
        }
        $rel = ltrim(str_replace('\\', '/', substr(str_replace('\\', '/', $path), strlen(self::PUBLIC_PREFIX))), '/');
        $parts = explode('/', $rel);
        // {modelo}/{id}/{coleccion}/{mediaId}/...
        if (isset($parts[3]) && ctype_digit($parts[3])) {
            return (int) $parts[3];
        }

        return null;
    }

    private static function wantsClear(Request $request, string $key): bool
    {
        if (! $request->has($key)) {
            return false;
        }
        $v = $request->input($key);

        return $v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'S';
    }

    private static function isProtectedThemePath(string $path): bool
    {
        return str_starts_with($path, 'temp02/')
            || str_starts_with($path, 'temp/')
            || str_starts_with($path, 'assets/')
            || str_starts_with($path, 'build/')
            || str_starts_with($path, 'spaville/')
            || str_starts_with($path, 'imagenes/')
            || str_starts_with($path, 'favicon/')
            || str_starts_with($path, 'icon/')
            || str_starts_with($path, 'wa-og/');
    }

    /**
     * GD/libpng dispara warning iCCP en PNG con perfil sRGB inválido;
     * Laravel lo convierte en excepción y Spatie aborta la conversión.
     */
    private static function withGdTolerance(callable $callback): mixed
    {
        $previous = null;
        $previous = set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0) use (&$previous) {
            if (str_contains($message, 'iCCP') || str_contains($message, 'libpng')) {
                return true;
            }
            if (is_callable($previous)) {
                return (bool) $previous($severity, $message, $file, $line);
            }

            return false;
        });
        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    /** Reescribe PNG para quitar el chunk iCCP que rompe GD. */
    private static function preparePathForGd(string $path): void
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $mime = @mime_content_type($path) ?: '';
        if ($ext !== 'png' && $mime !== 'image/png') {
            return;
        }
        self::withGdTolerance(function () use ($path) {
            $im = @imagecreatefrompng($path);
            if (! $im) {
                return;
            }
            imagealphablending($im, false);
            imagesavealpha($im, true);
            imagepng($im, $path, 6);
            imagedestroy($im);
        });
    }

    private static function safeName(UploadedFile $file): string
    {
        return self::safeNameFromPath($file->getClientOriginalName() ?: 'foto.jpg');
    }

    private static function safeNameFromPath(string $path): string
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) ?: 'jpg';
        if ($ext === 'jpeg' || $ext === 'jfif') {
            $ext = 'jpg';
        }
        $base = Str::slug((string) pathinfo($path, PATHINFO_FILENAME)) ?: 'foto';

        return $base.'-'.Str::lower(Str::random(8)).'.'.$ext;
    }
}
