<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Feriados oficiales del ciclo lectivo, para que los reportes de
 * asistencia no cuenten como falta un día en que el colegio
 * simplemente no tuvo clases.
 *
 * Fuente (Régimen Sierra-Amazonía 2026-2027): Ministerio de Educación
 * del Ecuador, vía El Mercurio
 * (https://elmercurio.com.ec/_educacion/2026/08/18/calendario-escolar-sierra-2026-2027/).
 * Cada ciclo lectivo nuevo necesita cargar sus propias fechas acá.
 */
class FeriadosEscolares
{
    /**
     * @return array<int, array<int, array{inicio: string, fin: string, nombre: string}>>
     */
    private static function rangosPorCiclo(): array
    {
        return [
            2026 => [
                ['inicio' => '2026-10-09', 'fin' => '2026-10-09', 'nombre' => 'Independencia de Guayaquil'],
                ['inicio' => '2026-11-02', 'fin' => '2026-11-03', 'nombre' => 'Día de Difuntos / Independencia de Cuenca'],
                ['inicio' => '2026-12-25', 'fin' => '2027-01-01', 'nombre' => 'Vacaciones de fin de año'],
                ['inicio' => '2027-02-08', 'fin' => '2027-02-09', 'nombre' => 'Carnaval'],
                ['inicio' => '2027-03-26', 'fin' => '2027-03-26', 'nombre' => 'Viernes Santo'],
                ['inicio' => '2027-04-30', 'fin' => '2027-04-30', 'nombre' => 'Día del Trabajo (trasladado)'],
                ['inicio' => '2027-05-24', 'fin' => '2027-05-24', 'nombre' => 'Batalla de Pichincha'],
            ],
        ];
    }

    /**
     * Nombre del feriado si la fecha cae dentro de uno, o null.
     */
    public static function nombre(CarbonInterface $fecha): ?string
    {
        $ciclo = CicloEscolar::deFecha($fecha);

        foreach (static::rangosPorCiclo()[$ciclo] ?? [] as $rango) {
            if ($fecha->between(CarbonImmutable::parse($rango['inicio']), CarbonImmutable::parse($rango['fin']))) {
                return $rango['nombre'];
            }
        }

        return null;
    }

    public static function esFeriado(CarbonInterface $fecha): bool
    {
        return static::nombre($fecha) !== null;
    }
}
