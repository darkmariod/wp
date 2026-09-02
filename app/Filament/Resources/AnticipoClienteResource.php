<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnticipoClienteResource\Pages;
use App\Filament\Resources\AnticipoClienteResource\RelationManagers\AmortizacionAnticipoRelationManager;
use App\Models\AnticipoCliente;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AnticipoClienteResource extends Resource
{
    protected static ?string $model = AnticipoCliente::class;

    protected static ?string $navigationGroup = 'Contabilidad';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('monto_total')
                ->label('Monto Total')
                ->prefix('$')
                ->required()
                ->numeric(),

            TextInput::make('porcentaje')
                ->label('Porcentaje')
                ->suffix('%')
                ->required()
                ->numeric(),

            Select::make('estado')
                ->label('Estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'aprobado' => 'Aprobado',
                    'amortizado' => 'Amortizado',
                    'cancelado' => 'Cancelado',
                ])
                ->default('pendiente')
                ->required(),

            DatePicker::make('fecha_concesion')
                ->label('Fecha de Concesión')
                ->required(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('obra.nombre')->label('Obra'),
            TextEntry::make('monto_total')->label('Monto Total'),
            TextEntry::make('porcentaje')->label('Porcentaje'),
            TextEntry::make('estado')->label('Estado'),
            TextEntry::make('fecha_concesion')->label('Fecha de Concesión'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('porcentaje')
                    ->label('Porcentaje')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'aprobado' => 'success',
                        'amortizado' => 'info',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('fecha_concesion')
                    ->label('Fecha Concesión')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('fecha_concesion', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            AmortizacionAnticipoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnticipoClientes::route('/'),
            'create' => Pages\CreateAnticipoCliente::route('/create'),
            'view' => Pages\ViewAnticipoCliente::route('/{record}'),
            'edit' => Pages\EditAnticipoCliente::route('/{record}/edit'),
        ];
    }
}
