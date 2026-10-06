{{-- El estudiante usa el layout de la Biblioteca (sin /admin ni migas); familia y personal, el del portal. --}}
@php($esEstudiante = auth()->user()->isEstudiante())

@extends($esEstudiante ? 'layouts.biblioteca' : 'layouts.app')

@section('title', $esEstudiante ? 'Perfil' : 'Perfil — Mi Escuelita')

@if ($esEstudiante)
    @section('heading', 'Perfil')
    @section('subtitle', 'Tus datos de acceso a Mi Escuelita.')
@endif

@section('content')
    @unless ($esEstudiante)
        <x-breadcrumb
            :items="[['label' => 'Inicio', 'url' => auth()->user()->isFamilia() ? route('mi-escuelita.home') : route('dashboard')], ['label' => 'Perfil']]"
        />
        <h1 class="mt-4 text-3xl font-semibold text-ink-900">Perfil</h1>
        <p class="mt-2 text-sm text-ink-600">Tus datos de acceso a Mi Escuelita.</p>
    @endunless

    <div class="{{ $esEstudiante ? 'bib-legacy max-w-2xl divide-y divide-bib-border border-t border-bib-border' : 'mt-6 max-w-2xl divide-y divide-green-100 border-t border-green-100' }}">
        @include('profile.partials.update-profile-information-form')

        @include('profile.partials.update-password-form')

        @include('profile.partials.delete-user-form')
    </div>
@endsection
