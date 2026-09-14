@php
    $mes = \App\Support\CicloEscolar::resumenMes(now(), $child);
    $cicloAnio = \App\Support\CicloEscolar::vigente();
    $ciclo = \App\Support\CicloEscolar::resumenCiclo($cicloAnio, $child);
    $etiqueta = \App\Support\CicloEscolar::etiqueta($cicloAnio);
    $estados = [
        'presente' => ['label' => 'Presentes', 'color' => 'success'],
        'atraso' => ['label' => 'Atrasos', 'color' => 'warning'],
        'falta_justificada' => ['label' => 'Justificadas', 'color' => 'info'],
        'falta_injustificada' => ['label' => 'Injustificadas', 'color' => 'danger'],
    ];
@endphp

<div style="display:grid;grid-template-columns:1fr;gap:1rem;">
    <div style="border:1px solid var(--gray-200);border-radius:.75rem;padding:1rem;">
        <p style="font-size:.875rem;font-weight:600;">Este mes</p>
        <div style="margin-top:.5rem;display:flex;flex-wrap:wrap;gap:.375rem;">
            @foreach ($estados as $clave => $info)
                <x-filament::badge :color="$info['color']">
                    {{ $mes[$clave] ?? 0 }} {{ $info['label'] }}
                </x-filament::badge>
            @endforeach
        </div>
    </div>

    <div style="border:1px solid var(--gray-200);border-radius:.75rem;padding:1rem;">
        <p style="font-size:.875rem;font-weight:600;">Ciclo lectivo {{ $etiqueta }}</p>
        <div style="margin-top:.5rem;display:flex;flex-wrap:wrap;gap:.375rem;">
            @foreach ($estados as $clave => $info)
                <x-filament::badge :color="$info['color']">
                    {{ $ciclo[$clave] ?? 0 }} {{ $info['label'] }}
                </x-filament::badge>
            @endforeach
        </div>
    </div>
</div>
