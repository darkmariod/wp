<?php

namespace App\Enums;

/**
 * Tipo de recurso de la Biblioteca. Define si el recurso se apoya en un
 * archivo subido, en una URL externa o en ambos.
 */
enum ResourceType: string
{
    case Pdf = 'pdf';
    case Document = 'document';
    case Presentation = 'presentation';
    case Video = 'video';
    case Audio = 'audio';
    case Image = 'image';
    case Link = 'link';
    case File = 'file';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => 'PDF',
            self::Document => 'Documento',
            self::Presentation => 'Presentación',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Image => 'Imagen',
            self::Link => 'Enlace',
            self::File => 'Archivo',
        };
    }

    /**
     * Clave corta para elegir el ícono SVG del tipo en las vistas.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Pdf => 'pdf',
            self::Document => 'document',
            self::Presentation => 'presentation',
            self::Video => 'video',
            self::Audio => 'audio',
            self::Image => 'image',
            self::Link => 'link',
            self::File => 'file',
        };
    }

    /**
     * Admite un archivo subido (el video puede alojarse acá o enlazarse).
     */
    public function usesFile(): bool
    {
        return $this !== self::Link;
    }

    /**
     * Admite una URL externa (enlace, o video alojado en otro sitio).
     */
    public function usesExternalUrl(): bool
    {
        return $this === self::Link || $this === self::Video;
    }

    /**
     * El archivo es obligatorio. En el video no: puede venir solo por URL.
     */
    public function requiresFile(): bool
    {
        return $this->usesFile() && $this !== self::Video;
    }

    public function requiresExternalUrl(): bool
    {
        return $this === self::Link;
    }

    /**
     * Se puede mostrar dentro del visor sin descargarlo.
     */
    public function isViewableInline(): bool
    {
        return in_array($this, [self::Pdf, self::Video, self::Audio, self::Image], true);
    }
}
