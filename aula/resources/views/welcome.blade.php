@extends('layouts.guest')

@section('full', true)

@section('title', 'Mi Escuelita')

@section('content')
    <div class="flex min-h-screen flex-col bg-bg">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
            <span class="text-2xl font-semibold tracking-tight text-green-700">
                Mi Escuelita
            </span>

            <nav class="flex items-center gap-2">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-full bg-green-800 px-6 py-2 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring"
                    >
                        Ir al panel
                    </a>
                @else
                    @if ($canLogin)
                        <a
                            href="{{ route('login') }}"
                            class="rounded-full px-5 py-2 text-sm font-medium text-green-800 transition-fast hover:bg-green-50 focus-ring"
                        >
                            Iniciar sesión
                        </a>
                    @endif
                    @if ($canRegister)
                        <a
                            href="{{ route('register') }}"
                            class="rounded-full bg-green-800 px-6 py-2 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring"
                        >
                            Crear cuenta
                        </a>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="flex flex-1 items-center">
            <div class="mx-auto w-full max-w-6xl px-6 py-16 text-center">
                <p class="text-sm font-medium uppercase tracking-widest text-accent-600">
                    Espacio para familias
                </p>

                <h1 class="mx-auto mt-4 max-w-3xl text-4xl font-semibold leading-tight text-ink-900 sm:text-5xl">
                    El espacio donde tu familia y el colegio comparten el
                    crecimiento de tus hijos
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg text-ink-600">
                    Descubre las experiencias de aprendizaje, recibe avisos y
                    comparte con las guías los momentos más importantes del
                    día a día de tu niño o niña.
                </p>

                @guest
                    @if ($canLogin || $canRegister)
                        <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                            @if ($canLogin)
                                <a
                                    href="{{ route('login') }}"
                                    class="rounded-full bg-green-800 px-8 py-3 text-base font-medium text-white transition-fast hover:bg-green-900 focus-ring"
                                >
                                    Iniciar sesión
                                </a>
                            @endif
                            @if ($canRegister)
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-full border border-green-200 bg-white px-8 py-3 text-base font-medium text-green-800 transition-fast hover:bg-green-50 focus-ring"
                                >
                                    Crear cuenta
                                </a>
                            @endif
                        </div>
                    @endif
                @endguest
            </div>
        </main>

        <footer class="py-8 text-center">
            <a
                href="{{ route('privacidad') }}"
                class="rounded-full px-4 py-2 text-sm text-ink-600 transition-fast hover:text-green-800 focus-ring"
            >
                Política de privacidad
            </a>
        </footer>
    </div>
@endsection