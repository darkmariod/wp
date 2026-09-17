<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Child;
use App\Support\ReporteAsistenciaMensual;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AsistenciaMensualExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private readonly Child $child,
        private readonly CarbonImmutable $mes,
    ) {}

    public function headings(): array
    {
        return ['Fecha', 'Día', 'Estado'];
    }

    public function array(): array
    {
        $reporte = ReporteAsistenciaMensual::generar($this->child, $this->mes);

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
        return ucfirst($this->mes->locale('es')->isoFormat('MMMM YYYY'));
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
