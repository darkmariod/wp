@props(['name' => '', 'class' => 'h-6 w-6'])

@php
    // Set propio de íconos de línea por área. Todos comparten la misma
    // caja (24×24), mismo grosor de trazo (1.5) y color actual, para que
    // formen un conjunto coherente y coloreable con los tokens de marca.
    // Los slugs viven en areas.icon (antes eran emojis).
    $paths = [
        'lectura' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        'matematica' => 'M3 9.75a21 21 0 0 0 9 2.25m0 0a21 21 0 0 1 9-2.25M12 12v9m0 0c-1.7 1.2-4.5 1.5-6 6m6-6c1.7 1.2 4.5 1.5 6 6M12 12l-2.25 6m2.25-6 2.25 6',
        'ingles' => 'M10.5 21 15 6l4.5 15M13.8 15.375h-3.6M18.75 12h-13.5',
        'vida-practica' => 'M2.25 12 11.204 3.045a1.125 1.125 0 0 1 1.591 0L21.75 12m-17.25 0v6.75c0 .621.504 1.125 1.125 1.125H9v-3.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75V19.5h3.375c.621 0 1.125-.504 1.125-1.125V12',
        'arte' => 'M5.281 15.727 4.5 19.5l3.773-.781M5.281 15.727l9.25-9.25a1.591 1.591 0 0 1 2.25 0l1.717 1.717a1.591 1.591 0 0 1 0 2.25l-9.25 9.25M5.281 15.727l3.719 3.719',
        'cultura' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM8.25 4.26l-.42.64a9 9 0 0 0 0 14.2l.42.64M15.75 4.26l.42.64a9 9 0 0 1 0 14.2l-.42.64',
    ];

    $d = $paths[$name] ?? $paths['cultura'];
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    class="{{ $class }}"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="1.5"
    aria-hidden="true"
>
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}" />
</svg>
