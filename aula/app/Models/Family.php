<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone'])]
class Family extends Model
{
    use HasFactory;

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /**
     * Familias que ese usuario puede ver por su grado: el personal todas,
     * la docente solo las que tienen algún niño en sus ambientes.
     */
    public function scopeWithChildrenTaughtBy(Builder $query, ?User $user, bool $includeEmpty = false): Builder
    {
        if ($user?->isStaff()) {
            return $query;
        }

        return $query->where(function (Builder $familias) use ($user, $includeEmpty) {
            $familias->whereHas('children', fn (Builder $children) => $children->taughtBy($user));

            // Una familia sin ningún niño todavía no pertenece a un grado: la
            // docente debe poder anotar a su primer hijo (selector del formulario).
            if ($includeEmpty && $user?->isGuia()) {
                $familias->orDoesntHave('children');
            }
        });
    }
}
