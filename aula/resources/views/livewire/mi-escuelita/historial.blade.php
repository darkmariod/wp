<div>
    <div>
        <x-breadcrumb
            :items="[['label' => 'Inicio', 'url' => route('mi-escuelita.home')], ['label' => 'Mis experiencias']]"
        />
        <h1 class="mt-4 text-3xl font-semibold text-ink-900">Mis experiencias</h1>
        <p class="mt-2 text-sm text-ink-600">
            Todo lo que esta familia ya compartió, con el estado y la respuesta de la guía.
        </p>
    </div>

    @php
        $etiquetado = [
            \App\Models\Evidence::STATUS_PENDING => 'Pendiente',
            \App\Models\Evidence::STATUS_SUBMITTED => 'Enviada',
            \App\Models\Evidence::STATUS_VIEWED => 'Vista por la guía',
            \App\Models\Evidence::STATUS_RESPONDED => 'Con respuesta',
            \App\Models\Evidence::STATUS_ARCHIVED => 'Archivada',
        ];
        $coloresEstado = [
            \App\Models\Evidence::STATUS_PENDING => 'text-amber-700',
            \App\Models\Evidence::STATUS_SUBMITTED => 'text-ink-600',
            \App\Models\Evidence::STATUS_VIEWED => 'text-sky-700',
            \App\Models\Evidence::STATUS_RESPONDED => 'font-medium text-green-800',
            \App\Models\Evidence::STATUS_ARCHIVED => 'text-ink-400',
        ];
    @endphp

    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <label class="relative sm:col-span-2">
            <span class="sr-only">Buscar por experiencia</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                type="search"
                placeholder="Buscar por experiencia…"
                wire:model.live.debounce.300ms="search"
                class="w-full border border-green-100 bg-white py-2 pl-9 pr-3 text-sm text-ink-900 focus-ring"
            >
        </label>

        <x-field-select label="Filtrar por área" wire:model.live="filtroArea">
            <option value="">Todas las áreas</option>
            @foreach ($this->areas as $area)
                <option value="{{ $area->id }}">{{ $area->name }}</option>
            @endforeach
        </x-field-select>

        <x-field-select label="Filtrar por estado" wire:model.live="filtro">
            @foreach ($this->filtros as $clave => $etiqueta)
                <option value="{{ $clave }}">{{ $etiqueta }}</option>
            @endforeach
        </x-field-select>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-b border-green-100 pb-3">
        <p class="text-sm text-ink-600">
            {{ $this->evidencias()->total() }}
            {{ $this->evidencias()->total() === 1 ? 'envío' : 'envíos' }}
        </p>

        <button
            type="button"
            wire:click="toggleOrden"
            class="inline-flex items-center gap-1.5 text-sm text-ink-600 transition-fast hover:text-green-800 focus-ring"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                @if ($orden === 'desc')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m7.5-9v12m0 0-3.75-3.75m3.75 3.75 3.75-3.75" />
                @endif
            </svg>
            {{ $orden === 'desc' ? 'Más recientes primero' : 'Más antiguas primero' }}
        </button>
    </div>

    @if ($this->evidencias()->isEmpty())
        <p class="mt-6 border border-green-100 p-6 text-center text-ink-400">
            Todavía no compartieron ninguna experiencia aquí. Las que envíen van a aparecer en esta lista.
        </p>
    @else
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-ink-900">
                        <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-ink-600 first:pl-0">Experiencia</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Área</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Fecha de envío</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Estado</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Respuesta de la guía</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-100">
                    @foreach ($this->evidencias() as $evidencia)
                        <tr class="align-top">
                            <td class="px-3 py-4 font-medium text-ink-900 first:pl-0">{{ $evidencia['experiencia'] }}</td>
                            <td class="px-3 py-4 text-ink-600">{{ $evidencia['area'] ?? '—' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-ink-600">
                                {{ $evidencia['enviada']?->locale('es')->isoFormat('D [de] MMMM [de] YYYY') ?? '—' }}
                            </td>
                            <td class="px-3 py-4 {{ $coloresEstado[$evidencia['estado']] ?? 'text-ink-400' }}">
                                {{ $etiquetado[$evidencia['estado']] ?? 'Desconocido' }}
                            </td>
                            <td class="px-3 py-4 text-ink-600">
                                @if ($evidencia['observacion'])
                                    <p class="border-l-2 border-green-200 pl-3 italic">{{ $evidencia['observacion'] }}</p>
                                @else
                                    <span class="text-ink-400">Sin respuesta aún</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->evidencias()->links() }}
        </div>
    @endif
</div>
