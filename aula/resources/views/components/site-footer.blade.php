{{--
    Pie de sitio del portal de familias.

    Sigue la estructura del sitio institucional (Institución / Contacto /
    Redes + barra legal) para que el aula se sienta parte de la misma casa
    y no un producto aparte.

    Los enlaces institucionales salen del portal hacia el sitio público:
    por eso llevan target y rel de seguridad.
--}}
@php
    $sitio = 'https://pestalozzi-opal.vercel.app';

    $institucion = [
        ['Propuesta educativa', $sitio . '/propuesta-educativa'],
        ['Niveles', $sitio . '/niveles'],
        ['Filosofía', $sitio . '/filosofia'],
        ['Admisiones', $sitio . '/admisiones'],
    ];

    $redes = [
        ['Facebook', 'https://www.facebook.com/'],
        ['WhatsApp', 'https://wa.me/'],
        ['TikTok', 'https://www.tiktok.com/'],
    ];
@endphp

<footer class="mt-16 border-t border-green-100 bg-white">
    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Marca --}}
            <div class="lg:col-span-1">
                <span class="text-lg font-semibold tracking-tight text-green-800">
                    Mi Escuelita
                </span>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-ink-400">
                    El espacio donde tu familia y el colegio siguen juntos el
                    crecimiento de tu niño.
                </p>
            </div>

            {{-- Institución --}}
            <nav aria-labelledby="footer-institucion">
                <h2 id="footer-institucion" class="text-sm font-semibold text-ink-900">
                    Institución
                </h2>
                <ul class="mt-4 space-y-1">
                    @foreach ($institucion as [$texto, $url])
                        <li>
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-[44px] items-center text-sm text-ink-400 transition-fast hover:text-green-800 focus-ring"
                            >{{ $texto }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Contacto --}}
            <div>
                <h2 class="text-sm font-semibold text-ink-900">Contacto</h2>
                <address class="mt-4 space-y-2 text-sm not-italic leading-relaxed text-ink-400">
                    <p>Tiwinza N.º 95 y Etza<br>Ambato, Ecuador</p>
                    <p>
                        <a
                            href="mailto:info@pestalozzi.edu.ec"
                            class="inline-flex min-h-[44px] items-center transition-fast hover:text-green-800 focus-ring"
                        >info@pestalozzi.edu.ec</a>
                    </p>
                </address>
            </div>

            {{-- Redes --}}
            <nav aria-labelledby="footer-redes">
                <h2 id="footer-redes" class="text-sm font-semibold text-ink-900">Redes</h2>
                <ul class="mt-4 space-y-1">
                    @foreach ($redes as [$texto, $url])
                        <li>
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-[44px] items-center text-sm text-ink-400 transition-fast hover:text-green-800 focus-ring"
                            >{{ $texto }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        {{-- Barra legal --}}
        <div class="mt-10 flex flex-col gap-3 border-t border-green-100 pt-6 text-sm text-ink-300 sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ now()->year }} Unidad Educativa Pestalozzi Ambato</span>

            <a
                href="{{ route('privacidad') }}"
                class="inline-flex min-h-[44px] items-center transition-fast hover:text-green-800 focus-ring"
            >Política de privacidad y cookies</a>
        </div>
    </div>
</footer>
