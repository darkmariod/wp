@extends('layouts.guest')

@section('title', 'Crear cuenta — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-900">Crear cuenta</h1>
        <p class="mt-1 text-sm text-ink-600">Regístrate para entrar al espacio de tu familia.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-ink-600">Nombre</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-ink-600">Correo</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
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
                autocomplete="new-password"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-ink-600">Confirmar contraseña</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="inline-flex min-h-[44px] w-full items-center justify-center rounded-full bg-green-800 px-6 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Crear cuenta
        </button>

        <p class="pt-2 text-center text-sm text-ink-600">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="rounded-md font-medium text-green-800 transition-fast hover:text-green-900 focus-ring">
                Iniciar sesión
            </a>
        </p>
    </form>
@endsection