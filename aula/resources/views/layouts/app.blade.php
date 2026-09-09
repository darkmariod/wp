<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Mi Escuelita')</title>

        <!-- SEO -->
        <meta name="description" content="Mi Escuelita Pestalozzi — el puente entre el colegio y tu familia en Ecuador.">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#047857">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <!-- Fonts — misma familia que pestalozzi-opal.vercel.app -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-bg font-sans text-ink-900 antialiased">
        <div x-data="{ menuAbierto: false }">
            <header class="border-b border-green-100 bg-white">
                <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4">
                    @if (auth()->user()?->isFamilia())
                        <a href="{{ route('mi-escuelita.home') }}" class="rounded-md text-lg font-semibold text-green-800 focus-ring">
                            Mi Escuelita
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="rounded-md text-lg font-semibold text-green-800 focus-ring">
                            Mi Escuelita
                        </a>
                    @endif

                    <nav class="hidden items-center gap-6 text-sm font-medium text-ink-600 sm:flex" aria-label="Navegación principal">
                        @if (auth()->user()?->isFamilia())
                            <a href="{{ route('mi-escuelita.experiencias.index') }}" class="rounded-md transition-fast hover:text-green-800 focus-ring">
                                Experiencias
                            </a>
                            <a href="{{ route('mi-escuelita.historial') }}" class="rounded-md transition-fast hover:text-green-800 focus-ring">
                                Mis experiencias
                            </a>
                            <a href="{{ route('mi-escuelita.notificaciones.edit') }}" class="rounded-md transition-fast hover:text-green-800 focus-ring">
                                Notificaciones
                            </a>

                            @if ($hermanos->count() > 1)
                                <form method="POST" action="{{ route('mi-escuelita.nino.cambiar', $ninoActual ?? $hermanos->first()) }}">
                                    @csrf
                                    <select
                                        name="child_id"
                                        aria-label="Seleccionar niño"
                                        class="cursor-pointer rounded-full border-green-100 text-sm focus-ring"
                                        onchange="var o = this.options[this.selectedIndex]; var f = this.form; f.action = o.getAttribute('data-action'); f.submit();"
                                    >
                                        @foreach ($hermanos as $hermano)
                                            <option
                                                value="{{ $hermano->id }}"
                                                data-action="{{ route('mi-escuelita.nino.cambiar', $hermano) }}"
                                                @selected($hermano->id === $ninoActual?->id)
                                            >
                                                {{ $hermano->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @elseif ($ninoActual)
                                <span class="flex min-h-[44px] items-center gap-2 py-2 text-ink-600">
                                    @if ($ninoActual->avatarUrl())
                                        <img src="{{ $ninoActual->avatarUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white {{ $ninoActual->avatarColorClass() }}">{{ $ninoActual->avatarInitials() }}</span>
                                    @endif
                                    {{ $ninoActual->name }}
                                </span>
                            @endif
                        @endif

                        <a href="{{ route('profile.edit') }}" class="rounded-md transition-fast hover:text-green-800 focus-ring">
                            Perfil
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="min-h-[44px] rounded-md text-ink-400 transition-fast hover:text-green-800 focus-ring">
                                Salir
                            </button>
                        </form>
                    </nav>

                    <button
                        class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md transition-fast hover:bg-green-50 focus-ring sm:hidden"
                        aria-label="Abrir menú"
                        :aria-expanded="menuAbierto"
                        @click="menuAbierto = !menuAbierto"
                    >
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            {{-- Alpine no puede usar <template x-if> dentro de un <svg>: el navegador
                                 lo parsea en el namespace SVG y template.content queda undefined.
                                 Por eso alternamos el trazo con :d sobre un único path. --}}
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="menuAbierto ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"
                            />
                        </svg>
                    </button>
                </div>

                <div
                    class="overflow-hidden bg-white transition-slow sm:hidden"
                    :class="menuAbierto ? 'max-h-[480px]' : 'max-h-0'"
                    x-show="menuAbierto"
                >
                    <div class="border-t border-green-100 px-6 py-4">
                        <div class="flex flex-col gap-1 text-sm font-medium text-ink-600">
                            @if (auth()->user()?->isFamilia())
                                <a href="{{ route('mi-escuelita.experiencias.index') }}" class="min-h-[44px] rounded-md px-3 py-2 transition-fast hover:bg-green-50 focus-ring">
                                    Experiencias
                                </a>
                                <a href="{{ route('mi-escuelita.historial') }}" class="min-h-[44px] rounded-md px-3 py-2 transition-fast hover:bg-green-50 focus-ring">
                                    Mis experiencias
                                </a>
                                <a href="{{ route('mi-escuelita.notificaciones.edit') }}" class="min-h-[44px] rounded-md px-3 py-2 transition-fast hover:bg-green-50 focus-ring">
                                    Notificaciones
                                </a>

                                @if ($hermanos->count() > 1)
                                    @foreach ($hermanos as $hermano)
                                        <form method="POST" action="{{ route('mi-escuelita.nino.cambiar', $hermano) }}">
                                            @csrf
                                            <button type="submit" class="min-h-[44px] w-full rounded-md px-3 py-2 text-left transition-fast hover:bg-green-50 focus-ring">
                                                {{ $hermano->name }} @if ($hermano->id === $ninoActual?->id) ✓ @endif
                                            </button>
                                        </form>
                                    @endforeach
                                @endif
                            @endif

                            <a href="{{ route('profile.edit') }}" class="min-h-[44px] rounded-md px-3 py-2 transition-fast hover:bg-green-50 focus-ring">
                                Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="min-h-[44px] w-full rounded-md px-3 py-2 text-left text-ink-400 transition-fast hover:bg-green-50 focus-ring">
                                    Salir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            @if (session('success'))
                <div class="mx-auto mt-4 max-w-5xl rounded-md bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            <main class="mx-auto max-w-5xl px-6 py-10">
                @yield('content')
            </main>

            <x-site-footer />
        </div>

        <x-cookie-consent />

        @livewireScripts
    </body>
</html>