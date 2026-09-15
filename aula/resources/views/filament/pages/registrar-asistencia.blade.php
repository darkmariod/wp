@php
    $estadosDisponibles = [
        ['valor' => 'presente', 'label' => 'Presente', 'color' => 'success'],
        ['valor' => 'atraso', 'label' => 'Atraso', 'color' => 'warning'],
        ['valor' => 'falta_justificada', 'label' => 'Justificada', 'color' => 'info'],
        ['valor' => 'falta_injustificada', 'label' => 'Injustificada', 'color' => 'danger'],
    ];
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Registro de asistencia</x-slot>
        <x-slot name="description">
            Elegí el ambiente y la fecha. Marcá el estado de cada niño: se registra una vez por día por niño.
        </x-slot>

        {{ $this->content }}
    </x-filament::section>

    <div wire:loading wire:target="environment_id,fecha">
        <x-filament::section>
            <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding-block:2rem;">
                <x-filament::loading-indicator style="height:1.25rem;width:1.25rem;" />
                <p style="font-size:.875rem;color:var(--gray-500);">Cargando niños del ambiente...</p>
            </div>
        </x-filament::section>
    </div>

    <div wire:loading.remove wire:target="environment_id,fecha">
    @if ($this->ninos->isEmpty())
        <x-filament::section>
            <div style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding-block:2rem;text-align:center;">
                <x-filament::icon icon="heroicon-o-face-smile" style="height:2rem;width:2rem;color:var(--gray-400);" />
                <p style="font-size:.875rem;color:var(--gray-500);">Elegí un ambiente para ver a sus niños.</p>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach ($this->ninos as $nino)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding-block:.5rem;border-bottom:1px solid var(--gray-200);">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            @if ($nino->avatarUrl())
                                <x-filament::avatar src="{{ $nino->avatarUrl() }}" alt="{{ $nino->name }}" size="sm" />
                            @else
                                <span style="display:flex;height:2.25rem;width:2.25rem;flex-shrink:0;align-items:center;justify-content:center;border-radius:9999px;font-size:.7rem;font-weight:600;color:#fff;background:var(--primary-500);">
                                    {{ $nino->avatarInitials() }}
                                </span>
                            @endif
                            <span style="font-weight:600;">{{ $nino->name }}</span>
                        </div>

                        <div style="display:flex;flex-wrap:wrap;gap:.375rem;">
                            @foreach ($estadosDisponibles as $estado)
                                <x-filament::button
                                    size="xs"
                                    :color="$estado['color']"
                                    :outlined="($this->estados[$nino->id] ?? null) !== $estado['valor']"
                                    wire:click="$set('estados.{{ $nino->id }}', '{{ $estado['valor'] }}')"
                                >
                                    {{ $estado['label'] }}
                                </x-filament::button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <div style="display:flex;justify-content:flex-end;">
            <x-filament::button
                color="primary"
                icon="heroicon-o-check"
                wire:click="guardar"
            >
                Guardar asistencia
            </x-filament::button>
        </div>
    @endif
    </div>
</x-filament-panels::page>
