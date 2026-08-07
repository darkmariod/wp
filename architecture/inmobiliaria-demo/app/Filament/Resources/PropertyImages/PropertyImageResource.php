<?php

namespace App\Filament\Resources\PropertyImages;

use App\Filament\Resources\PropertyImages\Pages\CreatePropertyImage;
use App\Filament\Resources\PropertyImages\Pages\EditPropertyImage;
use App\Filament\Resources\PropertyImages\Pages\ListPropertyImages;
use App\Filament\Resources\PropertyImages\Schemas\PropertyImageForm;
use App\Filament\Resources\PropertyImages\Tables\PropertyImagesTable;
use App\Models\PropertyImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PropertyImageResource extends Resource
{
    protected static ?string $model = PropertyImage::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return PropertyImageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyImagesTable::configure($table);
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
            'index' => ListPropertyImages::route('/'),
            'create' => CreatePropertyImage::route('/create'),
            'edit' => EditPropertyImage::route('/{record}/edit'),
        ];
    }
}
