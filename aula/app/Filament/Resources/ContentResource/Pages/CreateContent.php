<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    /**
     * Validación de servidor: no alcanza con ocultar opciones en el form.
     * Un ambiente "razonadoras" solo admite lecturas y tareas.
     *
     * El slug ya no lo tipea la guía (confundía más de lo que ayudaba):
     * se genera acá del título, con un sufijo corto para no chocar si dos
     * contenidos arrancan con el mismo nombre.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['type']) && ! ContentResource::esTipoPermitidoEnAmbiente($data['environment_id'] ?? null, $data['type'])) {
            throw ValidationException::withMessages([
                'type' => 'En este ambiente (razonadoras) solo se permiten lecturas y tareas.',
            ]);
        }

        $data['slug'] = Str::slug($data['title'] ?? '').'-'.Str::lower(Str::random(6));

        return $data;
    }
}
