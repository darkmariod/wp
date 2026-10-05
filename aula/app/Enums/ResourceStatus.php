<?php

namespace App\Enums;

/**
 * Estado editorial de un recurso: solo "publicado" es visible para quien
 * consulta la Biblioteca.
 */
enum ResourceStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Published => 'Publicado',
            self::Archived => 'Archivado',
        };
    }

    /**
     * Nombre de color de Filament para las insignias del panel.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Archived => 'warning',
        };
    }
}
