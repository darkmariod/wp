<?php

namespace App\Filament\Resources\ChildResource\RelationManagers;

use App\Models\Attendance;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * Historial de asistencia del nino + resumen del mes en curso y del
 * ciclo lectivo vigente en la cabecera de la tabla.
 */
class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Asistencia';

    public function table(Table $table): Table
    {
        return $table
            ->header(fn (): View => view('filament.pages.attendance-resumen', [
                'child' => $this->getOwnerRecord(),
            ]))
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Attendance::STATUS_PRESENTE => 'success',
                        Attendance::STATUS_ATRASO => 'warning',
                        Attendance::STATUS_FALTA_JUSTIFICADA => 'info',
                        Attendance::STATUS_FALTA_INJUSTIFICADA => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Attendance::STATUS_PRESENTE => 'Presente',
                        Attendance::STATUS_ATRASO => 'Atraso',
                        Attendance::STATUS_FALTA_JUSTIFICADA => 'Falta justificada',
                        Attendance::STATUS_FALTA_INJUSTIFICADA => 'Falta injustificada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Nota')
                    ->placeholder('—')
                    ->limit(60),
                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Registró')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mes')
                    ->label('Mes')
                    ->options($this->opcionesMeses())
                    ->query(fn (Builder $query, array $data): Builder => $this->filtrarMes($query, $data)),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                // La guia solo ve la asistencia de los ninos de sus ambientes.
                if (auth()->user()?->isGuia()) {
                    $query->whereHas(
                        'child.environment',
                        fn (Builder $q) => $q->where('teacher_id', auth()->id()),
                    );
                }

                return $query;
            });
    }

    /**
     * Meses del ciclo lectivo vigente (septiembre ... agosto siguiente).
     */
    public function opcionesMeses(): array
    {
        $anio = \App\Support\CicloEscolar::vigente();
        $inicio = \App\Support\CicloEscolar::inicio($anio);

        $opciones = [];

        for ($i = 0; $i < 12; $i++) {
            $mes = $inicio->addMonths($i);
            $clave = $mes->format('Y-m');
            $opciones[$clave] = ucfirst($mes->locale('es')->isoFormat('MMMM YYYY'));
        }

        return $opciones;
    }

    protected function filtrarMes(Builder $query, array $data): Builder
    {
        if (blank($data['value'])) {
            return $query;
        }

        [$anio, $mes] = explode('-', $data['value']);

        return $query->whereYear('date', $anio)->whereMonth('date', $mes);
    }
}