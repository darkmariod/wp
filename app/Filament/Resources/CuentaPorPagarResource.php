<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaPorPagarResource\Pages;
use App\Models\CuentaPorPagar;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CuentaPorPagarResource extends Resource
{
    protected static ?string $model = CuentaPorPagar::class;

    protected static ?string $navigationGroup = 'Contabilidad';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->searchable()
                ->preload()
                ->nullable(),

            Select::make('proveedor_id')
                ->label('Proveedor')
                ->relationship('proveedor', 'razon_social')
                ->required()
                ->searchable()
                ->preload(),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'factura' => 'Factura',
                    'nota_credito' => 'Nota de Crédito',
                    'anticipo' => 'Anticipo',
                ])
                ->required(),

            TextInput::make('numero_comprobante')
                ->label('Número de Comprobante')
                ->required()
                ->maxLength(50),

            DatePicker::make('fecha_emision')
                ->label('Fecha de Emisión')
                ->required(),

            DatePicker::make('fecha_vencimiento')
                ->label('Fecha de Vencimiento')
                ->required(),

            TextInput::make('monto_total')
                ->label('Monto Total')
                ->prefix('$')
                ->required()
                ->numeric(),

            TextInput::make('monto_pagado')
                ->label('Monto Pagado')
                ->prefix('$')
                ->default(0)
                ->numeric(),

            Select::make('estado')
                ->label('Estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'parcial' => 'Parcial',
                    'pagado' => 'Pagado',
                    'vencido' => 'Vencido',
                ])
                ->default('pendiente')
                ->required(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('numero_comprobante')->label('Número de Comprobante'),
            TextEntry::make('obra.nombre')->label('Obra'),
            TextEntry::make('proveedor.razon_social')->label('Proveedor'),
            TextEntry::make('monto_total')->label('Monto Total'),
            TextEntry::make('monto_pagado')->label('Monto Pagado'),
            TextEntry::make('estado')->label('Estado'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_comprobante')
                    ->label('Nro. Comprobante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->sortable(),

                Tables\Columns\TextColumn::make('proveedor.razon_social')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_pagado')
                    ->label('Monto Pagado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->state(fn ($record): float => (float) $record->monto_total - (float) $record->monto_pagado)
                    ->formatStateUsing(fn ($state): string => '$' . number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'parcial' => 'warning',
                        'pagado' => 'success',
                        'vencido' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('fecha_vencimiento');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentaPorPagars::route('/'),
            'create' => Pages\CreateCuentaPorPagar::route('/create'),
            'view' => Pages\ViewCuentaPorPagar::route('/{record}'),
            'edit' => Pages\EditCuentaPorPagar::route('/{record}/edit'),
        ];
    }
}
