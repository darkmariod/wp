<div>
    <div>
        <x-breadcrumb
            :items="[['label' => 'Inicio', 'url' => route('mi-escuelita.home')], ['label' => 'Asistencia']]"
        />
        <h1 class="mt-4 text-3xl font-semibold text-ink-900">Asistencia</h1>
        <p class="mt-2 text-sm text-ink-600">
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

    <div class="mt-6 flex flex-wrap items-end gap-3">
        <div class="inline-flex rounded-md border border-green-100 bg-white p-1">
            <button
                type="button"
                wire:click="$set('vista', 'semana')"
                @class([
                    'rounded px-3 py-1.5 text-sm font-medium transition-fast',
                    'bg-green-800 text-white' => $vista === 'semana',
                    'text-ink-600 hover:text-green-800' => $vista !== 'semana',
                ])
            >Semana</button>
            <button
                type="button"
                wire:click="$set('vista', 'mes')"
                @class([
                    'rounded px-3 py-1.5 text-sm font-medium transition-fast',
                    'bg-green-800 text-white' => $vista === 'mes',
                    'text-ink-600 hover:text-green-800' => $vista !== 'mes',
                ])
            >Mes</button>
        </div>

        <div class="max-w-xs">
            @if ($vista === 'semana')
                <x-field-select label="Semana" wire:model.live="semana">
                    @foreach ($this->opcionesSemanas as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </x-field-select>
            @else
                <x-field-select label="Mes" wire:model.live="mes">
                    <option value="">Mes actual</option>
                    @foreach ($this->opcionesMeses as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </x-field-select>
            @endif
        </div>
    </div>

    @if ($vista === 'semana')
        <x-fact-row :cols="4" class="mt-6">
            @foreach ([
                'Presentes' => $this->resumenSemana[\App\Models\Attendance::STATUS_PRESENTE] ?? 0,
                'Atrasos' => $this->resumenSemana[\App\Models\Attendance::STATUS_ATRASO] ?? 0,
                'Justificadas' => $this->resumenSemana[\App\Models\Attendance::STATUS_FALTA_JUSTIFICADA] ?? 0,
                'Injustificadas' => $this->resumenSemana[\App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA] ?? 0,
            ] as $etiqueta => $cantidad)
                <div class="py-3 sm:px-6 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                    <p class="text-2xl font-semibold text-ink-900">{{ $cantidad }}</p>
                    <p class="mt-0.5 text-sm text-ink-600">{{ $etiqueta }}</p>
                </div>
            @endforeach
        </x-fact-row>

        <h2 class="mt-10 text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">
            Semana del {{ $this->etiquetaSemana }}
        </h2>

        <ul class="mt-4 divide-y divide-green-100 rounded-lg border border-green-100 bg-white shadow-card">
            @foreach ($this->detalleSemana as $dia)
                <li @class(['flex items-center justify-between gap-3 px-5 py-3', 'bg-green-50/40' => $dia['esHoy']])>
                    <span class="text-sm text-ink-900">
                        {{ ucfirst($dia['fecha']->locale('es')->isoFormat('dddd D [de] MMMM')) }}
                        @if ($dia['esHoy'])
                            <span class="ml-1 text-xs text-green-700">(hoy)</span>
                        @endif
                    </span>
                    <span @class(['rounded-full px-2.5 py-0.5 text-xs font-medium', $colores[$dia['status']] ?? 'bg-gray-100 text-gray-500'])>
                        {{ $etiquetado[$dia['status']] ?? 'Sin registro' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <x-fact-row :cols="4" class="mt-6">
            @foreach ([
                'Presentes' => $this->resumenMes[\App\Models\Attendance::STATUS_PRESENTE] ?? 0,
                'Atrasos' => $this->resumenMes[\App\Models\Attendance::STATUS_ATRASO] ?? 0,
                'Justificadas' => $this->resumenMes[\App\Models\Attendance::STATUS_FALTA_JUSTIFICADA] ?? 0,
                'Injustificadas' => $this->resumenMes[\App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA] ?? 0,
            ] as $etiqueta => $cantidad)
                <div class="py-3 sm:px-6 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                    <p class="text-2xl font-semibold text-ink-900">{{ $cantidad }}</p>
                    <p class="mt-0.5 text-sm text-ink-600">{{ $etiqueta }}</p>
                </div>
            @endforeach
        </x-fact-row>

        <h2 class="mt-10 text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Calendario del mes</h2>

        @php
            $puntos = [
                \App\Models\Attendance::STATUS_PRESENTE => 'bg-green-600',
                \App\Models\Attendance::STATUS_ATRASO => 'bg-amber-500',
                \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => 'bg-sky-500',
                \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => 'bg-rose-500',
            ];
        @endphp

        @if ($this->detalleDias->isEmpty())
            <p class="mt-4 border border-green-100 p-6 text-center text-ink-400">
                No hay registros de asistencia para este mes todavía.
            </p>
        @else
            <div class="mt-4 border-y border-green-100 py-4 sm:py-6">
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
                                            'relative flex h-11 flex-col items-center justify-center text-xs font-medium sm:h-12 sm:text-sm',
                                            $colores[$celda['status']] ?? ($celda['esFinDeSemana'] ? 'bg-bg text-ink-300' : 'bg-bg text-ink-600'),
                                            'border-2 border-green-800' => $celda['esHoy'],
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

                <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs text-ink-600">
                    @foreach ($etiquetado as $clave => $etiqueta)
                        <span class="inline-flex items-center gap-1.5">
                            <span @class(['h-2.5 w-2.5', $puntos[$clave]])></span>
                            {{ $etiqueta }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    @if ($this->resumenAnual)
        <div class="mt-10">
            <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">
                Resumen del ciclo {{ $this->resumenAnual['etiqueta'] }}, ya cerrado
            </h2>

            <x-fact-row :cols="4" class="mt-4">
                @foreach ([
                    'Presentes' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_PRESENTE] ?? 0,
                    'Atrasos' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_ATRASO] ?? 0,
                    'Justificadas' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_FALTA_JUSTIFICADA] ?? 0,
                    'Injustificadas' => $this->resumenAnual['counts'][\App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA] ?? 0,
                ] as $etiqueta => $cantidad)
                    <div class="py-3 sm:px-6 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                        <p class="text-2xl font-semibold text-ink-900">{{ $cantidad }}</p>
                        <p class="mt-0.5 text-sm text-ink-600">{{ $etiqueta }}</p>
                    </div>
                @endforeach
            </x-fact-row>
        </div>
    @endif
</div>
