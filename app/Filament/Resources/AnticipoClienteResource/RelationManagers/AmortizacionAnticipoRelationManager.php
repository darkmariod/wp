<?php

namespace App\Filament\Resources\AnticipoClienteResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AmortizacionAnticipoRelationManager extends RelationManager
{
    protected static string $relationship = 'amortizaciones';

    protected static ?string $recordTitleAttribute = 'numero_amortizacion';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            TextInput::make('numero_amortizacion')
                ->label('Número de Amortización')
                ->required()
                ->maxLength(50),

            TextInput::make('porcentaje_amortizar')
                ->label('Porcentaje a Amortizar')
                ->suffix('%')
                ->required()
                ->numeric(),

            TextInput::make('monto_amortizado')
                ->label('Monto Amortizado')
                ->prefix('$')
                ->required()
                ->numeric(),

            TextInput::make('avance_porcentaje')
                ->label('Avance Porcentaje')
                ->suffix('%')
                ->numeric(),

            DatePicker::make('fecha_amortizacion')
                ->label('Fecha de Amortización')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_amortizacion')
            ->columns([
                Tables\Columns\TextColumn::make('numero_amortizacion')
                    ->label('Nro. Amortización')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('porcentaje_amortizar')
                    ->label('% Amortizar')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_amortizado')
                    ->label('Monto Amortizado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('avance_porcentaje')
                    ->label('Avance %')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_amortizacion')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('fecha_amortizacion', 'desc');
    }
}
