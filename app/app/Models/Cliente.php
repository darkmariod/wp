<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'razon_social',
        'ruc',
        'tipo',
        'email',
        'telefono',
        'direccion',
        'representa_legal',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => 'string',
        ];
    }

    public function obras(): HasMany
    {
        return $this->hasMany(Obra::class);
    }
}
