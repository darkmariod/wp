@props([
    'active' => false,
])

<button
    type="button"
    {{ $attributes }}
    @class([
        'inline-flex min-h-[44px] items-center rounded-full px-4 py-2 text-sm font-medium transition-base focus-ring',
        'bg-green-800 text-white shadow-card-hover' => $active,
        'border border-green-100 bg-white text-ink-600 hover:border-green-300 hover:text-green-800' => ! $active,
    ])
>
    {{ $slot }}
</button>