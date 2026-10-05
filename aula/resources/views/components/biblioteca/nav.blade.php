@php
    $usuario = auth()->user();
    $enCursos = Route::has('biblioteca.cursos') && request()->routeIs('biblioteca.cursos', 'biblioteca.cursos.*');
@endphp

<nav aria-label="Navegación principal" {{ $attributes }}>
    <ul class="space-y-1">
        <li>
            <x-biblioteca.nav-link :href="route('biblioteca.index')" icon="book-open" :active="request()->routeIs('biblioteca.*') && ! $enCursos">
                Biblioteca
            </x-biblioteca.nav-link>
        </li>

        @if (Route::has('biblioteca.cursos'))
            <li>
                <x-biblioteca.nav-link :href="route('biblioteca.cursos')" icon="academic-cap" :active="$enCursos">
                    Mis cursos
                </x-biblioteca.nav-link>
            </li>
        @endif

        <li>
            <x-biblioteca.nav-link :href="route('profile.edit')" icon="user" :active="request()->routeIs('profile.*')">
                Perfil
            </x-biblioteca.nav-link>
        </li>

        @if ($usuario?->isPanelRole())
            <li>
                <x-biblioteca.nav-link :href="url('/admin')" icon="cog">
                    Administrar
                </x-biblioteca.nav-link>
            </li>
        @endif
    </ul>
</nav>
