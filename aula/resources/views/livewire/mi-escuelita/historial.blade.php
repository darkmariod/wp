<div>
    <div>
        <x-breadcrumb
            :items="[['label' => 'Inicio', 'url' => route('mi-escuelita.home')], ['label' => 'Mis experiencias']]"
        />
        <h1 class="mt-3 text-2xl font-semibold text-ink-900">Mis experiencias</h1>
        <p class="mt-1.5 max-w-lg text-sm text-ink-600">
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
        $puntoEstado = [
            \App\Models\Evidence::STATUS_PENDING => 'bg-amber-500',
            \App\Models\Evidence::STATUS_SUBMITTED => 'bg-ink-300',
            \App\Models\Evidence::STATUS_VIEWED => 'bg-sky-500',
            \App\Models\Evidence::STATUS_RESPONDED => 'bg-green-700',
            \App\Models\Evidence::STATUS_ARCHIVED => 'bg-ink-200',
        ];
        $textoEstado = [
            \App\Models\Evidence::STATUS_PENDING => 'text-amber-700',
            \App\Models\Evidence::STATUS_SUBMITTED => 'text-ink-600',
            \App\Models\Evidence::STATUS_VIEWED => 'text-sky-700',
            \App\Models\Evidence::STATUS_RESPONDED => 'font-medium text-green-800',
            \App\Models\Evidence::STATUS_ARCHIVED => 'text-ink-400',
        ];
    @endphp

    {{-- El buscador es la acción principal; área, estado y orden son ajustes secundarios debajo, no cuatro controles del mismo peso. --}}
    <div class="mt-5">
        <label class="relative block">
            <span class="sr-only">Buscar por experiencia</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                type="search"
                placeholder="Buscar experiencias…"
                wire:model.live.debounce.300ms="search"
                class="w-full border border-green-100 bg-white py-2.5 pl-9 pr-3 text-sm text-ink-900 focus-ring"
            >
        </label>

        <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-2">
            <div class="w-40">
                <x-field-select label="Área" wire:model.live="filtroArea">
                    <option value="">Todas las áreas</option>
                    @foreach ($this->areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </x-field-select>
            </div>

            <div class="w-40">
                <x-field-select label="Estado" wire:model.live="filtro">
                    @foreach ($this->filtros as $clave => $etiqueta)
                        <option value="{{ $clave }}">{{ $etiqueta }}</option>
                    @endforeach
                </x-field-select>
            </div>

            <button
                type="button"
                wire:click="toggleOrden"
                class="inline-flex min-h-[44px] items-center gap-1 text-sm text-ink-600 transition-fast hover:text-green-800 focus-ring"
            >
                <span class="text-ink-400">Ordenar:</span>
                {{ $orden === 'desc' ? 'Más recientes' : 'Más antiguas' }}
            </button>
        </div>
    </div>

    <p class="mt-5 text-sm text-ink-600">
        {{ $this->evidencias()->total() }}
        {{ $this->evidencias()->total() === 1 ? 'experiencia enviada' : 'experiencias enviadas' }}
    </p>

    @if ($this->evidencias()->isEmpty())
        <p class="mt-4 border border-green-100 p-6 text-center text-ink-400">
            Todavía no compartieron ninguna experiencia aquí. Las que envíen van a aparecer en esta lista.
        </p>
    @else
        <div class="mt-2 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-green-100">
                        <th scope="col" class="py-2 pr-3 text-left text-xs font-medium uppercase tracking-wide text-ink-400">Experiencia</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-ink-400">Área</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-ink-400">Fecha de envío</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-ink-400">Estado</th>
                        <th scope="col" class="pl-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-ink-400">Respuesta de la guía</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-100">
                    @foreach ($this->evidencias() as $evidencia)
                        <tr class="align-top">
                            <td class="py-4 pr-3 font-medium text-ink-900">{{ $evidencia['experiencia'] }}</td>
                            <td class="px-3 py-4 text-ink-600">{{ $evidencia['area'] ?? '—' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-ink-600">
                                {{ $evidencia['enviada']?->locale('es')->isoFormat('D [de] MMMM [de] YYYY') ?? '—' }}
                            </td>
                            <td class="px-3 py-4">
                                <span class="inline-flex items-center gap-1.5 {{ $textoEstado[$evidencia['estado']] ?? 'text-ink-400' }}">
                                    <span @class(['h-1.5 w-1.5 rounded-full', $puntoEstado[$evidencia['estado']] ?? 'bg-ink-200'])></span>
                                    {{ $etiquetado[$evidencia['estado']] ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td class="pl-3 py-4 text-sm text-ink-500">
                                @if ($evidencia['observacion'])
                                    <p class="border-l-2 border-green-200 pl-3 italic">{{ $evidencia['observacion'] }}</p>
                                @else
                                    <span class="text-ink-300">Sin respuesta aún</span>
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
