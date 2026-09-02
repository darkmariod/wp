<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlujoCajaResource\Pages;
use App\Models\FlujoCaja;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class FlujoCajaResource extends Resource
{
    protected static ?string $model = FlujoCaja::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string
    {
        return 'Contabilidad';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-banknotes';
    }

    public static function getNavigationLabel(): string
    {
        return 'Flujo de Caja';
    }

    public static function getModelLabel(): string
    {
        return 'Flujo de Caja';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Flujo de Caja';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información del Flujo')
                    ->schema([
                        Forms\Components\Select::make('obra_id')
                            ->label('Obra')
                            ->relationship('obra', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'ingreso' => 'Ingreso',
                                'egreso' => 'Egreso',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Detalle')
                    ->schema([
                        Forms\Components\Select::make('categoria')
                            ->label('Categoría')
                            ->options([
                                'materiales' => 'Materiales',
                                'mano_obra' => 'Mano de Obra',
                                'subcontrato' => 'Subcontrato',
                                'equipos' => 'Equipos',
                                'administrativo' => 'Administrativo',
                                'impuestos' => 'Impuestos',
                                'otros' => 'Otros',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('monto')
                            ->label('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('referencia')
                            ->label('Referencia')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(3),

                Forms\Components\Section::make('Asociación Contable')
                    ->schema([
                        Forms\Components\Select::make('asiento_id')
                            ->label('Asiento Contable')
                            ->relationship('asiento', 'numero_asiento')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->numero_asiento} - {$record->descripcion}"),
                    ]),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoría')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'materiales' => 'Materiales',
                        'mano_obra' => 'Mano de Obra',
                        'subcontrato' => 'Subcontrato',
                        'equipos' => 'Equipos',
                        'administrativo' => 'Administrativo',
                        'impuestos' => 'Impuestos',
                        'otros' => 'Otros',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                    ]),
                Tables\Filters\SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'materiales' => 'Materiales',
                        'mano_obra' => 'Mano de Obra',
                        'subcontrato' => 'Subcontrato',
                        'equipos' => 'Equipos',
                        'administrativo' => 'Administrativo',
                        'impuestos' => 'Impuestos',
                        'otros' => 'Otros',
                    ]),
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
                Infolists\Components\TextEntry::make('fecha')
                    ->label('Fecha')
                    ->date(),
                Infolists\Components\TextEntry::make('obra.nombre')
                    ->label('Obra'),
                Infolists\Components\TextEntry::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('categoria')
                    ->label('Categoría'),
                Infolists\Components\TextEntry::make('monto')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('referencia')
                    ->label('Referencia'),
                Infolists\Components\TextEntry::make('asiento.numero_asiento')
                    ->label('Asiento Contable'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
