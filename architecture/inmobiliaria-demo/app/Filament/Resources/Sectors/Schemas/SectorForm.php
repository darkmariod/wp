<?php

namespace App\Filament\Resources\Sectors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SectorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Nombre'),
                TextInput::make('slug')
                    ->required()
                    ->label('Identificador URL'),
                Select::make('city_id')
                    ->relationship('city', 'name')
                    ->label('Ciudad')
                    ->placeholder('Seleccionar ciudad'),
                Select::make('visibility')
                    ->options([
                        'visible' => 'Visible',
                        'hidden'  => 'Oculto',
                    ])
                    ->default('visible')
                    ->label('Visibilidad'),
            ]);
    }
}
