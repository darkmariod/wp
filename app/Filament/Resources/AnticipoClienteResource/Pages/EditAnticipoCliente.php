<?php

namespace App\Filament\Resources\AnticipoClienteResource\Pages;

use App\Filament\Resources\AnticipoClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnticipoCliente extends EditRecord
{
    protected static string $resource = AnticipoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
