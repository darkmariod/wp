<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObraResource\Pages;
use App\Models\Obra;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ObraResource extends Resource
{
    protected static ?string $model = Obra::class;

    protected static ?string $navigationGroup = 'Obra';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Información General')
                ->schema([
                    TextInput::make('codigo')
                        ->label('Código')
                        ->required()
                        ->maxLength(50),

                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),

                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->relationship('cliente', 'razon_social')
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Fechas')
                ->schema([
                    DatePicker::make('fecha_inicio')
                        ->label('Fecha Inicio')
                        ->required(),

                    DatePicker::make('fecha_fin_estimada')
                        ->label('Fecha Fin Estimada')
                        ->required(),

                    DatePicker::make('fecha_fin_real')
                        ->label('Fecha Fin Real')
                        ->nullable(),
                ])
                ->columns(3),

            Section::make('Estado')
                ->schema([
                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'planificacion' => 'Planificación',
                            'en_curso' => 'En Curso',
                            'paralizada' => 'Paralizada',
                            'finalizada' => 'Finalizada',
                        ])
                        ->required(),
                ]),

            Section::make('Financiero')
                ->schema([
                    TextInput::make('contrato_monto')
                        ->label('Monto Contrato')
                        ->prefix('$')
                        ->numeric(),

                    TextInput::make('anticipo_porcentaje')
                        ->label('Anticipo (%)')
                        ->suffix('%')
                        ->numeric(),

                    TextInput::make('aiu_administracion')
                        ->label('AIU Administración (%)')
                        ->suffix('%')
                        ->numeric(),

                    TextInput::make('aiu_imprevistos')
                        ->label('AIU Imprevistos (%)')
                        ->suffix('%')
                        ->numeric(),

                    TextInput::make('aiu_utilidad')
                        ->label('AIU Utilidad (%)')
                        ->suffix('%')
                        ->numeric(),

                    TextInput::make('costo_fijo_mensual')
                        ->label('Costo Fijo Mensual')
                        ->prefix('$')
                        ->numeric(),
                ])
                ->columns(3),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('codigo')->label('Código'),
            TextEntry::make('nombre')->label('Nombre'),
            TextEntry::make('cliente.razon_social')->label('Cliente'),
            TextEntry::make('estado')->label('Estado'),
            TextEntry::make('contrato_monto')->label('Monto Contrato'),
            TextEntry::make('fecha_inicio')->label('Fecha Inicio'),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planificacion' => 'gray',
                        'en_curso' => 'success',
                        'paralizada' => 'danger',
                        'finalizada' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('contrato_monto')
                    ->label('Monto Contrato')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('codigo');
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
