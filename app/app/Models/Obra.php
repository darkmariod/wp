<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obra extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'cliente_id',
        'direccion',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'estado',
        'contrato_monto',
        'anticipo_porcentaje',
        'aiu_administracion',
        'aiu_imprevistos',
        'aiu_utilidad',
        'costo_fijo_mensual',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin_estimada' => 'date',
            'fecha_fin_real' => 'date',
            'contrato_monto' => 'decimal:2',
            'anticipo_porcentaje' => 'decimal:2',
            'aiu_administracion' => 'decimal:2',
            'aiu_imprevistos' => 'decimal:2',
            'aiu_utilidad' => 'decimal:2',
            'costo_fijo_mensual' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class);
    }

    public function asientos(): HasMany
    {
        return $this->hasMany(AsientoContable::class);
    }

    public function flujoCajas(): HasMany
    {
        return $this->hasMany(FlujoCaja::class);
    }

    public function anticipoClientes(): HasMany
    {
        return $this->hasMany(AnticipoCliente::class);
    }

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class);
    }

    public function cuentasPorPagar(): HasMany
    {
        return $this->hasMany(CuentaPorPagar::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaObra::class);
    }
}
