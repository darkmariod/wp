@extends('layouts.app')

@section('title', 'Inicio — Mi Escuelita')

@section('content')
    <div>
        {{--
            Hero del portal. Mismo panel verde profundo que la portada:
            ancla la identidad del colegio y corta la sucesión de tarjetas
            blancas que hacía ver todo plano.
        --}}
        <section class="relative overflow-hidden rounded-2xl bg-green-950 px-8 py-12 sm:px-12 sm:py-14">
            <svg
                class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 text-green-800/40"
                viewBox="0 0 200 200"
                fill="none"
                stroke="currentColor"
                aria-hidden="true"
            >
                <circle cx="100" cy="100" r="30" />
                <circle cx="100" cy="100" r="50" />
                <circle cx="100" cy="100" r="70" />
                <circle cx="100" cy="100" r="90" />
            </svg>

            <div class="relative">
                <p class="text-sm font-medium uppercase tracking-widest text-accent-500">
                    Espacio para familias
                </p>

                <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">
                    ¡Hola, {{ $familia['nombre'] ?? 'familia' }}!
                </h1>

                @if ($ninoActual)
                    <a
                        href="{{ route('mi-escuelita.historial') }}"
                        class="mt-6 inline-flex min-h-[44px] items-center gap-3 rounded-full border border-green-800 bg-green-900/60 p-2 pr-5 transition-fast hover:border-green-700 focus-ring"
                    >
                        @if ($ninoActual->avatarUrl())
                            <img src="{{ $ninoActual->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold text-white {{ $ninoActual->avatarColorClass() }}">{{ $ninoActual->avatarInitials() }}</span>
                        @endif
                        <span class="text-left">
                            <span class="block text-xs font-medium text-green-300">Seguimos el desarrollo de</span>
                            <span class="block text-sm font-semibold text-white">{{ $ninoActual->name }}</span>
                        </span>
                    </a>
                @endif
            </div>
        </section>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a
                href="{{ route('mi-escuelita.experiencias.index') }}"
                class="group rounded-lg border border-green-100 bg-white p-6 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Explorar experiencias</h2>
                        <p class="mt-1 text-sm text-ink-400">Descubre las experiencias que la guía va presentando.</p>
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
                        <p class="mt-1 text-sm text-ink-400">Revisa lo que ya compartiste y la respuesta de la guía.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700/40 transition-base group-hover:text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>

            <a
                href="{{ route('mi-escuelita.asistencia') }}"
                class="group rounded-lg border border-green-100 bg-white p-6 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Asistencia</h2>
                        <p class="mt-1 text-sm text-ink-400">Sigue la asistencia de tu niño, mes a mes.</p>
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
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-green-50 text-green-700">
                            <x-area-icon :name="$area->icon" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-3 font-semibold text-ink-900">{{ $area->name }}</h3>
                        @if ($area->description)
                            <p class="mt-1 text-sm text-ink-400">{{ $area->description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection