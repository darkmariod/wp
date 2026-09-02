<?php

namespace App\Filament\Resources\TrabajadorResource\Pages;

use App\Filament\Resources\TrabajadorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrabajador extends EditRecord
{
    protected static string $resource = TrabajadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
