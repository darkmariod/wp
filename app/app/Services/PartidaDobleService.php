<?php

namespace App\Services;

use App\Exceptions\LineaInvalidaException;
use App\Exceptions\PartidaDobleException;
use App\Models\AsientoContable;
use App\Models\DetalleAsiento;
use Illuminate\Support\Facades\DB;

class PartidaDobleService
{
    /**
     * Valida que la suma de DEBE sea igual a la suma de HABER.
     *
     * @param  array<int, array{cuenta_id: int, debe: string, haber: string, referencia?: string}>  $lines
     *
     * @throws PartidaDobleException si total_debe != total_haber
     */
    public function validateDebitCreditBalance(array $lines): void
    {
        $totalDebe = '0';
        $totalHaber = '0';

        foreach ($lines as $line) {
            $totalDebe = bcadd($totalDebe, (string) ($line['debe'] ?? 0), 4);
            $totalHaber = bcadd($totalHaber, (string) ($line['haber'] ?? 0), 4);
        }

        if (bccomp($totalDebe, $totalHaber, 4) !== 0) {
            throw PartidaDobleException::desbalance(
                (float) $totalDebe,
                (float) $totalHaber,
            );
        }
    }

    /**
     * Valida la integridad de cada línea contable:
     * - No puede tener DEBE > 0 Y HABER > 0 simultáneamente.
     * - Debe tener al menos DEBE > 0 O HABER > 0.
     *
     * @param  array<int, array{cuenta_id: int, debe: string, haber: string, referencia?: string}>  $lines
     *
     * @throws LineaInvalidaException si una línea incumple las reglas
     */
    public function validateLineIntegrity(array $lines): void
    {
        foreach ($lines as $indice => $line) {
            $debe = (string) ($line['debe'] ?? 0);
            $haber = (string) ($line['haber'] ?? 0);

            $debeEsPositivo = bccomp($debe, '0', 4) > 0;
            $haberEsPositivo = bccomp($haber, '0', 4) > 0;

            if ($debeEsPositivo && $haberEsPositivo) {
                throw LineaInvalidaException::ambasCuentas($indice);
            }

            if (! $debeEsPositivo && ! $haberEsPositivo) {
                throw LineaInvalidaException::sinMonto($indice);
            }
        }
    }

    /**
     * Valida integridad y balance, y crea el asiento contable en una transacción.
     *
     * @param  array{obra_id?: int, descripcion: string, fecha?: string, tipo?: string, usuario_creacion: int}  $data
     * @param  array<int, array{cuenta_id: int, debe: string, haber: string, referencia?: string}>  $lines
     */
    public function createAsiento(array $data, array $lines): AsientoContable
    {
        $this->validateLineIntegrity($lines);
        $this->validateDebitCreditBalance($lines);

        return DB::transaction(function () use ($data, $lines) {
            $asiento = AsientoContable::create([
                'numero_asiento' => $this->generarNumeroAsiento(),
                'fecha' => $data['fecha'] ?? now()->toDateString(),
                'descripcion' => $data['descripcion'],
                'obra_id' => $data['obra_id'] ?? null,
                'tipo' => $data['tipo'] ?? 'manual',
                'estado' => 'borrador',
                'usuario_creacion' => $data['usuario_creacion'],
            ]);

            foreach ($lines as $line) {
                $asiento->detalles()->create([
                    'cuenta_id' => $line['cuenta_id'],
                    'debe' => $line['debe'] ?? 0,
                    'haber' => $line['haber'] ?? 0,
                    'referencia' => $line['referencia'] ?? null,
                ]);
            }

            return $asiento->load('detalles');
        });
    }

    /**
     * Genera un número de asiento único basado en timestamp.
     */
    private function generarNumeroAsiento(): string
    {
        return 'ASI-' . now()->format('YmdHis');
    }
}
