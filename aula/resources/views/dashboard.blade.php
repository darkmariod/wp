@extends('layouts.app')

@section('title', 'Panel — Mi Escuelita')

@section('content')
    <div class="rounded-lg border border-green-100 bg-white p-6 shadow-card">
        @if (auth()->user()?->isFamilia())
            <h1 class="text-xl font-semibold text-ink-900">¡Bienvenido/a a Mi Escuelita!</h1>
            <p class="mt-2 text-sm text-ink-600">
                Entrá al portal de tu familia para ver las experiencias y compartir momentos con la guía.
            </p>
            <a
                href="{{ route('mi-escuelita.home') }}"
                class="mt-6 inline-flex items-center justify-center rounded-full bg-green-800 px-6 py-2.5 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring"
            >
                Ir al portal de mi familia
            </a>
        @else
            <h1 class="text-xl font-semibold text-ink-900">Panel</h1>
            <p class="mt-2 text-sm text-ink-600">
                El panel de administración está en <a href="/admin" class="rounded-md font-medium text-green-800 transition-fast hover:text-green-900 focus-ring">/admin</a>.
            </p>
        @endif
    </div>
@endsection