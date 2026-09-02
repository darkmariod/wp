<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanCuentaResource\Pages;
use App\Models\PlanCuenta;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PlanCuentaResource extends Resource
{
    protected static ?string $model = PlanCuenta::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return 'Contabilidad';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calculator';
    }

    public static function getNavigationLabel(): string
    {
        return 'Plan de Cuentas';
    }

    public static function getModelLabel(): string
    {
        return 'Cuenta';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Plan de Cuentas';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información de la Cuenta')
                    ->schema([
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('grupo')
                            ->label('Grupo')
                            ->options([
                                'activo' => 'Activo',
                                'pasivo' => 'Pasivo',
                                'patrimonio' => 'Patrimonio',
                                'ingreso' => 'Ingreso',
                                'gasto' => 'Gasto',
                                'resultado' => 'Resultado',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'deudor' => 'Deudor',
                                'acreedor' => 'Acreedor',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('es_auxiliar')
                            ->label('Es Auxiliar')
                            ->default(false),
                        Forms\Components\Select::make('padre_id')
                            ->label('Cuenta Padre')
                            ->relationship('padre', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo} - {$record->nombre}"),
                        Forms\Components\Toggle::make('activa')
                            ->label('Activa')
                            ->default(true),
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
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grupo')
                    ->label('Grupo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'info',
                        'pasivo' => 'warning',
                        'patrimonio' => 'success',
                        'ingreso' => 'info',
                        'gasto' => 'danger',
                        'resultado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\IconColumn::make('es_auxiliar')
                    ->label('Auxiliar')
                    ->boolean(),
                Tables\Columns\IconColumn::make('activa')
                    ->label('Activa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grupo')
                    ->label('Grupo')
                    ->options([
                        'activo' => 'Activo',
                        'pasivo' => 'Pasivo',
                        'patrimonio' => 'Patrimonio',
                        'ingreso' => 'Ingreso',
                        'gasto' => 'Gasto',
                        'resultado' => 'Resultado',
                    ]),
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'deudor' => 'Deudor',
                        'acreedor' => 'Acreedor',
                    ]),
                Tables\Filters\TernaryFilter::make('es_auxiliar')
                    ->label('Es Auxiliar'),
                Tables\Filters\TernaryFilter::make('activa')
                    ->label('Activa'),
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
                Infolists\Components\TextEntry::make('nombre')
                    ->label('Nombre'),
                Infolists\Components\TextEntry::make('grupo')
                    ->label('Grupo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'info',
                        'pasivo' => 'warning',
                        'patrimonio' => 'success',
                        'ingreso' => 'info',
                        'gasto' => 'danger',
                        'resultado' => 'gray',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Infolists\Components\IconEntry::make('es_auxiliar')
                    ->label('Es Auxiliar')
                    ->boolean(),
                Infolists\Components\TextEntry::make('padre.codigo')
                    ->label('Cuenta Padre')
                    ->formatStateUsing(fn ($state, $record): ?string => $record->padre ? "{$record->padre->codigo} - {$record->padre->nombre}" : null),
                Infolists\Components\IconEntry::make('activa')
                    ->label('Activa')
                    ->boolean(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanCuentas::route('/'),
            'create' => Pages\CreatePlanCuenta::route('/create'),
            'view' => Pages\ViewPlanCuenta::route('/{record}'),
            'edit' => Pages\EditPlanCuenta::route('/{record}/edit'),
        ];
    }
}
