<?php

namespace App\Exceptions;

use InvalidArgumentException;

class PartidaDobleException extends InvalidArgumentException
{
    public static function desbalance(float $totalDebe, float $totalHaber): static
    {
        return new static(
            sprintf(
                'Desbalance en partida doble: DEBE (%s) != HABER (%s)',
                number_format($totalDebe, 2, '.', ''),
                number_format($totalHaber, 2, '.', ''),
            ),
        );
    }
}
