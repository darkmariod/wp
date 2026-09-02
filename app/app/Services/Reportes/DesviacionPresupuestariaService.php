<?php

namespace App\Services\Reportes;

use App\Models\DetalleAPU;
use App\Models\Obra;
use App\Models\Presupuesto;
use App\Services\DesviacionService;

class DesviacionPresupuestariaService
{
    public function generar(int $obraId): array
    {
        $obra = Obra::findOrFail($obraId);

        $presupuestos = Presupuesto::where('obra_id', $obraId)
            ->with('detalleAPUs')
            ->get();

        $desviacionService = app(DesviacionService::class);

        $items = [];
        $totalPresupuestado = '0';
        $totalEjecutado = '0';

        foreach ($presupuestos as $presupuesto) {
            $costoPresupuestado = (string) $presupuesto->subtotal_costo;

            $costoEjecutado = (string) $presupuesto->detalleAPUs->sum('costo_total');

            $desviacion = $desviacionService->calcularDesviacion(
                (float) $costoPresupuestado,
                (float) $costoEjecutado,
            );

            $totalPresupuestado = bcadd($totalPresupuestado, $costoPresupuestado, 2);
            $totalEjecutado = bcadd($totalEjecutado, $costoEjecutado, 2);

            $items[] = [
                'presupuesto_id' => $presupuesto->id,
                'codigo' => $presupuesto->codigo,
                'descripcion' => $presupuesto->descripcion,
                'unidad_medida' => $presupuesto->unidad_medida,
                'cantidad_presupuestada' => (float) $presupuesto->cantidad,
                'costo_unitario_presupuestado' => (float) $presupuesto->costo_unitario,
                'subtotal_presupuestado' => (float) $costoPresupuestado,
                'subtotal_ejecutado' => (float) $costoEjecutado,
                'desviacion' => $desviacion['desviacion'],
                'porcentaje' => $desviacion['porcentaje'],
                'clasificacion' => $desviacion['tipo'],
            ];
        }

        $desviacionTotal = $desviacionService->calcularDesviacion(
            (float) $totalPresupuestado,
            (float) $totalEjecutado,
        );

        return [
            'obra' => [
                'id' => $obra->id,
                'codigo' => $obra->codigo,
                'nombre' => $obra->nombre,
            ],
            'items' => $items,
            'totales' => [
                'presupuestado' => (float) $totalPresupuestado,
                'ejecutado' => (float) $totalEjecutado,
                'desviacion' => $desviacionTotal['desviacion'],
                'porcentaje' => $desviacionTotal['porcentaje'],
                'clasificacion' => $desviacionTotal['tipo'],
            ],
        ];
    }
}
