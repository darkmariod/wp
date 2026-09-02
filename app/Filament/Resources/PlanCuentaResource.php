<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanCuentaResource\Pages;
use App\Models\PlanCuenta;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PlanCuentaResource extends Resource
{
    protected static ?string $model = PlanCuenta::class;

    protected static ?string $navigationGroup = 'Contabilidad';

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('codigo')
                ->label('Código')
                ->required()
                ->maxLength(20),

            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            Select::make('grupo')
                ->label('Grupo')
                ->options([
                    'activo' => 'Activo',
                    'pasivo' => 'Pasivo',
                    'patrimonio' => 'Patrimonio',
                    'ingreso' => 'Ingreso',
                    'gasto' => 'Gasto',
                    'costo' => 'Costo',
                ])
                ->required(),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'deudor' => 'Deudor',
                    'acreedor' => 'Acreedor',
                ])
                ->required(),

            Toggle::make('es_auxiliar')
                ->label('Es Auxiliar')
                ->default(false),

            Select::make('padre_id')
                ->label('Cuenta Padre')
                ->relationship('padre', 'codigo')
                ->searchable()
                ->preload()
                ->nullable(),

            Toggle::make('activa')
                ->label('Activa')
                ->default(true),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('codigo')->label('Código'),
            TextEntry::make('nombre')->label('Nombre'),
            TextEntry::make('grupo')->label('Grupo'),
            TextEntry::make('tipo')->label('Tipo'),
            TextEntry::make('es_auxiliar')->label('Es Auxiliar'),
            TextEntry::make('activa')->label('Activa'),
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
                        'activo' => 'success',
                        'pasivo' => 'danger',
                        'patrimonio' => 'info',
                        'ingreso' => 'warning',
                        'gasto' => 'gray',
                        'costo' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo'),

                Tables\Columns\TextColumn::make('es_auxiliar')
                    ->label('Es Auxiliar')
                    ->boolean(),

                Tables\Columns\TextColumn::make('activa')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('codigo');
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
