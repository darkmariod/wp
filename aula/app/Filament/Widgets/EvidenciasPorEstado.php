<?php

namespace App\Filament\Widgets;

use App\Models\Child;
use App\Models\Evidence;
use Filament\Widgets\ChartWidget;

class EvidenciasPorEstado extends ChartWidget
{
    protected ?string $heading = 'Evidencias por estado';

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
        return 'doughnut';
    }

    protected function getData(): array
    {
        $ninoIds = $this->ninoIds();

        $estados = [
            Evidence::STATUS_PENDING => 'Pendiente',
            Evidence::STATUS_SUBMITTED => 'Enviada',
            Evidence::STATUS_VIEWED => 'Vista',
            Evidence::STATUS_RESPONDED => 'Respondida',
        ];

        $conteos = Evidence::whereIn('child_id', $ninoIds)
            ->whereIn('status', array_keys($estados))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'Evidencias',
                    'data' => collect($estados)->keys()->map(fn ($estado) => $conteos[$estado] ?? 0)->all(),
                    'backgroundColor' => ['#f59e0b', '#3b82f6', '#a855f7', '#10b981'],
                ],
            ],
            'labels' => array_values($estados),
        ];
    }
}
