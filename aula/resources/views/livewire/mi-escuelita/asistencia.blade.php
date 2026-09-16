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

    <h2 class="mt-10 text-xl font-semibold text-ink-900">Calendario del mes</h2>

    @php
        $puntos = [
            \App\Models\Attendance::STATUS_PRESENTE => 'bg-green-600',
            \App\Models\Attendance::STATUS_ATRASO => 'bg-amber-500',
            \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => 'bg-sky-500',
            \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => 'bg-rose-500',
        ];
    @endphp

    @if ($this->detalleDias->isEmpty())
        <p class="mt-4 rounded-lg border border-green-100 bg-white p-6 text-center text-ink-400 shadow-card">
            No hay registros de asistencia para este mes todavía.
        </p>
    @else
        <div class="mt-4 rounded-lg border border-green-100 bg-white p-4 shadow-card sm:p-6">
            <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-400 sm:gap-2 sm:text-xs">
                <span>Lun</span>
                <span>Mar</span>
                <span>Mié</span>
                <span>Jue</span>
                <span>Vie</span>
                <span>Sáb</span>
                <span>Dom</span>
            </div>

            <div class="mt-2 space-y-1 sm:space-y-2">
                @foreach ($this->calendario as $semana)
                    <div class="grid grid-cols-7 gap-1 sm:gap-2">
                        @foreach ($semana as $celda)
                            @if ($celda === null)
                                <div></div>
                            @else
                                <div
                                    @class([
                                        'relative flex aspect-square flex-col items-center justify-center rounded-md text-xs font-medium sm:text-sm',
                                        $colores[$celda['status']] ?? ($celda['esFinDeSemana'] ? 'bg-bg text-ink-300' : 'bg-bg text-ink-600'),
                                        'ring-2 ring-green-700 ring-offset-1' => $celda['esHoy'],
                                    ])
                                    @if ($celda['status'])
                                        title="{{ $celda['fecha']->locale('es')->isoFormat('D [de] MMMM') }} · {{ $etiquetado[$celda['status']] }}"
                                    @endif
                                >
                                    {{ $celda['fecha']->day }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-green-100 pt-4 text-xs text-ink-600">
                @foreach ($etiquetado as $clave => $etiqueta)
                    <span class="inline-flex items-center gap-1.5">
                        <span @class(['h-2.5 w-2.5 rounded-full', $puntos[$clave]])></span>
                        {{ $etiqueta }}
                    </span>
                @endforeach
            </div>
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