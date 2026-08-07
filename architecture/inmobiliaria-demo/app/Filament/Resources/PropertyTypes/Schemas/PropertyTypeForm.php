<?php

namespace App\Filament\Resources\PropertyTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class PropertyTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('slug')
                    ->label('Identificador URL')
                    ->required(),
                Select::make('visibility')
                    ->label('Visibilidad')
                    ->options([
                        'visible' => 'Visible',
                        'hidden'  => 'Oculto',
                    ])
                    ->required()
                    ->default('visible'),
            ]);
    }
}
