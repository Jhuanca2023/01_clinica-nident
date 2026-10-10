<?php

namespace App\Services\Web;

use App\Models\MetadatosPagina;
use App\Models\Producto;
use App\Models\WebAbout;
use App\Models\WebPaginaNosotros;
use App\Models\WebTestimonios;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteSearchService
{
    /** @var list<array{title: string, url: string, keywords: string, content: string, boost?: int}>|null */
    private ?array $index = null;

    public function findBest(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'url'   => route('home'),
                'title' => 'Inicio',
                'score' => 0,
            ];
        }

        $best = null;
        foreach ($this->buildIndex() as $entry) {
            $score = $this->scoreEntry($entry, $query);
            if ($best === null || $score > $best['score']) {
                $best = [
                    'url'   => $entry['url'],
                    'title' => $entry['title'],
                    'score' => $score,
                ];
            }
        }

        if ($best === null || $best['score'] < 5) {
            return [
                'url'   => route('tienda') . '?q=' . urlencode($query),
                'title' => 'Tienda',
                'score' => 0,
            ];
        }

        return $best;
    }

    /** @return list<array{title: string, url: string, score: int}> */
    public function findResults(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $scored = [];
        foreach ($this->buildIndex() as $entry) {
            $score = $this->scoreEntry($entry, $query);
            if ($score >= 5) {
                $scored[] = [
                    'title' => $entry['title'],
                    'url'   => $entry['url'],
                    'score' => $score,
                ];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    /** @return list<array{title: string, url: string, keywords: string, content: string, boost?: int}> */
    private function buildIndex(): array
    {
        if ($this->index !== null) {
            return $this->index;
        }

        $entries = $this->staticEntries();
        $entries = array_merge($entries, $this->metadataEntries());
        $entries = array_merge($entries, $this->productEntries());
        $entries = array_merge($entries, $this->testimonialEntries());
        $entries = array_merge($entries, $this->aboutEntries());

        $this->index = $entries;

        return $this->index;
    }

    /** @return list<array{title: string, url: string, keywords: string, content: string, boost?: int}> */
    private function staticEntries(): array
    {
        $home = route('home');

        return [
            [
                'title'    => 'Inicio',
                'url'      => $home,
                'keywords' => 'inicio home principal Valento polos',
                'content'  => 'tienda polos moda',
            ],
            [
                'title'    => 'Tienda',
                'url'      => route('tienda'),
                'keywords' => 'tienda productos polos comprar catálogo',
                'content'  => 'explora nuestros polos y productos',
                'boost'    => 8,
            ],
            [
                'title'    => 'Nosotros',
                'url'      => route('nosotros'),
                'keywords' => 'nosotros about equipo historia quiénes somos',
                'content'  => 'conoce nuestra marca',
            ],
            [
                'title'    => 'Productos',
                'url'      => route('productos'),
                'keywords' => 'productos polos colección',
                'content'  => 'nuestros productos',
            ],
            [
                'title'    => 'Contacto',
                'url'      => route('contacto.pagina'),
                'keywords' => 'contacto whatsapp teléfono escribir mensaje',
                'content'  => 'contáctanos',
            ],
            [
                'title'    => 'Videos',
                'url'      => route('videos'),
                'keywords' => 'videos galería multimedia',
                'content'  => 'contenido en video',
            ],
            [
                'title'    => 'Maquinarias',
                'url'      => route('maquinarias'),
                'keywords' => 'maquinarias equipos',
                'content'  => 'maquinaria y equipos',
            ],
            [
                'title'    => 'Libro de reclamaciones',
                'url'      => route('liam.reclamaciones'),
                'keywords' => 'libro reclamaciones queja reclamo',
                'content'  => 'presenta tu reclamo',
            ],
            [
                'title'    => 'Términos y condiciones',
                'url'      => route('liam.terminos'),
                'keywords' => 'términos condiciones legal contrato',
                'content'  => 'términos del servicio',
            ],
            [
                'title'    => 'Política de privacidad',
                'url'      => route('liam.privacidad'),
                'keywords' => 'política privacidad datos personales cookies',
                'content'  => 'protección de datos',
            ],
        ];
    }

    /** @return list<array{title: string, url: string, keywords: string, content: string, boost?: int}> */
    private function metadataEntries(): array
    {
        if (! Schema::hasTable('metadatos_paginas')) {
            return [];
        }

        $map = [
            'home'                     => route('home'),
            'web_about'                => route('nosotros'),
            'web_gallery'              => route('tienda'),
            'web_contact'              => route('contacto.pagina'),
            'web_terminos_condiciones' => route('liam.terminos'),
            'web_politica_privacidad'  => route('liam.privacidad'),
        ];

        $entries = [];
        foreach ($map as $pagina => $url) {
            $meta = MetadatosPagina::metaVigente($pagina);
            if (! $meta) {
                continue;
            }
            $entries[] = [
                'title'    => (string) ($meta->titulo_pagina ?? $pagina),
                'url'      => $url,
                'keywords' => (string) ($meta->nombre_pagina ?? $pagina),
                'content'  => (string) ($meta->descripcion_pagina ?? ''),
            ];
        }

        return $entries;
    }

    /** @return list<array{title: string, url: string, keywords: string, content: string, boost?: int}> */
    private function productEntries(): array
    {
        if (! Schema::hasTable('administracion_producto')) {
            return [];
        }

        $entries = [];
        foreach (Producto::where('Activo', 'S')->orderBy('nombre')->limit(200)->get() as $producto) {
            $slug = $producto->slug ?: Str::slug((string) $producto->nombre);
            $url = $slug ? route('product.detail', $slug) : route('tienda');

            $entries[] = [
                'title'    => (string) ($producto->nombre ?? 'Producto'),
                'url'      => $url,
                'keywords' => implode(' ', array_filter([
                    $producto->sku ?? null,
                    'producto polo tienda',
                ])),
                'content'  => strip_tags((string) ($producto->descripcion ?? '')),
                'boost'    => 10,
            ];
        }

        return $entries;
    }

    /** @return list<array{title: string, url: string, keywords: string, content: string, boost?: int}> */
    private function testimonialEntries(): array
    {
        if (! Schema::hasTable('web_testimonios')) {
            return [];
        }

        $entries = [];
        foreach (WebTestimonios::where('Activo', 'S')->orderBy('orden')->get() as $t) {
            $entries[] = [
                'title'    => (string) ($t->nombre ?? 'Testimonio'),
                'url'      => route('home') . '#testimonios',
                'keywords' => 'testimonio reseña opinión ' . ($t->subtitulo ?? ''),
                'content'  => strip_tags((string) ($t->testimonio ?? '')),
            ];
        }

        return $entries;
    }

    /** @return list<array{title: string, url: string, keywords: string, content: string, boost?: int}> */
    private function aboutEntries(): array
    {
        $entries = [];

        if (Schema::hasTable('web_pagina_nosotros')) {
            $pagina = WebPaginaNosotros::first();
            if ($pagina) {
                $entries[] = [
                    'title'    => (string) ($pagina->titulo ?? 'Nosotros'),
                    'url'      => route('nosotros'),
                    'keywords' => 'nosotros historia empresa about',
                    'content'  => strip_tags((string) ($pagina->descripcion ?? $pagina->contenido ?? '')),
                ];
            }
        }

        if (Schema::hasTable('web_about')) {
            $about = WebAbout::where('Activo', 'S')->first();
            if ($about) {
                $entries[] = [
                    'title'    => (string) ($about->titulo ?? 'About'),
                    'url'      => route('home') . '#nosotros',
                    'keywords' => 'about nosotros bienvenida',
                    'content'  => strip_tags(implode(' ', array_filter([
                        $about->descripcion ?? null,
                        $about->parrafo ?? null,
                    ]))),
                ];
            }
        }

        return $entries;
    }

    /** @param array{title: string, url: string, keywords: string, content: string, boost?: int} $entry */
    private function scoreEntry(array $entry, string $query): int
    {
        $q = $this->normalize($query);
        if ($q === '') {
            return 0;
        }

        $title = $this->normalize($entry['title']);
        $keywords = $this->normalize($entry['keywords']);
        $content = $this->normalize($entry['content']);
        $haystack = trim($title . ' ' . $keywords . ' ' . $content);

        $score = (int) ($entry['boost'] ?? 0);

        if ($title === $q) {
            $score += 120;
        } elseif (str_contains($title, $q)) {
            $score += 80;
        }

        if (str_contains($keywords, $q)) {
            $score += 50;
        }

        if (str_contains($content, $q)) {
            $score += 25;
        }

        if (str_contains($haystack, $q)) {
            $score += 15;
        }

        foreach (preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            if (mb_strlen($word) < 3) {
                continue;
            }
            if (str_contains($title, $word)) {
                $score += 18;
            }
            if (str_contains($keywords, $word)) {
                $score += 12;
            }
            if (str_contains($content, $word)) {
                $score += 6;
            }
        }

        return $score;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim(strip_tags($text)));

        return strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        ]);
    }
}
