<?php

namespace App\Filament\Resources\AsientoContableResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DetalleAsientoRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $recordTitleAttribute = 'referencia';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Select::make('cuenta_id')
                ->label('Cuenta')
                ->relationship('cuenta', 'codigo')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('debe')
                ->label('Debe')
                ->prefix('$')
                ->numeric()
                ->default(0),

            TextInput::make('haber')
                ->label('Haber')
                ->prefix('$')
                ->numeric()
                ->default(0),

            TextInput::make('referencia')
                ->label('Referencia')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('referencia')
            ->columns([
                Tables\Columns\TextColumn::make('cuenta.codigo')
                    ->label('Código Cuenta')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cuenta.nombre')
                    ->label('Nombre Cuenta')
                    ->searchable(),

                Tables\Columns\TextColumn::make('debe')
                    ->label('Debe')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('haber')
                    ->label('Haber')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->limit(30),
            ])
            ->defaultSort('cuenta.codigo');
    }
}
