<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanCuenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'grupo',
        'tipo',
        'es_auxiliar',
        'padre_id',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'es_auxiliar' => 'boolean',
            'activa' => 'boolean',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(self::class, 'padre_id');
    }

    public function detalleAsientos(): HasMany
    {
        return $this->hasMany(DetalleAsiento::class, 'cuenta_id');
    }
}
