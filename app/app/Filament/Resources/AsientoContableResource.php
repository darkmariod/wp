<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsientoContableResource\Pages;
use App\Filament\Resources\AsientoContableResource\RelationManagers;
use App\Models\AsientoContable;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AsientoContableResource extends Resource
{
    protected static ?string $model = AsientoContable::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return 'Contabilidad';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-duplicate';
    }

    public static function getNavigationLabel(): string
    {
        return 'Asientos Contables';
    }

    public static function getModelLabel(): string
    {
        return 'Asiento Contable';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Asientos Contables';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información del Asiento')
                    ->schema([
                        Forms\Components\TextInput::make('numero_asiento')
                            ->label('Número de Asiento')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('obra_id')
                            ->label('Obra')
                            ->relationship('obra', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'diario' => 'Diario',
                                'ingreso' => 'Ingreso',
                                'egreso' => 'Egreso',
                                'ajuste' => 'Ajuste',
                                'cierre' => 'Cierre',
                            ])
                            ->required(),
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'borrador' => 'Borrador',
                                'aprobado' => 'Aprobado',
                                'anulado' => 'Anulado',
                            ])
                            ->required()
                            ->default('borrador')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Auditoría')
                    ->schema([
                        Forms\Components\TextInput::make('usuario_creacion')
                            ->label('Creado por')
                            ->default(fn () => Auth::id())
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_asiento')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diario' => 'gray',
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        'ajuste' => 'warning',
                        'cierre' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'aprobado' => 'success',
                        'anulado' => 'danger',
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
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'diario' => 'Diario',
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                        'ajuste' => 'Ajuste',
                        'cierre' => 'Cierre',
                    ]),
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'aprobado' => 'Aprobado',
                        'anulado' => 'Anulado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Asiento')
                    ->modalDescription('¿Está seguro que desea aprobar este asiento contable?')
                    ->modalSubmitActionLabel('Sí, aprobar')
                    ->visible(fn ($record) => $record->estado === 'borrador')
                    ->action(function ($record) {
                        $record->update([
                            'estado' => 'aprobado',
                            'usuario_aprobacion' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Asiento aprobado')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->estado === 'borrador'),
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
                Infolists\Components\TextEntry::make('numero_asiento')
                    ->label('Número de Asiento'),
                Infolists\Components\TextEntry::make('fecha')
                    ->label('Fecha')
                    ->date(),
                Infolists\Components\TextEntry::make('descripcion')
                    ->label('Descripción'),
                Infolists\Components\TextEntry::make('obra.nombre')
                    ->label('Obra'),
                Infolists\Components\TextEntry::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diario' => 'gray',
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        'ajuste' => 'warning',
                        'cierre' => 'info',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'aprobado' => 'success',
                        'anulado' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('usuarioCreacion.name')
                    ->label('Creado por'),
                Infolists\Components\TextEntry::make('usuarioAprobacion.name')
                    ->label('Aprobado por'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DetalleAsientoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsientoContables::route('/'),
            'create' => Pages\CreateAsientoContable::route('/create'),
            'view' => Pages\ViewAsientoContable::route('/{record}'),
            'edit' => Pages\EditAsientoContable::route('/{record}/edit'),
        ];
    }
}
