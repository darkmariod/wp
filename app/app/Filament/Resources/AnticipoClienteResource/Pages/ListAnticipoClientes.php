<?php

namespace App\Filament\Resources\AnticipoClienteResource\Pages;

use App\Filament\Resources\AnticipoClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnticipoClientes extends ListRecords
{
    protected static string $resource = AnticipoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
