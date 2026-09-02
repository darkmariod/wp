<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaPorPagarResource\Pages;
use App\Models\CuentaPorPagar;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class CuentaPorPagarResource extends Resource
{
    protected static ?string $model = CuentaPorPagar::class;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): string
    {
        return 'Contabilidad';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-up-on-square';
    }

    public static function getNavigationLabel(): string
    {
        return 'Cuentas por Pagar';
    }

    public static function getModelLabel(): string
    {
        return 'Cuenta por Pagar';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cuentas por Pagar';
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
                        Forms\Components\Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'razon_social')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'factura' => 'Factura',
                                'nota_venta' => 'Nota de Venta',
                                'anticipo' => 'Anticipo',
                                'retencion' => 'Retención',
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
                        Forms\Components\TextInput::make('monto_pagado')
                            ->label('Monto Pagado')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required(),
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'parcial' => 'Parcial',
                                'pagado' => 'Pagado',
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
                Tables\Columns\TextColumn::make('proveedor.razon_social')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_pagado')
                    ->label('Monto Pagado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record): string => '$' . number_format((float) $record->monto_total - (float) $record->monto_pagado, 2))
                    ->color(fn ($record): string => ($record->monto_total - $record->monto_pagado) > 0 ? 'danger' : 'success'),
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
                        'pagado' => 'success',
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
                        'pagado' => 'Pagado',
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
                        $nuevoPagado = (float) $record->monto_pagado + (float) $data['monto_pago'];
                        $nuevoEstado = $nuevoPagado >= (float) $record->monto_total ? 'pagado' : 'parcial';

                        $record->update([
                            'monto_pagado' => $nuevoPagado,
                            'estado' => $nuevoEstado,
                        ]);

                        Notification::make()
                            ->title('Pago registrado')
                            ->body("Monto: $" . number_format((float) $data['monto_pago'], 2))
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => !in_array($record->estado, ['pagado', 'cancelado'])),
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
                Infolists\Components\TextEntry::make('proveedor.razon_social')
                    ->label('Proveedor'),
                Infolists\Components\TextEntry::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),
                Infolists\Components\TextEntry::make('monto_total')
                    ->label('Monto Total')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2)),
                Infolists\Components\TextEntry::make('monto_pagado')
                    ->label('Monto Pagado')
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
                        'pagado' => 'success',
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
            'index' => Pages\ListCuentaPorPagars::route('/'),
            'create' => Pages\CreateCuentaPorPagar::route('/create'),
            'view' => Pages\ViewCuentaPorPagar::route('/{record}'),
            'edit' => Pages\EditCuentaPorPagar::route('/{record}/edit'),
        ];
    }
}
