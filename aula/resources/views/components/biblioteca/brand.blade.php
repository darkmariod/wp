@php
    $marca = (array) config('biblioteca.brand', []);
    $nombre = (string) ($marca['name'] ?? 'Pestalozzi');
    $logo = $marca['logo'] ?? null;
    $logoUrl = is_string($logo) && $logo !== ''
        ? (preg_match('#^(https?://|/)#i', $logo) === 1 ? $logo : asset($logo))
        : null;
@endphp

<a
    href="{{ route('biblioteca.index') }}"
    aria-label="{{ $nombre }} Biblioteca, ir al inicio"
    {{ $attributes->class('flex min-w-0 items-center gap-3 rounded-xl') }}
>
    @if ($logoUrl)
        <img src="{{ $logoUrl }}" alt="" class="h-9 w-auto max-w-[3rem] shrink-0 object-contain">
    @else
        <span class="bib-logo-default shrink-0"><x-brand-logo icon-class="h-9 w-auto" :show-text="false" /></span>
    @endif

    <span class="flex min-w-0 flex-col leading-tight">
        <span class="truncate text-base font-semibold text-bib-text">{{ $nombre }}</span>
        <span class="text-xs font-medium text-bib-text-secondary">Biblioteca</span>
    </span>
</a>
