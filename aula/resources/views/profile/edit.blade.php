@extends('layouts.app')

@section('title', 'Perfil — Mi Escuelita')

@section('content')
    <h1 class="text-2xl font-bold text-ink-900">Perfil</h1>

    <div class="mt-6 max-w-2xl space-y-6">
        @include('profile.partials.update-profile-information-form')

        @include('profile.partials.update-password-form')

        @include('profile.partials.delete-user-form')
    </div>
@endsection