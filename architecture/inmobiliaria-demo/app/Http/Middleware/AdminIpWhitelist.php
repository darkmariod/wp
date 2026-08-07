<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpWhitelist
{
    /**
     * Restringe el acceso al admin por IP en producción.
     *
     * En local permite todo. En producción solo las IPs en ADMIN_ALLOWED_IPS.
     * Si no está en la whitelist, responde 404 (no revela que el admin existe).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $allowed = collect(explode(',', env('ADMIN_ALLOWED_IPS', '')))
            ->map(fn ($ip) => trim($ip))
            ->filter();

        if ($allowed->isEmpty()) {
            return $next($request);
        }

        $requestIp = $request->ip();

        foreach ($allowed as $cidr) {
            if ($this->ipMatches($requestIp, $cidr)) {
                return $next($request);
            }
        }

        abort(404);
    }

    private function ipMatches(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $bits] = explode('/', $cidr, 2);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
