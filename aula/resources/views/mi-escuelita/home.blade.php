@extends('layouts.app')

@section('title', 'Inicio — Mi Escuelita')

@section('content')
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-ink-900">
                    ¡Hola, {{ $familia['nombre'] ?? 'familia' }}! 👋
                </h1>
                @if ($ninoActual)
                    <a href="{{ route('mi-escuelita.historial') }}" class="mt-3 inline-flex items-center gap-3 rounded-lg border border-green-100 bg-white p-2 pr-4 shadow-card transition-base hover:shadow-card-hover focus-ring">
                        @if ($ninoActual->avatarUrl())
                            <img src="{{ $ninoActual->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold text-white {{ $ninoActual->avatarColorClass() }}">{{ $ninoActual->avatarInitials() }}</span>
                        @endif
                        <span>
                            <span class="block text-xs font-medium text-ink-400">Seguimos el desarrollo de</span>
                            <span class="block text-sm font-semibold text-ink-900">{{ $ninoActual->name }}</span>
                        </span>
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a
                href="{{ route('mi-escuelita.experiencias.index') }}"
                class="group rounded-lg border border-green-100 bg-white p-6 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Explorar experiencias</h2>
                        <p class="mt-1 text-sm text-ink-400">Descubrí las experiencias que la guía va presentando.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700/40 transition-base group-hover:text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>

            <a
                href="{{ route('mi-escuelita.historial') }}"
                class="group rounded-lg border border-green-100 bg-white p-6 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Mis experiencias</h2>
                        <p class="mt-1 text-sm text-ink-400">Revisá lo que ya compartiste y la respuesta de la guía.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700/40 transition-base group-hover:text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>
        </div>

        @if ($areas->isNotEmpty())
            <h2 class="mt-10 text-xl font-semibold text-ink-900">Áreas de desarrollo</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($areas as $area)
                    <a
                        href="{{ route('mi-escuelita.experiencias.index', ['area' => $area->id]) }}"
                        class="rounded-lg border border-green-100 bg-white p-5 shadow-card transition-base hover:shadow-card-hover focus-ring"
                    >
                        <span class="text-2xl" aria-hidden="true">{{ $area->icon }}</span>
                        <h3 class="mt-2 font-semibold text-ink-900">{{ $area->name }}</h3>
                        @if ($area->description)
                            <p class="mt-1 text-sm text-ink-400">{{ $area->description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection