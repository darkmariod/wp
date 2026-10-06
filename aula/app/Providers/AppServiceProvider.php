<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\LayoutComposer;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        View::composer('layouts.app', LayoutComposer::class);

        $this->configureBibliotecaRateLimiters();

        // Sin esto, una familia ya logueada que vuelve a /login (un
        // link viejo, "atrás" del navegador) cae en /dashboard — la
        // pantalla intermedia de "Bienvenido/a" con un botón más para
        // tocar — en vez de directo a su portal (o a la Biblioteca, si es
        // estudiante). Mismo criterio que ya usa
        // AuthenticatedSessionController::store() al loguearse.
        RedirectIfAuthenticated::redirectUsing(function () {
            /** @var User|null $user */
            $user = Auth::user();

            return match (true) {
                $user?->isFamilia() === true => route('mi-escuelita.home'),
                $user?->isEstudiante() === true => route('biblioteca.index'),
                default => route('dashboard'),
            };
        });
    }

    /**
     * Límites de la entrega de archivos de la Biblioteca, por usuario (con la
     * IP como respaldo si no hubiera sesión). Los valores salen de la config.
     */
    private function configureBibliotecaRateLimiters(): void
    {
        foreach (['archivos' => 'biblioteca-archivos', 'descargas' => 'biblioteca-descargas'] as $clave => $nombre) {
            RateLimiter::for($nombre, fn (Request $request) => Limit::perMinute(max(1, (int) config("biblioteca.rate_limit.{$clave}")))
                ->by($nombre.':'.($request->user()?->id ?? $request->ip())));
        }
    }
}
