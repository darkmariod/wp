<?php

namespace App\Filament\Widgets;

use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;

class CuentasPorVencerWidget extends TableWidget
{
    protected static ?string $heading = 'Cuentas por Vencer (7 días)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $hoy = Carbon::now()->toDateString();
        $limite = Carbon::now()->addDays(7)->toDateString();

        $cuentasCobrar = CuentaPorCobrar::where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [$hoy, $limite])
            ->with('cliente:id,razon_social')
            ->get()
            ->map(fn ($c) => [
                'id' => 'cobrar-' . $c->id,
                'tipo' => 'Cobrar',
                'entidad' => $c->cliente->razon_social ?? 'N/A',
                'monto' => (float) ($c->monto_total - $c->monto_cobrado),
                'fecha_vencimiento' => $c->fecha_vencimiento,
                'dias_para_vencer' => (int) Carbon::parse($c->fecha_vencimiento)->diffInDays(now()),
            ]);

        $cuentasPagar = CuentaPorPagar::where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [$hoy, $limite])
            ->with('proveedor:id,razon_social')
            ->get()
            ->map(fn ($c) => [
                'id' => 'pagar-' . $c->id,
                'tipo' => 'Pagar',
                'entidad' => $c->proveedor->razon_social ?? 'N/A',
                'monto' => (float) ($c->monto_total - $c->monto_pagado),
                'fecha_vencimiento' => $c->fecha_vencimiento,
                'dias_para_vencer' => (int) Carbon::parse($c->fecha_vencimiento)->diffInDays(now()),
            ]);

        $items = $cuentasCobrar->concat($cuentasPagar)
            ->sortBy('fecha_vencimiento')
            ->values()
            ->all();

        return $table
            ->records(fn () => $items)
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cobrar' => 'success',
                        'Pagar' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('entidad')
                    ->label('Cliente / Proveedor'),
                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->alignRight(),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('dias_para_vencer')
                    ->label('Días')
                    ->badge()
                    ->color(fn (int|string $state): string => match (true) {
                        (int) $state <= 0 => 'danger',
                        (int) $state <= 3 => 'warning',
                        default => 'info',
                    }),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No hay cuentas por vencer')
            ->emptyStateDescription('No se encontraron cuentas con vencimiento en los próximos 7 días.');
    }
}
