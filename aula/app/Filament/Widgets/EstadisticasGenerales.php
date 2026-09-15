<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Evidence;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstadisticasGenerales extends StatsOverviewWidget
{
    protected function ninosVisibles()
    {
        $query = Child::query()->where('status', 'active');

        if (auth()->user()?->isGuia()) {
            $query->whereHas('environment', fn ($q) => $q->where('teacher_id', auth()->id()));
        }

        return $query;
    }

    protected function getStats(): array
    {
        $ninoIds = $this->ninosVisibles()->pluck('id');

        return [
            Stat::make('Niños activos', $ninoIds->count())
                ->icon('heroicon-o-face-smile')
                ->color('success'),

            Stat::make(
                'Asistencias hoy',
                Attendance::whereIn('child_id', $ninoIds)->whereDate('date', now())->count()
            )
                ->icon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make(
                'Evidencias por revisar',
                Evidence::whereIn('child_id', $ninoIds)
                    ->whereIn('status', [Evidence::STATUS_SUBMITTED, Evidence::STATUS_PENDING])
                    ->count()
            )
                ->icon('heroicon-o-inbox')
                ->color('warning'),

            Stat::make(
                'Evidencias respondidas (mes)',
                Evidence::whereIn('child_id', $ninoIds)
                    ->where('status', Evidence::STATUS_RESPONDED)
                    ->whereMonth('reviewed_at', now()->month)
                    ->whereYear('reviewed_at', now()->year)
                    ->count()
            )
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
