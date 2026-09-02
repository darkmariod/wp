<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObraResource\Pages;
use App\Models\Obra;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ObraResource extends Resource
{
    protected static ?string $model = Obra::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return 'Obra';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map';
    }

    public static function getNavigationLabel(): string
    {
        return 'Obras';
    }

    public static function getModelLabel(): string
    {
        return 'Obra';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Obras';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'razon_social')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('direccion')
                            ->label('Dirección')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Fechas')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->label('Fecha Inicio')
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_fin_estimada')
                            ->label('Fecha Fin Estimada')
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_fin_real')
                            ->label('Fecha Fin Real')
                            ->nullable(),
                    ])->columns(3),

                Forms\Components\Section::make('Estado y Montos')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'planificada' => 'Planificación',
                                'en_curse' => 'En Curso',
                                'suspendida' => 'Suspendida',
                                'culminada' => 'Culminada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->required()
                            ->default('planificada'),
                        Forms\Components\TextInput::make('contrato_monto')
                            ->label('Monto del Contrato')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('anticipo_porcentaje')
                            ->label('Porcentaje de Anticipo (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('AIU (Administración, Imprevistos, Utilidad)')
                    ->schema([
                        Forms\Components\TextInput::make('aiu_administracion')
                            ->label('Administración (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),
                        Forms\Components\TextInput::make('aiu_imprevistos')
                            ->label('Imprevistos (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),
                        Forms\Components\TextInput::make('aiu_utilidad')
                            ->label('Utilidad (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),
                        Forms\Components\TextInput::make('costo_fijo_mensual')
                            ->label('Costo Fijo Mensual')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                    ])->columns(4),
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
                Tables\Columns\TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planificada' => 'gray',
                        'en_curse' => 'info',
                        'suspendida' => 'warning',
                        'culminada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planificada' => 'Planificación',
                        'en_curse' => 'En Curso',
                        'suspendida' => 'Suspendida',
                        'culminada' => 'Culminada',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('contrato_monto')
                    ->label('Monto Contrato')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
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
                        'planificada' => 'Planificación',
                        'en_curse' => 'En Curso',
                        'suspendida' => 'Suspendida',
                        'culminada' => 'Culminada',
                        'cancelada' => 'Cancelada',
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
                Infolists\Components\TextEntry::make('codigo')
                    ->label('Código'),
                Infolists\Components\TextEntry::make('nombre')
                    ->label('Nombre'),
                Infolists\Components\TextEntry::make('cliente.razon_social')
                    ->label('Cliente'),
                Infolists\Components\TextEntry::make('direccion')
                    ->label('Dirección'),
                Infolists\Components\TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planificada' => 'gray',
                        'en_curse' => 'info',
                        'suspendida' => 'warning',
                        'culminada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('contrato_monto')
                    ->label('Monto Contrato')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date(),
                Infolists\Components\TextEntry::make('fecha_fin_estimada')
                    ->label('Fecha Fin Estimada')
                    ->date(),
                Infolists\Components\TextEntry::make('fecha_fin_real')
                    ->label('Fecha Fin Real')
                    ->date(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObras::route('/'),
            'create' => Pages\CreateObra::route('/create'),
            'view' => Pages\ViewObra::route('/{record}'),
            'edit' => Pages\EditObra::route('/{record}/edit'),
        ];
    }
}
