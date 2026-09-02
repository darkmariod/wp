<?php

namespace App\Services\Reportes;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ReporteExportService
{
    public function exportarPDF(string $vista, array $datos, string $titulo): Response
    {
        $html = view("reportes.{$vista}", array_merge($datos, [
            'titulo' => $titulo,
            'generado_en' => now()->format('d/m/Y H:i:s'),
        ]))->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $this->slugificar($titulo) . '.pdf"');
    }

    public function exportarExcel(string $vista, array $datos, string $titulo): Response
    {
        $filas = $this->extraerFilasPlanas($datos);

        $csv = $this->generarCSV($filas, $titulo);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $this->slugificar($titulo) . '.csv"');
    }

    private function generarCSV(array $filas, string $titulo): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($filas as $fila) {
            fputcsv($handle, $fila, ';');
        }

        rewind($handle);
        $contenido = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . $contenido;
    }

    private function extraerFilasPlanas(array $datos): array
    {
        $filas = [];

        if (isset($datos['periodo'])) {
            $filas[] = ['Período', $datos['periodo']['inicio'] ?? '', $datos['periodo']['fin'] ?? ''];
            $filas[] = [];
        }

        if (isset($datos['obra'])) {
            $filas[] = ['Obra', $datos['obra']['codigo'] ?? '', $datos['obra']['nombre'] ?? ''];
            $filas[] = [];
        }

        if (isset($datos['grupos']) && is_array($datos['grupos'])) {
            foreach ($datos['grupos'] as $grupo => $info) {
                if (is_array($info) && isset($info['items'])) {
                    $filas[] = [ucwords(str_replace('_', ' ', $grupo))];
                    $filas[] = ['Código', 'Nombre', 'Saldo'];

                    foreach ($info['items'] as $item) {
                        $filas[] = [
                            $item['codigo'] ?? '',
                            $item['nombre'] ?? '',
                            number_format($item['saldo'] ?? 0, 2, '.', ''),
                        ];
                    }

                    $filas[] = ['Total', '', number_format($info['total'] ?? 0, 2, '.', '')];
                    $filas[] = [];
                }
            }
        }

        if (isset($datos['items']) && is_array($datos['items'])) {
            foreach ($datos['items'] as $item) {
                $filas[] = array_values($item);
            }
            $filas[] = [];
        }

        if (isset($datos['totales'])) {
            $filas[] = ['TOTALES'];
            foreach ($datos['totales'] as $clave => $valor) {
                $filas[] = [ucwords(str_replace('_', ' ', $clave)), $valor];
            }
        }

        if (isset($datos['calculado'])) {
            $filas[] = [];
            $filas[] = ['RESUMEN'];
            foreach ($datos['calculado'] as $clave => $info) {
                $filas[] = [
                    ucwords(str_replace('_', ' ', $clave)),
                    number_format($info['monto'] ?? 0, 2, '.', ''),
                    ($info['porcentaje'] ?? 0) . '%',
                ];
            }
        }

        if (isset($datos['movimientos']) && is_array($datos['movimientos'])) {
            $filas[] = ['Fecha', 'N° Asiento', 'Referencia', 'Debe', 'Haber', 'Saldo'];
            foreach ($datos['movimientos'] as $mov) {
                $filas[] = [
                    $mov['fecha'] ?? '',
                    $mov['numero_asiento'] ?? '',
                    $mov['referencia'] ?? '',
                    number_format($mov['debe'] ?? 0, 2, '.', ''),
                    number_format($mov['haber'] ?? 0, 2, '.', ''),
                    number_format($mov['saldo'] ?? 0, 2, '.', ''),
                ];
            }
        }

        return $filas;
    }

    private function slugificar(string $texto): string
    {
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
        $texto = trim($texto, '_');

        return $texto;
    }
}
