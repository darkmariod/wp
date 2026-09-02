<?php

namespace App\Filament\Widgets;

use App\Models\FlujoCaja;
use App\Models\Obra;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ResumenFinancieroWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $hoy = Carbon::now();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $obrasActivas = Obra::whereIn('estado', ['planificada', 'en_curse'])->count();

        $ingresosMes = (float) FlujoCaja::where('tipo', 'ingreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $egresosMes = (float) FlujoCaja::where('tipo', 'egreso')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        $resultadoNeto = bcsub(
            number_format($ingresosMes, 2, '.', ''),
            number_format($egresosMes, 2, '.', ''),
            2,
        );

        return [
            Stat::make('Obras Activas', $obrasActivas)
                ->description('Proyectos en ejecución')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('info'),

            Stat::make('Ingresos del Mes', '$' . number_format($ingresosMes, 2))
                ->description($inicioMes->format('M Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Egresos del Mes', '$' . number_format($egresosMes, 2))
                ->description($inicioMes->format('M Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('Resultado Neto', '$' . number_format((float) $resultadoNeto, 2))
                ->description('Ingresos - Egresos')
                ->descriptionIcon('heroicon-o-calculator')
                ->color((float) $resultadoNeto >= 0 ? 'success' : 'danger'),
        ];
    }
}
