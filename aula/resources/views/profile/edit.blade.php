@extends('layouts.app')

@section('title', 'Perfil — Mi Escuelita')

@section('content')
    <x-breadcrumb
        :items="[['label' => 'Inicio', 'url' => auth()->user()->isFamilia() ? route('mi-escuelita.home') : route('dashboard')], ['label' => 'Perfil']]"
    />
    <h1 class="mt-4 text-3xl font-bold text-ink-900">Perfil</h1>
    <p class="mt-2 text-sm text-ink-400">Tus datos de acceso a Mi Escuelita.</p>

    <div class="mt-6 max-w-2xl space-y-6">
        @include('profile.partials.update-profile-information-form')

        @include('profile.partials.update-password-form')

        @include('profile.partials.delete-user-form')
    </div>
@endsection