<?php

namespace App\Filament\Resources\Sectors;

use App\Filament\Resources\Sectors\Pages\CreateSector;
use App\Filament\Resources\Sectors\Pages\EditSector;
use App\Filament\Resources\Sectors\Pages\ListSectors;
use App\Filament\Resources\Sectors\Schemas\SectorForm;
use App\Filament\Resources\Sectors\Tables\SectorsTable;
use App\Models\Sector;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SectorResource extends Resource
{
    protected static ?string $model = Sector::class;

    protected static ?string $pluralModelLabel = 'Zonas';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map';

    protected static UnitEnum|string|null $navigationGroup = 'Ubicaciones';

    public static function form(Schema $schema): Schema
    {
        return SectorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectorsTable::configure($table);
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
            'index' => ListSectors::route('/'),
            'create' => CreateSector::route('/create'),
            'edit' => EditSector::route('/{record}/edit'),
        ];
    }
}
