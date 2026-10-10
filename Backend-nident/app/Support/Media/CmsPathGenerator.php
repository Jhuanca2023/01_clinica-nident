<?php

namespace App\Support\Media;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Rutas legibles bajo public/storage_/media/{modelo}/{id}/{coleccion}/{mediaId}/
 */
class CmsPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->base($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->base($media).'/c/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->base($media).'/r/';
    }

    private function base(Media $media): string
    {
        $model = Str::kebab(class_basename((string) $media->model_type));

        return $model.'/'.$media->model_id.'/'.$media->collection_name.'/'.$media->getKey();
    }
}
