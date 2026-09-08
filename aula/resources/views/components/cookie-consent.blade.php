{{--
    Aviso de cookies y privacidad.

    Se muestra una sola vez por navegador y la decisión queda en localStorage.
    Todo el acceso a localStorage va envuelto en try/catch: en modo privado o
    con las cookies de sitio bloqueadas, leerlo puede lanzar excepción y eso
    dejaría el aviso colgado o, peor, rompería Alpine en toda la página.

    Es un AVISO, no un muro: la app solo usa cookies propias de sesión y
    seguridad (login y CSRF), que son imprescindibles para que el portal
    funcione. Por eso no ofrecemos un "rechazar": no hay rastreo de terceros
    que apagar. Si algún día se suma analítica externa, este componente debe
    pasar a ser un consentimiento real con opción de rechazo.
--}}
<div
    x-data="{
        visible: false,
        init() {
            try {
                this.visible = window.localStorage.getItem('pestalozzi-cookies-v1') === null;
            } catch (e) {
                // Sin acceso a localStorage no podemos recordar la decisión:
                // mostramos el aviso igual, pero sin romper nada.
                this.visible = true;
            }
        },
        aceptar() {
            try {
                window.localStorage.setItem('pestalozzi-cookies-v1', 'aceptado');
            } catch (e) {
                // Se acepta igual para esta visita aunque no podamos persistirlo.
            }
            this.visible = false;
        },
    }"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    @keydown.escape.window="aceptar()"
    role="region"
    aria-label="Aviso de cookies y privacidad"
    class="fixed inset-x-0 bottom-0 z-50 p-4 sm:p-6"
>
    <div class="mx-auto flex max-w-3xl flex-col gap-4 rounded-lg border border-green-100 bg-white p-5 shadow-card sm:flex-row sm:items-center sm:gap-6 sm:p-6">
        <p class="text-sm leading-relaxed text-ink-600">
            Usamos cookies propias para mantener tu sesión abierta y proteger el
            ingreso. No compartimos tus datos ni los de tu familia con terceros.
            Podés leer más en nuestra
            <a
                href="{{ route('privacidad') }}"
                class="font-semibold text-green-800 underline underline-offset-2 transition-fast hover:text-green-900 focus-ring"
            >política de privacidad</a>.
        </p>

        <button
            type="button"
            @click="aceptar()"
            class="min-h-[44px] shrink-0 rounded-md bg-green-800 px-6 py-2 text-sm font-semibold text-white transition-fast hover:bg-green-900 focus-ring"
        >
            Entendido
        </button>
    </div>
</div>
