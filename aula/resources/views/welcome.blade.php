@extends('layouts.guest')

@section('full', true)

@section('title', 'Mi Escuelita — Unidad Educativa Pestalozzi')

@section('content')
    <div class="flex min-h-screen flex-col bg-bg">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-6 py-6">
            <a href="{{ url('/') }}" class="flex min-h-[44px] items-center focus-ring">
                <x-brand-logo icon-class="h-10 w-auto" :show-text="false" />
            </a>

            {{--
                Guest ya tiene un único botón de entrada bien visible en el
                hero ("Entrar al aula"): repetir "Iniciar sesión" acá arriba
                era el mismo destino con otra palabra, dos formas de hacer
                lo mismo en una sola pantalla — confuso para alguien poco
                acostumbrado a webs. Solo queda algo acá cuando ya hay
                sesión iniciada.
            --}}
            @auth
                <nav class="flex items-center gap-2">
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex min-h-[44px] items-center rounded-full bg-green-800 px-6 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring"
                    >
                        Ir al portal
                    </a>
                </nav>
            @endauth
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

                        {{-- Línea de acento corta, mismo detalle que pestalozzi-opal.vercel.app --}}
                        <span class="mt-4 block h-1 w-14 rounded-full bg-accent-500" aria-hidden="true"></span>

                        <h1 class="mt-4 text-5xl font-bold leading-tight text-white sm:text-6xl">
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

        </main>
    </div>
@endsection
