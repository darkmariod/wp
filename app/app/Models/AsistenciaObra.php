<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaObra extends Model
{
    use HasFactory;

    protected $fillable = [
        'obra_id',
        'trabajador_id',
        'fecha',
        'horas_trabajadas',
        'hora_entrada',
        'hora_salida',
        'tipo_jornada',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'horas_trabajadas' => 'decimal:2',
            'hora_entrada' => 'time',
            'hora_salida' => 'time',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }
}
