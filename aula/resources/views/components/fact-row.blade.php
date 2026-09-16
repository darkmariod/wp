@props(['cols' => 3])

{{--
    Fila de datos separados por líneas, no tarjetas: "3 experiencias · Al
    día con la guía · Presente hoy" leído como una sola frase de estado,
    no como widgets de dashboard. Apilada en mobile (líneas horizontales),
    en columnas desde sm (líneas verticales).
--}}
@php
    $colsClass = match ((int) $cols) {
        4 => 'sm:grid-cols-4',
        2 => 'sm:grid-cols-2',
        default => 'sm:grid-cols-3',
    };
@endphp

<div {{ $attributes->class(["grid grid-cols-1 divide-y divide-green-100 border-y border-green-100 sm:divide-y-0 sm:divide-x $colsClass"]) }}>
    {{ $slot }}
</div>
