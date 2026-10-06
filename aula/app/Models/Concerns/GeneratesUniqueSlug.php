<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Genera el slug al crear el registro y lo deja fijo después: cambiar el
 * título no debe romper los enlaces ya compartidos.
 *
 * @mixin Model
 */
trait GeneratesUniqueSlug
{
    /**
     * Columna de la que sale el slug (title, name...).
     */
    abstract protected function slugSourceColumn(): string;

    /**
     * Slug base cuando el texto de origen no deja ninguna letra o número.
     */
    abstract protected function slugFallback(): string;

    public static function bootGeneratesUniqueSlug(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('slug'))) {
                $model->setAttribute('slug', $model->generateUniqueSlug());
            }
        });
    }

    protected function generateUniqueSlug(): string
    {
        // Se corta antes de slugificar: deja margen en la columna para el sufijo.
        $source = mb_substr((string) $this->getAttribute($this->slugSourceColumn()), 0, 150);
        $base = Str::slug($source) ?: $this->slugFallback();

        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
