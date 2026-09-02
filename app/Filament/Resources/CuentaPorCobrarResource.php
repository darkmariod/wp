<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaPorCobrarResource\Pages;
use App\Models\CuentaPorCobrar;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CuentaPorCobrarResource extends Resource
{
    protected static ?string $model = CuentaPorCobrar::class;

    protected static ?string $navigationGroup = 'Contabilidad';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->searchable()
                ->preload()
                ->nullable(),

            Select::make('cliente_id')
                ->label('Cliente')
                ->relationship('cliente', 'razon_social')
                ->required()
                ->searchable()
                ->preload(),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'factura' => 'Factura',
                    'nota_venta' => 'Nota de Venta',
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

            TextInput::make('monto_cobrado')
                ->label('Monto Cobrado')
                ->prefix('$')
                ->default(0)
                ->numeric(),

            Select::make('estado')
                ->label('Estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'parcial' => 'Parcial',
                    'cobrado' => 'Cobrado',
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
            TextEntry::make('cliente.razon_social')->label('Cliente'),
            TextEntry::make('monto_total')->label('Monto Total'),
            TextEntry::make('monto_cobrado')->label('Monto Cobrado'),
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

                Tables\Columns\TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_cobrado')
                    ->label('Monto Cobrado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->state(fn ($record): float => (float) $record->monto_total - (float) $record->monto_cobrado)
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
                        'cobrado' => 'success',
                        'vencido' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('fecha_vencimiento');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentaPorCobrars::route('/'),
            'create' => Pages\CreateCuentaPorCobrar::route('/create'),
            'view' => Pages\ViewCuentaPorCobrar::route('/{record}'),
            'edit' => Pages\EditCuentaPorCobrar::route('/{record}/edit'),
        ];
    }
}
