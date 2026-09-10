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

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Mi Escuelita">
        <meta property="og:title" content="Mi Escuelita Pestalozzi">
        <meta property="og:description" content="Conecta a tu familia con la experiencia educativa de tus hijos.">

        <!-- Fonts — misma familia que pestalozzi-opal.vercel.app -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        @hasSection('full')
            <div class="min-h-screen bg-bg text-ink-900">
                @yield('content')
            </div>
        @else
            <div class="flex min-h-screen flex-col items-center bg-bg pt-6 sm:justify-center sm:pt-0">
                <div>
                    <a href="{{ url('/') }}" class="focus-ring">
                        <x-brand-logo icon-class="h-11 w-auto" text-class="text-2xl font-semibold tracking-tight text-green-800" />
                    </a>
                </div>

                <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-card sm:max-w-md sm:rounded-lg">
                    @yield('content')
                </div>
            </div>
        @endif

        <x-cookie-consent />

        @livewireScripts
    </body>
</html>