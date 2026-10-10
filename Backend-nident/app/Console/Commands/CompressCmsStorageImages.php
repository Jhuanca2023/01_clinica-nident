<?php

namespace App\Console\Commands;

use App\Support\OptimizedMedia;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\HasMedia;

/**
 * Recomprime fotos legacy (public/storage_/, avatar, etc.) con el mismo WebP del CMS.
 *
 *   php artisan cms:compress-storage
 *   php artisan cms:compress-storage --dry-run
 */
class CompressCmsStorageImages extends Command
{
    protected $signature = 'cms:compress-storage
        {--dry-run : Solo lista; no escribe}
        {--force : Recomprime también las que ya están en storage_/media}';

    protected $description = 'Comprime a WebP las imágenes del CMS que aún no pasan por OptimizedMedia.';

    /** @var array<string, string> */
    private array $pathCache = [];

    /** @var array<string, true> */
    private array $legacyPending = [];

    public function handle(): int
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $ok = $skip = $missing = $errors = 0;

        foreach ($this->mediaModels() as $class) {
            try {
                if (! Schema::hasTable((new $class)->getTable())) {
                    continue;
                }
            } catch (\Throwable) {
                continue;
            }

            $class::query()->orderBy((new $class)->getKeyName())->each(function (Model $row) use ($class, $dry, $force, &$ok, &$skip, &$missing, &$errors) {
                if (! $row instanceof HasMedia) {
                    return;
                }
                $dirty = false;
                foreach ($row->getAttributes() as $column => $value) {
                    if (! $this->isImageAttribute($column, $value)) {
                        continue;
                    }
                    $old = ltrim(str_replace('\\', '/', (string) $value), '/');
                    if (! $force && OptimizedMedia::isManaged($old)) {
                        $skip++;
                        continue;
                    }
                    if (isset($this->pathCache[$old])) {
                        if ($row->{$column} !== $this->pathCache[$old]) {
                            $row->{$column} = $this->pathCache[$old];
                            $dirty = true;
                        }
                        $this->legacyPending[$old] = true;
                        $ok++;
                        continue;
                    }
                    if (! OptimizedMedia::absolutePath($old)) {
                        $this->warn("Falta archivo: {$class}#{$row->getKey()} {$column} = {$old}");
                        $missing++;
                        continue;
                    }
                    if ($dry) {
                        $this->line("[dry-run] {$class}#{$row->getKey()} {$column}  {$old}");
                        $ok++;
                        continue;
                    }
                    try {
                        $collection = OptimizedMedia::inferCollection($column);
                        $new = OptimizedMedia::ingestFromDisk($row, $collection, $old);
                        if (! $new) {
                            $errors++;
                            continue;
                        }
                        $this->pathCache[$old] = $new;
                        $this->legacyPending[$old] = true;
                        $row->{$column} = $new;
                        $dirty = true;
                        $ok++;
                        $this->line("OK {$class}#{$row->getKey()} {$column}");
                    } catch (\Throwable $e) {
                        $errors++;
                        $this->error("Error {$class}#{$row->getKey()} {$column}: ".$e->getMessage());
                    }
                }
                if ($dirty && ! $dry) {
                    $row->save();
                }
            }, 50);
        }

        if (! $dry) {
            foreach (array_keys($this->legacyPending) as $legacy) {
                if (OptimizedMedia::isManaged($legacy)) {
                    continue;
                }
                OptimizedMedia::deleteLegacy($legacy);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Comprimidas/actualizadas: %d  |  ya en media: %d  |  archivo ausente: %d  |  errores: %d%s',
            $ok,
            $skip,
            $missing,
            $errors,
            $dry ? '  (dry-run)' : ''
        ));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return list<class-string<HasMedia>> */
    private function mediaModels(): array
    {
        $out = [];
        foreach (File::files(app_path('Models')) as $file) {
            $class = 'App\\Models\\'.$file->getBasename('.php');
            if (! class_exists($class) || ! is_subclass_of($class, HasMedia::class)) {
                continue;
            }
            $out[] = $class;
        }

        return $out;
    }

    private function isImageAttribute(string $column, mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }
        if (preg_match('/(enlace|link|href|video|whatsapp|mapa|facebook|twitter|youtube|embed)/i', $column)) {
            return false;
        }
        if (str_contains($value, 'fa-') && ! str_contains($value, '/')) {
            return false;
        }

        return OptimizedMedia::isIngestibleImagePath($value);
    }
}
