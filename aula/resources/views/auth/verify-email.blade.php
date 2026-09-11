@extends('layouts.guest')

@section('title', 'Verificar correo — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-ink-900">Verificá tu correo</h1>
        <p class="mt-2 text-sm text-ink-600">
            Te enviamos un enlace de verificación. Revisá tu bandeja de entrada
            (y también la de correo no deseado) y hacé clic en el enlace.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <p class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            Te enviamos un enlace nuevo a la dirección de correo que registraste.
        </p>
    @endif

    <div class="mt-6 flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex min-h-[44px] w-full items-center justify-center rounded-full bg-green-800 px-6 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
            >
                Reenviar enlace de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex min-h-[44px] w-full items-center justify-center rounded-full border border-green-200 bg-white px-6 py-3 text-base font-medium text-green-800 transition-fast hover:bg-green-50 focus-ring"
            >
                Cerrar sesión
            </button>
        </form>
    </div>
@endsection