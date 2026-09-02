<?php

namespace App\Exceptions;

use InvalidArgumentException;

class LineaInvalidaException extends InvalidArgumentException
{
    public static function ambasCuentas(int $indice): static
    {
        return new static(
            sprintf('Línea #%d: no puede tener DEBE y HABER ambos mayores a cero.', $indice + 1),
        );
    }

    public static function sinMonto(int $indice): static
    {
        return new static(
            sprintf('Línea #%d: debe tener al menos DEBE o HABER mayores a cero.', $indice + 1),
        );
    }
}
