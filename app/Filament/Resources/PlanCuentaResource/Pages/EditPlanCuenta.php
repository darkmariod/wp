<?php

namespace App\Filament\Resources\PlanCuentaResource\Pages;

use App\Filament\Resources\PlanCuentaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanCuenta extends EditRecord
{
    protected static string $resource = PlanCuentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
