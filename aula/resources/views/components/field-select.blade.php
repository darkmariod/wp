@props([
    'label' => null,
    'hideLabel' => true,
])

{{--
    Select de la marca.

    Envuelve un <select> real en vez de reimplementarlo con Alpine: en móvil
    el selector nativo del sistema es mejor experiencia que cualquier lista
    propia, y además llega gratis el teclado, el lector de pantalla y el
    binding de Livewire.

    Lo único que hacemos es apagar la pintura del sistema (appearance-none)
    y poner nuestro propio chevron, que es lo que hacía ver el control como
    un formulario sin diseñar.
--}}
<label class="block">
    @if ($label)
        <span @class(['sr-only' => $hideLabel, 'mb-1 block text-sm font-medium text-ink-600' => ! $hideLabel])>
            {{ $label }}
        </span>
    @endif

    <span class="relative block">
        <select
            {{ $attributes->class([
                'w-full min-h-[44px] appearance-none rounded-md border border-green-100 bg-white',
                'py-2 pl-4 pr-10 text-sm font-medium text-ink-900',
                'transition-fast hover:border-green-300 focus-ring',
                'cursor-pointer',
            ]) }}
        >
            {{ $slot }}
        </select>

        <svg
            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-green-700"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </span>
</label>
