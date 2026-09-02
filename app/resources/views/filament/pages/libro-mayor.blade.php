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
                {{ $resultado['cuenta']['codigo'] }} - {{ $resultado['cuenta']['nombre'] }}
            </x-slot>

            <p class="text-sm text-gray-500 mb-4">
                Periodo: {{ $resultado['periodo']['inicio'] }} al {{ $resultado['periodo']['fin'] }}
                | Tipo: {{ ucfirst($resultado['cuenta']['tipo']) }}
            </p>

            <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-4">
                <p class="text-sm">Saldo Inicial: <strong class="font-mono">${{ number_format($resultado['saldo_inicial'], 2) }}</strong></p>
            </div>

            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell>Fecha</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>N Asiento</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Referencia</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">Debe</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">Haber</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">Saldo</x-filament-tables::header-cell>
                </x-slot>
                @foreach($resultado['movimientos'] as $mov)
                    <x-filament-tables::row>
                        <x-filament-tables::cell>{{ $mov['fecha'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell class="font-mono">{{ $mov['numero_asiento'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $mov['referencia'] ?? '—' }}</x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono">
                            {{ $mov['debe'] > 0 ? '$' . number_format($mov['debe'], 2) : '' }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono">
                            {{ $mov['haber'] > 0 ? '$' . number_format($mov['haber'], 2) : '' }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono font-bold">
                            ${{ number_format($mov['saldo'], 2) }}
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                @endforeach
            </x-filament-tables::table>

            <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mt-4 flex justify-between text-sm">
                <span>Total Debe: <strong class="font-mono">${{ number_format($resultado['total_debe'], 2) }}</strong></span>
                <span>Total Haber: <strong class="font-mono">${{ number_format($resultado['total_haber'], 2) }}</strong></span>
            </div>

            <div class="border-t-2 border-primary-500 mt-4 pt-2">
                <p class="text-right text-lg font-bold">
                    Saldo Final: <span class="font-mono">${{ number_format($resultado['saldo_final'], 2) }}</span>
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
