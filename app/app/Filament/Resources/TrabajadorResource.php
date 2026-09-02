<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrabajadorResource\Pages;
use App\Models\Trabajador;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class TrabajadorResource extends Resource
{
    protected static ?string $model = Trabajador::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string
    {
        return 'Administración';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return 'Trabajadores';
    }

    public static function getModelLabel(): string
    {
        return 'Trabajador';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Trabajadores';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('cedula')
                            ->label('Cédula')
                            ->required()
                            ->maxLength(10)
                            ->minLength(10)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nombres')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Información Laboral')
                    ->schema([
                        Forms\Components\TextInput::make('cargo')
                            ->label('Cargo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sueldo_base')
                            ->label('Sueldo Base')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('tipo_contrato')
                            ->label('Tipo de Contrato')
                            ->options([
                                'indefinido' => 'Indefinido',
                                'termino_fijo' => 'Término Fijo',
                                'obra_determinada' => 'Obra Determinada',
                                'medio_tiempo' => 'Medio Tiempo',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('activo')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
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
                    ->label('Estado')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo'),
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
                Infolists\Components\TextEntry::make('cedula')
                    ->label('Cédula'),
                Infolists\Components\TextEntry::make('nombres')
                    ->label('Nombres'),
                Infolists\Components\TextEntry::make('apellidos')
                    ->label('Apellidos'),
                Infolists\Components\TextEntry::make('cargo')
                    ->label('Cargo'),
                Infolists\Components\TextEntry::make('sueldo_base')
                    ->label('Sueldo Base')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('tipo_contrato')
                    ->label('Tipo de Contrato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'indefinido' => 'success',
                        'termino_fijo' => 'info',
                        'obra_determinada' => 'warning',
                        'medio_tiempo' => 'gray',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('activo')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
