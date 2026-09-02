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
                Resultados al {{ $data['fecha'] ?? now()->format('d/m/Y') }}
            </x-slot>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- ACTIVO --}}
                <div>
                    <h3 class="text-lg font-bold text-primary-600 mb-4">ACTIVO</h3>

                    @if(!empty($resultado['activo']['activo_corriente']['items']))
                        <h4 class="font-semibold text-gray-700 mb-2">Activo Corriente</h4>
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                            </x-slot>
                            @foreach($resultado['activo']['activo_corriente']['items'] as $item)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell class="text-right font-mono">
                                        ${{ number_format($item['saldo'], 2) }}
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                        <p class="text-right font-bold mt-1">
                            Total Activo Corriente: ${{ number_format($resultado['activo']['activo_corriente']['total'], 2) }}
                        </p>
                    @endif

                    @if(!empty($resultado['activo']['activo_no_corriente']['items']))
                        <h4 class="font-semibold text-gray-700 mb-2 mt-4">Activo No Corriente</h4>
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                            </x-slot>
                            @foreach($resultado['activo']['activo_no_corriente']['items'] as $item)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell class="text-right font-mono">
                                        ${{ number_format($item['saldo'], 2) }}
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                        <p class="text-right font-bold mt-1">
                            Total Activo No Corriente: ${{ number_format($resultado['activo']['activo_no_corriente']['total'], 2) }}
                        </p>
                    @endif

                    <div class="border-t-2 border-primary-500 mt-4 pt-2">
                        <p class="text-right text-lg font-bold">
                            TOTAL ACTIVO: ${{ number_format($resultado['total_activo'], 2) }}
                        </p>
                    </div>
                </div>

                {{-- PASIVO + PATRIMONIO --}}
                <div>
                    <h3 class="text-lg font-bold text-danger-600 mb-4">PASIVO</h3>

                    @if(!empty($resultado['pasivo']['corriente']['items']))
                        <h4 class="font-semibold text-gray-700 mb-2">Pasivo Corriente</h4>
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                            </x-slot>
                            @foreach($resultado['pasivo']['corriente']['items'] as $item)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell class="text-right font-mono">
                                        ${{ number_format($item['saldo'], 2) }}
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                        <p class="text-right font-bold mt-1">
                            Total Pasivo Corriente: ${{ number_format($resultado['pasivo']['corriente']['total'], 2) }}
                        </p>
                    @endif

                    @if(!empty($resultado['pasivo']['no_corriente']['items']))
                        <h4 class="font-semibold text-gray-700 mb-2 mt-4">Pasivo No Corriente</h4>
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                            </x-slot>
                            @foreach($resultado['pasivo']['no_corriente']['items'] as $item)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell class="text-right font-mono">
                                        ${{ number_format($item['saldo'], 2) }}
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                        <p class="text-right font-bold mt-1">
                            Total Pasivo No Corriente: ${{ number_format($resultado['pasivo']['no_corriente']['total'], 2) }}
                        </p>
                    @endif

                    <h3 class="text-lg font-bold text-warning-600 mt-6 mb-4">PATRIMONIO</h3>

                    @if(!empty($resultado['patrimonio']['items']))
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                            </x-slot>
                            @foreach($resultado['patrimonio']['items'] as $item)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>{{ $item['codigo'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell>{{ $item['nombre'] }}</x-filament-tables::cell>
                                    <x-filament-tables::cell class="text-right font-mono">
                                        ${{ number_format($item['saldo'], 2) }}
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                        <p class="text-right font-bold mt-1">
                            Total Patrimonio: ${{ number_format($resultado['patrimonio']['total'], 2) }}
                        </p>
                    @endif

                    <div class="border-t-2 border-danger-500 mt-4 pt-2">
                        <p class="text-right text-lg font-bold">
                            TOTAL PASIVO + PATRIMONIO: ${{ number_format($resultado['total_pasivo_patrimonio'], 2) }}
                        </p>
                    </div>
                </div>
            </div>

            @if(abs($resultado['total_activo'] - $resultado['total_pasivo_patrimonio']) < 0.01)
                <div class="mt-4 p-3 bg-success-50 dark:bg-success-950 rounded-lg text-center">
                    <p class="font-bold text-success-700 dark:text-success-300">
                        Balance cuadrado: Total Activo = Total Pasivo + Patrimonio
                    </p>
                </div>
            @else
                <div class="mt-4 p-3 bg-danger-50 dark:bg-danger-950 rounded-lg text-center">
                    <p class="font-bold text-danger-700 dark:text-danger-300">
                        ALERTA: El balance NO cuadrado. Diferencia: ${{ number_format(abs($resultado['total_activo'] - $resultado['total_pasivo_patrimonio']), 2) }}
                    </p>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
