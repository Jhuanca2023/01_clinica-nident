<?php

namespace App\Support;

use App\Models\WebTestimonios;
use Illuminate\Support\Facades\Schema;

/** Reseñas del carrusel de inicio (admin ▸ Testimonios). Marcadas con badge_seccion = "rossety". */
class RossetyTestimonios
{
    public const SECCION = 'rossety';
    public const EYEBROW = 'Opiniones';
    public const TITULO = 'Lo que dicen nuestros clientes';

    /** @return array{eyebrow: string, titulo: string, items: array<int, array<string, mixed>>} */
    public static function seccion(bool $soloActivos = true): array
    {
        $out = ['eyebrow' => self::EYEBROW, 'titulo' => self::TITULO, 'items' => []];
        if (! Schema::hasTable('web_testimonios') || ! Schema::hasColumn('web_testimonios', 'seccion_eyebrow')) {
            return $out;
        }

        $q = WebTestimonios::query()->where('badge_seccion', self::SECCION)->orderBy('orden')->orderBy('id_testimonio');
        $todos = $q->get();
        $primero = $todos->first();
        if ($primero) {
            $out['eyebrow'] = trim((string) $primero->seccion_eyebrow) !== '' ? $primero->seccion_eyebrow : self::EYEBROW;
            $out['titulo'] = trim((string) $primero->seccion_titulo) !== '' ? $primero->seccion_titulo : self::TITULO;
        }

        $out['items'] = $todos
            ->when($soloActivos, fn ($c) => $c->where('Activo', 'S'))
            ->map(fn ($r) => [
                'id' => $r->id_testimonio,
                'nombre' => (string) $r->nombre,
                'ciudad' => (string) $r->subtitulo,
                'texto' => (string) $r->testimonio,
                'estrellas' => max(1, min(5, (int) round((float) $r->calificacion))),
                'avatar' => CmsStorageUrl::forWeb($r->url_avatar),
                'activo' => $r->Activo,
            ])->values()->all();

        return $out;
    }
}
