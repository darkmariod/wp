<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puerta del personal (admin, coordinación y guías). Cualquier otro rol
 * (familia, estudiante) que intente entrar acá se rechaza.
 */
class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPanelRole()) {
            abort(403, 'Acceso restringido al personal.');
        }

        $key = 'staff:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 120)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Demasiadas peticiones. Intenta de nuevo en {$seconds} segundos.");
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
