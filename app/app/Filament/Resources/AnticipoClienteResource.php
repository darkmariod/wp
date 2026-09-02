<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnticipoClienteResource\Pages;
use App\Filament\Resources\AnticipoClienteResource\RelationManagers;
use App\Models\AnticipoCliente;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AnticipoClienteResource extends Resource
{
    protected static ?string $model = AnticipoCliente::class;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string
    {
        return 'Contabilidad';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-trending-up';
    }

    public static function getNavigationLabel(): string
    {
        return 'Anticipos de Clientes';
    }

    public static function getModelLabel(): string
    {
        return 'Anticipo de Cliente';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Anticipos de Clientes';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información del Anticipo')
                    ->schema([
                        Forms\Components\Select::make('obra_id')
                            ->label('Obra')
                            ->relationship('obra', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('monto_total')
                            ->label('Monto Total')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('porcentaje')
                            ->label('Porcentaje (%)')
                            ->numeric()
                            ->suffix('%')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'aprobado' => 'Aprobado',
                                'amortizado' => 'Amortizado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pendiente'),
                        Forms\Components\DatePicker::make('fecha_concesion')
                            ->label('Fecha de Concesión')
                            ->required()
                            ->default(now()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->searchable()
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
                        'aprobado' => 'info',
                        'amortizado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('fecha_concesion')
                    ->label('Fecha Concesión')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'amortizado' => 'Amortizado',
                        'cancelado' => 'Cancelado',
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
                Infolists\Components\TextEntry::make('obra.nombre')
                    ->label('Obra'),
                Infolists\Components\TextEntry::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('porcentaje')
                    ->label('Porcentaje')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%'),
                Infolists\Components\TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'aprobado' => 'info',
                        'amortizado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('fecha_concesion')
                    ->label('Fecha de Concesión')
                    ->date(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AmortizacionAnticipoRelationManager::class,
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
