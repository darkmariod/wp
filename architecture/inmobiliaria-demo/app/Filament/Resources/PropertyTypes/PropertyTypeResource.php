<?php

namespace App\Filament\Resources\PropertyTypes;

use App\Filament\Resources\PropertyTypes\Pages\CreatePropertyType;
use App\Filament\Resources\PropertyTypes\Pages\EditPropertyType;
use App\Filament\Resources\PropertyTypes\Pages\ListPropertyTypes;
use App\Filament\Resources\PropertyTypes\Schemas\PropertyTypeForm;
use App\Filament\Resources\PropertyTypes\Tables\PropertyTypesTable;
use App\Models\PropertyType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PropertyTypeResource extends Resource
{
    protected static ?string $model = PropertyType::class;

    protected static ?string $pluralModelLabel = 'Tipos de inmueble';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return PropertyTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyTypes::route('/'),
            'create' => CreatePropertyType::route('/create'),
            'edit' => EditPropertyType::route('/{record}/edit'),
        ];
    }
}
