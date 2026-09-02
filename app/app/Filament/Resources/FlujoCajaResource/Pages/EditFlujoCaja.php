<?php

namespace App\Filament\Resources\FlujoCajaResource\Pages;

use App\Filament\Resources\FlujoCajaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlujoCaja extends EditRecord
{
    protected static string $resource = FlujoCajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
