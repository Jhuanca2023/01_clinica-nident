<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsVerifyEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)

    {
        if (!Auth::user()->is_email_verified) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Necesita confirmar su cuenta. Le hemos enviado un link de activación. Por favor, revise su correo electrónico.',
            ]);
          }
          return $next($request);
    }
}
