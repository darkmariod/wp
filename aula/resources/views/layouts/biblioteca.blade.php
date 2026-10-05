{{--
    Layout de la Biblioteca. Sirve a páginas Blade (@extends + @section) y a
    componentes Livewire de página completa ({{ $slot }}).

    Secciones opcionales: title, heading (h1), subtitle. En Livewire llegan
    como $title, $heading y $subtitle.
--}}
@php
    $marca = (array) config('biblioteca.brand', []);
    $nombreMarca = (string) ($marca['name'] ?? 'Pestalozzi');

    $favicon = $marca['favicon'] ?? null;
    $faviconUrl = is_string($favicon) && $favicon !== ''
        ? (preg_match('#^(https?://|/)#i', $favicon) === 1 ? $favicon : asset($favicon))
        : null;

    // yieldContent ya devuelve el texto escapado: se decodifica para escapar una sola vez.
    $tituloPagina = trim(html_entity_decode($__env->yieldContent('title'), ENT_QUOTES, 'UTF-8')) ?: trim((string) ($title ?? ''));
    $tituloDocumento = ($tituloPagina !== '' ? $tituloPagina.' · ' : '').'Biblioteca · '.$nombreMarca;

    $tieneEncabezado = $__env->hasSection('heading') || isset($heading);
@endphp
<!DOCTYPE html>
<html lang="es" data-theme="system">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <meta name="color-scheme" content="light dark">
        <meta name="theme-color" content="#FAFAF7">

        <title>{{ $tituloDocumento }}</title>

        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @else
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif

        {{-- Aplica el tema guardado antes del primer pintado, para que no parpadee. --}}
        <script>
            (function () {
                var tema = 'system';
                try {
                    var guardado = localStorage.getItem('bib-theme');
                    if (guardado === 'light' || guardado === 'dark') tema = guardado;
                } catch (e) {}
                var oscuro = tema === 'dark' || (tema === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.setAttribute('data-theme', tema);
                var esquema = document.querySelector('meta[name="color-scheme"]');
                var barra = document.querySelector('meta[name="theme-color"]');
                if (esquema) esquema.setAttribute('content', tema === 'system' ? 'light dark' : tema);
                if (barra) barra.setAttribute('content', oscuro ? '#0F1511' : '#FAFAF7');
            })();
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/js/biblioteca.js'])
        <x-biblioteca.brand-style />
        @livewireStyles
    </head>
    <body
        class="min-h-dvh bg-bib-background font-sans leading-normal text-bib-text antialiased"
        x-data="bibDrawer"
        @keydown.escape.window="close()"
        @resize.window.debounce.150ms="closeOnDesktop()"
    >
        <a
            href="#contenido"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-bib-primary focus:px-4 focus:py-3 focus:text-sm focus:font-semibold focus:text-bib-primary-contrast"
        >
            Saltar al contenido
        </a>

        {{-- Escritorio: barra lateral fija --}}
        <aside class="hidden border-r border-bib-border bg-bib-surface lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:w-64 lg:flex-col">
            <div class="flex h-16 shrink-0 items-center px-5">
                <x-biblioteca.brand />
            </div>
            <x-biblioteca.nav class="flex-1 overflow-y-auto px-3 py-4" />
            <x-biblioteca.user-menu />
        </aside>

        {{-- Móvil: barra superior y menú lateral --}}
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-bib-border bg-bib-surface px-4 lg:hidden">
            <x-biblioteca.brand />
            <button
                type="button"
                @click="toggle()"
                :aria-expanded="open"
                aria-expanded="false"
                aria-controls="bib-drawer"
                aria-label="Abrir menú"
                class="flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-xl text-bib-text-secondary transition-colors duration-150 hover:bg-bib-surface-secondary hover:text-bib-text"
            >
                <x-biblioteca.icon name="bars-3" class="h-6 w-6" />
            </button>
        </header>

        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.200ms
            @click="close()"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            aria-hidden="true"
        ></div>

        <div
            id="bib-drawer"
            x-show="open"
            x-cloak
            x-trap.noscroll.inert="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            role="dialog"
            aria-modal="true"
            aria-label="Menú de la Biblioteca"
            class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col overflow-y-auto border-r border-bib-border bg-bib-surface lg:hidden"
        >
            <div class="flex h-16 shrink-0 items-center justify-between pl-4 pr-2">
                <x-biblioteca.brand />
                <button
                    type="button"
                    @click="close()"
                    aria-label="Cerrar menú"
                    class="flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-xl text-bib-text-secondary transition-colors duration-150 hover:bg-bib-surface-secondary hover:text-bib-text"
                >
                    <x-biblioteca.icon name="x-mark" class="h-6 w-6" />
                </button>
            </div>
            <x-biblioteca.nav class="flex-1 px-3 py-4" />
            <x-biblioteca.user-menu />
        </div>

        <div class="lg:pl-64">
            <main id="contenido" tabindex="-1" class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-10 lg:py-12">
                @if (session('success'))
                    <div role="status" class="mb-6 rounded-xl border border-bib-border bg-bib-primary-soft px-4 py-3 text-sm text-bib-text">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($tieneEncabezado)
                    <header class="mb-8">
                        <h1 class="text-2xl font-semibold tracking-tight text-bib-text sm:text-3xl">
                            @hasSection('heading')@yield('heading')@else{{ $heading }}@endif
                        </h1>
                        @if ($__env->hasSection('subtitle') || isset($subtitle))
                            <p class="mt-2 max-w-2xl text-base text-bib-text-secondary">
                                @hasSection('subtitle')@yield('subtitle')@else{{ $subtitle }}@endif
                            </p>
                        @endif
                    </header>
                @endif

                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>

        @livewireScripts
    </body>
</html>
