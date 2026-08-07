<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sector extends Model
{
    protected $fillable = ['name', 'slug', 'visibility', 'city_id'];

    protected function casts(): array
    {
        return ['visibility' => 'string'];
    }

    protected static function booted(): void
    {
        static::creating(fn (Sector $sector) => $sector->slug ??= Str::slug($sector->name));
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function properties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Property::class);
    }
}
