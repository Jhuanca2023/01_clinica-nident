<?php

namespace App\Support;

use App\Helpers\Helper;
use App\Models\MetadatosPagina;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SocialSharePreviewData
{
    public static function forRequest(Request $request): array
    {
        $slug = self::pageSlug($request);
        $meta = MetadatosPagina::metaVigente($slug);

        $title = self::cleanText(
            $meta?->titulo_pagina
            ?? 'Valento | Ropa laboral y textil'
        );

        $description = self::cleanText(
            $meta?->descripcion_pagina
            ?? 'Valento — Ropa laboral y textil de calidad para tu empresa.'
        );

        if (strlen($description) > 200) {
            $description = Str::limit($description, 197, '…');
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => Helper::siteOgImageUrl(),
            'imageWidth' => Helper::LIAM_OG_IMAGE_WIDTH,
            'imageHeight' => Helper::LIAM_OG_IMAGE_HEIGHT,
            'url' => self::canonicalUrlForRequest($request),
            'siteName' => 'Valento',
        ];
    }

    private static function canonicalUrlForRequest(Request $request): string
    {
        $path = trim($request->path(), '/');
        $base = Helper::publicBaseUrl();

        return $path === '' ? $base : $base . '/' . $path;
    }

    private static function pageSlug(Request $request): string
    {
        $path = trim($request->path(), '/');

        return match ($path) {
            '', 'home' => 'home',
            'nosotros' => 'web_about',
            'contacto' => 'web_contact',
            'tienda', 'web_gallery' => 'web_gallery',
            'terminos-condiciones', 'web_terminos' => 'web_terminos_condiciones',
            'politica-privacidad' => 'web_politica_privacidad',
            'libro-reclamaciones', 'web_libro_reclamos' => 'web_libro_reclamos',
            'shop-detail-lista' => 'web_shopDetail_lista',
            default => 'home',
        };
    }

    private static function cleanText(?string $text): string
    {
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }
}
