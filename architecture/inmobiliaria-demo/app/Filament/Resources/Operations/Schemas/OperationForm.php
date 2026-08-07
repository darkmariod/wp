<?php

namespace App\Filament\Resources\Operations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OperationForm
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
