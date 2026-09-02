<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsientoContableResource\Pages;
use App\Filament\Resources\AsientoContableResource\RelationManagers\DetalleAsientoRelationManager;
use App\Models\AsientoContable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AsientoContableResource extends Resource
{
    protected static ?string $model = AsientoContable::class;

    protected static ?string $navigationGroup = 'Contabilidad';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('numero_asiento')
                ->label('Número de Asiento')
                ->required()
                ->maxLength(50),

            DatePicker::make('fecha')
                ->label('Fecha')
                ->required(),

            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(3)
                ->maxLength(500),

            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->searchable()
                ->preload()
                ->nullable(),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'apertura' => 'Apertura',
                    'ingreso' => 'Ingreso',
                    'egreso' => 'Egreso',
                    'ajuste' => 'Ajuste',
                    'cierre' => 'Cierre',
                ])
                ->required(),

            Select::make('estado')
                ->label('Estado')
                ->options([
                    'borrador' => 'Borrador',
                    'aprobado' => 'Aprobado',
                    'anulado' => 'Anulado',
                ])
                ->default('borrador')
                ->required(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('numero_asiento')->label('Número de Asiento'),
            TextEntry::make('fecha')->label('Fecha'),
            TextEntry::make('descripcion')->label('Descripción'),
            TextEntry::make('obra.nombre')->label('Obra'),
            TextEntry::make('tipo')->label('Tipo'),
            TextEntry::make('estado')->label('Estado'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_asiento')
                    ->label('Número de Asiento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),

                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'apertura' => 'info',
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        'ajuste' => 'warning',
                        'cierre' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'aprobado' => 'success',
                        'anulado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('fecha', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DetalleAsientoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsientoContables::route('/'),
            'create' => Pages\CreateAsientoContable::route('/create'),
            'view' => Pages\ViewAsientoContable::route('/{record}'),
            'edit' => Pages\EditAsientoContable::route('/{record}/edit'),
        ];
    }
}
