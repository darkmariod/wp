<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleAPU extends Model
{
    use HasFactory;

    protected $table = 'detalle_apus';

    protected $fillable = [
        'presupuesto_id',
        'tipo',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'costo_unitario',
        'costo_total',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => 'string',
            'cantidad' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'costo_total' => 'decimal:2',
        ];
    }

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
