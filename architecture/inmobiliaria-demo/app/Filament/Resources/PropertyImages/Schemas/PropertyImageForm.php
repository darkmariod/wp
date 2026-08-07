<?php

namespace App\Filament\Resources\PropertyImages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PropertyImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->label('Propiedad')
                    ->required(),
                FileUpload::make('image_path')
                    ->image()
                    ->label('Imagen')
                    ->required(),
                TextInput::make('alt_text')
                    ->label('Texto alternativo'),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_main')
                    ->label('Principal'),
            ]);
    }
}
