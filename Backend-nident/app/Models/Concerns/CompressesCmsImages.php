<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Conversiones Spatie: WebP, máx. 1920px, calidad 78. En cola sync (XAMPP).
 *
 * Opcional en el modelo:
 *   protected array $cmsMediaSingle = ['imagen', 'hero'];
 *   protected array $cmsMediaMany   = ['galeria'];
 */
trait CompressesCmsImages
{
    use InteractsWithMedia;

    public bool $registerMediaConversionsUsingModelInstance = true;

    public function registerMediaCollections(): void
    {
        foreach ($this->cmsMediaSingle() as $name) {
            $this->addMediaCollection($name)->singleFile();
        }
        foreach ($this->cmsMediaMany() as $name) {
            $this->addMediaCollection($name);
        }
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && in_array($media->mime_type, ['image/gif', 'image/svg+xml', 'image/svg'], true)) {
            return;
        }

        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(78)
            ->fit(Fit::Max, 1920, 1920)
            ->nonQueued();
    }

    /** @return list<string> */
    protected function cmsMediaSingle(): array
    {
        return property_exists($this, 'cmsMediaSingle') ? (array) $this->cmsMediaSingle : [];
    }

    /** @return list<string> */
    protected function cmsMediaMany(): array
    {
        return property_exists($this, 'cmsMediaMany') ? (array) $this->cmsMediaMany : [];
    }
}
