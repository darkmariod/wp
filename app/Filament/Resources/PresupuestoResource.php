<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresupuestoResource\Pages;
use App\Filament\Resources\PresupuestoResource\RelationManagers\DetalleAPURelationManager;
use App\Models\Presupuesto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PresupuestoResource extends Resource
{
    protected static ?string $model = Presupuesto::class;

    protected static ?string $navigationGroup = 'Obra';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('codigo')
                ->label('Código')
                ->required()
                ->maxLength(50),

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

            TextInput::make('precio_venta_unitario')
                ->label('Precio Venta Unitario')
                ->prefix('$')
                ->required()
                ->numeric(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('codigo')->label('Código'),
            TextEntry::make('obra.nombre')->label('Obra'),
            TextEntry::make('descripcion')->label('Descripción'),
            TextEntry::make('unidad_medida')->label('Unidad de Medida'),
            TextEntry::make('cantidad')->label('Cantidad'),
            TextEntry::make('costo_unitario')->label('Costo Unitario'),
            TextEntry::make('precio_venta_unitario')->label('Precio Venta Unitario'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad'),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                Tables\Columns\TextColumn::make('costo_unitario')
                    ->label('Costo Unitario')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('precio_venta_unitario')
                    ->label('Precio Venta')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
            ])
            ->defaultSort('codigo');
    }

    public static function getRelations(): array
    {
        return [
            DetalleAPURelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPresupuestos::route('/'),
            'create' => Pages\CreatePresupuesto::route('/create'),
            'view' => Pages\ViewPresupuesto::route('/{record}'),
            'edit' => Pages\EditPresupuesto::route('/{record}/edit'),
        ];
    }
}
