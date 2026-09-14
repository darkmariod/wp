<?php

namespace App\Support;

use App\Models\Child;
use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;

class CicloEscolar
{
    /**
     * Inicio del ciclo: 1 de septiembre del anio dado.
     */
    public static function inicio(int $anio): CarbonImmutable
    {
        return CarbonImmutable::createFromDate($anio, 9, 1);
    }

    /**
     * Fin del ciclo: 31 de julio del anio siguiente.
     */
    public static function fin(int $anio): CarbonImmutable
    {
        return CarbonImmutable::createFromDate($anio + 1, 7, 31);
    }

    /**
     * Anio del ciclo al que pertenece una fecha.
     * Mes >= 9 (septiembre) => anio de la fecha, sino => anio - 1.
     */
    public static function deFecha(CarbonInterface $fecha): int
    {
        return $fecha->month >= 9 ? $fecha->year : $fecha->year - 1;
    }

    /**
     * Etiqueta legible del ciclo: "2026-2027".
     */
    public static function etiqueta(int $anio): string
    {
        return $anio . '-' . ($anio + 1);
    }

    /**
     * Anio del ciclo vigente (el que contiene a now()).
     */
    public static function vigente(): int
    {
        return static::deFecha(now());
    }

    /**
     * Un ciclo esta cerrado si now() ya paso su fecha de fin.
     */
    public static function cerrado(int $anio): bool
    {
        return now()->greaterThan(static::fin($anio));
    }

    /**
     * Anio del ultimo ciclo cerrado. Si el vigente ya cerro, es ese.
     * Si no, el anterior. Null si el resultado es anterior a 2025
     * (para no mostrar anual vacio prehistorico).
     */
    public static function ultimoCerrado(): ?int
    {
        $vigente = static::vigente();

        if (static::cerrado($vigente)) {
            return $vigente;
        }

        $anterior = $vigente - 1;

        return $anterior >= 2025 ? $anterior : null;
    }

    /**
     * Resumen de asistencia de un nino en un mes calendario determinado.
     * Retorna conteos por estado (base 0).
     */
    public static function resumenMes(CarbonInterface $mes, Child $child): array
    {
        $inicio = CarbonImmutable::createFromDate($mes->year, $mes->month, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        return static::contarEstados(
            $child->attendances()->whereBetween('date', [$inicio, $fin])->get()
        );
    }

    /**
     * Resumen de asistencia de un nino durante todo un ciclo lectivo.
     * Retorna conteos por estado (base 0).
     */
    public static function resumenCiclo(int $anio, Child $child): array
    {
        $inicio = static::inicio($anio);
        $fin = static::fin($anio);

        return static::contarEstados(
            $child->attendances()->whereBetween('date', [$inicio, $fin])->get()
        );
    }

    /**
     * Cuenta las ocurrencias de cada estado en una coleccion de attendances.
     */
    private static function contarEstados($attendances): array
    {
        $counts = [
            \App\Models\Attendance::STATUS_PRESENTE => 0,
            \App\Models\Attendance::STATUS_ATRASO => 0,
            \App\Models\Attendance::STATUS_FALTA_JUSTIFICADA => 0,
            \App\Models\Attendance::STATUS_FALTA_INJUSTIFICADA => 0,
        ];

        foreach ($attendances as $a) {
            if (array_key_exists($a->status, $counts)) {
                $counts[$a->status]++;
            }
        }

        return $counts;
    }
}
