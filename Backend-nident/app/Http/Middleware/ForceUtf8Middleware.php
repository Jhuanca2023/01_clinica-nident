<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class ForceUtf8Middleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('comprobantes/*')
            || $request->is('sitemap.xml')
            || $request->is('robots.txt')
            || $request->is('cotizacion/ver/*/pdf')
            || $request->is('cotizacion/ver/*/descargar')
            || $request->is('sitemap.xml')
            || $request->is('robots.txt')
            || $response instanceof BinaryFileResponse) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'application/pdf')
            || str_contains($contentType, 'application/octet-stream')
            || str_contains($contentType, 'image/')
            || str_contains($contentType, 'application/zip')
            || str_contains($contentType, 'application/xml')
            || str_contains($contentType, 'text/xml')
            || str_contains($contentType, 'application/json')
            || str_contains($contentType, 'text/plain')) {
            return $response;
        }

        $body = ltrim((string) $response->getContent());
        if (str_starts_with($body, '<?xml')) {
            $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

            return $response;
        }

        $response->header('Content-Type', 'text/html; charset=UTF-8');

        return $response;
    }
}