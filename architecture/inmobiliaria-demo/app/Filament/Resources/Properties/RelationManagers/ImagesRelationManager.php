<?php

namespace App\Filament\Resources\Properties\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->image()
                    ->label('Imagen')
                    ->directory('properties')
                    ->required(),
                TextInput::make('alt_text')
                    ->label('Texto alternativo'),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_main')
                    ->label('Principal'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->getStateUsing(fn ($record): string => $record->url),
                TextColumn::make('alt_text')
                    ->label('Texto alternativo'),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_main')
                    ->label('Principal')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordTitleAttribute('id');
    }
}
