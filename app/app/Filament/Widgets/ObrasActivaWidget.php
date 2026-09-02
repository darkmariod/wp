<?php

namespace App\Filament\Widgets;

use App\Models\Obra;
use App\Services\FlujoCajaService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ObrasActivaWidget extends TableWidget
{
    protected static ?string $heading = 'Obras Activas';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '500px';

    public function table(Table $table): Table
    {
        $flujoService = app(FlujoCajaService::class);

        return $table
            ->query(
                Obra::whereIn('estado', ['planificada', 'en_curse'])
                    ->withSum('presupuestos as total_presupuesto_costo', 'subtotal_costo')
            )
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contrato_monto')
                    ->label('Contrato')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avance_porcentaje')
                    ->label('Avance %')
                    ->getStateUsing(function (Obra $record): string {
                        $totalPresupuesto = (float) ($record->total_presupuesto_costo ?? 0);
                        $totalContrato = (float) $record->contrato_monto;

                        if ($totalContrato <= 0) {
                            return '0.00%';
                        }

                        $avance = bcmul(
                            bcdiv(
                                number_format($totalPresupuesto, 2, '.', ''),
                                number_format($totalContrato, 2, '.', ''),
                                6,
                            ),
                            '100',
                            2,
                        );

                        return number_format((float) $avance, 2) . '%';
                    })
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resultado_neto')
                    ->label('Resultado Neto')
                    ->getStateUsing(fn (Obra $record): string => '$' . number_format(
                        $flujoService->resultadoNeto($record),
                        2,
                    ))
                    ->alignRight()
                    ->sortable()
                    ->color(fn (Obra $record): string => match (true) {
                        $flujoService->resultadoNeto($record) >= 0 => 'success',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('resultado_neto', 'asc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No hay obras activas')
            ->emptyStateDescription('No se encontraron obras en planificación o en curso.');
    }
}
