<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sin acceso — Mi Escuelita</title>
        @vite(['resources/js/app.js'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-bg font-sans text-ink-900 antialiased">
        <div class="mx-auto max-w-sm px-6 text-center">
            <x-brand-logo icon-class="mx-auto h-12 w-auto" />

            <h1 class="mt-8 text-xl font-semibold text-ink-900">No tenés acceso a esta página</h1>
            <p class="mt-2 text-sm text-ink-600">
                Esta cuenta no puede entrar aquí. Si creés que es un error, salí y volvé a entrar con tu usuario del colegio.
            </p>

            <div class="mt-8 flex flex-col items-center gap-3">
                <a
                    href="{{ url('/') }}"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-lg bg-green-800 px-6 text-sm font-semibold text-white transition-fast hover:bg-green-700 focus-ring"
                >
                    Ir al inicio
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="min-h-[44px] text-sm text-ink-400 transition-fast hover:text-green-800 focus-ring">
                        Salir y entrar con otra cuenta
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>
