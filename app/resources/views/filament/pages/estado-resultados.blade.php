<x-filament-panels::page>
    <form wire:submit="generar">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Generar Reporte
            </x-filament::button>
        </div>
    </form>

    @if($resultado)
        <x-filament::section class="mt-8">
            <x-slot name="heading">
                Periodo: {{ $resultado['periodo']['inicio'] }} al {{ $resultado['periodo']['fin'] }}
            </x-slot>

            {{-- INGRESOS --}}
            @if(!empty($resultado['grupos']['ingresos']['items']))
                <h4 class="font-bold text-success-600 mb-2">INGRESOS</h4>
                <x-filament-tables::table>
                    <x-slot name="header">
                        <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                    </x-slot>
                    @foreach($resultado['grupos']['ingresos']['items'] as $item)
                        <x-filament-tables::row>
                            <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell class="text-right font-mono">
                                ${{ number_format($item['saldo'], 2) }}
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                </x-filament-tables::table>
                <p class="text-right font-bold mt-1">Total Ingresos: ${{ number_format($resultado['grupos']['ingresos']['total'], 2) }}</p>
            @endif

            {{-- COSTOS --}}
            @if(!empty($resultado['grupos']['costos']['items']))
                <h4 class="font-bold text-danger-600 mb-2 mt-4">COSTOS</h4>
                <x-filament-tables::table>
                    <x-slot name="header">
                        <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                    </x-slot>
                    @foreach($resultado['grupos']['costos']['items'] as $item)
                        <x-filament-tables::row>
                            <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell class="text-right font-mono">
                                ${{ number_format($item['saldo'], 2) }}
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                </x-filament-tables::table>
                <p class="text-right font-bold mt-1">Total Costos: ${{ number_format($resultado['grupos']['costos']['total'], 2) }}</p>
            @endif

            {{-- UTILIDAD BRUTA --}}
            <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mt-4">
                <p class="text-right font-bold text-lg">
                    UTILIDAD BRUTA: ${{ number_format($resultado['calculado']['utilidad_bruta']['monto'], 2) }}
                    <span class="text-sm font-normal text-gray-500">({{ number_format($resultado['calculado']['utilidad_bruta']['porcentaje'], 1) }}%)</span>
                </p>
            </div>

            {{-- GASTOS --}}
            @foreach(['gastos_administrativos' => 'Gastos Administrativos', 'gastos_venta' => 'Gastos de Venta', 'gastos_financieros' => 'Gastos Financieros'] as $key => $label)
                @if(!empty($resultado['grupos'][$key]['items']))
                    <h4 class="font-bold text-warning-600 mb-2 mt-4">{{ strtoupper($label) }}</h4>
                    <x-filament-tables::table>
                        <x-slot name="header">
                            <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                        </x-slot>
                        @foreach($resultado['grupos'][$key]['items'] as $item)
                            <x-filament-tables::row>
                                <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                                <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                                <x-filament-tables::cell class="text-right font-mono">
                                    ${{ number_format($item['saldo'], 2) }}
                                </x-filament-tables::cell>
                            </x-filament-tables::row>
                        @endforeach
                    </x-filament-tables::table>
                    <p class="text-right font-bold mt-1">Total {{ $label }}: ${{ number_format($resultado['grupos'][$key]['total'], 2) }}</p>
                @endif
            @endforeach

            {{-- UTILIDAD OPERATIVA --}}
            <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mt-4">
                <p class="text-right font-bold text-lg">
                    UTILIDAD OPERATIVA: ${{ number_format($resultado['calculado']['utilidad_operativa']['monto'], 2) }}
                    <span class="text-sm font-normal text-gray-500">({{ number_format($resultado['calculado']['utilidad_operativa']['porcentaje'], 1) }}%)</span>
                </p>
            </div>

            {{-- RESUMEN FINAL --}}
            <div class="border-t-2 border-primary-500 mt-4 pt-4 space-y-2">
                <p class="text-right">
                    Utilidad Antes de Impuestos: <span class="font-bold">${{ number_format($resultado['calculado']['utilidad_antes_impuestos']['monto'], 2) }}</span>
                </p>
                <p class="text-right text-xl font-bold">
                    UTILIDAD NETA: ${{ number_format($resultado['calculado']['utilidad_neta']['monto'], 2) }}
                    <span class="text-sm font-normal text-gray-500">({{ number_format($resultado['calculado']['utilidad_neta']['porcentaje'], 1) }}%)</span>
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
