{{--
    Pie de sitio del portal de familias.

    Sin enlaces a las páginas de marketing del sitio público (Propuesta
    educativa, Niveles, Filosofía, Admisiones) — tenían sentido en
    pestalozzi-opal.vercel.app, donde alguien todavía está decidiendo si
    inscribirse, pero acá adentro son ruido: quien entra ya es familia
    del colegio y usa esto todos los días, no está de visita.
--}}
@php
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{-- Marca --}}
            <div class="flex flex-col gap-1">
                <x-brand-logo icon-class="h-6 w-auto" text-class="text-sm font-semibold text-green-800" />
                <p class="text-xs text-ink-400">Unidad Educativa · Ambato, Ecuador</p>
            </div>

            {{-- Contacto --}}
            <address class="text-xs not-italic leading-relaxed text-ink-400 sm:text-right">
                <p>Tiwinza N.º 95 y Etza, Ambato, Ecuador</p>
                <a
                    href="mailto:uepestalozzi.ambato@gmail.com"
                    class="inline-flex min-h-[44px] items-center transition-fast hover:text-green-800 focus-ring sm:min-h-0"
                >uepestalozzi.ambato@gmail.com</a>
            </address>

            {{-- Síguenos --}}
            <nav aria-labelledby="footer-redes" class="flex items-center gap-1">
                <h2 id="footer-redes" class="sr-only">Síguenos</h2>
                @foreach ($redes as [$texto, $url])
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ $texto }}"
                        class="flex h-9 w-9 items-center justify-center rounded-full text-ink-400 transition-fast hover:bg-green-50 hover:text-green-800 focus-ring"
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
