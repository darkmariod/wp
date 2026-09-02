<?php

namespace App\Filament\Resources\AsientoContableResource\Pages;

use App\Filament\Resources\AsientoContableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsientoContable extends EditRecord
{
    protected static string $resource = AsientoContableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
