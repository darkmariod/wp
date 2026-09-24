@extends('layouts.app')

@section('title', 'Inicio — Mi Escuelita')

@section('content')
    @php
        $etiquetaHoy = [
            \App\Models\Attendance::STATUS_PRESENTE => 'Presente',
            \App\Models\Attendance::STATUS_ATRASO => 'Llegó tarde',
            \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => 'Falta justificada',
            \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => 'Falta injustificada',
        ][$asistenciaHoy] ?? null;
    @endphp

    <header class="border-b border-green-100 pb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-green-700">Espacio para familias</p>

        <h1 class="mt-2 text-[28px] font-semibold leading-tight text-ink-900 sm:text-3xl">
            ¡Hola, {{ $familia['nombre'] ?? 'familia' }}!
        </h1>

        @if ($ninoActual)
            <p class="mt-3 max-w-prose text-ink-600">
                Estamos acompañando el desarrollo de
                <a
                    href="{{ route('mi-escuelita.historial') }}"
                    class="font-semibold text-green-800 underline decoration-green-300 underline-offset-4 transition-fast hover:decoration-green-700 focus-ring"
                >{{ $ninoActual->name }}</a>.
                @if ($etiquetaHoy)
                    Hoy en el colegio: {{ strtolower($etiquetaHoy) }}.
                @endif
            </p>
        @endif
    </header>

    {{--
        Resumen leído como una frase, no como tres widgets: cuántas
        experiencias hay para ver, si algo espera respuesta, y cómo viene
        la asistencia. Sin cajas — son hechos, no botones.
    --}}
    <x-fact-row :cols="3" class="mt-8">
        <a href="{{ route('mi-escuelita.experiencias.index') }}" class="group block py-3 transition-fast sm:px-6 sm:py-0 sm:first:pl-0">
            <p class="text-2xl font-semibold text-ink-900 group-hover:text-green-800">{{ $totalExperiencias }}</p>
            <p class="mt-0.5 text-sm text-ink-600">experiencias disponibles</p>
        </a>

        <a href="{{ route('mi-escuelita.historial') }}" class="group block py-3 transition-fast sm:px-6">
            <p class="text-2xl font-semibold text-ink-900 group-hover:text-green-800">
                {{ $sinResponder > 0 ? $sinResponder : 'Al día' }}
            </p>
            <p class="mt-0.5 text-sm text-ink-600">
                {{ $sinResponder > 0 ? 'esperando respuesta de la docente' : 'con la docente' }}
            </p>
        </a>

        <a href="{{ route('mi-escuelita.asistencia') }}" class="group block py-3 transition-fast sm:px-6 sm:pr-0">
            <p class="text-2xl font-semibold text-ink-900 group-hover:text-green-800">{{ $etiquetaHoy ?? '—' }}</p>
            <p class="mt-0.5 text-sm text-ink-600">asistencia de hoy</p>
        </a>
    </x-fact-row>

    @if ($areas->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Áreas de desarrollo</h2>

            <ul class="mt-4 border-t border-green-100">
                @foreach ($areas as $area)
                    <li class="border-b border-green-100">
                        <a
                            href="{{ route('mi-escuelita.experiencias.index', ['area' => $area->id]) }}"
                            class="flex items-center justify-between gap-6 py-4 transition-fast hover:bg-green-50/40 focus-ring"
                        >
                            <span class="flex items-center gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-700">
                                    <x-area-icon :name="$area->icon" class="h-5 w-5" />
                                </span>
                                <span class="font-medium text-ink-900">{{ $area->name }}</span>
                            </span>

                            @if ($area->description)
                                <span class="hidden max-w-sm text-right text-sm text-ink-400 sm:block">{{ $area->description }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
