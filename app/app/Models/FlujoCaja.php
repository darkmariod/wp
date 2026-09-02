<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlujoCaja extends Model
{
    use HasFactory;

    protected $fillable = [
        'obra_id',
        'fecha',
        'tipo',
        'categoria',
        'monto',
        'referencia',
        'asiento_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'tipo' => 'string',
            'categoria' => 'string',
            'monto' => 'decimal:2',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(AsientoContable::class);
    }
}
