<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Child;
use Filament\Widgets\ChartWidget;

class AsistenciaDelMes extends ChartWidget
{
    protected ?string $heading = 'Asistencia del mes';

    protected function ninoIds()
    {
        $query = Child::query()->where('status', 'active');

        if (auth()->user()?->isGuia()) {
            $query->whereHas('environment', fn ($q) => $q->where('teacher_id', auth()->id()));
        }

        return $query->pluck('id');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $ninoIds = $this->ninoIds();

        $estados = [
            Attendance::STATUS_PRESENTE => 'Presente',
            Attendance::STATUS_ATRASO => 'Atraso',
            Attendance::STATUS_FALTA_JUSTIFICADA => 'Justificada',
            Attendance::STATUS_FALTA_INJUSTIFICADA => 'Injustificada',
        ];

        $conteos = Attendance::whereIn('child_id', $ninoIds)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'Registros',
                    'data' => collect($estados)->keys()->map(fn ($estado) => $conteos[$estado] ?? 0)->all(),
                    'backgroundColor' => ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
                ],
            ],
            'labels' => array_values($estados),
        ];
    }
}
