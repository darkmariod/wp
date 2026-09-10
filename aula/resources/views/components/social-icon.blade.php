@props(['name'])

{{-- Íconos de línea propios (mismo trazo que el resto del sitio), uno por red social. --}}
<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
    @switch($name)
        @case('Facebook')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 9h-2a2 2 0 0 0-2 2v9M9 13h5M14 5.5a2.5 2.5 0 0 0-2.5 2.5v3" />
            <rect x="3" y="3" width="18" height="18" rx="4" />
            @break

        @case('WhatsApp')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 17.5 4 20l2.6-.7A8 8 0 1 0 4 12a7.96 7.96 0 0 0 1 3.9Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9.8c0 3.4 2.8 6.2 6.2 6.2.5 0 .9-.4.9-.9v-1l-2-.6-.6.6a5 5 0 0 1-2.6-2.6l.6-.6-.6-2h-1c-.5 0-.9.4-.9.9Z" />
            @break

        @case('TikTok')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v9.5a3 3 0 1 1-2-2.83" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 4c.3 2.1 1.9 3.7 4 4" />
            @break
    @endswitch
</svg>
