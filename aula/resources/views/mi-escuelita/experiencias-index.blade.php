@extends('layouts.app')

@section('title', 'Experiencias — Mi Escuelita')

@section('content')
    <livewire:mi-escuelita.experiencia-lista :areas="$areas" :filtro-area="$filtroArea" />
@endsection