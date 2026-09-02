<?php

namespace App\Filament\Resources\FlujoCajaResource\Pages;

use App\Filament\Resources\FlujoCajaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFlujoCaja extends ViewRecord
{
    protected static string $resource = FlujoCajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
