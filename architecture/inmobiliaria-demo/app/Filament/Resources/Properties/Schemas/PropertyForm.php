<?php

namespace App\Filament\Resources\Properties\Schemas;

use App\Models\City;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Código')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Set $set) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')
                    ->label('Identificador URL')
                    ->required()
                    ->unique(ignoreRecord: true),
                RichEditor::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('address')
                    ->label('Dirección')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('bedrooms')
                    ->label('Dormitorios')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('bathrooms')
                    ->label('Baños')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('area_m2')
                    ->label('Área (m²)')
                    ->required()
                    ->numeric()
                    ->suffix('m²'),
                TextInput::make('parking_spaces')
                    ->label('Parqueaderos')
                    ->numeric()
                    ->default(0),
                Select::make('city_id')
                    ->label('Ciudad')
                    ->options(City::pluck('name', 'id'))
                    ->placeholder('Seleccionar ciudad')
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('sector_id', null))
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->sector) {
                            $component->state($record->sector->city_id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->dehydrated(false),
                Select::make('sector_id')
                    ->label('Zona / Barrio')
                    ->relationship('sector', 'name')
                    ->placeholder('Primero selecciona una ciudad')
                    ->options(function ($get) {
                        $cityId = $get('city_id');
                        if (!$cityId) {
                            return \App\Models\Sector::pluck('name', 'id');
                        }
                        return \App\Models\Sector::where('city_id', $cityId)
                            ->orWhereNull('city_id')
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),
                Select::make('property_type_id')
                    ->label('Tipo de inmueble')
                    ->relationship('propertyType', 'name')
                    ->required(),
                Select::make('operation_id')
                    ->label('Operación')
                    ->relationship('operation', 'name')
                    ->required(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft'     => 'Borrador',
                        'available' => 'Disponible',
                        'sold'      => 'Vendido',
                        'rented'    => 'Alquilado',
                    ])
                    ->required(),
                Toggle::make('is_featured')
                    ->label('Destacado'),
                DateTimePicker::make('published_at')
                    ->label('Fecha de publicación'),
                TextInput::make('latitude')
                    ->label('Latitud')
                    ->numeric()
                    ->step('0.0000001')
                    ->placeholder('-1.6708'),
                TextInput::make('longitude')
                    ->label('Longitud')
                    ->numeric()
                    ->step('0.0000001')
                    ->placeholder('-78.6483'),
            ]);
    }
}
