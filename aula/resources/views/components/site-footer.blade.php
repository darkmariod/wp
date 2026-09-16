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

    // Mismas redes y correo que pestalozzi-opal.vercel.app: es la
    // presencia real de la institución, no un placeholder inventado.
    $redes = [
        ['Facebook', 'https://facebook.com/pestalozziambato'],
        ['WhatsApp', 'https://wa.me/593998246396'],
        ['TikTok', 'https://www.tiktok.com/@pestalozziambato'],
    ];
@endphp

<footer class="mt-10 border-t border-green-100 bg-white">
    <div class="mx-auto max-w-6xl px-6 py-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            {{-- Marca + institución --}}
            <div class="flex flex-col gap-2">
                <x-brand-logo icon-class="h-6 w-auto" text-class="text-sm font-semibold text-green-800" />
                <p class="text-xs text-ink-400">Unidad Educativa · Ambato, Ecuador</p>
                <nav aria-label="Institución" class="flex flex-wrap gap-x-4 gap-y-1">
                    @foreach ($institucion as [$texto, $url])
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-h-[44px] items-center text-xs text-ink-400 transition-fast hover:text-green-800 focus-ring sm:min-h-0"
                        >{{ $texto }}</a>
                    @endforeach
                </nav>
            </div>

            {{-- Contacto --}}
            <address class="text-xs not-italic leading-relaxed text-ink-400">
                <p>Tiwinza N.º 95 y Etza, Ambato, Ecuador</p>
                <a
                    href="mailto:uepestalozzi.ambato@gmail.com"
                    class="inline-flex min-h-[44px] items-center transition-fast hover:text-green-800 focus-ring sm:min-h-0"
                >uepestalozzi.ambato@gmail.com</a>
            </address>

            {{-- Síguenos --}}
            <nav aria-labelledby="footer-redes" class="flex items-center gap-3">
                <h2 id="footer-redes" class="sr-only">Síguenos</h2>
                @foreach ($redes as [$texto, $url])
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ $texto }}"
                        class="flex h-8 w-8 items-center justify-center text-ink-400 transition-fast hover:text-green-800 focus-ring"
                    >
                        <x-social-icon :name="$texto" class="h-4 w-4" />
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Barra legal --}}
        <div class="mt-4 flex flex-col gap-2 border-t border-green-100 pt-3 text-xs text-ink-300 sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ now()->year }} Unidad Educativa Pestalozzi Ambato</span>

            <a
                href="{{ route('privacidad') }}"
                class="inline-flex min-h-[44px] items-center transition-fast hover:text-green-800 focus-ring sm:min-h-0"
            >Política de privacidad y cookies</a>
        </div>
    </div>
</footer>
