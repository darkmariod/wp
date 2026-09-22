<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Child;
use Carbon\CarbonImmutable;

/**
 * Arma el detalle día a día de la asistencia de un niño en un mes,
 * para el reporte mensual que ve el personal del colegio (nunca las
 * familias): un feriado se marca como tal y no como falta, y un día
 * de colegio sin registro queda como "sin registro", no como falta
 * injustificada — eso lo decide quien lee el reporte, no el sistema.
 */
class ReporteAsistenciaMensual
{
    public const SIN_REGISTRO = 'sin_registro';

    public const FERIADO = 'feriado';

    /**
     * @return array{
     *     dias: array<int, array{fecha: CarbonImmutable, status: ?string, feriado: ?string, finDeSemana: bool}>,
     *     resumen: array<string, int>,
     * }
     */
    public static function generar(Child $child, CarbonImmutable $mes): array
    {
        return self::generarRango($child, $mes->startOfMonth(), $mes->copy()->endOfMonth());
    }

    /**
     * Igual que generar(), pero para la semana (lun-dom) que contiene
     * la fecha dada, en vez de un mes calendario completo.
     *
     * @return array{
     *     dias: array<int, array{fecha: CarbonImmutable, status: ?string, feriado: ?string, finDeSemana: bool}>,
     *     resumen: array<string, int>,
     * }
     */
    public static function generarSemana(Child $child, CarbonImmutable $semana): array
    {
        $inicio = $semana->startOfWeek(CarbonImmutable::MONDAY);
        $fin = $inicio->copy()->endOfWeek(CarbonImmutable::SUNDAY);

        return self::generarRango($child, $inicio, $fin);
    }

    /**
     * Igual que generar(), pero para el ciclo lectivo completo (1 de
     * septiembre al 31 de julio siguiente) en vez de un mes.
     *
     * @return array{
     *     dias: array<int, array{fecha: CarbonImmutable, status: ?string, feriado: ?string, finDeSemana: bool}>,
     *     resumen: array<string, int>,
     * }
     */
    public static function generarAnual(Child $child, int $anioCiclo): array
    {
        return self::generarRango($child, CicloEscolar::inicio($anioCiclo), CicloEscolar::fin($anioCiclo));
    }

    /**
     * @return array{
     *     dias: array<int, array{fecha: CarbonImmutable, status: ?string, feriado: ?string, finDeSemana: bool}>,
     *     resumen: array<string, int>,
     * }
     */
    private static function generarRango(Child $child, CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        $registros = $child->attendances()
            ->whereBetween('date', [$inicio, $fin])
            ->get()
            ->keyBy(fn (Attendance $registro) => $registro->date->toDateString());

        $dias = [];
        $resumen = [
            Attendance::STATUS_PRESENTE => 0,
            Attendance::STATUS_ATRASO => 0,
            Attendance::STATUS_FALTA_JUSTIFICADA => 0,
            Attendance::STATUS_FALTA_INJUSTIFICADA => 0,
            self::FERIADO => 0,
            self::SIN_REGISTRO => 0,
        ];

        for ($dia = $inicio; $dia->lte($fin); $dia = $dia->addDay()) {
            $feriado = FeriadosEscolares::nombre($dia);
            $finDeSemana = $dia->isWeekend();
            $registro = $registros->get($dia->toDateString());

            $dias[] = [
                'fecha' => $dia,
                'status' => $registro?->status,
                'feriado' => $feriado,
                'finDeSemana' => $finDeSemana,
            ];

            if ($feriado !== null) {
                $resumen[self::FERIADO]++;
            } elseif ($registro !== null) {
                $resumen[$registro->status] = ($resumen[$registro->status] ?? 0) + 1;
            } elseif (! $finDeSemana) {
                $resumen[self::SIN_REGISTRO]++;
            }
        }

        return ['dias' => $dias, 'resumen' => $resumen];
    }
}
