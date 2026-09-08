@extends('layouts.guest')

@section('title', 'Recuperar contraseña — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-900">¿Olvidaste tu contraseña?</h1>
        <p class="mt-1 text-sm text-ink-600">
            Te enviamos un enlace para restablecerla a tu correo.
        </p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
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

        @if (session('status'))
            <p class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</p>
        @endif

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-full bg-green-800 px-6 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Enviar enlace
        </button>

        <a href="{{ route('login') }}" class="block text-center text-sm text-ink-600 transition-fast hover:text-green-800 focus-ring">
            Volver a iniciar sesión
        </a>
    </form>
@endsection