<?php

namespace App\Filament\Resources\PresupuestoResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DetalleAPURelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $recordTitleAttribute = 'descripcion';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'material' => 'Material',
                    'mano_obra' => 'Mano de Obra',
                    'subcontrato' => 'Subcontrato',
                    'equipo' => 'Equipo',
                ])
                ->required(),

            TextInput::make('descripcion')
                ->label('Descripción')
                ->required()
                ->maxLength(255),

            TextInput::make('unidad_medida')
                ->label('Unidad de Medida')
                ->required()
                ->maxLength(20),

            TextInput::make('cantidad')
                ->label('Cantidad')
                ->required()
                ->numeric(),

            TextInput::make('costo_unitario')
                ->label('Costo Unitario')
                ->prefix('$')
                ->required()
                ->numeric(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descripcion')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'material' => 'info',
                        'mano_obra' => 'warning',
                        'subcontrato' => 'danger',
                        'equipo' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),

                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad'),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                Tables\Columns\TextColumn::make('costo_unitario')
                    ->label('Costo Unitario')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('costo_total')
                    ->label('Costo Total')
                    ->state(fn ($record): float => (float) $record->cantidad * (float) $record->costo_unitario)
                    ->formatStateUsing(fn ($state): string => '$' . number_format($state, 2))
                    ->sortable(),
            ])
            ->defaultSort('tipo');
    }
}
