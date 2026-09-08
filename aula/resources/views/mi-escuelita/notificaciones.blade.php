@extends('layouts.app')

@section('title', 'Notificaciones — Mi Escuelita')

@section('content')
    <div>
        <h1 class="text-3xl font-bold text-ink-900">Notificaciones</h1>
        <p class="mt-2 text-sm text-ink-400">
            Elegí qué avisos querés recibir por correo.
        </p>

        <form method="POST" action="{{ route('mi-escuelita.notificaciones.update') }}" class="mt-8 space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-lg border border-green-100 bg-white p-6 shadow-card">
                <label for="content_published" class="flex cursor-pointer items-start gap-3">
                    <input
                        id="content_published"
                        name="content_published"
                        type="checkbox"
                        value="1"
                        @checked($preferencias['content_published'])
                        class="mt-1 h-4 w-4 rounded border-green-300 text-green-800 focus-ring"
                    />
                    <span>
                        <span class="block font-medium text-ink-900">Nueva experiencia publicada</span>
                        <span class="mt-0.5 block text-sm text-ink-400">
                            Avisame cuando la guía publique una experiencia nueva.
                        </span>
                    </span>
                </label>
            </div>

            <div class="rounded-lg border border-green-100 bg-white p-6 shadow-card">
                <label for="guide_responded" class="flex cursor-pointer items-start gap-3">
                    <input
                        id="guide_responded"
                        name="guide_responded"
                        type="checkbox"
                        value="1"
                        @checked($preferencias['guide_responded'])
                        class="mt-1 h-4 w-4 rounded border-green-300 text-green-800 focus-ring"
                    />
                    <span>
                        <span class="block font-medium text-ink-900">Respuesta de la guía</span>
                        <span class="mt-0.5 block text-sm text-ink-400">
                            Avisame cuando la guía responde a una experiencia compartida.
                        </span>
                    </span>
                </label>
            </div>

            @error('content_published')
                <p class="text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
            @error('guide_responded')
                <p class="text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-green-800 px-6 py-2.5 text-sm font-medium text-white shadow-card-hover transition-base hover:bg-green-700 focus-ring"
            >
                Guardar preferencias
            </button>
        </form>
    </div>
@endsection