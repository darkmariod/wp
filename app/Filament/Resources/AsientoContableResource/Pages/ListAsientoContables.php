<?php

namespace App\Filament\Resources\AsientoContableResource\Pages;

use App\Filament\Resources\AsientoContableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsientoContables extends ListRecords
{
    protected static string $resource = AsientoContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
