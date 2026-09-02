<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trabajador extends Model
{
    use HasFactory;

    protected $table = 'trabajadores';

    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'cargo',
        'sueldo_base',
        'tipo_contrato',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'sueldo_base' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaObra::class);
    }
}
