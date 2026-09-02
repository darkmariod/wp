<?php

namespace App\Filament\Resources\CuentaPorPagarResource\Pages;

use App\Filament\Resources\CuentaPorPagarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentaPorPagars extends ListRecords
{
    protected static string $resource = CuentaPorPagarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
