{{-- Provisional: T07 reemplaza este contenido por la Biblioteca real (búsqueda, filtros y recursos). --}}
@extends('layouts.biblioteca')

@section('heading', 'Biblioteca')
@section('subtitle', 'Encuentra materiales, documentos y recursos para tus clases.')

@section('content')
    <section class="flex flex-col items-center rounded-xl border border-dashed border-bib-border px-6 py-16 text-center">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-bib-primary-soft text-bib-primary">
            <x-biblioteca.icon name="book-open" class="h-6 w-6" />
        </span>
        <h2 class="mt-4 text-lg font-semibold text-bib-text">Aún no hay recursos para mostrar</h2>
        <p class="mt-2 max-w-md text-base text-bib-text-secondary">
            Cuando tus docentes publiquen materiales de tus cursos, los encontrarás aquí.
        </p>
    </section>
@endsection
