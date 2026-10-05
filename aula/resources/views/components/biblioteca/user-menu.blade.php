@php
    $usuario = auth()->user();
    $roles = [
        'administrador' => 'Administrador',
        'coordinacion' => 'Coordinación',
        'guia' => 'Docente',
        'estudiante' => 'Estudiante',
    ];
    $iniciales = $usuario
        ? collect(preg_split('/\s+/', trim($usuario->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('')
        : '';
@endphp

@if ($usuario)
    <div {{ $attributes->class('border-t border-bib-border p-4') }}>
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bib-primary-soft text-sm font-semibold text-bib-primary" aria-hidden="true">{{ $iniciales }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-bib-text">{{ $usuario->name }}</p>
                <p class="truncate text-xs text-bib-text-secondary">{{ $roles[$usuario->role] ?? ucfirst((string) $usuario->role) }}</p>
            </div>
        </div>

        <x-biblioteca.theme-toggle class="mt-4" />

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button
                type="submit"
                class="flex min-h-[44px] w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-bib-text-secondary transition-colors duration-150 hover:bg-bib-surface-secondary hover:text-bib-text"
            >
                <x-biblioteca.icon name="arrow-right-on-rectangle" />
                Salir
            </button>
        </form>
    </div>
@endif
