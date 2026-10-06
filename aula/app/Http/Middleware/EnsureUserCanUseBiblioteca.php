<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puerta de la Biblioteca: personal y estudiantes con la cuenta activa.
 * Las familias y las cuentas inactivas se rechazan.
 */
class EnsureUserCanUseBiblioteca
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canUseBiblioteca(), 403, 'No tienes acceso a la Biblioteca.');

        return $next($request);
    }
}
