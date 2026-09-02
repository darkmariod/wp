<?php

namespace App\Filament\Resources\AnticipoClienteResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AmortizacionAnticipoRelationManager extends RelationManager
{
    protected static string $relationship = 'amortizaciones';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('numero_amortizacion')
                    ->label('Número de Amortización')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('porcentaje_amortizar')
                    ->label('Porcentaje a Amortizar (%)')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('monto_amortizado')
                    ->label('Monto Amortizado')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\TextInput::make('avance_porcentaje')
                    ->label('Avance Porcentaje (%)')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_amortizacion')
                    ->label('Fecha de Amortización')
                    ->required()
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_amortizacion')
            ->columns([
                Tables\Columns\TextColumn::make('numero_amortizacion')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('porcentaje_amortizar')
                    ->label('Porcentaje')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_amortizado')
                    ->label('Monto Amortizado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('avance_porcentaje')
                    ->label('Avance')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_amortizacion')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
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
