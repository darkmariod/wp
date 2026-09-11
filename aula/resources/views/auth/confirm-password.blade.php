@extends('layouts.guest')

@section('title', 'Confirmar contraseña — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-900">Confirmá tu contraseña</h1>
        <p class="mt-1 text-sm text-ink-600">
            Esta es una zona protegida. Confirmá tu contraseña para continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

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

        <button
            type="submit"
            class="inline-flex min-h-[44px] w-full items-center justify-center rounded-full bg-green-800 px-6 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Confirmar contraseña
        </button>
    </form>
@endsection