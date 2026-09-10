@props([
    'iconClass' => 'h-9 w-auto',
    'textClass' => 'text-lg font-semibold tracking-tight text-green-800',
    'tagline' => false,
])

{{--
    Marca del portal: el isotipo real de la institución + el nombre del
    producto ("Mi Escuelita" es el sub-nombre amigable del portal, como
    "Classroom" al lado del logo de Google — no reemplaza a Pestalozzi,
    lo acompaña). El SVG es el logo oficial del colegio, el mismo que usa
    pestalozzi-opal.vercel.app: es apropiado reusarlo acá porque este
    portal ES del colegio, a diferencia de fotos de alumnos, que no se
    replican sin que la institución las provea.
--}}
<span {{ $attributes->class(['inline-flex items-center gap-2.5']) }}>
    <img
        src="{{ asset('images/pestalozzi-logo.svg') }}"
        alt="Unidad Educativa Pestalozzi Ambato"
        class="{{ $iconClass }}"
    >
    <span class="flex flex-col justify-center leading-none">
        <span class="{{ $textClass }}">Mi Escuelita</span>
        @if ($tagline)
            <span class="mt-0.5 text-xs font-medium text-ink-400">Unidad Educativa Pestalozzi</span>
        @endif
    </span>
</span>
