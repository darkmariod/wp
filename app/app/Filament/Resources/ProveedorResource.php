<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return 'Administración';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-truck';
    }

    public static function getNavigationLabel(): string
    {
        return 'Proveedores';
    }

    public static function getModelLabel(): string
    {
        return 'Proveedor';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Proveedores';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información del Proveedor')
                    ->schema([
                        Forms\Components\TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ruc')
                            ->label('RUC')
                            ->required()
                            ->maxLength(13)
                            ->minLength(13),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'material' => 'Material',
                                'servicio' => 'Servicio',
                                'subcontrato' => 'Subcontrato',
                                'equipo' => 'Equipo',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Contacto')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('direccion')
                            ->label('Dirección')
                            ->maxLength(255),
                    ])->columns(3),
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
                        'servicio' => 'success',
                        'subcontrato' => 'warning',
                        'equipo' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'material' => 'Material',
                        'servicio' => 'Servicio',
                        'subcontrato' => 'Subcontrato',
                        'equipo' => 'Equipo',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono'),
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
                        'material' => 'Material',
                        'servicio' => 'Servicio',
                        'subcontrato' => 'Subcontrato',
                        'equipo' => 'Equipo',
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
                Infolists\Components\TextEntry::make('razon_social')
                    ->label('Razón Social'),
                Infolists\Components\TextEntry::make('ruc')
                    ->label('RUC'),
                Infolists\Components\TextEntry::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'material' => 'info',
                        'servicio' => 'success',
                        'subcontrato' => 'warning',
                        'equipo' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('email')
                    ->label('Email'),
                Infolists\Components\TextEntry::make('telefono')
                    ->label('Teléfono'),
                Infolists\Components\TextEntry::make('direccion')
                    ->label('Dirección'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
