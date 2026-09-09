<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['family_id', 'environment_id', 'name', 'photo_path', 'birth_date', 'status'])]
class Child extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    public function avatarUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        // Ruta relativa a la raíz: Storage::url() devuelve una URL absoluta
        // armada con APP_URL, así que la imagen se rompe apenas la app se
        // sirve en otro host o puerto del configurado.
        return parse_url(Storage::disk('public')->url($this->photo_path), PHP_URL_PATH);
    }

    public function avatarInitials(): string
    {
        $partes = preg_split('/\s+/', trim($this->name)) ?: [];

        if (count($partes) === 1) {
            return mb_strtoupper(mb_substr($partes[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($partes[0], 0, 1).mb_substr(end($partes), 0, 1));
    }

    public function avatarColorClass(): string
    {
        $paleta = [
            'bg-green-700',
            'bg-sky-700',
            'bg-amber-600',
            'bg-rose-600',
            'bg-indigo-600',
            'bg-teal-600',
            'bg-fuchsia-600',
            'bg-lime-700',
        ];

        return $paleta[abs(crc32($this->name)) % count($paleta)];
    }
}
