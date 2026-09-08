<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    /**
     * Validación de servidor: no alcanza con ocultar opciones en el form.
     * Un ambiente "razonadoras" solo admite lecturas y tareas.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['type']) && ! ContentResource::esTipoPermitidoEnAmbiente($data['environment_id'] ?? null, $data['type'])) {
            throw ValidationException::withMessages([
                'type' => 'En este ambiente (razonadoras) solo se permiten lecturas y tareas.',
            ]);
        }

        return $data;
    }
}
