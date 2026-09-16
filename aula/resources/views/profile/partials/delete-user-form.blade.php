<section class="py-8 last:pb-0">
    <header>
        <h2 class="text-lg font-semibold text-ink-900">Eliminar cuenta</h2>
        <p class="mt-1 text-sm text-ink-600">
            Al eliminar tu cuenta se retira todo tu historial de forma permanente.
            Esta acción no se puede deshacer.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-4"
          x-data="{ confirmar: false }">
        @csrf
        @method('delete')

        <div x-show="confirmar" x-cloak>
            <label for="password" class="block text-sm font-medium text-ink-600">
                Confirmá con tu contraseña
            </label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="Contraseña"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="button"
            x-show="!confirmar"
            @click="confirmar = true"
            class="border border-red-200 bg-white px-6 py-2 text-sm font-medium text-red-600 transition-fast hover:bg-red-50 focus-ring"
        >
            Eliminar cuenta
        </button>

        <button
            type="submit"
            x-show="confirmar"
            class="inline-flex min-h-[44px] items-center bg-red-600 px-6 py-2 text-sm font-medium text-white transition-fast hover:bg-red-700 focus-ring"
        >
            Eliminar definitivamente
        </button>
    </form>
</section>