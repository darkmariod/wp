<div>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <x-breadcrumb
                :items="[['label' => 'Inicio', 'url' => route('mi-escuelita.home')], ['label' => 'Experiencias']]"
            />
            <h1 class="mt-4 text-3xl font-bold text-ink-900">Experiencias</h1>
        </div>
    </div>

    @php
        $etiquetasTipo = [
            \App\Models\Content::TYPE_READING => 'Lectura',
            \App\Models\Content::TYPE_TASK => 'Tarea',
            \App\Models\Content::TYPE_EXPERIENCE => 'Experiencia',
            \App\Models\Content::TYPE_VIDEO => 'Video',
            \App\Models\Content::TYPE_DOCUMENT => 'Documento',
            \App\Models\Content::TYPE_GALLERY => 'Galería',
            \App\Models\Content::TYPE_ANNOUNCEMENT => 'Aviso',
        ];
    @endphp

    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <label class="sm:col-span-1">
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
                    <option value="{{ $area->id }}">{{ $area->icon }} {{ $area->name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-400">
            {{ $this->experiencias()->total() }}
            {{ $this->experiencias()->total() === 1 ? 'experiencia' : 'experiencias' }}
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

    @if ($this->experiencias()->isEmpty())
        <p class="mt-10 rounded-lg border border-green-100 bg-white p-6 text-center text-ink-400 shadow-card">
            Todavía no hay experiencias publicadas para este ambiente. Pasate por la colección más tarde.
        </p>
    @else
        <div class="mt-4 overflow-x-auto rounded-lg border border-green-100 bg-white shadow-card">
            <table class="min-w-full divide-y divide-green-100 text-sm">
                <thead class="bg-green-50/60">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Experiencia</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Área</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Publicada</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-100">
                    @foreach ($this->experiencias() as $exp)
                        <tr class="align-top transition-base hover:bg-green-50/40">
                            <td class="px-5 py-4">
                                <a
                                    href="{{ route('mi-escuelita.experiencias.show', $exp->slug) }}"
                                    class="font-medium text-ink-900 hover:text-green-800 focus-ring"
                                >
                                    {{ $exp->title }}
                                </a>
                                @if ($exp->requires_evidence)
                                    <span class="mt-1 block w-fit rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Pide compartir foto o video
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-ink-600">
                                @if ($exp->area)
                                    <span class="inline-flex items-center gap-1.5">
                                        <span aria-hidden="true">{{ $exp->area->icon }}</span>
                                        <span>{{ $exp->area->name }}</span>
                                    </span>
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-ink-600">
                                {{ $exp->published_at?->locale('es')->isoFormat('D [de] MMMM [de] YYYY') ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <a
                                    href="{{ route('mi-escuelita.experiencias.show', $exp->slug) }}"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-green-700 hover:text-green-800 focus-ring"
                                >
                                    Ver
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->experiencias()->links() }}
        </div>
    @endif
</div>
