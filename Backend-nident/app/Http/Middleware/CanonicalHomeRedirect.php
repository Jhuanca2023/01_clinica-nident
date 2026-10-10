<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Colapsa URLs con doble barra (//) a la canónica.
 */
class CanonicalHomeRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $request->is('admin', 'admin/*', 'api/*')) {
            return $next($request);
        }

        $pathInfo = $request->getPathInfo();
        $canonical = Helper::publicBaseUrl();

        if (str_contains($pathInfo, '//')) {
            $cleanPath = preg_replace('#/+#', '/', $pathInfo) ?: '/';
            $target = $cleanPath === '/' ? $canonical : $canonical . $cleanPath;

            return redirect($target, 301);
        }

        return $next($request);
    }
}
