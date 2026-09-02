<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Proveedor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 2;

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
                    'material' => 'Material',
                    'equipo' => 'Equipo',
                    'servicio' => 'Servicio',
                    'subcontrato' => 'Subcontrato',
                ])
                ->required(),

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
                        'material' => 'info',
                        'equipo' => 'warning',
                        'servicio' => 'success',
                        'subcontrato' => 'danger',
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
            'index' => Pages\ListProveedors::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'view' => Pages\ViewProveedor::route('/{record}'),
            'edit' => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }
}
