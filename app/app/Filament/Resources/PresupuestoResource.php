<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresupuestoResource\Pages;
use App\Filament\Resources\PresupuestoResource\RelationManagers;
use App\Models\Presupuesto;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PresupuestoResource extends Resource
{
    protected static ?string $model = Presupuesto::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return 'Obra';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Presupuestos';
    }

    public static function getModelLabel(): string
    {
        return 'Presupuesto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Presupuestos';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información del Presupuesto')
                    ->schema([
                        Forms\Components\Select::make('obra_id')
                            ->label('Obra')
                            ->relationship('obra', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Cantidades y Costos')
                    ->schema([
                        Forms\Components\TextInput::make('unidad_medida')
                            ->label('Unidad de Medida')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('costo_unitario')
                            ->label('Costo Unitario')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('precio_venta_unitario')
                            ->label('Precio Venta Unitario')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])->columns(4),

                Forms\Components\Section::make('Subtotales')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal_costo')
                            ->label('Subtotal Costo')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraAttributes(['class' => 'font-bold']),
                        Forms\Components\TextInput::make('subtotal_venta')
                            ->label('Subtotal Venta')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraAttributes(['class' => 'font-bold']),
                    ])->columns(2),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad'),
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('costo_unitario')
                    ->label('Costo Unitario')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_venta')
                    ->label('Subtotal Venta')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Infolists\Components\TextEntry::make('codigo')
                    ->label('Código'),
                Infolists\Components\TextEntry::make('obra.nombre')
                    ->label('Obra'),
                Infolists\Components\TextEntry::make('descripcion')
                    ->label('Descripción'),
                Infolists\Components\TextEntry::make('unidad_medida')
                    ->label('Unidad de Medida'),
                Infolists\Components\TextEntry::make('cantidad')
                    ->label('Cantidad'),
                Infolists\Components\TextEntry::make('costo_unitario')
                    ->label('Costo Unitario')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('precio_venta_unitario')
                    ->label('Precio Venta Unitario')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('subtotal_costo')
                    ->label('Subtotal Costo')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('subtotal_venta')
                    ->label('Subtotal Venta')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DetalleAPURelationManager::class,
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
