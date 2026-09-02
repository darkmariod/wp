<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsistenciaObraResource\Pages;
use App\Models\AsistenciaObra;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AsistenciaObraResource extends Resource
{
    protected static ?string $model = AsistenciaObra::class;

    protected static ?string $navigationGroup = 'Obra';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->relationship('obra', 'nombre')
                ->required()
                ->searchable()
                ->preload(),

            Select::make('trabajador_id')
                ->label('Trabajador')
                ->relationship('trabajador', 'nombres')
                ->required()
                ->searchable()
                ->preload(),

            DatePicker::make('fecha')
                ->label('Fecha')
                ->required(),

            TextInput::make('horas_trabajadas')
                ->label('Horas Trabajadas')
                ->required()
                ->numeric(),

            TextInput::make('hora_entrada')
                ->label('Hora Entrada')
                ->time()
                ->maxLength(5),

            TextInput::make('hora_salida')
                ->label('Hora Salida')
                ->time()
                ->maxLength(5),

            Select::make('tipo_jornada')
                ->label('Tipo de Jornada')
                ->options([
                    'normal' => 'Normal',
                    'extras' => 'Extras',
                    'feriado' => 'Feriado',
                ])
                ->default('normal')
                ->required(),

            Textarea::make('observaciones')
                ->label('Observaciones')
                ->rows(2)
                ->maxLength(500),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('fecha')->label('Fecha'),
            TextEntry::make('obra.nombre')->label('Obra'),
            TextEntry::make('trabajador.nombres')->label('Trabajador'),
            TextEntry::make('horas_trabajadas')->label('Horas Trabajadas'),
            TextEntry::make('tipo_jornada')->label('Tipo de Jornada'),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('trabajador.nombres')
                    ->label('Trabajador')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('horas_trabajadas')
                    ->label('Horas Trabajadas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo_jornada')
                    ->label('Tipo Jornada')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'normal' => 'gray',
                        'extras' => 'warning',
                        'feriado' => 'info',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('fecha', 'desc');
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
