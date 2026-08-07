<?php

namespace App\Filament\Resources\Operations;

use App\Filament\Resources\Operations\Pages\CreateOperation;
use App\Filament\Resources\Operations\Pages\EditOperation;
use App\Filament\Resources\Operations\Pages\ListOperations;
use App\Filament\Resources\Operations\Schemas\OperationForm;
use App\Filament\Resources\Operations\Tables\OperationsTable;
use App\Models\Operation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OperationResource extends Resource
{
    protected static ?string $model = Operation::class;

    protected static ?string $pluralModelLabel = 'Operaciones';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Schema $schema): Schema
    {
        return OperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperationsTable::configure($table);
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
            'index' => ListOperations::route('/'),
            'create' => CreateOperation::route('/create'),
            'edit' => EditOperation::route('/{record}/edit'),
        ];
    }
}
