<?php

namespace App\Filament\Resources\Properties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('sector.name')
                    ->label('Zona')
                    ->sortable(),
                TextColumn::make('propertyType.name')
                    ->label('Tipo'),
                TextColumn::make('operation.name')
                    ->label('Operación'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'available' => 'success',
                        'sold'      => 'danger',
                        'rented'    => 'warning',
                    }),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft'     => 'Borrador',
                        'available' => 'Disponible',
                        'sold'      => 'Vendido',
                        'rented'    => 'Alquilado',
                    ]),
                SelectFilter::make('sector_id')
                    ->label('Zona')
                    ->relationship('sector', 'name'),
                SelectFilter::make('operation_id')
                    ->label('Operación')
                    ->relationship('operation', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
