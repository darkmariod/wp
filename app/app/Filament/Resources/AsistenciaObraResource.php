<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsistenciaObraResource\Pages;
use App\Models\AsistenciaObra;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AsistenciaObraResource extends Resource
{
    protected static ?string $model = AsistenciaObra::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string
    {
        return 'Obra';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationLabel(): string
    {
        return 'Asistencia a Obras';
    }

    public static function getModelLabel(): string
    {
        return 'Asistencia';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Asistencia a Obras';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Información de Asistencia')
                    ->schema([
                        Forms\Components\Select::make('obra_id')
                            ->label('Obra')
                            ->relationship('obra', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('trabajador_id')
                            ->label('Trabajador')
                            ->relationship('trabajador', 'nombres')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nombres} {$record->apellidos}"),
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha')
                            ->required()
                            ->default(now()),
                    ])->columns(3),

                Forms\Components\Section::make('Horario')
                    ->schema([
                        Forms\Components\TimePicker::make('hora_entrada')
                            ->label('Hora Entrada')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('hora_salida')
                            ->label('Hora Salida')
                            ->seconds(false),
                        Forms\Components\TextInput::make('horas_trabajadas')
                            ->label('Horas Trabajadas')
                            ->numeric()
                            ->suffix('hrs')
                            ->required(),
                        Forms\Components\Select::make('tipo_jornada')
                            ->label('Tipo de Jornada')
                            ->options([
                                'normal' => 'Normal',
                                'extra' => 'Extra',
                                'dominical' => 'Dominical',
                                'festiva' => 'Festiva',
                                'nocturna' => 'Nocturna',
                            ])
                            ->required()
                            ->default('normal'),
                    ])->columns(4),

                Forms\Components\Section::make('Observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('obra.nombre')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trabajador.nombres')
                    ->label('Trabajador')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record): string => "{$record->trabajador->nombres} {$record->trabajador->apellidos}"),
                Tables\Columns\TextColumn::make('horas_trabajadas')
                    ->label('Horas')
                    ->sortable()
                    ->suffix(' hrs'),
                Tables\Columns\TextColumn::make('tipo_jornada')
                    ->label('Jornada')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'normal' => 'gray',
                        'extra' => 'warning',
                        'dominical' => 'info',
                        'festiva' => 'success',
                        'nocturna' => 'danger',
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
                Tables\Filters\SelectFilter::make('tipo_jornada')
                    ->label('Jornada')
                    ->options([
                        'normal' => 'Normal',
                        'extra' => 'Extra',
                        'dominical' => 'Dominical',
                        'festiva' => 'Festiva',
                        'nocturna' => 'Nocturna',
                    ]),
                Tables\Filters\Filter::make('fecha')
                    ->label('Hoy')
                    ->query(fn ($query) => $query->whereDate('fecha', now()))
                    ->toggle(),
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
                Infolists\Components\TextEntry::make('trabajador.nombres')
                    ->label('Trabajador')
                    ->formatStateUsing(fn ($state, $record): string => "{$record->trabajador->nombres} {$record->trabajador->apellidos}"),
                Infolists\Components\TextEntry::make('fecha')
                    ->label('Fecha')
                    ->date(),
                Infolists\Components\TextEntry::make('hora_entrada')
                    ->label('Hora Entrada'),
                Infolists\Components\TextEntry::make('hora_salida')
                    ->label('Hora Salida'),
                Infolists\Components\TextEntry::make('horas_trabajadas')
                    ->label('Horas Trabajadas')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' hrs'),
                Infolists\Components\TextEntry::make('tipo_jornada')
                    ->label('Tipo de Jornada')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'normal' => 'gray',
                        'extra' => 'warning',
                        'dominical' => 'info',
                        'festiva' => 'success',
                        'nocturna' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('observaciones')
                    ->label('Observaciones'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsistenciaObras::route('/'),
            'create' => Pages\CreateAsistenciaObra::route('/create'),
            'view' => Pages\ViewAsistenciaObra::route('/{record}'),
            'edit' => Pages\EditAsistenciaObra::route('/{record}/edit'),
        ];
    }
}
