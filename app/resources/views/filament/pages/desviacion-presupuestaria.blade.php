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
                {{ $resultado['obra']['codigo'] }} - {{ $resultado['obra']['nombre'] }}
            </x-slot>

            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell>Codigo</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Descripcion</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>UdM</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">Presupuestado</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">Ejecutado</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">Desviacion</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-right">%</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Estado</x-filament-tables::header-cell>
                </x-slot>
                @foreach($resultado['items'] as $item)
                    <x-filament-tables::row>
                        <x-filament-tables::cell class="font-mono text-xs">{{ $item['codigo'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $item['descripcion'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $item['unidad_medida'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono">${{ number_format($item['subtotal_presupuestado'], 2) }}</x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono">${{ number_format($item['subtotal_ejecutado'], 2) }}</x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono {{ $item['desviacion'] > 0 ? 'text-danger-600' : ($item['desviacion'] < 0 ? 'text-success-600' : '') }}">
                            ${{ number_format($item['desviacion'], 2) }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell class="text-right font-mono">{{ number_format($item['porcentaje'], 1) }}%</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                {{ $item['clasificacion'] === 'sobrecosto' ? 'bg-danger-100 text-danger-700' : ($item['clasificacion'] === 'subconsumo' ? 'bg-success-100 text-success-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ ucfirst($item['clasificacion']) }}
                            </span>
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                @endforeach
            </x-filament-tables::table>

            <div class="border-t-2 border-primary-500 mt-4 pt-4">
                <div class="flex justify-between text-sm mb-1">
                    <span>Total Presupuestado:</span>
                    <strong class="font-mono">${{ number_format($resultado['totales']['presupuestado'], 2) }}</strong>
                </div>
                <div class="flex justify-between text-sm mb-1">
                    <span>Total Ejecutado:</span>
                    <strong class="font-mono">${{ number_format($resultado['totales']['ejecutado'], 2) }}</strong>
                </div>
                <div class="flex justify-between text-sm mb-1">
                    <span>Desviacion Total:</span>
                    <strong class="font-mono {{ $resultado['totales']['desviacion'] > 0 ? 'text-danger-600' : ($resultado['totales']['desviacion'] < 0 ? 'text-success-600' : '') }}">
                        ${{ number_format($resultado['totales']['desviacion'], 2) }}
                    </strong>
                </div>
                <div class="flex justify-between text-lg font-bold mt-2">
                    <span>Porcentaje:</span>
                    <span>{{ number_format($resultado['totales']['porcentaje'], 1) }}%</span>
                </div>
                <div class="mt-2 text-center">
                    <span class="px-3 py-1 rounded text-sm font-semibold
                        {{ $resultado['totales']['clasificacion'] === 'sobrecosto' ? 'bg-danger-100 text-danger-700' : ($resultado['totales']['clasificacion'] === 'subconsumo' ? 'bg-success-100 text-success-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ ucfirst($resultado['totales']['clasificacion']) }}
                    </span>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
