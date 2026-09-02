<?php

namespace App\Filament\Resources\PlanCuentaResource\Pages;

use App\Filament\Resources\PlanCuentaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPlanCuenta extends ViewRecord
{
    protected static string $resource = PlanCuentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
