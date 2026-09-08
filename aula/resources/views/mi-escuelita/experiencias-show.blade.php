@extends('layouts.app')

@section('title', $content->title . ' — Mi Escuelita')

@section('content')
    <livewire:mi-escuelita.experiencia-detalle
        :content="$content"
        :ya-enviada="$yaEnviada"
        :es-previa="$esPrevia ?? false"
    />
@endsection