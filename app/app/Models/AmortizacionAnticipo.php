<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmortizacionAnticipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'anticipo_id',
        'numero_amortizacion',
        'porcentaje_amortizar',
        'monto_amortizado',
        'avance_porcentaje',
        'fecha_amortizacion',
        'asiento_id',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_amortizar' => 'decimal:2',
            'monto_amortizado' => 'decimal:2',
            'avance_porcentaje' => 'decimal:2',
            'fecha_amortizacion' => 'date',
        ];
    }

    public function anticipo(): BelongsTo
    {
        return $this->belongsTo(AnticipoCliente::class, 'anticipo_id');
    }

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(AsientoContable::class);
    }
}
