<?php

namespace App\Filament\Widgets;

use App\Models\FlujoCaja;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FlujoCajaChartWidget extends ChartWidget
{
    protected ?string $heading = 'Flujo de Caja - Últimos 12 Meses';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $hoy = Carbon::now();
        $labels = [];
        $ingresos = [];
        $egresos = [];

        for ($i = 11; $i >= 0; $i--) {
            $mes = $hoy->copy()->subMonths($i);
            $labels[] = $mes->format('M Y');

            $inicioMes = $mes->copy()->startOfMonth();
            $finMes = $mes->copy()->endOfMonth();

            $ingresos[] = (float) FlujoCaja::where('tipo', 'ingreso')
                ->whereBetween('fecha', [$inicioMes, $finMes])
                ->sum('monto');

            $egresos[] = (float) FlujoCaja::where('tipo', 'egreso')
                ->whereBetween('fecha', [$inicioMes, $finMes])
                ->sum('monto');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $ingresos,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Egresos',
                    'data' => $egresos,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
