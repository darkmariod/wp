<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrabajadorResource\Pages;
use App\Models\Trabajador;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TrabajadorResource extends Resource
{
    protected static ?string $model = Trabajador::class;

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('cedula')
                ->label('Cédula')
                ->required()
                ->maxLength(10),

            TextInput::make('nombres')
                ->label('Nombres')
                ->required()
                ->maxLength(255),

            TextInput::make('apellidos')
                ->label('Apellidos')
                ->required()
                ->maxLength(255),

            TextInput::make('cargo')
                ->label('Cargo')
                ->maxLength(255),

            TextInput::make('sueldo_base')
                ->label('Sueldo Base')
                ->prefix('$')
                ->numeric(),

            Select::make('tipo_contrato')
                ->label('Tipo de Contrato')
                ->options([
                    'indefinite' => 'Indefinido',
                    'temporary' => 'Temporal',
                    'obra' => 'Obra',
                ]),

            Toggle::make('activo')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('cedula')->label('Cédula'),
            TextEntry::make('nombres')->label('Nombres'),
            TextEntry::make('apellidos')->label('Apellidos'),
            TextEntry::make('cargo')->label('Cargo'),
            TextEntry::make('sueldo_base')->label('Sueldo Base'),
            TextEntry::make('activo')->label('Activo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula')
                    ->label('Cédula')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombres')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('apellidos')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sueldo_base')
                    ->label('Sueldo Base')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('activo')
                    ->label('Activo')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->defaultSort('apellidos');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrabajadors::route('/'),
            'create' => Pages\CreateTrabajador::route('/create'),
            'view' => Pages\ViewTrabajador::route('/{record}'),
            'edit' => Pages\EditTrabajador::route('/{record}/edit'),
        ];
    }
}
