<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAllowedDomains
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowedDomains = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
            'http://127.0.0.1',
            'https://rossety.pe',
            'https://www.rossety.pe',
        ];

        $origin = $request->headers->get('Origin');

        if ($origin && !in_array($origin, $allowedDomains)) {
            return response('Forbidden', 403);
        }

        return $next($request);
    }
}