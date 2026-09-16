@extends('layouts.app')

@section('title', 'Inicio — Mi Escuelita')

@section('content')
    @php
        $etiquetaHoy = [
            \App\Models\Attendance::STATUS_PRESENTE => ['Presente hoy', 'bg-green-800/60 text-green-100'],
            \App\Models\Attendance::STATUS_ATRASO => ['Llegó tarde hoy', 'bg-amber-500/20 text-amber-200'],
            \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => ['Falta justificada hoy', 'bg-sky-500/20 text-sky-200'],
            \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => ['Falta injustificada hoy', 'bg-rose-500/20 text-rose-200'],
        ][$asistenciaHoy] ?? null;
    @endphp

    <div>
        {{--
            Hero del portal. Mismo panel verde profundo que la portada:
            ancla la identidad del colegio. Sin formas decorativas — el
            contenido real (niño actual + asistencia de hoy) hace el
            trabajo que antes hacían los círculos de fondo.
        --}}
        <section class="rounded-2xl bg-green-950 px-8 py-10 sm:px-12 sm:py-12">
            <p class="text-sm font-medium uppercase tracking-widest text-accent-500">
                Espacio para familias
            </p>

            <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">
                ¡Hola, {{ $familia['nombre'] ?? 'familia' }}!
            </h1>

            @if ($ninoActual)
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('mi-escuelita.historial') }}"
                        class="inline-flex min-h-[44px] items-center gap-3 rounded-full border border-green-800 bg-green-900/60 p-2 pr-5 transition-fast hover:border-green-700 focus-ring"
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

                    @if ($etiquetaHoy)
                        <span class="inline-flex min-h-[44px] items-center rounded-full px-4 text-sm font-medium {{ $etiquetaHoy[1] }}">
                            {{ $etiquetaHoy[0] }}
                        </span>
                    @endif
                </div>
            @endif
        </section>

        {{--
            Tres accesos, cada uno con un dato real en vez de solo un
            ícono decorativo — así la familia ve de un vistazo si hay
            algo esperando, no solo tres tarjetas idénticas.
        --}}
        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a
                href="{{ route('mi-escuelita.experiencias.index') }}"
                class="rounded-lg border border-green-100 bg-white p-5 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                <p class="text-3xl font-bold text-ink-900">{{ $totalExperiencias }}</p>
                <h2 class="mt-1 font-semibold text-ink-900">Experiencias</h2>
                <p class="mt-1 text-sm text-ink-400">Explorá lo que la guía va presentando.</p>
            </a>

            <a
                href="{{ route('mi-escuelita.historial') }}"
                class="rounded-lg border border-green-100 bg-white p-5 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                @if ($sinResponder > 0)
                    <p class="text-3xl font-bold text-accent-600">{{ $sinResponder }}</p>
                    <h2 class="mt-1 font-semibold text-ink-900">Mis experiencias</h2>
                    <p class="mt-1 text-sm text-ink-400">Esperando respuesta de la guía.</p>
                @else
                    <p class="text-3xl font-bold text-ink-900">✓</p>
                    <h2 class="mt-1 font-semibold text-ink-900">Mis experiencias</h2>
                    <p class="mt-1 text-sm text-ink-400">Todo al día con la guía.</p>
                @endif
            </a>

            <a
                href="{{ route('mi-escuelita.asistencia') }}"
                class="rounded-lg border border-green-100 bg-white p-5 shadow-card transition-base hover:shadow-card-hover focus-ring"
            >
                <p class="text-3xl font-bold text-ink-900">{{ $etiquetaHoy[0] ?? '—' }}</p>
                <h2 class="mt-1 font-semibold text-ink-900">Asistencia</h2>
                <p class="mt-1 text-sm text-ink-400">Calendario mes a mes de tu niño.</p>
            </a>
        </div>

        @if ($areas->isNotEmpty())
            <h2 class="mt-10 text-xl font-semibold text-ink-900">Áreas de desarrollo</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($areas as $area)
                    <a
                        href="{{ route('mi-escuelita.experiencias.index', ['area' => $area->id]) }}"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-full border border-green-100 bg-white px-4 text-sm font-medium text-ink-700 transition-fast hover:border-green-300 hover:bg-green-50 focus-ring"
                    >
                        <x-area-icon :name="$area->icon" class="h-4 w-4 text-green-700" />
                        {{ $area->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
