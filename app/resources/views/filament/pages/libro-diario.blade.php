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
                {{ count($resultado) }} asientos contables
            </x-slot>

            @forelse($resultado as $asiento)
                <div class="mb-6 border dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 flex flex-wrap gap-4 text-sm">
                        <span class="font-bold">Asiento #{{ $asiento['numero_asiento'] }}</span>
                        <span>{{ $asiento['fecha'] }}</span>
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            {{ $asiento['estado'] === 'aprobado' ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700' }}">
                            {{ ucfirst($asiento['estado']) }}
                        </span>
                        @if($asiento['obra'] !== '—')
                            <span class="text-gray-500">Obra: {{ $asiento['obra'] }}</span>
                        @endif
                    </div>

                    <p class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 italic">
                        {{ $asiento['descripcion'] }}
                    </p>

                    <x-filament-tables::table>
                        <x-slot name="header">
                            <x-filament-tables::header-cell>Cuenta</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>Referencia</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell class="text-right">Debe</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell class="text-right">Haber</x-filament-tables::header-cell>
                        </x-slot>
                        @foreach($asiento['detalles'] as $detalle)
                            <x-filament-tables::row>
                                <x-filament-tables::cell class="font-mono text-xs">{{ $detalle['cuenta_codigo'] }}</x-filament-tables::cell>
                                <x-filament-tables::cell>{{ $detalle['cuenta_nombre'] }}</x-filament-tables::cell>
                                <x-filament-tables::cell>{{ $detalle['referencia'] ?? '—' }}</x-filament-tables::cell>
                                <x-filament-tables::cell class="text-right font-mono">
                                    {{ $detalle['debe'] > 0 ? '$' . number_format($detalle['debe'], 2) : '' }}
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="text-right font-mono">
                                    {{ $detalle['haber'] > 0 ? '$' . number_format($detalle['haber'], 2) : '' }}
                                </x-filament-tables::cell>
                            </x-filament-tables::row>
                        @endforeach
                    </x-filament-tables::table>

                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 text-sm flex justify-end gap-8">
                        <span>Total Debe: <strong class="font-mono">${{ number_format($asiento['total_debe'], 2) }}</strong></span>
                        <span>Total Haber: <strong class="font-mono">${{ number_format($asiento['total_haber'], 2) }}</strong></span>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No se encontraron asientos contables en el periodo seleccionado.</p>
            @endforelse
        </x-filament::section>
    @endif
</x-filament-panels::page>
