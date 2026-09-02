<?php

namespace App\Filament\Resources\AsistenciaObraResource\Pages;

use App\Filament\Resources\AsistenciaObraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsistenciaObra extends EditRecord
{
    protected static string $resource = AsistenciaObraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
