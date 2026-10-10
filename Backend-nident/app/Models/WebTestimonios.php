<?php

namespace App\Models;

use App\Models\Concerns\CompressesCmsImages;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebTestimonios extends Model implements HasMedia
{
    use HasFactory, CompressesCmsImages;

    
    /** @var list<string> */
    protected array $cmsMediaSingle = ['avatar', 'captura'];
public const TIPO_TEXTO = 'texto';
    public const TIPO_CAPTURA = 'captura';

    protected $table = 'web_testimonios';
    protected $primaryKey = 'id_testimonio';
    protected $guarded = [];

    protected $casts = [
        'calificacion' => 'float',
    ];

    protected $appends = [
        'url_captura_publica',
        'url_avatar_publica',
    ];

    public function getUrlCapturaPublicaAttribute(): ?string
    {
        return $this->url_captura ? asset($this->url_captura) : null;
    }

    public function getUrlAvatarPublicaAttribute(): ?string
    {
        return $this->url_avatar ? asset($this->url_avatar) : null;
    }

    public function esCaptura(): bool
    {
        return ($this->tipo ?? self::TIPO_TEXTO) === self::TIPO_CAPTURA;
    }

    public function esTexto(): bool
    {
        return !$this->esCaptura();
    }
}
