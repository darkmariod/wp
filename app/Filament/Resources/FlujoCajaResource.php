<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlujoCajaResource\Pages;
use App\Models\FlujoCaja;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FlujoCajaResource extends Resource
{
    protected static ?string $model = FlujoCaja::class;

    protected static ?string $navigationGroup = 'Contabilidad';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->searchable()
                ->preload()
                ->nullable(),

            DatePicker::make('fecha')
                ->label('Fecha')
                ->required(),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'ingreso' => 'Ingreso',
                    'egreso' => 'Egreso',
                ])
                ->required(),

            Select::make('categoria')
                ->label('Categoría')
                ->options([
                    'venta' => 'Venta',
                    'material' => 'Material',
                    'mano_obra' => 'Mano de Obra',
                    'subcontrato' => 'Subcontrato',
                    'equipo' => 'Equipo',
                    'otro' => 'Otro',
                ])
                ->required(),

            TextInput::make('monto')
                ->label('Monto')
                ->prefix('$')
                ->required()
                ->numeric(),

            Textarea::make('referencia')
                ->label('Referencia')
                ->rows(2)
                ->maxLength(255),

            Select::make('asiento_id')
                ->label('Asiento Contable')
                ->relationship('asiento', 'numero_asiento')
                ->searchable()
                ->preload()
                ->nullable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('fecha')->label('Fecha'),
            TextEntry::make('obra.nombre')->label('Obra'),
            TextEntry::make('tipo')->label('Tipo'),
            TextEntry::make('categoria')->label('Categoría'),
            TextEntry::make('monto')->label('Monto'),
            TextEntry::make('referencia')->label('Referencia'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoría')
                    ->badge(),

                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->limit(30),
            ])
            ->defaultSort('fecha', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlujoCajas::route('/'),
            'create' => Pages\CreateFlujoCaja::route('/create'),
            'view' => Pages\ViewFlujoCaja::route('/{record}'),
            'edit' => Pages\EditFlujoCaja::route('/{record}/edit'),
        ];
    }
}
