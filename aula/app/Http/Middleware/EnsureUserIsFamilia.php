<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mi Escuelita es solo para familias. Una guía o admin autenticado que
 * intente entrar por acá también se rechaza — cada rol tiene su propia
 * puerta, ninguna sirve para la otra.
 *
 * Además se limita el ritmo de peticiones (60/min) para frenar el abuso
 * automatizado del lado de la familia.
 */
class EnsureUserIsFamilia
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isFamilia(), 403);

        $key = 'familia:'.$request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 60)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Demasiadas peticiones. Intenta de nuevo en {$seconds} segundos.");
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
