@php
    $reporte = $this->reporte();
    $etiquetas = [
        'presente' => 'Presente',
        'atraso' => 'Atraso',
        'falta_justificada' => 'Falta justificada',
        'falta_injustificada' => 'Falta injustificada',
    ];
    $colores = [
        'presente' => 'success',
        'atraso' => 'warning',
        'falta_justificada' => 'info',
        'falta_injustificada' => 'danger',
    ];
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Generar reporte</x-slot>
        <x-slot name="description">
            Elegí ambiente, niño y período. El reporte es de uso interno: las familias nunca lo ven.
        </x-slot>

        {{ $this->content }}
    </x-filament::section>

    @if ($reporte)
        <x-filament::section>
            <x-slot name="heading">Resumen — {{ $this->tituloPeriodo() }}</x-slot>

            <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
                @foreach ($etiquetas as $estado => $label)
                    <x-filament::badge :color="$colores[$estado]">
                        {{ $label }}: {{ $reporte['resumen'][$estado] }}
                    </x-filament::badge>
                @endforeach
                <x-filament::badge color="gray">Feriados: {{ $reporte['resumen']['feriado'] }}</x-filament::badge>
                <x-filament::badge color="gray">Sin registro: {{ $reporte['resumen']['sin_registro'] }}</x-filament::badge>
            </div>

            <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
                <x-filament::button color="primary" icon="heroicon-o-document-arrow-down" wire:click="descargarPdf">
                    Descargar PDF
                </x-filament::button>
                <x-filament::button color="gray" icon="heroicon-o-table-cells" wire:click="descargarExcel">
                    Descargar Excel
                </x-filament::button>
            </div>

            <table style="width:100%;border-collapse:collapse;font-size:.8125rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--gray-200);text-align:left;">
                        <th style="padding:.4rem .5rem;">Fecha</th>
                        <th style="padding:.4rem .5rem;">Día</th>
                        <th style="padding:.4rem .5rem;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reporte['dias'] as $dia)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:.4rem .5rem;">{{ $dia['fecha']->format('d/m/Y') }}</td>
                            <td style="padding:.4rem .5rem;">{{ ucfirst($dia['fecha']->locale('es')->isoFormat('dddd')) }}</td>
                            <td style="padding:.4rem .5rem;">
                                @if ($dia['feriado'])
                                    <span style="color:var(--gray-500);font-style:italic;">Feriado: {{ $dia['feriado'] }}</span>
                                @elseif ($dia['finDeSemana'])
                                    <span style="color:var(--gray-500);">Fin de semana</span>
                                @elseif ($dia['status'])
                                    <x-filament::badge :color="$colores[$dia['status']] ?? 'gray'">
                                        {{ $etiquetas[$dia['status']] ?? $dia['status'] }}
                                    </x-filament::badge>
                                @else
                                    <span style="color:var(--gray-400);">Sin registro</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @else
        <x-filament::section>
            <div style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding-block:2rem;text-align:center;">
                <x-filament::icon icon="heroicon-o-document-chart-bar" style="height:2rem;width:2rem;color:var(--gray-400);" />
                <p style="font-size:.875rem;color:var(--gray-500);">Elegí ambiente, niño y período para ver el reporte.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
