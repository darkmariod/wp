<?php

namespace App\Filament\Resources\AsientoContableResource\RelationManagers;

use App\Models\PlanCuenta;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DetalleAsientoRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('cuenta_id')
                    ->label('Cuenta')
                    ->relationship('cuenta', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo} - {$record->nombre}")
                    ->query(fn ($query) => $query->where('es_auxiliar', true)->where('activa', true)),
                Forms\Components\TextInput::make('debe')
                    ->label('Debe')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->requiredWithout('haber'),
                Forms\Components\TextInput::make('haber')
                    ->label('Haber')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->requiredWithout('debe'),
                Forms\Components\TextInput::make('referencia')
                    ->label('Referencia')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('referencia')
            ->columns([
                Tables\Columns\TextColumn::make('cuenta.codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cuenta.nombre')
                    ->label('Cuenta')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('debe')
                    ->label('Debe')
                    ->formatStateUsing(fn ($state): string => (float) $state > 0 ? '$' . number_format((float) $state, 2) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('haber')
                    ->label('Haber')
                    ->formatStateUsing(fn ($state): string => (float) $state > 0 ? '$' . number_format((float) $state, 2) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
