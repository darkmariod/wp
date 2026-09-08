<div>
    <div>
        <x-breadcrumb
            :items="[['label' => 'Inicio', 'url' => route('mi-escuelita.home')], ['label' => 'Mis experiencias']]"
        />
        <h1 class="mt-4 text-3xl font-bold text-ink-900">Mis experiencias</h1>
        <p class="mt-2 text-sm text-ink-400">
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
    @endphp

    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <label class="sm:col-span-2">
            <span class="sr-only">Buscar por experiencia</span>
            <input
                type="search"
                placeholder="Buscar por experiencia…"
                wire:model.live.debounce.300ms="search"
                class="w-full rounded-md border border-green-100 bg-white px-3 py-2 text-sm text-ink-900 focus-ring"
            >
        </label>

        <label>
            <span class="sr-only">Filtrar por área</span>
            <select
                wire:model.live="filtroArea"
                class="w-full rounded-md border border-green-100 bg-white px-3 py-2 text-sm text-ink-900 focus-ring"
            >
                <option value="">Todas las áreas</option>
                @foreach ($this->areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="sr-only">Filtrar por estado</span>
            <select
                wire:model.live="filtro"
                class="w-full rounded-md border border-green-100 bg-white px-3 py-2 text-sm text-ink-900 focus-ring"
            >
                @foreach ($this->filtros as $clave => $etiqueta)
                    <option value="{{ $clave }}">{{ $etiqueta }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-400">
            {{ $this->evidencias()->total() }}
            {{ $this->evidencias()->total() === 1 ? 'envío' : 'envíos' }}
        </p>

        <button
            type="button"
            wire:click="toggleOrden"
            class="inline-flex items-center gap-2 rounded-md border border-green-100 bg-white px-3 py-2 text-sm font-medium text-ink-600 transition-fast hover:border-green-300 hover:text-green-800 focus-ring"
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
        <p class="mt-10 rounded-lg border border-green-100 bg-white p-6 text-center text-ink-400 shadow-card">
            Todavía no compartieron ninguna experiencia acá. Las que envíen van a aparecer en esta lista.
        </p>
    @else
        <div class="mt-4 overflow-x-auto rounded-lg border border-green-100 bg-white shadow-card">
            <table class="min-w-full divide-y divide-green-100 text-sm">
                <thead class="bg-green-50/60">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Experiencia</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Área</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Fecha de envío</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Estado</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Respuesta de la guía</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-100">
                    @foreach ($this->evidencias() as $evidencia)
                        <tr class="align-top transition-base hover:bg-green-50/40">
                            <td class="px-5 py-4 font-medium text-ink-900">{{ $evidencia['experiencia'] }}</td>
                            <td class="px-5 py-4 text-ink-600">{{ $evidencia['area'] ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-ink-600">
                                {{ $evidencia['enviada']?->locale('es')->isoFormat('D [de] MMMM [de] YYYY') ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium',
                                    'bg-amber-50 text-amber-700' => $evidencia['estado'] === \App\Models\Evidence::STATUS_PENDING,
                                    'bg-green-50 text-green-800' => $evidencia['estado'] === \App\Models\Evidence::STATUS_SUBMITTED,
                                    'bg-sky-50 text-sky-700' => $evidencia['estado'] === \App\Models\Evidence::STATUS_VIEWED,
                                    'bg-accent-600/10 text-accent-600' => $evidencia['estado'] === \App\Models\Evidence::STATUS_RESPONDED,
                                    'bg-ink-100 text-ink-400' => $evidencia['estado'] === \App\Models\Evidence::STATUS_ARCHIVED,
                                ])>
                                    {{ $etiquetado[$evidencia['estado']] ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-ink-600">
                                @if ($evidencia['observacion'])
                                    {{ $evidencia['observacion'] }}
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