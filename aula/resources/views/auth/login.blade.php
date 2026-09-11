@extends('layouts.guest')

@section('title', 'Iniciar sesión — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-900">Iniciar sesión</h1>
        <p class="mt-1 text-sm text-ink-600">Entra al espacio de tu familia en Mi Escuelita.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ink-600">Correo</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink-600">Contraseña</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-4 text-sm">
            <label for="remember" class="flex min-h-[44px] items-center gap-2 text-ink-600">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                />
                Recordarme
            </label>

            @if ($canResetPassword)
                <a href="{{ route('password.request') }}" class="rounded-md text-green-800 transition-fast hover:text-green-900 focus-ring">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        @if (session('status'))
            <p class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</p>
        @endif

        <button
            type="submit"
            class="inline-flex min-h-[44px] w-full items-center justify-center rounded-full bg-green-800 px-6 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Iniciar sesión
        </button>

        @if (Route::has('register'))
            <p class="pt-2 text-center text-sm text-ink-600">
                ¿Todavía no tienes cuenta?
                <a href="{{ route('register') }}" class="rounded-md font-medium text-green-800 transition-fast hover:text-green-900 focus-ring">
                    Crear cuenta
                </a>
            </p>
        @endif
    </form>
@endsection