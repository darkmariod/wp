<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('razon_social')
                ->label('Razón Social')
                ->required()
                ->maxLength(255),

            TextInput::make('ruc')
                ->label('RUC')
                ->required()
                ->maxLength(13),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'publico' => 'Público',
                    'privado' => 'Privado',
                ]),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255),

            TextInput::make('telefono')
                ->label('Teléfono')
                ->maxLength(20),

            TextInput::make('direccion')
                ->label('Dirección')
                ->maxLength(255),

            TextInput::make('representa_legal')
                ->label('Representante Legal')
                ->maxLength(255),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('razon_social')->label('Razón Social'),
            TextEntry::make('ruc')->label('RUC'),
            TextEntry::make('tipo')->label('Tipo'),
            TextEntry::make('email')->label('Email'),
            TextEntry::make('telefono')->label('Teléfono'),
            TextEntry::make('direccion')->label('Dirección'),
            TextEntry::make('representa_legal')->label('Representante Legal'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('razon_social')
                    ->label('Razón Social')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ruc')
                    ->label('RUC')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'publico' => 'info',
                        'privado' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono'),
            ])
            ->defaultSort('razon_social');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view' => Pages\ViewCliente::route('/{record}'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
