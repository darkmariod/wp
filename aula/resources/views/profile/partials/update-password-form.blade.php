<section class="rounded-lg border border-green-100 bg-white p-6 shadow-card">
    <header>
        <h2 class="text-lg font-semibold text-ink-900">Actualizar contraseña</h2>
        <p class="mt-1 text-sm text-ink-600">Usá una contraseña larga y que no uses en otro lado.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="current_password" class="block text-sm font-medium text-ink-600">Contraseña actual</label>
            <input
                id="current_password"
                type="password"
                name="current_password"
                required
                autocomplete="current-password"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('current_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink-600">Contraseña nueva</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-ink-600">Confirmar contraseña</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="mt-1 w-full rounded-md border-green-100 text-ink-900 focus-ring"
            />
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-full bg-green-800 px-6 py-2 text-sm font-medium text-white transition-fast hover:bg-green-900 focus-ring disabled:opacity-60"
        >
            Actualizar contraseña
        </button>
    </form>
</section>