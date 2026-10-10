<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

/**
 * Ancla Spatie cuando no hay modelo de negocio (CKEditor, SPs, evidencias sueltas).
 */
class CmsMediaHolder extends Model implements HasMedia
{
    use CompressesCmsImages;

    protected $table = 'cms_media_holders';

    protected $guarded = [];

    /** @var list<string> */
    protected array $cmsMediaSingle = ['file'];
}
