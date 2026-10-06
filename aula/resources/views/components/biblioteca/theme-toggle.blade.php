@php
    $opciones = [
        'light' => ['Claro', 'sun'],
        'dark' => ['Oscuro', 'moon'],
        'system' => ['Automático', 'computer-desktop'],
    ];
@endphp

{{-- Estado en el store 'theme' (resources/js/biblioteca.js); se guarda en localStorage. --}}
<div
    role="group"
    aria-label="Tema de color"
    {{ $attributes->class('grid grid-cols-3 gap-1 rounded-xl border border-bib-border bg-bib-surface-secondary p-1') }}
>
    @foreach ($opciones as $valor => [$etiqueta, $icono])
        <button
            type="button"
            @click="$store.theme.set('{{ $valor }}')"
            :aria-pressed="$store.theme.value === '{{ $valor }}'"
            :class="$store.theme.value === '{{ $valor }}' ? 'bg-bib-surface text-bib-primary ring-1 ring-bib-border' : 'text-bib-text-secondary hover:text-bib-text'"
            aria-pressed="false"
            aria-label="Tema {{ mb_strtolower($etiqueta) }}"
            title="Tema {{ mb_strtolower($etiqueta) }}"
            class="flex min-h-[44px] cursor-pointer items-center justify-center rounded-sm transition-colors duration-150"
        >
            <x-biblioteca.icon :name="$icono" />
        </button>
    @endforeach
</div>
