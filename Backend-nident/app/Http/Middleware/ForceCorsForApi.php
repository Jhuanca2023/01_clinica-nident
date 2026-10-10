<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuerza cabeceras CORS correctas en respuestas API para que el front (ej. localhost:5173)
 * reciba Access-Control-Allow-Origin con su propio origen, no el del backend.
 */
class ForceCorsForApi
{
    protected $allowedOrigins = [
        'http://localhost',
        'http://localhost:5173',
        'http://localhost:8000',
        'http://127.0.0.1',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8000',
        'https://rossety.pe',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        if ($origin && $this->isOriginAllowed($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            // JWT usa Authorization header, no cookies; credentials no necesarias
            // $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }

        return $response;
    }

    protected function isOriginAllowed(string $origin): bool
    {
        if (in_array($origin, $this->allowedOrigins, true)) {
            return true;
        }
        foreach (['#^https?://localhost(:\d+)?$#', '#^https?://127\.0\.0\.1(:\d+)?$#'] as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }
        return false;
    }
}
