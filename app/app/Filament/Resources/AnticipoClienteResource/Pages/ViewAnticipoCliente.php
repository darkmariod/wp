<?php

namespace App\Filament\Resources\AnticipoClienteResource\Pages;

use App\Filament\Resources\AnticipoClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnticipoCliente extends ViewRecord
{
    protected static string $resource = AnticipoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
