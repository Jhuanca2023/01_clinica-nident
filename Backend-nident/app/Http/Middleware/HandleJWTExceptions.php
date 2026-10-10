<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class HandleJWTExceptions
{
    public function handle($request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Token expirado',
                'error' => 'token_expired'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Token inválido',
                'error' => 'token_invalid'
            ], 401);
        }

        return $next($request);
    }
}