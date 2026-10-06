@props(['href', 'icon', 'active' => false])

{{-- El estado activo no depende solo del color: lleva barra a la izquierda y más peso. --}}
<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'relative flex min-h-[44px] items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors duration-150',
        'bg-bib-primary-soft font-semibold text-bib-primary before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-bib-primary' => $active,
        'font-medium text-bib-text-secondary hover:bg-bib-surface-secondary hover:text-bib-text' => ! $active,
    ]) }}
>
    <x-biblioteca.icon :name="$icon" />
    <span>{{ $slot }}</span>
</a>
