@extends('layouts.guest')

@section('title', 'Restablecer contraseña — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-900">Restablecer contraseña</h1>
        <p class="mt-1 text-sm text-ink-600">Elegí una contraseña nueva para tu cuenta.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}" />

        <div>
            <label for="email" class="block text-sm font-medium text-ink-600">Correo</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $email) }}"
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
            <label for="password" class="block text-sm font-medium text-ink-600">Contraseña nueva</label>
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
            class="inline-flex w-full items-center justify-center rounded-full bg-green-800 px-6 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Restablecer contraseña
        </button>
    </form>
@endsection