<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presupuesto extends Model
{
    use HasFactory;

    protected $table = 'presupuestos';

    protected $fillable = [
        'obra_id',
        'codigo',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'costo_unitario',
        'precio_venta_unitario',
        'subtotal_costo',
        'subtotal_venta',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'precio_venta_unitario' => 'decimal:2',
            'subtotal_costo' => 'decimal:2',
            'subtotal_venta' => 'decimal:2',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function detalleAPUs(): HasMany
    {
        return $this->hasMany(DetalleAPU::class);
    }
}
