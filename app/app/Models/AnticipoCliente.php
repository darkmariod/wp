<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnticipoCliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'obra_id',
        'monto_total',
        'porcentaje',
        'estado',
        'fecha_concesion',
    ];

    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'porcentaje' => 'decimal:2',
            'fecha_concesion' => 'date',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function amortizaciones(): HasMany
    {
        return $this->hasMany(AmortizacionAnticipo::class, 'anticipo_id');
    }
}
