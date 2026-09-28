<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\LayoutComposer;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
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

        // Sin esto, una familia ya logueada que vuelve a /login (un
        // link viejo, "atrás" del navegador) cae en /dashboard — la
        // pantalla intermedia de "Bienvenido/a" con un botón más para
        // tocar — en vez de directo a su portal. Mismo criterio que ya
        // usa AuthenticatedSessionController::store() al loguearse.
        RedirectIfAuthenticated::redirectUsing(function () {
            /** @var User|null $user */
            $user = Auth::user();

            return $user?->isFamilia() ? route('mi-escuelita.home') : route('dashboard');
        });
    }
}
