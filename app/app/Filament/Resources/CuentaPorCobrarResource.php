<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaPorCobrarResource\Pages;
use App\Models\CuentaPorCobrar;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class CuentaPorCobrarResource extends Resource
{
    protected static ?string $model = CuentaPorCobrar::class;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string
    {
        return 'Contabilidad';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-down-on-square';
    }

    public static function getNavigationLabel(): string
    {
        return 'Cuentas por Cobrar';
    }

    public static function getModelLabel(): string
    {
        return 'Cuenta por Cobrar';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cuentas por Cobrar';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información de la Cuenta')
                    ->schema([
                        Forms\Components\Select::make('obra_id')
                            ->label('Obra')
                            ->relationship('obra', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'razon_social')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'factura' => 'Factura',
                                'nota_venta' => 'Nota de Venta',
                                'anticipo' => 'Anticipo',
                                'otro' => 'Otro',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Comprobante')
                    ->schema([
                        Forms\Components\TextInput::make('numero_comprobante')
                            ->label('Número de Comprobante')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('fecha_emision')
                            ->label('Fecha de Emisión')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Montos')
                    ->schema([
                        Forms\Components\TextInput::make('monto_total')
                            ->label('Monto Total')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('monto_cobrado')
                            ->label('Monto Cobrado')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required(),
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'parcial' => 'Parcial',
                                'cobrado' => 'Cobrado',
                                'vencido' => 'Vencido',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pendiente'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_comprobante')
                    ->label('Comprobante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.razon_social')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_cobrado')
                    ->label('Monto Cobrado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record): string => '$' . number_format((float) $record->monto_total - (float) $record->monto_cobrado, 2))
                    ->color(fn ($record): string => ($record->monto_total - $record->monto_cobrado) > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'parcial' => 'warning',
                        'cobrado' => 'success',
                        'vencido' => 'danger',
                        'cancelado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
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
                        'parcial' => 'Parcial',
                        'cobrado' => 'Cobrado',
                        'vencido' => 'Vencido',
                        'cancelado' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('registrarPago')
                    ->label('Registrar Pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('monto_pago')
                            ->label('Monto del Pago')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $nuevoCobrado = (float) $record->monto_cobrado + (float) $data['monto_pago'];
                        $nuevoEstado = $nuevoCobrado >= (float) $record->monto_total ? 'cobrado' : 'parcial';

                        $record->update([
                            'monto_cobrado' => $nuevoCobrado,
                            'estado' => $nuevoEstado,
                        ]);

                        Notification::make()
                            ->title('Pago registrado')
                            ->body("Monto: $" . number_format((float) $data['monto_pago'], 2))
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => !in_array($record->estado, ['cobrado', 'cancelado'])),
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
                Infolists\Components\TextEntry::make('numero_comprobante')
                    ->label('Comprobante'),
                Infolists\Components\TextEntry::make('obra.nombre')
                    ->label('Obra'),
                Infolists\Components\TextEntry::make('cliente.razon_social')
                    ->label('Cliente'),
                Infolists\Components\TextEntry::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),
                Infolists\Components\TextEntry::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('monto_cobrado')
                    ->label('Monto Cobrado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('fecha_emision')
                    ->label('Fecha Emisión')
                    ->date(),
                Infolists\Components\TextEntry::make('fecha_vencimiento')
                    ->label('Fecha Vencimiento')
                    ->date(),
                Infolists\Components\TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'parcial' => 'warning',
                        'cobrado' => 'success',
                        'vencido' => 'danger',
                        'cancelado' => 'gray',
                        default => 'gray',
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentaPorCobrars::route('/'),
            'create' => Pages\CreateCuentaPorCobrar::route('/create'),
            'view' => Pages\ViewCuentaPorCobrar::route('/{record}'),
            'edit' => Pages\EditCuentaPorCobrar::route('/{record}/edit'),
        ];
    }
}
