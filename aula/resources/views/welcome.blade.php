@extends('layouts.guest')

@section('full', true)

@section('title', 'Mi Escuelita — Unidad Educativa Pestalozzi')

@section('content')
    <div class="flex min-h-screen flex-col bg-bg">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-6 py-6">
            <a href="{{ url('/') }}" class="flex min-h-[44px] flex-col justify-center focus-ring">
                <span class="text-lg font-semibold leading-tight tracking-tight text-green-800">
                    Mi Escuelita
                </span>
                <span class="text-xs leading-tight text-ink-400">
                    Unidad Educativa Pestalozzi
                </span>
            </a>

            <nav class="flex items-center gap-2">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex min-h-[44px] items-center rounded-full bg-green-800 px-6 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring"
                    >
                        Ir al portal
                    </a>
                @else
                    @if ($canLogin)
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex min-h-[44px] items-center rounded-full px-5 text-sm font-medium text-green-800 transition-fast hover:bg-green-50 focus-ring"
                        >
                            Iniciar sesión
                        </a>
                    @endif
                    @if ($canRegister)
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex min-h-[44px] items-center rounded-full bg-green-800 px-6 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring"
                        >
                            Crear cuenta
                        </a>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="flex-1">
            {{--
                Hero institucional. Panel verde profundo a sangre: rompe la
                monotonía de tarjetas blancas y ancla la identidad del colegio.
                Cuando haya fotografía real de los ambientes, va como fondo de
                este bloque con un velo verde encima para sostener el contraste.
            --}}
            <section class="mx-auto w-full max-w-6xl px-6">
                <div class="relative overflow-hidden rounded-2xl bg-green-950 px-8 py-16 sm:px-14 sm:py-20">
                    {{-- Trama sutil: círculos concéntricos, guiño a los materiales Montessori --}}
                    <svg
                        class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 text-green-800/40"
                        viewBox="0 0 200 200"
                        fill="none"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <circle cx="100" cy="100" r="30" />
                        <circle cx="100" cy="100" r="50" />
                        <circle cx="100" cy="100" r="70" />
                        <circle cx="100" cy="100" r="90" />
                    </svg>

                    <div class="relative max-w-2xl">
                        <p class="text-sm font-medium uppercase tracking-widest text-accent-500">
                            Aula virtual
                        </p>

                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-white sm:text-5xl">
                            Unidad Educativa Pestalozzi
                        </h1>

                        <p class="mt-6 text-lg leading-relaxed text-green-100">
                            Comprometidos con una formación de excelencia que promueve
                            la creatividad, el pensamiento crítico y los valores humanos.
                            Este es el espacio donde las familias siguen ese camino
                            día a día.
                        </p>

                        @guest
                            @if ($canLogin || $canRegister)
                                <div class="mt-10 flex flex-wrap items-center gap-3">
                                    @if ($canLogin)
                                        <a
                                            href="{{ route('login') }}"
                                            class="group inline-flex min-h-[44px] items-center gap-2 rounded-full bg-white px-7 text-base font-medium text-green-900 transition-fast hover:bg-green-50 focus-ring"
                                        >
                                            Entrar al aula
                                            <span aria-hidden="true" class="transition-fast group-hover:translate-x-0.5">&rarr;</span>
                                        </a>
                                    @endif
                                    @if ($canRegister)
                                        <a
                                            href="{{ route('register') }}"
                                            class="group inline-flex min-h-[44px] items-center gap-2 rounded-full border border-green-700 px-7 text-base font-medium text-green-100 transition-fast hover:border-green-500 hover:text-white focus-ring"
                                        >
                                            Crear cuenta
                                            <span aria-hidden="true" class="transition-fast group-hover:translate-x-0.5">&rarr;</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @endguest
                    </div>
                </div>
            </section>

            {{--
                Tres pilares, numerados como en el sitio institucional.
                Sin tarjetas: apoyados sobre el fondo, separados por regla.
                Es lo que rompe la sensación de "todo es la misma cajita blanca".
            --}}
            <section class="mx-auto w-full max-w-6xl px-6 py-20">
                <div class="grid grid-cols-1 gap-10 sm:grid-cols-3 sm:gap-8">
                    @foreach ([
                        ['01', 'Seguí sus experiencias', 'Cada propuesta que la guía presenta en el ambiente, con su área de desarrollo y sus materiales.'],
                        ['02', 'Compartí sus momentos', 'Subí una foto, un video o unas líneas contando cómo vivió la experiencia en casa.'],
                        ['03', 'Conversá con la guía', 'Recibí su devolución y mantené abierto el diálogo entre la casa y el colegio.'],
                    ] as [$numero, $titulo, $texto])
                        <div class="border-t border-green-100 pt-6">
                            <span class="text-sm font-semibold tracking-widest text-accent-600">{{ $numero }}</span>
                            <h2 class="mt-3 text-xl font-semibold text-ink-900">{{ $titulo }}</h2>
                            <p class="mt-2 text-sm leading-relaxed text-ink-400">{{ $texto }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <x-site-footer />
    </div>
@endsection
