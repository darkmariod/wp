<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsientoContable extends Model
{
    use HasFactory;

    protected $table = 'asiento_contables';

    protected $fillable = [
        'numero_asiento',
        'fecha',
        'descripcion',
        'obra_id',
        'tipo',
        'estado',
        'usuario_creacion',
        'usuario_aprobacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creacion');
    }

    public function usuarioAprobacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_aprobacion');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAsiento::class, 'asiento_id');
    }
}
