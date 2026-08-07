<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PropertyImage extends Model
{
    protected $fillable = ['property_id', 'image_path', 'alt_text', 'sort_order', 'is_main'];

    protected function casts(): array
    {
        return [
            'is_main'    => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Devuelve la URL completa de la imagen.
     * Soporta tanto rutas locales (storage/) como URLs externas.
     */
    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }
}
