<section class="py-8 first:pt-0">
    <header>
        <h2 class="text-lg font-semibold text-ink-900">Información del perfil</h2>
        <p class="mt-1 text-sm text-ink-600">Actualizá tu nombre y correo de la cuenta.</p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-medium text-ink-600">Nombre</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', auth()->user()->name) }}"
                required
                autofocus
                autocomplete="name"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-ink-600">Correo</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', auth()->user()->email) }}"
                required
                autocomplete="username"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($mustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-ink-600">
                        Tu correo no está verificado.
                    </p>
                    <button
                        form="send-verification"
                        type="submit"
                        class="mt-2 border border-green-200 bg-white px-4 py-1.5 text-sm font-medium text-green-800 transition-fast hover:bg-green-50 focus-ring"
                    >
                        Reenviar enlace de verificación
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-green-800" role="status">
                            Te enviamos un enlace nuevo a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <button
            type="submit"
            class="inline-flex min-h-[44px] items-center justify-center bg-green-800 px-6 py-2 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Guardar
        </button>
    </form>
</section>