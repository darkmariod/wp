<div>
    <div>
        <x-breadcrumb
            :items="[['label' => 'Inicio', 'url' => route('mi-escuelita.home')], ['label' => 'Asistencia']]"
        />
        <h1 class="mt-4 text-3xl font-bold text-ink-900">Asistencia</h1>
        <p class="mt-2 text-sm text-ink-400">
            El día a día de {{ $this->nino?->name ?? 'tu niño' }} en el colegio, mes a mes y el resumen del último ciclo lectivo ya cerrado.
        </p>
    </div>

    @php
        $etiquetado = [
            \App\Models\Attendance::STATUS_PRESENTE => 'Presente',
            \App\Models\Attendance::STATUS_ATRASO => 'Atraso',
            \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => 'Falta justificada',
            \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => 'Falta injustificada',
        ];
        $colores = [
            \App\Models\Attendance::STATUS_PRESENTE => 'bg-green-50 text-green-800',
            \App\Models\Attendance::STATUS_ATRASO => 'bg-amber-50 text-amber-700',
            \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => 'bg-sky-50 text-sky-700',
            \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => 'bg-rose-50 text-rose-700',
        ];
    @endphp

    <div class="mt-6 max-w-xs">
        <x-field-select label="Mes" wire:model.live="mes">
            <option value="">Mes actual</option>
            @foreach ($this->opcionesMeses as $valor => $etiqueta)
                <option value="{{ $valor }}">{{ $etiqueta }}</option>
            @endforeach
        </x-field-select>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            'Presentes' => $this->resumenMes[\App\Models\Attendance::STATUS_PRESENTE] ?? 0,
            'Atrasos' => $this->resumenMes[\App\Models\Attendance::STATUS_ATRASO] ?? 0,
            'Justificadas' => $this->resumenMes[\App\Models\Attendance::STATUS_FALTA_JUSTIFICADA] ?? 0,
            'Injustificadas' => $this->resumenMes[\App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA] ?? 0,
        ] as $etiqueta => $cantidad)
            <div class="rounded-lg border border-green-100 bg-white p-5 shadow-card">
                <p class="text-3xl font-bold text-ink-900">{{ $cantidad }}</p>
                <p class="mt-1 text-sm font-medium text-ink-400">{{ $etiqueta }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mt-10 text-xl font-semibold text-ink-900">Día por día</h2>

    @if ($this->detalleDias->isEmpty())
        <p class="mt-4 rounded-lg border border-green-100 bg-white p-6 text-center text-ink-400 shadow-card">
            No hay registros de asistencia para este mes todavía.
        </p>
    @else
        <div class="mt-4 overflow-x-auto rounded-lg border border-green-100 bg-white shadow-card">
            <table class="min-w-full divide-y divide-green-100 text-sm">
                <thead class="bg-green-50/60">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Fecha</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Estado</th>
                        @if ($this->detalleDias->contains(fn ($d) => filled($d->notes)))
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-600">Nota</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-100">
                    @foreach ($this->detalleDias as $dia)
                        <tr class="transition-base hover:bg-green-50/40">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-ink-900">
                                {{ $dia->date->locale('es')->isoFormat('D [de] MMMM') }}
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium',
                                    $colores[$dia->status] ?? 'bg-ink-100 text-ink-400',
                                ])>
                                    {{ $etiquetado[$dia->status] ?? $dia->status }}
                                </span>
                            </td>
                            @if ($this->detalleDias->contains(fn ($d) => filled($d->notes)))
                                <td class="px-5 py-4 text-ink-600">{{ $dia->notes ?? '—' }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($this->resumenAnual)
        <div class="mt-10 rounded-lg border border-green-100 bg-white p-6 shadow-card">
            <h2 class="text-xl font-semibold text-ink-900">Resumen anual — ciclo {{ $this->resumenAnual['etiqueta'] }}</h2>
            <p class="mt-1 text-sm text-ink-400">
                Corresponde al ciclo lectivo {{ $this->resumenAnual['etiqueta'] }} ya cerrado.
            </p>
            <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ([
                    'Presentes' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_PRESENTE] ?? 0,
                    'Atrasos' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_ATRASO] ?? 0,
                    'Justificadas' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_FALTA_JUSTIFICADA] ?? 0,
                    'Injustificadas' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA] ?? 0,
                ] as $etiqueta => $cantidad)
                    <div class="rounded-lg border border-green-100 bg-green-50/40 p-5">
                        <p class="text-3xl font-bold text-ink-900">{{ $cantidad }}</p>
                        <p class="mt-1 text-sm font-medium text-ink-400">{{ $etiqueta }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>