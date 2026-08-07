<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inmobiliaria Riobamba') — Tu propiedad en Riobamba</title>
    <meta name="description" content="@yield('description', 'Encontrá la mejor propiedad en Riobamba: casas, departamentos y terrenos en venta y alquiler. Explorá nuestro catálogo con fotos, mapa y datos reales.')">
    <meta property="og:title" content="@yield('title', 'Inmobiliaria Riobamba')">
    <meta property="og:description" content="@yield('description', 'Encontrá la mejor propiedad en Riobamba. Explorá nuestro catálogo.')">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-cream text-slate-800 font-sans antialiased">
    {{-- Navbar --}}
    <nav class="bg-navy text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold tracking-tight">
                    <span class="flex items-center justify-center w-8 h-8 rounded-sm bg-terracota text-white text-sm">{{ config('brand.short_name') }}</span>
                    <span class="hidden sm:inline text-white/90">{{ config('brand.name') }}</span>
                </a>
                <div class="flex gap-8 text-sm font-medium">
                    <a href="{{ url('/') }}"
                       class="text-white/70 hover:text-white transition {{ request()->routeIs('home') ? 'text-white' : '' }}">Inicio</a>
                    <a href="{{ route('properties.index') }}"
                       class="text-white/70 hover:text-white transition {{ request()->routeIs('properties.*') ? 'text-white' : '' }}">Propiedades</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-navy text-white/60 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-3">{{ config('brand.name') }}</h3>
                    <p class="text-sm leading-relaxed">{{ config('brand.tagline') }}</p>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-3">Contacto</h3>
                    <ul class="text-sm space-y-2.5">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 002.25-2.25v-1.372a1 1 0 00-.804-.98l-4.107-.822a1 1 0 00-.986.27l-.93.94a1 1 0 01-1.215.184 12.035 12.035 0 01-5.871-5.871 1 1 0 01.184-1.214l.94-.93a1 1 0 00.27-.987L8.6 3.054a1 1 0 00-.98-.804H6.25a2.25 2.25 0 00-2.25 2.25v1.5z"/></svg>
                            {{ config('brand.phone') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            {{ config('brand.email') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            {{ config('brand.address') }}
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-3">Enlaces</h3>
                    <ul class="text-sm space-y-2">
                        <li><a href="{{ url('/') }}" class="hover:text-white transition">Inicio</a></li>
                        <li><a href="{{ route('properties.index') }}" class="hover:text-white transition">Propiedades</a></li>
                        <li><a href="{{ route('properties.index') }}" class="hover:text-white transition">Venta</a></li>
                        <li><a href="{{ route('properties.index') }}?operation=2" class="hover:text-white transition">Alquiler</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-8 pt-8 text-center text-sm">
                &copy; {{ date('Y') }} {{ config('brand.name') }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
