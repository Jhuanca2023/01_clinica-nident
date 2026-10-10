<?php

namespace App\Services\Branding;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class AppFaviconService
{
    private const META_FILE = 'favicon-meta.json';

    /** Rutas donde debe existir el favicon del panel (y web pública). */
    public function targetDirectories(): array
    {
        $dirs = [public_path(), public_path('admin'), public_path('icon')];

        $frontendPublic = dirname(base_path()) . DIRECTORY_SEPARATOR . 'Valento_Frontend' . DIRECTORY_SEPARATOR . 'public';
        if (is_dir($frontendPublic)) {
            $dirs[] = $frontendPublic;
            $dirs[] = $frontendPublic . DIRECTORY_SEPARATOR . 'icon';
        }

        // Compatibilidad con nombre antiguo del frontend
        $legacyFrontend = dirname(base_path()) . DIRECTORY_SEPARATOR . 'impacto-gigante-Frontend' . DIRECTORY_SEPARATOR . 'public';
        if (is_dir($legacyFrontend)) {
            $dirs[] = $legacyFrontend;
            $dirs[] = $legacyFrontend . DIRECTORY_SEPARATOR . 'icon';
        }

        return array_values(array_unique($dirs));
    }

    public function getMeta(): array
    {
        $path = public_path(self::META_FILE);
        if (! is_file($path)) {
            return $this->buildMetaFromFiles();
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            return $this->buildMetaFromFiles();
        }

        return array_merge($this->buildMetaFromFiles(), $data);
    }

    public function buildMetaFromFiles(): array
    {
        $svg = public_path('favicon.svg');
        $ico = public_path('favicon.ico');
        $png = public_path('icon/favicon-96x96.png');
        if (! is_file($png)) {
            $png = public_path('favicon-96x96.png');
        }

        $version = max(
            is_file($svg) ? (int) filemtime($svg) : 0,
            is_file($ico) ? (int) filemtime($ico) : 0,
            is_file($png) ? (int) filemtime($png) : 0,
            1
        );

        return [
            'version' => $version,
            'updated_at' => $version > 1 ? date('c', $version) : null,
            'has_svg' => is_file($svg),
            'has_ico' => is_file($ico),
            'has_png' => is_file($png),
        ];
    }

    /**
     * @return array{version: int, files: list<string>}
     */
    public function storeUpload(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        $mime = strtolower($file->getMimeType() ?: '');

        $map = $this->resolveFilename($ext, $mime);
        if ($map === null) {
            throw new \InvalidArgumentException('Formato no válido. Use SVG, PNG o ICO.');
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! is_readable($realPath)) {
            throw new \RuntimeException('No se pudo leer el archivo subido.');
        }
        $contents = file_get_contents($realPath);

        $written = [];
        foreach ($this->targetDirectories() as $dir) {
            if (! is_dir($dir)) {
                File::ensureDirectoryExists($dir);
            }
            $dest = $dir . DIRECTORY_SEPARATOR . $map['name'];
            File::put($dest, $contents);
            $written[] = $dest;
        }

        $version = time();
        $meta = array_merge($this->buildMetaFromFiles(), [
            'version' => $version,
            'updated_at' => date('c', $version),
            'last_upload' => $map['name'],
        ]);
        File::put(public_path(self::META_FILE), json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return ['version' => $version, 'files' => $written];
    }

    /** @return array{name: string}|null */
    private function resolveFilename(string $ext, string $mime): ?array
    {
        if ($ext === 'svg' || str_contains($mime, 'svg')) {
            return ['name' => 'favicon.svg'];
        }
        if ($ext === 'ico' || str_contains($mime, 'icon') || $mime === 'image/x-icon') {
            return ['name' => 'favicon.ico'];
        }
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true) || str_contains($mime, 'png') || str_contains($mime, 'jpeg')) {
            return ['name' => 'favicon-96x96.png'];
        }

        return null;
    }

    public function urlsForApi(): array
    {
        $meta = $this->getMeta();
        $v = (int) ($meta['version'] ?? 1);
        $q = '?v=' . $v;

        return [
            'version' => $v,
            'updated_at' => $meta['updated_at'] ?? null,
            'svg' => ($meta['has_svg'] ?? false) ? asset('favicon.svg') . $q : null,
            'ico' => ($meta['has_ico'] ?? false) ? asset('favicon.ico') . $q : null,
            'png' => ($meta['has_png'] ?? false) ? asset('icon/favicon-96x96.png') . $q : null,
            'admin_base' => rtrim(asset('admin'), '/'),
        ];
    }
}
