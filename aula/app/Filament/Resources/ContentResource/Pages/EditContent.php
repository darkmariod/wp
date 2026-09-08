<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Validación de servidor: no alcanza con ocultar opciones en el form.
     * Un ambiente "razonadoras" solo admite lecturas y tareas.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['type']) && ! ContentResource::esTipoPermitidoEnAmbiente($data['environment_id'] ?? null, $data['type'])) {
            throw ValidationException::withMessages([
                'type' => 'En este ambiente (razonadoras) solo se permiten lecturas y tareas.',
            ]);
        }

        return $data;
    }
}
