<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Support\ReporteAsistenciaMensual;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Recibe el reporte ya generado (semana, mes o año) en vez de calcularlo
 * él mismo: así no le importa qué período fue, solo lo vuelca a filas.
 */
class AsistenciaMensualExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param  array{dias: array, resumen: array<string, int>}  $reporte
     */
    public function __construct(
        private readonly array $reporte,
        private readonly string $titulo,
    ) {}

    public function headings(): array
    {
        return ['Fecha', 'Día', 'Estado'];
    }

    public function array(): array
    {
        $reporte = $this->reporte;

        $filas = collect($reporte['dias'])
            ->map(fn (array $dia) => [
                $dia['fecha']->format('d/m/Y'),
                ucfirst($dia['fecha']->locale('es')->isoFormat('dddd')),
                $this->etiqueta($dia),
            ])
            ->all();

        $filas[] = ['', '', ''];
        $filas[] = ['Resumen', '', ''];
        $filas[] = ['Presentes', $reporte['resumen'][Attendance::STATUS_PRESENTE], ''];
        $filas[] = ['Atrasos', $reporte['resumen'][Attendance::STATUS_ATRASO], ''];
        $filas[] = ['Faltas justificadas', $reporte['resumen'][Attendance::STATUS_FALTA_JUSTIFICADA], ''];
        $filas[] = ['Faltas injustificadas', $reporte['resumen'][Attendance::STATUS_FALTA_INJUSTIFICADA], ''];
        $filas[] = ['Feriados', $reporte['resumen'][ReporteAsistenciaMensual::FERIADO], ''];
        $filas[] = ['Sin registro', $reporte['resumen'][ReporteAsistenciaMensual::SIN_REGISTRO], ''];

        return $filas;
    }

    public function title(): string
    {
        return $this->titulo;
    }

    private function etiqueta(array $dia): string
    {
        if ($dia['feriado']) {
            return 'Feriado: '.$dia['feriado'];
        }

        if ($dia['finDeSemana']) {
            return 'Fin de semana';
        }

        return match ($dia['status']) {
            Attendance::STATUS_PRESENTE => 'Presente',
            Attendance::STATUS_ATRASO => 'Atraso',
            Attendance::STATUS_FALTA_JUSTIFICADA => 'Falta justificada',
            Attendance::STATUS_FALTA_INJUSTIFICADA => 'Falta injustificada',
            default => 'Sin registro',
        };
    }
}
